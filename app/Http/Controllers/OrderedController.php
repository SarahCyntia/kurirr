<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Input;
use App\Models\Riwayat;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderedController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {

        $query = Input::query();
        if ($request->has('exclude_status')) {
            $excluded = is_array($request->exclude_status)
                ? $request->exclude_status
                : [$request->exclude_status];

            $query->whereNotIn('status', $excluded);

            // if ($request->aksi === 'keluar') {
            //     $query->where('status', '!=', 'masuk gudang'); // atau sesuai kondisi
            // } elseif ($request->aksi === 'masuk') {
            //     $query->where('status', 'masuk gudang');
            // }

            $data = Input::with('riwayat')->get(); // atau Order::with('riwayat')->get();
            $per = $request->input('per', 10);
            $page = $request->input('page', 1);

            DB::statement('SET @no := ' . (($page - 1) * $per));

            $data = Input::when($request->search, function (Builder $query, string $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama_pengirim', 'like', "%$search%")
                        ->orWhere('alamat_pengirim', 'like', "%$search%")
                        ->orWhere('no_telp_pengirim', 'like', "%$search%")
                        ->orWhere('nama_penerima', 'like', "%$search%")
                        ->orWhere('alamat_penerima', 'like', "%$search%")
                        ->orWhere('no_telp_penerima', 'like', "%$search%")
                        ->orWhere('jenis_barang', 'like', "%$search%")
                        ->orWhere('jenis_layanan', 'like', "%$search%")
                        ->orWhere('berat_barang', 'like', "%$search%")
                        ->orWhere('riwayat_pengiriman', 'like', "%$search%");
                });
            })
                ->when(auth()->user()->role->name === 'kurir', function ($query) {
                    $kurirId = auth()->user()->kurir->kurir_id;

                    $query->where(function ($q) use ($kurirId) {
                        $q->whereNull('kurir_id') // Belum diambil siapa pun
                            ->orWhere(function ($subQuery) use ($kurirId) {
                                $subQuery->where('kurir_id', '!=', $kurirId) // Milik kurir lain
                                    ->where('status', '!=', 'keluar gudang'); // Dan belum keluar
                            })
                            ->orWhere(function ($subQuery) use ($kurirId) {
                                $subQuery->where('kurir_id', $kurirId)
                                    ->where('status', '!=', 'keluar gudang'); // Milik dia tapi belum keluar
                            });
                    });
                })

                 ->when($request->status_pembayaran, function ($query, $status_pembayaran) {
                // Jika parameter 'status_pembayaran' ada, filter berdasarkan status_pembayaran tersebut
                $query->where('status_pembayaran', $status_pembayaran);
            })
                // ->when(auth()->user()->role->name === 'kurir', function ($query) {
                //     $kurirId = auth()->user()->kurir->kurir_id;
                //     $query->where(function ($q) use ($kurirId) {
                //         $q->whereNull('kurir_id') // belum diambil
                //         ->orWhere('kurir_id', $kurirId); // paket milik dia sendiri
                //         Log::info('Kurir ID:', ['kurir_id' => auth()->user()->kurir->kurir_id]);
                //     });
                //     })
                ->when($request->has('exclude_status'), function ($query) use ($request) {
                    $excludedStatuses = (array) $request->exclude_status;

                    // Tetap tampilkan jika paket milik dia sendiri walau status-nya dikecualikan
                    $query->where(function ($q) use ($excludedStatuses) {
                        $q->whereNotIn('status', $excludedStatuses)
                            ->orWhere('kurir_id', auth()->user()->kurir->kurir_id); // tetap tampilkan milik sendiri
                    });
                });
                $statuses = $request->input('status', []);
                if (!empty($statuses)) {
                    $query->whereIn('status', $statuses);
                };

                // ->when($request->status, function ($query, $status) {
                //     $query->where('status', $status);
                // })
                // ->when($request->has('exclude_status'), function ($query) use ($request) {
                //     $query->where('status', '!=', $request->exclude_status);
                // })
                // ->latest()
                // ->paginate($per);

            // Tambah nomor urut
            $no = ($data->currentPage() - 1) * $per + 1;
            foreach ($data as $item) {
                $item->no = $no++;
            }
            // ->orderBy('created_at', 'desc')
            // ->paginate($per);

            return response()->json($data);
            // return datatables()->of($query)->make(true);
        }
    }
    public function claim($id)
    {
        $user = auth()->user();

        // Validasi: hanya role kurir yang boleh ambil paket
        if ($user->role->name !== 'kurir') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya kurir yang boleh mengambil paket.',
            ], 403);
        }

        $input = Input::findOrFail($id);
        $kurirId = $user->kurir->kurir_id;

        // Cek apakah sudah diambil kurir lain
        if ($input->kurir_id && $input->kurir_id !== $kurirId) {
            return response()->json([
                'success' => false,
                'message' => 'Paket sudah diambil oleh kurir lain.',
            ]);
        }

        // Jika belum diambil, set kurir_id
        if (!$input->kurir_id) {
            $input->kurir_id = $kurirId;
            $input->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Paket berhasil diambil oleh kurir ini.',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_pengirim' => 'required|string|max:255',
            'alamat_pengirim' => 'required|string|max:255',
            'no_telp_pengirim' => 'required|string|max:20',
            'nama_penerima' => 'required|string|max:255',
            'alamat_penerima' => 'required|string|max:255',
            'no_telp_penerima' => 'required|string|max:20',
            'jenis_barang' => 'required|string|max:255',
            'jenis_layanan' => 'required|string|max:255',
            'berat_barang' => 'required|numeric|min:1',
            'riwayat_pengiriman' => 'nullable|string|max:255',
        ]);

        $input = new Input($validated);
        $input->no_resi = 'RESI-' . now()->timestamp . '-' . rand(1000, 9999);
        $input->status = 'menunggu';
        $input->riwayat_pengiriman = '';
        $input->save();

        return response()->json([
            'message' => 'Input berhasil disimpan',
            'data' => $input,
        ]);
    }

    // public function show(Input $input)
    // {
    //     return response()->json($input);

    // }
    public function show($id)
    {
        $inputorder = Input::with(['riwayat' => fn($q) => $q->orderBy('created_at')])->findOrFail($id);

        return response()->json($inputorder);
        // $input = Input::with('riwayat')->findOrFail($id);
        // return response()->json($input);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string',
            'riwayat_pengiriman' => 'nullable|array',
            'riwayat_pengiriman.*' => 'string',
            'kurir_id'   => $kurir->id,
        ]);

        // $validated = $request->validate([
        //     'status' => 'required|string',
        //     'riwayat_pengiriman' => 'nullable|string', // hanya 1 pesan log baru
        // ]);

        $input = Input::findOrFail($id);

        // Ambil dan decode riwayat lama (jika ada)
        // $riwayat = json_decode($input->riwayat_pengiriman ?? '[]', true);
        $input = Input::find($id);
        $input->status = $request->status;
        // switch ($request->status) {
        //     case 'menunggu':
        //         $input->tanggal_menunggu = now();
        //         break;
        //     case 'dalam proses':
        //         $input->tanggal_dikemas = now();
        //         break;
        //     case 'pengambilan paket':
        //         $input->tanggal_pengambilan = now();
        //         break;
        //     case 'dikirim':
        //         $input->tanggal_dikirim = now();
        //         break;
        //     case 'selesai':
        //         $input->tanggal_penerimaan = now();
        //         break;
        // }
        $input = Input::findOrFail($id);

        // Update status (jika ada)
        if ($request->filled('status')) {
            $input->status = $request->status;
            $input->save();
        }

        // Tambahkan ke tabel riwayat
        if ($request->filled('riwayat_pengiriman')) {
            Riwayat::create([
                'inputorder_id' => $input->id,
                'keterangan' => $request->riwayat_pengiriman,
                'waktu' => now(), // atau $request->waktu jika pakai waktu manual
            ]);

            //  $waktuBaru = now()->format('d-m-Y H:i:s');
            // $statusString = $request->status . ' (' . $waktuBaru . ')';
            // switch ($request->status) {
            //     case 'dalam proses':
            //         $input->tanggal_dikemas = now();
            //         break;
            //     case 'dikirim':
            //         $input->tanggal_dikirim = now();
            //         break;
            //     case 'selesai':
            //         $input->tanggal_penerimaan = now();
            //         break;
            // }

            // Tambahkan pesan baru ke array
            if (!empty($validated['riwayat'])) {
                $riwayat[] = [
                    'pesan' => $validated['riwayat'],
                    'waktu' => now()->format('Y-m-d H:i'),
                ];
            }

            // Simpan status baru dan riwayat yang sudah diperbarui
            // $input->status = $validated['status'];
            $input->status = $request->status;
            // $input->riwayat = json_encode($riwayat);
            $riwayat = json_decode($_POST['riwayat_pengiriman'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log('JSON Error: ' . json_last_error_msg());
            }
            $input->save();

            return response()->json([
                'message' => 'Status dan riwayat berhasil diperbarui',
                'status' => $input->status,
                'riwayat' => $riwayat
            ]);
        }
    }
    protected $casts = [
        'riwayat_pengiriman' => 'array',
    ];
    // Misalnya ini model Input
    public function riwayat()
    {
        return $this->hasMany(Riwayat::class, 'id_riwayat', 'id');
        // 'id' = foreign key di tabel `riwayat`, 'id' = primary key di `input`
    }

    public function showRiwayat($id)
    {
        // $input = Input::with('riwayat')->findOrFail($id);
        $input = Input::with(['riwayat.user.kurir'])->find($id);
        // return response()->json($input);


        return response()->json([
            'riwayat_pengiriman' => $input->riwayat->map(function ($item) {
                return [
                    'deskripsi' => $item->deskripsi,
                    'created_at' => $item->created_at->toDateTimeString(), // penting!
                ];
            }),
        ]);
    }

    public function showByKurir($id)
{
    $data = Input::where('kurir_id', $id)->get();
    return response()->json($data);
}

    //    public function update(Request $request, $id)
