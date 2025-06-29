<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Province;
use App\Models\Input;
use App\Models\Riwayat;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use illuminate\Support\Str;

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
        $response = Http::withHeaders([
            'key' => config('services.rajaongkir.key')
        ])->post('https://api.rajaongkir.com/starter/cost', [
                    'origin' => $request->origin,
                    'destination' => $request->destination,
                    'weight' => $request->weight, // dalam gram
                    'courier' => $request->courier, // jne / tiki / pos
                ]);

        Log::info("Cost", $response['rajaongkir']);

        $cost = $response['rajaongkir']['results'][0]['costs'];

        return response()->json($cost);
    }


    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $per = $request->input('per', 10);
        $page = $request->input('page', 1);

        DB::statement('SET @no := ' . (($page - 1) * $per));

        $data = Input::with(['asalProvinsi', 'asalKota', 'tujuanProvinsi', 'tujuanKota','riwayat'])
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
            ->when($request->status, function ($query, $status) {
                // Jika parameter 'status' ada, filter berdasarkan status tersebut
                $query->where('status', $status);
            })
            ->when($request->has('exclude_status'), function ($query) use ($request) {
                // Jika parameter 'exclude_status' ada, ambil data yang status-nya bukan nilai itu
                $query->where('status', '!=', $request->exclude_status);
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

    public function get($id)
    {
        $input = Input::with(['asalProvinsi', 'asalKota', 'tujuanProvinsi', 'tujuanKota', 'pengguna'])
            ->findOrFail($id);

        return response()->json($input);
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

    // public function show(Input $input)
    // {
    //     return response()->json($input);
    // }use App\Models\Input;

    public function show($id)
{
    $inputorder = Input::findOrFail($id);
    $riwayat = Riwayat::where('id_riwayat', $id)->orderBy('created_at')->get();

    return response()->json([
        'input' => $inputorder,
        'riwayat' => $riwayat,
    ]);
}
//     public function show($id)
//     {
//         $inputorder = Input::with('riwayat')->findOrFail($id);
//         return response()->json([
//     'success' => true,
//     'data' => $inputorder,
//     'message' => 'Data berhasil diambil',
// ]);

        // return view('inputorder.show', compact('inputorder'));
        // return response()->json(['message' => 'Update berhasil']);

    

    // public function show($id)
    // {
    //     $input = Input::with('riwayat')->findOrFail($id);

    //     return view('inputorder.show', compact('inputorder'));
    // }



    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'biaya' => 'nullable|numeric|min:0',
            'status' => 'nullable|string',
            'riwayat' => 'nullable|string',
            // 'tagnggal_order' => 'nullable|datatime',
            // 'tanggal_dikemas' => 'nullable|datetime',
            // 'tanggal_dikirim' => 'nullable|datetime',
            // 'tanggal_penerimaan' => 'nullable|datetime',
            // 'berat_barang' => 'required|numeric|min:1',
            // 'jarak' => 'nullable|numeric|min:1',
            // 'biaya_pengiriman' => 'required|numeric|min:0',
            // 'kurir_id' => 'nullable|integer|exists:kurirs,id',
        ]);

        $input = Input::findOrFail($id);

        $user = auth()->user();
        $kurirId = $user->kurir->id ?? null; // Asumsi relasi user->kurir

        $input->status = $validated['status'];
        // $input->riwayat = $validated['riwayat'];
        $input->riwayat = $validated['riwayat'] ?? null;
        // $request->riwayat['riwayat];
        $waktuBaru = now()->format('d-m-Y H:i:s');


        // Simpan status dengan waktu sebagai string keterangan (opsional)
        // $input->keterangan_status = $request->status . ' (' . $waktuBaru . ')';
        
    // Update status
    // $input->status = $request->status;

    // ⬇️ LETAKKAN SWITCH DI SINI
    // switch ($request->status) {
    //     case 'menunggu':
    //         if (!$input->tanggal_order) {
    //             $input->tanggal_order = now();
    //         }
    //         break;

    //     case 'dalam proses':
    //         if (!$input->tanggal_dikemas) {
    //             $input->tanggal_dikemas = now();
    //         }
    //         break;

    //     case 'pengambilan paket':
    //         if (!$input->tanggal_pengambilan) {
    //             $input->tanggal_pengambilan = now();
    //         }
    //         break;

    //     case 'dikirim':
    //         if (!$input->tanggal_dikirim) {
    //             $input->tanggal_dikirim = now();
    //         }
    //         break;

    //     case 'selesai':
    //         if (!$input->tanggal_penerimaan) {
    //             $input->tanggal_penerimaan = now();
    //         }
    //         break;
    // }

    $input->save(); // simpan perubahan

    return response()->json([
        'message' => 'Status berhasil diperbarui.',
        // 'data' => $input
        'status' => $input->status,
    ]);
}

// public function cetakResi($noResi) {
//     $data = Input::where('no_resi', $noResi)->first();
//     if (!$data) abort(404);
//     return view('cetak-resi', compact('data'));
// }

//UPDATE

    //     switch ($request->status) {
    //         case 'dalam proses':
    //             $input->tanggal_dikemas = now();
    //             break;

    //         case 'pengambilan paket':
    //             $input->tanggal_pengambilan = now();
    //             break;

    //         case 'dikirim':
    //             $input->tanggal_dikirim = now();
    //             break;

    //         case 'selesai':
    //             $input->tanggal_penerimaan = now();
    //             break;
    //     }
    //     if ($request->status === 'menunggu') {
    // $input->tanggal_menunggu = now();} // buat kolom ini di database



        // $input->berat_barang = $validated['berat_barang'];
        // $input->jarak = $validated['jarak'] ?? $input->jarak;
        // $input->biaya_pengiriman = $validated['biaya_pengiriman'];
        // $input->kurir_id = $validated['kurir_id'] ?? $kurirId;
        // $input->save();

        // return response()->json([
        //     'message' => 'Status berhasil diperbarui',
        //     'status' => $input->status,
        // ]);
    

    // public function get()
    // {
    //     $data = Input::select(
    //         'nama_pengirim',
    //         'alamat_pengirim',
    //         'no_telp_pengirim',
    //         'nama_penerima',
    //         'alamat_penerima',
    //         'no_telp_penerima',
    //         'jenis_barang',
    //         'jenis_layanan',
    //         'berat_barang'
    //     )->get();

    //     return response()->json([
    //         'success' => true,
    //         'data' => $data
    //     ]);
    // }


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
        'item_details' => [[
            'id' => 'ongkir',
            'price' => (int) $validated['biaya'],
            'quantity' => 1,
            'name' => 'Ongkir Kurir',
        ]],
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
}
