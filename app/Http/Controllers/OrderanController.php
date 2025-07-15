<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Province;
use App\Models\Input;
use App\Models\Kurir;
use App\Models\Riwayat;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use illuminate\Support\Str;

class OrderanController extends Controller
{

     public function index(Request $request)
    {
        $per = $request->input('per', 10);
        $page = $request->input('page', 1);

        DB::statement('SET @no := ' . (($page - 1) * $per));
        $kurirId = auth()->user()->role->name === 'kurir'
        ? auth()->user()->kurir->id
        : null;

        $data = Input::with(['asalProvinsi', 'asalKota', 'tujuanProvinsi', 'tujuanKota', 'riwayat'])
            ->when($request->search, function (Builder $query, string $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama_pengirim', 'like', "%$search%")
                        ->orWhere('alamat_pengirim', 'like', "%$search%")
                        ->orWhere('no_telp_pengirim', 'like', "%$search%")
                        ->orWhere('nama_penerima', 'like', "%$search%")
                        ->orWhere('alamat_penerima', 'like', "%$search%")
                        ->orWhere('no_telp_penerima', 'like', "%$search%")
                        ->orWhere('jenis_barang', 'like', "%$search%")
                        ->orWhere('ekspedisi', 'like', "%$search%")
                        ->orWhere('jenis_layanan', 'like', "%$search%")
                        ->orWhere('berat_barang', 'like', "%$search%")
                        ->orWhere('biaya', 'like', "%$search%");
                });
            })
            // Role: kurir — tampilkan transaksi yang belum ada kurir_id atau milik kurir itu sendiri 
            ->when(auth()->user()->role->name === 'kurir', function ($query) use ($kurirId) {
                Log::info('b'); // log debug
                $query->where(function ($q) use ($kurirId) {
                    Log::info($kurirId); // log debug
                    $q->whereNull('kurir_id')
                      ->orWhere('kurir_id', $kurirId);
                });
            })
           
            ->when($request->status, function ($query, $status) {
                // Jika parameter 'status' ada, filter berdasarkan status tersebut
                $query->where('status', $status);
            })
            
            ->when($request->has('exclude_status'), function ($query) use ($request) {
            $query->whereNotIn('status', $request->exclude_status);
        })
            ->latest() // Urutkan berdasarkan kolom 'created_at' secara descending
            ->paginate($per); // Paginasi berdasarkan nilai $per

        // Tambahkan nomor urut manual berdasarkan halaman saat ini
        $no = ($data->currentPage() - 1) * $per + 1;
        foreach ($data as $item) {
            $item->no = $no++;
        }

        return response()->json($data);
    }

    public function claim(Request $request, $id)
    {
        $input = Input::find($id);

        if (!$input) {
            return response()->json(['success' => false, 'message' => 'Orderan tidak ditemukan'], 404);
        }

        $user = auth()->user();


        $kurir = $user->kurir;

        if (!$kurir) {
            return response()->json([
                'success' => false,
                'message' => 'Akun ini tidak memiliki data kurir.'
            ], 403);
        }

        // Cegah pengambilan ulang oleh kurir lain
        if ($input->kurir_id && $input->kurir_id !== $kurir->kurir_id) {
            return response()->json([
                'success' => false,
                'message' => 'Orderan sudah diambil oleh kurir lain.'
            ], 403);
        }

        $input->kurir_id = $kurir->id;
        $input->save();

        return response()->json([
            'success' => true,
            'message' => 'Orderan berhasil diambil oleh ' . $kurir->user->name,
            'input id' => $input->id,
            'kurir_id' => $input->kurir_id,
            'status' => $input->status,
        ]);
    }


    public function store(Request $request, $id = null)
    {
        $validated = $request->validate([
            'nama_pengirim' => 'required|string|max:255',
            'tujuan_provinsi_id' => 'required|exists:provinces,id',
            'tujuan_kota_id' => 'required|exists:cities,id',
            'alamat_pengirim' => 'required|string|max:255',
            'no_telp_pengirim' => 'required|string|max:20',

            'nama_penerima' => 'required|string|max:255',
            'asal_provinsi_id' => 'required|exists:provinces,id',
            'asal_kota_id' => 'required|exists:cities,id',
            'alamat_penerima' => 'required|string|max:255',
            'no_telp_penerima' => 'required|string|max:20',
            'jenis_barang' => 'required|string|max:255',
            'ekspedisi' => 'required|string',
            'jenis_layanan' => 'required|string|max:255',
            'biaya' => 'nullable|integer',
            'berat_barang' => 'required|numeric|min:1',
            'rating' => 'nullable|integer|min:1|max:5',
            'status' => 'required|in:menunggu,dalam proses,dikirim,selesai',
            'ulasan' => 'nullable|string',
        ]);

        $input = $id ? Input::findOrFail($id) : new Input(); // ini sudah benar
        if (!$id) {
            // Loop hingga menghasilkan no_resi yang belum ada di database
            do {
                $resi = 'RESI-' . now()->timestamp . '-' . rand(1000, 9999);
            } while (Input::where('no_resi', $resi)->exists());

            $input->no_resi = $resi;
            $input->status = 'menunggu';

            if (empty($input->waktu)) {
                $input->waktu = now()->format('Y-m-d H:i:s');
            }
        }

        $input->fill($validated);
        $input->save();

        return response()->json([
            'message' => 'Berhasil menambahkan input',
            'data' => $input
        ]);
    }

    public function show($id) {
        $inputorder = Input::with(['riwwayat' => fn($q) => $q->orderBy('created_at')])->findOrfail($id);

        return response()->json($inputorder);
    }
    // public function show($id)
    // {
    //     $inputorder = Input::findOrFail($id);
    //     $riwayat = Riwayat::where('id_riwayat', $id)->orderBy('created_at')->get();

    //     return response()->json([
    //         'input' => $inputorder,
    //         'riwayat' => $riwayat,
    //     ]);
    // }

     public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string',
            'riwayat_pengiriman' => 'nullable|array',
            'riwayat_pengiriman.*' => 'string',
        ]);

        $input = Input::findOrFail($id);

        $input = Input::find($id);
        $input->status = $request->status;
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

            // Tambahkan pesan baru ke array
            if (!empty($validated['riwayat'])) {
                $riwayat[] = [
                    'pesan' => $validated['riwayat'],
                    'waktu' => now()->format('Y-m-d H:i'),
                ];
            }
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
        $input = Input::with('riwayat')->findOrFail($id);

        return response()->json([
            'riwayat_pengiriman' => $input->riwayat->map(function ($item) {
                return [
                    'deskripsi' => $item->deskripsi,
                    'created_at' => $item->created_at->toDateTimeString(), // penting!
                ];
            }),
        ]);
    }

    // public function update(Request $request, $id)
    // {
    //     $validated = $request->validate([
    //         'biaya' => 'nullable|numeric|min:0',
    //         'status' => 'nullable|string',
    //         'riwayat' => 'nullable|string',
    //     ]);

    //     $input = Input::findOrFail($id);

    //     $user = auth()->user();
    //     $kurirId = $user->kurir->id ?? null; // Asumsi relasi user->kurir

    //     // Cegah kurir mengambil pesanan yang sudah diambil kurir lain
    //     if ($user->role === 'kurir') {
    //         if ($input->kurir_id && $input->kurir_id != $kurirId) {
    //             return response()->json([
    //                 'message' => 'Pesanan sudah diambil oleh kurir lain.'
    //             ], 403);
    //         }
    //     }
    //     $kurir = auth()->user()->kurir;
    //     // // Cek apakah kurir sudah pegang 1 order yang belum selesai
    //     $masihAktif = Input::where('kurir_id', $kurir->kurir_id)
    //         ->whereIn('status', ['Diproses', 'Sedang Dikirim']) // sesuaikan dengan status aktifmu
    //         ->exists();

    //     if ($masihAktif) {
    //         return response()->json([
    //             'message' => 'Kurir hanya bisa mengambil 1 order dalam satu waktu.'
    //         ], 404);
    //     }

    //     $input->status = $validated['status'];
    //     // $input->riwayat = $validated['riwayat'];
    //     $input->riwayat = $validated['riwayat'] ?? null;
    //     // $request->riwayat['riwayat];
    //     $waktuBaru = now()->format('d-m-Y H:i:s');

    //     $input->save(); // simpan perubahan

    //     return response()->json([
    //         'message' => 'Status berhasil diperbarui.',
    //         // 'data' => $input
    //         'status' => $input->status,
    //     ]);
    // }

    
    public function handleCallback(Request $request)
    {
        $notif = $request->all();

        if (
            isset($notif['transaction_status']) &&
            $notif['transaction_status'] === 'settlement'
        ) {
            // Ambil data form dari custom_field1
            $data = json_decode($notif['custom_field1'], true);

            // Simpan transaksi ke DB
            $trans = new Input();
            $trans->fill($data);
            $trans->status = 'dibayar';
            $trans->save();

            // Simpan riwayat pembayaran
            $pay = new Input();
            $pay->Input_id = $trans->id;
            $pay->external_id = $notif['order_id'];
            $pay->status = 'success';
            $pay->save();
        }

        return response()->json(['message' => 'Callback diproses']);
    }

    public function destroy(Input $input)
    {
        $input->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data pesanan berhasil dihapus',
        ]);
    }

    public function operKeKurirLain(Request $request, $id_order)
    {
        $request->validate([
            'kurir_id_baru' => 'required|exists:kurirs,id', // asumsi kamu punya tabel `kurirs`
        ]);

        // Ambil riwayat terakhir (kurir saat ini)
        $lastRiwayat = Riwayat::where('id_order', $id_order)
            ->latest()
            ->first();

        $kurir_lama_id = $lastRiwayat?->kurir_id;

        // Buat entri baru untuk kurir baru
        Riwayat::create([
            'id_order' => $id_order,
            'kurir_id' => $request->kurir_id_baru,
            'deskripsi' => "Paket dioper dari kurir ID {$kurir_lama_id} ke kurir ID {$request->kurir_id_baru}",
        ]);

        return response()->json([
            'message' => 'Paket berhasil dioper ke kurir baru.',
        ]);
    }

    public function serahkanKeGudang(Request $request, $id)
    {
        $kurir_id = auth()->user()->kurir->id;

        // Tambah riwayat
        Riwayat::create([
            'id' => $id,
            'kurir_id' => $kurir_id,
            'deskripsi' => 'Dikembalikan ke gudang',
        ]);

        // Update status paket jadi "di gudang"
        $input = Input::findOrFail($id);
        $input->status = 'di gudang';
        $input->save();

        return response()->json(['message' => 'Paket berhasil dikembalikan ke gudang dan status diupdate.']);
    }

    public function operDariGudang(Request $request, $id)
    {
        $request->validate([
            'kurir_id_baru' => 'required|exists:kurirs,id',
        ]);

        Riwayat::create([
            'id' => $id,
            'kurir_id' => $request->kurir_id_baru,
            'deskripsi' => 'Dioper ke kurir baru dari gudang',
        ]);

        $input = Input::findOrFail($id);
        $input->status = 'dalam proses';
        $input->save();

        return response()->json(['message' => 'Paket berhasil dioper ke kurir dan status diubah.']);
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
    $input->status = 'antar'; // Ubah ke status antar
    $input->save();

    $input->riwayat()->create([
        'deskripsi' => $request->deskripsi,
    ]);

    return response()->json(['success' => true]);
}

    // public function claim($id)
    // {
    //     $input = Input::findOrFail($id);

    //     if ($input->kurir_id !== null) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Paket sudah diambil oleh kurir lain.'
    //         ], 409); // Conflict
    //     }

    //     // Simpan kurir yang login saat ini
    //     $user = auth()->user(); // pastikan guard-nya kurir
    //     $input->kurir_id = $user->kurir->id ?? null;
    //     $input->save();

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Paket berhasil diambil.',
    //     ]);
    // }

    public function ambil(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string',
            // 'kurir_id' => 'required|string',
        ]);
        $input = Input::find($id);
        if (!$input) {
            return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);
        }


        // $input = input::with('kurir')->findOrFail($id);
        $statusBaru = $request->status;

        $user = auth()->user();
        $kurir = $user->kurir;

        if (!$kurir) {
            return response()->json([
                'message' => 'Kurir belum terdaftar atau tidak punya relasi.',
            ], 403);
        }

        // Update transaksi
        $input->kurir_id = $kurir->kurir_id;
        $input->status = 'diambil kurir';
        $input->waktu_diambil = now();
        $input->save();


        Log::info('Status Update:', [$request->status]);

        switch ($request->status) {
            case 'diambil kurir':
                $input->waktu_diambil = now();
                // Pengiriman::create([
                //     'kurir_id' => $kurir->kurir_id,
                //     'input_id' => $input->id,
                //     'deskripsi' => 'Kurir sedang menuju rumahmu untuk mengambil barang'
                // ]);
                break;

            case 'dikurir':
                $input->waktu_dikurir = now();
                // Pengiriman::create([
                //     'kurir_id' => $kurir->kurir_id,
                //     'input_id' => $input->id,
                //     'deskripsi' => 'Kurir menuju gudang penempatan paket'
                // ]);
                break;

            case 'digudang':
                $input->waktu_digudang = now();
                // Pengiriman::create([
                //     'kurir_id' => $kurir->kurir_id,
                //     'input_id' => $input->id,
                //     'deskripsi' => 'Paket telah sampai di gudang'
                // ]);
                break;

            case 'diproses':
                $input->waktu_proses = now();
                // Pengiriman::create([
                //     // 'kurir_id' => $kurir->kurir_id,
                //     'input_id' => $input->id,
                //     'deskripsi' => 'Paket akan dikirim ke provinsi ' . $input->tujuan_provinsi . ' dan ke kota ' . $input->asal_kota
                // ]);
                break;

            case 'tiba digudang':
                $input->waktu_tiba = now();
                // Pengiriman::create([
                //     // 'kurir_id' => $kurir->kurir_id,
                //     'input_id' => $input->id,
                //     'deskripsi' => 'Paket telah tiba digudang kota ' . $input->asal_kota
                // ]);
                break;
        }

        $input->status = $request->status;
        $input->save();

        // Update field waktu jika sesuai
        // if (isset($waktuMap[$statusBaru])) {
        //     $field = $waktuMap[$statusBaru];
        //     $input->$field = now();
        // }

        // Tandai pernah masuk gudang
        if ($statusBaru === 'digudang') {
            $input->pernah_digudang = true;
        }

        // // Update status input
        // $input->status = $statusBaru;
        // $input->save();

        // Ambil ulang data kurir agar sinkron
        $input->load('kurir');

        return response()->json([
            'message' => 'Status berhasil diubah.',
            'status' => $input->status,
            'kurir' => $input->kurir,
        ]);
    }
}
