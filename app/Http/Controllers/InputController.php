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
use Illuminate\Notifications\Notification;
use illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\Snap;

class InputController extends Controller
{
    // public function cetakpdf($noResi)
    // {
    //     $data = Input::where('no_resi', $noResi)->first();

    //     if (!$data) {
    //         abort(404, 'Data tidak ditemukan');
    //     }

    //     // Gunakan view yang sama
    //     $pdf = Pdf::loadView('cetak-resi-pdf', compact('data'));
    //     $pdf->setPaper([0, 0, 165, 566], 'portrait'); // 58mm x 200mm

    //     return $pdf->stream("struk-{$noResi}.pdf"); // tampilkan di browser
    // }
    public function handleResiPdf($noResi, $mode = 'view')
    {
        $data = Input::where('no_resi', $noResi)->first();

        if (!$data) {
            abort(404, 'Data tidak ditemukan');
        }

        $pdf = Pdf::loadView('cetak-resi-pdf', compact('data'));
        $pdf->setPaper([0, 0, 165, 566], 'portrait');

        if ($mode === 'download') {
            return $pdf->download("struk-{$noResi}.pdf");
        }

        return $pdf->stream("struk-{$noResi}.pdf");
    }

    public function downloadResi($noResi)
    {
        $data = Input::where('no_resi', $noResi)->first();

        if (!$data) {
            abort(404, 'Data tidak ditemukan');
        }

        // Gunakan view yang sama
        $pdf = Pdf::loadView('cetak-resi-pdf', compact('data'));
        $pdf->setPaper([0, 0, 165, 566], 'portrait'); // 58mm x 200mm

        return $pdf->download("struk-{$noResi}.pdf"); // download otomatis
    }


    public function getProvinces()
    {
        // $response = Http::withHeaders([
        //     'key' => env('RAJAONGKIR_API_KEY'),
        // ])->get('https://api.rajaongkir.com/starter/province');

        // $provinces = collect($response['rajaongkir'])
        //     ->pluck('province', 'province_id');
        $provinces = Province::all()->pluck('name', 'id');

        return response()->json($provinces);
    }

    // public function getCities($provinceId)
    // {
    //     $response = Http::withHeaders([
    //         'key' => env('RAJAONGKIR_API_KEY'),
    //     ])->get('https://api.rajaongkir.com/starter/city', [
    //                 'province' => $provinceId
    //             ]);

    //     $cities = collect($response['rajaongkir']['results'])
    //         ->pluck('city_name', 'city_id');

    //     return response()->json($cities);
    // }

    public function getCities($provinceId)
    {
        $response = Http::withHeaders([
            'key' => env('RAJAONGKIR_API_KEY'),
        ])->get('https://api.rajaongkir.com/starter/city?province=' . $provinceId);

        $cities = collect($response['rajaongkir'])
            ->pluck('city_name', 'city_id');

        return response()->json($cities);
    }

    public function hitungOngkir(Request $request)
        {
            Log::info('Check Ongkir', [
                'origin' => $request->input('origin'),
                'destination' => $request->input('destination'),
                'weight' => $request->input('weight'),
                'courier' => $request->input('courier'),
            ]);

            //tambahkan ini
            $response = Http::asForm()->withHeaders([
                'key' => '2503fca740f27ebbbba89fa405755bfc', // Ambil dari config/ENV
                'Accept' => 'application/json',
            ])->post('https://rajaongkir.komerce.id/api/v1/calculate/domestic-cost', [
            // ])->post('https://rajaongkir.komerce.id/api/v1/calculate/district/domestic-cost', [
                'origin'      => $request->input('origin'),       // ID kecamatan asal  
                'destination' => $request->input('destination'),  // ID kecamatan tujuan
                'weight'      => $request->input('weight'),       // Berat gram
                'courier'     => $request->input('courier'),      // jne, tiki, pos
                'price'       => "lowest", // lowest / highest, default lowest 
            ]);

            $data = $response->json();

            if ($response->successful() && isset($data['data'])) {
                return response()->json($data['data'], 200);
            }

            return response()->json([
                'error' => 'Gagal mengambil data ongkir',
                'response' => $data
            ], 500);
        }
    // public function hitungOngkir(Request $request)
    // {
    //     $response = Http::asForm()->withHeaders([
    //         'key' => config('services.rajaongkir.key')
    //     ])->post('https://api.rajaongkir.com/starter/cost', [
    //                 'origin' => $request->origin,
    //                 'destination' => $request->destination,
    //                 'weight' => $request->weight, // dalam gram
    //                 'courier' => $request->courier, // jne / tiki / pos
    //             ]);