// {

    //     $validated = $request->validate([
//         'status' => 'required|string',
//         'riwayat_pengiriman' => 'nullable|string', // isi = pesan baru$input = Input::findOrFail($id);
//     $data = $request->all();

    //     $existingRiwayat = $input->riwayat_pengiriman ?? [];
//     $newRiwayat = array_merge($existingRiwayat, $data['riwayat_pengiriman'] ?? []);

    //     $data['riwayat_pengiriman'] = $newRiwayat;

    //     $input->update($data);

    //     return response()->json([
//         'message' => 'Data berhasil diperbarui',
//         'data' => $input,
//     ]);
// ]);

    //     $input = Input::findOrFail($id);

    //     // Ambil riwayat sebelumnya dan decode
//     $riwayat = json_decode($input->riwayat_pengiriman ?? '[]', true);

    //     // Tambahkan riwayat baru jika ada input pesan
//     if ($validated['riwayat_pengiriman']) {
//         $riwayat[] = [
//             'pesan' => $validated['riwayat_pengiriman'],
//             'waktu' => now()->format('Y-m-d H:i'),
//         ];
//     }

    //     // Simpan kembali status & riwayat ke database
//     $input->status = $validated['status'];
//     $input->riwayat_pengiriman = json_encode($riwayat);
//     $input->save();

    //     return response()->json([