    //     Log::info("Cost", $response['rajaongkir']);

    //     $cost = $response['rajaongkir']['results'][0]['costs'];

    //     return response()->json($cost);
    // }

    

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $per = $request->input('per', 10);
        $page = $request->input('page', 1);

        DB::statement('SET @no := ' . (($page - 1) * $per));
        $kurirId = auth()->user()->role->name === 'kurir'
            ? auth()->user()->kurir->id
            : null;

        $data = Input::with(['asalProvinsi', 'asalKota', 'tujuanProvinsi', 'tujuanKota', 'asalKecamatan', 'tujuanKecamatan', 'riwayat'])
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
            // sebelumnya atas situ

            // ->when(auth()->user()->role->name === 'kurir', function ($query) use ($kurirId) {
            //     $query->where(function ($q) use ($kurirId) {
            //         $q->whereNull('kurir_id') // belum diklaim siapa pun
            //             ->orWhere('kurir_id', $kurirId) // milik dia sendiri
            //             ->orWhere(function ($sub) {
            //                 $sub->where('status', 'keluar gudang')->whereNull('kurir_id');
            //             });
            //     });
            // })

        //     ->when($kurirId, function ($query) use ($request, $kurirId) {
        //     $status = $request->status;

        //     // Cek jika tidak sedang mencari status keluar gudang
        //     $isKeluarGudang = false;
        //     if (is_array($status)) {
        //         $isKeluarGudang = in_array('keluar gudang', $status);
        //     } elseif ($status === 'keluar gudang') {
        //         $isKeluarGudang = true;
        //     }

        //     // Jika status bukan keluar gudang, filter berdasarkan kurir
        //     if (!$isKeluarGudang) {
        //         $query->where('kurir_id', $kurirId);
        //     }
        // })

        

            //             ->when($user->role->name === 'kurir', function ($query) use ($user) {
//     $kurirId = $user->kurir->kurir_id;
//     $query->where(function ($q) use ($kurirId) {
//         $q->whereNull('kurir_id') // Belum diambil
//           ->orWhere('kurir_id', $kurirId); // Diambil oleh kurir ini
//     });
// })

            // ->when(auth()->user()->role->name === 'kurir', function ($query) {
            //     Log::info('b'); // log debug
            //     $query->where(function ($q) {
            //         Log::info('e'); // log debug
            //         $kurirId = auth()->user()->kurir->kurir_id;
            //         $q->whereNull('kurir_id')
            //           ->orWhere('kurir_id', $kurirId);
            //     });
            // })

            // ->when(auth()->user()->role->name === 'kurir', function ($query) {
            //     $kurirId = auth()->user()->kurir->kurir_id;
            //     $query->where(function ($q) use ($kurirId) {
            //         $q->whereNull('kurir_id') // belum diambil siapa pun
            //             ->orWhere('kurir_id', $kurirId); // milik dia sendiri (walau sudah selesai)
            //     });
            // })
            // ->when(auth()->user()->role->name === 'kurir', function ($query) {
            //     $kurirId = auth()->user()->kurir->id; // pastikan ini ID kurir yang login
            //     $query->where(function ($q) use ($kurirId) {
            //         $q->whereNull('kurir_id') // paket belum diambil siapa pun
            //             ->orWhere('kurir_id', $kurirId); // atau milik dia sendiri
            //     });
            // })
            // ->when(auth()->user()->role->name === 'kurir', function ($query) use ($kurirId) {
            // $query->where(function ($q) use ($kurirId) {
            //     $q->whereNull('kurir_id')  // belum diambil
            //       ->orWhere('kurir_id', $kurirId); // milik kurir login
            // });
            // })

            // ->when(auth()->user()->role->name === 'pengguna', function ($query) {
            //     Log::info('pengguna'); // log debug
            //     $penggunaId = auth()->user()->pengguna->pengguna_id;
            //     $query->where('pengguna_id', $penggunaId);
            // })
            ->when($request->status, function ($query, $status) {
                // Jika parameter 'status' ada, filter berdasarkan status tersebut
                $query->where('status', $status);
            })
            ->when($request->status_pembayaran, function ($query, $status_pembayaran) {
                // Jika parameter 'status_pembayaran' ada, filter berdasarkan status_pembayaran tersebut
                $query->where('status_pembayaran', $status_pembayaran);
            })
            // ->when($request->has('exclude_status'), function ($query) use ($request) {
            //     // Jika parameter 'exclude_status' ada, ambil data yang status-nya bukan nilai itu
            //     $query->where('status', '!=', $request->exclude_status);
            // })
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



    // public function claim(Request $request, $id)
    // {
    //     $input = Input::find($id);

    //     if (!$input) {
    //         return response()->json(['success' => false, 'message' => 'Orderan tidak ditemukan'], 404);
    //     }

    //     $user = auth()->user();


    //     $kurir = $user->kurir;

    //     if (!$kurir) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Akun ini tidak memiliki data kurir.'
    //         ], 403);
    //     }

    //     // Cegah pengambilan ulang oleh kurir lain
    //     if ($input->kurir_id && $input->kurir_id !== $kurir->kurir_id) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Orderan sudah diambil oleh kurir lain.'
    //         ], 403);
    //     }

    //     // Update klaim kurir & status
    //     // Log::info($kurir->id);
    //     $input->riwayat()->create([
    //     'deskripsi' => $request->deskripsi,
    // ]);
    //     $input->kurir_id = $kurir->id;
    //     $input->save();

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Orderan berhasil diambil oleh ' . $kurir->user->name,
    //         'input id' => $input->id,
    //         'kurir_id' => $input->kurir_id,
    //         'status' => $input->status,
    //     ]);
    // }


    // public function claim($id)
// {
//     $kurirId = auth()->user()->kurir->kurir_id;
//     $input = Input::findOrFail($id);

    //     if ($input->kurir_id && $input->kurir_id !== $kurirId) {
//         return response()->json([
//             'success' => false,
//             'message' => 'Orderan sudah diambil oleh kurir lain.'
//         ]);
//     }

    //     $input->kurir_id = $kurirId;
//     $input->save();

    //     return response()->json([
//         'success' => true,
//         'message' => 'Orderan berhasil diambil.'
//     ]);
// }



    // public function releasePackage(Request $request, $id)
    // {
    //     try {
    //         $package = Input::findOrFail($id);
    //         $kurirId = auth()->user()->kurir->kurir_id;

    //         // Cek apakah paket milik kurir ini
    //         if ($package->kurir_id !== $kurirId) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Anda tidak berhak melepas paket ini'
    //             ], 403);
    //         }

    //         // Lepas paket (kembalikan ke pool umum)
    //         $package->kurir_id = null;
    //         $package->status = 'tersedia'; // atau status yang sesuai
    //         $package->save();

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Paket berhasil dilepas'
    //         ]);

    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Gagal melepas paket: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }


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
    if ($input->kurir_id && $input->kurir_id !== $kurir->id) {
        return response()->json([
            'success' => false,
            'message' => 'Orderan sudah diambil oleh kurir lain.'
        ], 403);
    }

    // Update klaim kurir
    $input->kurir_id = $kurir->id;

    // Opsional: update status jadi "dalam proses"
    if ($input->status === 'menunggu') {
        $input->status = 'dalam proses';
    }

    $input->save();

    // Tambahkan riwayat otomatis
    $input->riwayat()->create([
        'kurir_id' => $kurir->id,
        'deskripsi' => 'Paket diambil kurir ' . $kurir->user->name,
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Orderan berhasil diambil oleh ' . $kurir->user->name,
        'input_id' => $input->id,
        'kurir_id' => $input->kurir_id,
        'status' => $input->status,
    ]);
}

    
    public function get($id)
    {
        $input = Input::with(['asalProvinsi', 'asalKota', 'tujuanProvinsi', 'tujuanKota', 'pengguna', 'asalKecamatan', 'tujuanKecamatan'])
            ->findOrFail($id);

        return response()->json($input);
    }