//         'message' => 'Status dan riwayat berhasil diperbarui',
//         'status' => $input->status,
//         'riwayat_pengiriman' => $riwayat
//     ]);
// }


    public function get()
    {
        $data = Input::select(
            'nama_pengirim',
            'alamat_pengirim',
            'no_telp_pengirim',
            'nama_penerima',
            'alamat_penerima',
            'no_telp_penerima',
            'jenis_barang',
            'jenis_layanan',
            'berat_barang',
            'riwayat'
        )->get();

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function destroy(Input $input)
    {
        $input->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data pesanan berhasil dihapus',
        ]);
    }
    public function masuk(Request $request)
    {
        $input = Input::findOrFail($request->input_id);
        $input->status = 'masuk gudang';
        $input->save();

        $input->riwayat()->create([
            'deskripsi' => $request->deskripsi,
        ]);

        return response()->json(['success' => true]);
    }

    public function keluar(Request $request)
    {
        $input = Input::findOrFail($request->input_id);
        $input->status = 'keluar gudang';
        $input->save();

        $input->riwayat()->create([
            'deskripsi' => $request->deskripsi,
        ]);

        return response()->json(['success' => true]);
    }


    public function orderanSaya(Request $request)
{
    $user = auth()->user();

    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'User tidak ditemukan atau belum login.',
        ], 401);
    }

    if ($user->role->name !== 'kurir') {
        return response()->json([
            'success' => false,
            'message' => 'Hanya kurir yang bisa melihat data ini.',
        ], 403);
    }

    // Ambil ID kurir yang login
    $kurirId = $user->kurir->id ?? null;

    if (!$kurirId) {
        return response()->json([
            'success' => false,
            'message' => 'Data kurir tidak ditemukan.',
        ], 404);
    }

    // Ambil orderan yang diambil oleh kurir ini saja
    $data = Input::with('riwayat')
        ->where('kurir_id', $kurirId)
        ->orderByDesc('created_at')
        ->get();

    return response()->json([
        'success' => true,
        'data' => $data,
    ]);
}


//     public function orderanSaya()
// {
//     $user = auth()->user();

//     if ($user->role->name !== 'kurir') {
//         return response()->json([
//             'success' => false,
//             'message' => 'Hanya kurir yang bisa melihat data ini.',
//         ], 403);
//     }

//     $kurirId = $user->kurir->kurir_id;

//     // Ambil hanya orderan milik kurir login
//     $data = Input::with('riwayat')
//         ->where('kurir_id', $kurirId)
//         ->orderByDesc('created_at')
//         ->get();

//     return response()->json([
//         'success' => true,
//         'data' => $data,
//     ]);
// }
    public function showOrdered()
{
    $user = auth()->user();

    if ($user->role->name !== 'kurir') {
        return response()->json([
            'success' => false,
            'message' => 'Hanya kurir yang bisa melihat data ini.',
        ], 403);
    }

    $kurirId = $user->kurir->kurir_id;

    // Ambil hanya orderan milik kurir login
    $data = Input::with('riwayat')
        ->where('kurir_id', $kurirId)
        ->orderByDesc('created_at')
        ->get();

    return response()->json([
        'success' => true,
        'data' => $data,
    ]);
}

    public function paketPernahDitangani()
{
    $user = auth()->user();
    $kurir = $user->kurir;

    // Ambil semua paket yang pernah ditangani kurir ini lewat tabel riwayat
    $input = Input::whereHas('riwayat', function($q) use ($kurir) {
            $q->where('kurir_id', $kurir->id);
        })
        ->with(['riwayat' => function($q) use ($kurir) {
            $q->where('kurir_id', $kurir->id)
              ->with('kurir.user');
        }])
        ->orderBy('created_at', 'desc')
        ->get();

    return response()->json($input);
}

}