//     public function get($id)
//     {
//         $transaksii = Transaksii::with(['asalProvinsi', 'asalKota', 'tujuanProvinsi', 'tujuanKota', 'asalKecamatan', 'tujuanKecamatan'])->with('pengiriman.kurir.user')
//         // $transaksii = Transaksii::with(['asalProvinsi', 'asalKota', 'tujuanProvinsi', 'tujuanKota', 'pengguna'])
//             ->findOrFail($id);

//         return response()->json($transaksii);
//     }


    public function store(Request $request, $id = null)
    {
        $validated = $request->validate([
            'nama_pengirim' => 'required|string|max:255',
            'tujuan_provinsi_id' => 'required|exists:provinces,id',
            'tujuan_kota_id' => 'required|exists:cities,id',
            'tujuan_kecamatan_id' => 'required|exists:districts,id',
            'alamat_pengirim' => 'required|string|max:255',
            'no_telp_pengirim' => 'required|string|max:20',

            'nama_penerima' => 'required|string|max:255',
            'asal_provinsi_id' => 'required|exists:provinces,id',
            'asal_kota_id' => 'required|exists:cities,id',
            'asal_kecamatan_id' => 'required|exists:districts,id',
            'alamat_penerima' => 'required|string|max:255',
            'no_telp_penerima' => 'required|string|max:20',
            'jenis_barang' => 'required|string|max:255',
            'ekspedisi' => 'required|string',
            'jenis_layanan' => 'required|string|max:255',
            'biaya' => 'nullable|integer',
            'berat_barang' => 'required|numeric|min:1',
            'rating' => 'nullable|integer|min:1|max:5',
            'status' => 'required|in:menunggu,dalam proses,masuk gudang,keluar gudang, dikirim,dikirim,selesai',
            'ulasan' => 'nullable|string',
            // 'status_pembayaran' => 'nullable|in:menunggu, belum dibayar,dibayar',
        ]);

        $input = $id ? Input::findOrFail($id) : new Input(); // ini sudah benar
        if (!$id) {
            // Loop hingga menghasilkan no_resi yang belum ada di database
            do {
                $resi = 'RESI-' . now()->timestamp . '-' . rand(1000, 9999);
            } while (Input::where('no_resi', $resi)->exists());

            $input->no_resi = $resi;
            $input->status = 'menunggu';

            $input->status_pembayaran = 'pending';
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
    public function show($id)
    {
        $inputorder = Input::findOrFail($id);
        $riwayat = Riwayat::where('id_riwayat', $id)->orderBy('created_at')->get();

        return response()->json([
            'input' => $inputorder,
            'riwayat' => $riwayat,
        ]);
    }
    

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'biaya' => 'nullable|numeric|min:0',
            'status' => 'nullable|string',
            // 'riwayat' => 'nullable|string',
        ]);

        $input = Input::findOrFail($id);

        $user = auth()->user();
        $kurirId = $user->kurir->id ?? null; // Asumsi relasi user->kurir

        // Cegah kurir mengambil pesanan yang sudah diambil kurir lain
        if ($user->role === 'kurir') {
            if ($input->kurir_id && $input->kurir_id != $kurirId) {
                return response()->json([
                    'message' => 'Pesanan sudah diambil oleh kurir lain.'
                ], 403);
            }
        }
        $kurir = auth()->user()->kurir;
        // // Cek apakah kurir sudah pegang 1 order yang belum selesai
        $masihAktif = Input::where('kurir_id', $kurir->kurir_id)
            ->whereIn('status', ['Diproses', 'Sedang Dikirim']) // sesuaikan dengan status aktifmu
            ->exists();

        if ($masihAktif) {
            return response()->json([
                'message' => 'Kurir hanya bisa mengambil 1 order dalam satu waktu.'
            ], 404);
        }

        $input->status = $validated['status'];
        // $input->riwayat = $validated['riwayat'];
        $input->riwayat = $validated['riwayat'] ?? null;
        // $request->riwayat['riwayat];
        $waktuBaru = now()->format('d-m-Y H:i:s');

         $input->status = $validated['status'] ?? $input->status;

    // ✅ Tambahkan riwayat otomatis jika status == selesai
    if (strtolower($input->status) === 'selesai') {
        Riwayat::create([
            'id' => $id,
            'deskripsi' => 'Paket telah diterima oleh yang bersangkutan',
            'created_at' => now(),
        ]);
    }
        $input->save(); // simpan perubahan

        return response()->json([
            'message' => 'Status berhasil diperbarui.',
            // 'data' => $input
            'status' => $input->status,
        ]);
    }

    public function beriRating(Request $request)
    {
        try {
            $validated = $request->validate([
                'no_resi' => 'required|string',
                'rating' => 'required|integer|min:1|max:5',
                'ulasan' => 'required|string|max:500'
            ]);

            // Hapus baris log debug
            // Log::info('Rating data:', $validated);

            $input = Input::where('no_resi', $validated['no_resi'])->first();

            if (!$input) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data dengan no resi tersebut tidak ditemukan'
                ], 404);
            }

            if (!empty($input->rating)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rating sudah pernah diberikan'
                ], 400);
            }

            $input->update([
                'rating' => $validated['rating'],
                'ulasan' => $validated['ulasan'],
                'tanggal_rating' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Rating berhasil disimpan'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            // Atau pakai error_log() biasa
            error_log('Error saving rating: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }


    public function payment(Request $request)
    {
        $biaya = $request->input('biaya');

        if (!$biaya || !is_numeric($biaya) || $biaya <= 0) {
            return response()->json([
                'message' => 'Biaya ongkir belum valid',
            ], 400);
        }

        $order_id = 'ONGKIR-' . now()->timestamp . '-' . Str::random(4);

        $params = [
            'transaction_details' => [
                'order_id' => $order_id,
                'gross_amount' => (int) $biaya,
            ],
            'item_details' => [
                [
                    'id' => $order_id,
                    'price' => (int) $biaya,
                    'quantity' => 1,
                    'name' => 'Biaya Ongkir Transaksi',
                ]
            ],
            'customer_details' => [
                'first_name' => $request->input('pengirim') ?? 'User',
                'email' => 'default@email.com',
            ],
            'callbacks' => [
                // 'finish' => url('/payment/success'),
                'finish' => env('APP_URL') . '/#/payment/success',
                'unfinish' => url('/payment/failed'),
                'error' => url('/payment/error'),
            ]
        ];

        $auth = base64_encode(env('MIDTRANS_SERVER_KEY'));
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => "Basic $auth",
        ])->withBody(json_encode($params), 'application/json')
            ->post('https://app.sandbox.midtrans.com/snap/v1/transactions');

        $data = json_decode($response->body());

        if (!isset($data->redirect_url)) {
            return response()->json([
                'message' => 'Gagal membuat link pembayaran Midtrans',
                'error' => $data,
            ], 500);
        }

        return response()->json([
            'redirect_url' => $data->redirect_url,
            'order_id' => $order_id,
        ]);
    }

    public function createSnap(Request $request)
    {
        $validated = $request->validate([
            'pengirim' => 'required|string',
            'penerima' => 'required|string',
            'alamat_asal' => 'required|string',
            'alamat_tujuan' => 'required|string',
            'berat_barang' => 'required|numeric',
            'biaya' => 'required|numeric|min:1000',
            'kurir_id' => 'required|exists:kurir,id',
        ]);

        $orderId = 'ORDER-' . Str::uuid();

        $payload = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) $validated['biaya'],
            ],
            'item_details' => [
                [
                    'id' => 'ongkir',
                    'price' => (int) $validated['biaya'],
                    'quantity' => 1,
                    'name' => 'Ongkir Kurir',
                ]
            ],
            'customer_details' => [
                'first_name' => $validated['pengirim'],
                'email' => 'user@example.com',
            ],
            'callbacks' => [
                'finish' => url('/payment/callback'),
            ],
            'custom_field1' => json_encode($validated), // simpan data sementara
        ];

        $auth = base64_encode(env('MIDTRANS_SERVER_KEY'));

        $res = Http::withHeaders([
            'Authorization' => "Basic $auth",
            'Content-Type' => 'application/json',
        ])->post('https://app.sandbox.midtrans.com/snap/v1/transactions', $payload);

        $body = json_decode($res->body());

        if (isset($body->token)) {
            return response()->json([
                'snap_token' => $body->token,
            ]);
        }

        return response()->json([
            'message' => 'Gagal membuat token pembayaran',
            'error' => $body,
        ], 500);
    }
    
    public function getSnapToken($id)
    {
        // $transaksii = Transaksii::with(['pengguna'])->findOrFail($id);
        $input = Input::where('id', $id)->firstOrFail();
        $input->status_pembayaran = 'belum dibayar';
        $input->save();

        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = false;
        Config::$isSanitized = true;
        Config::$is3ds = true;

        $params = [
            'transaction_details' => [
                'order_id' => $input->no_resi,
                'gross_amount' => (int) $input->biaya,
            ],
            'customer_details' => [
                'first_name' => $input->pengirim,
                // 'email' => $input->pengguna->email ?? 'user@gmail.com',
                // 'email' => $input->pengguna_id ? ($input->pengguna->email ?? 'user@gmail.com') : 'user@gmail.com',
                'email' => optional($input->pengirim)->email ?: 'user@gmail.com',


            ]
        ];

        $snapToken = Snap::getSnapToken($params);
        return response()->json(['snap_token' => $snapToken]);
    }
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

    // Update status & reset kurir
    $input->status = 'masuk gudang';
    $input->kurir_id = null; // Reset klaim kurir
    Input::where('status', 'masuk gudang')->whereNull('kurir_id')->get();
    $input->save();

    // Tambahkan riwayat
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


    // 2. Terima notifikasi dari Midtrans dan update status_pembayaran
    // public function handleNotification(Request $request)
    // {
    //     $notification = new Notification();

    //     $transactionStatus = $notification->transaction_status;
    //     $orderId = $notification->order_id;

    //     $input = Input::find($orderId);
    //     if (!$input) {
    //         return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);
    //     }

    //     $input->status_pembayaran = $transactionStatus;
    //     $input->save();

    //     return response()->json(['message' => 'Notifikasi diproses']);
    // }

    // public function manualUpdateStatus(Request $request)
    // {
    //     $input = Input::find($request->order_id);
    //     if ($input) {
    //         $input->status_pembayaran = $request->transaction_status ?? 'dibayar';
    //         // $input->payment_type = $request->payment_type ?? 'manual';
    //         $input->save();

    //         \log::info("Manual update: order_id={$request->order_id}, status={$input->status_pembayaran}");
    //         return response()->json(['message' => 'Status pembayaran berhasil diperbarui']);
    //     }

    //     return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);
    // }

//     public function manualUpdateStatus(Request $request)
// {
//     $input = Input::find($request->order_id);
//     if ($input) {
//         // Mapping status dari Midtrans ke status lokal
//         $midtransStatus = $request->transaction_status;

//         $statusMap = [
//             'settlement' => 'dibayar',
//             'pending' => 'belum dibayar',
//             'cancel' => 'batal',
//             'expire' => 'kedaluwarsa',
//             'deny' => 'ditolak',
//             'failure' => 'gagal',
//             'refund' => 'refund',
//         ];

//         $localStatus = $statusMap[$midtransStatus] ?? $midtransStatus;

//         $input->status_pembayaran = $localStatus;
//         $input->save();

//         \Log::info("Manual update: order_id={$request->order_id}, midtrans_status={$midtransStatus}, local_status={$localStatus}");

//         return response()->json(['message' => 'Status pembayaran berhasil diperbarui']);
//     }

//     return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);
// }

//     public function manualUpdateStatus(Request $request)
// {
//     $input = Input::find($request->order_id);
//     if ($input) {
//         $input->status_pembayaran = $request->transaction_status ?? 'settlement';
//         // $input->payment_type = $request->payment_type ?? 'manual';
//         $input->save();

//         \Log::info("Manual update: order_id={$request->order_id}, status={$input->status_pembayaran}");
//         return response()->json(['message' => 'Status pembayaran berhasil diperbarui']);
//     }

//     return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);
// }

    // PembayaranController.php
    public function updateStatus(Request $request, $id)
    {
        $input = Input::findOrFail($id);
        $input->status_pembayaran = $request->input('status_pembayaran');
        $input->save();

        return response()->json(['message' => 'Status pembayaran diperbarui.']);
    }

}
