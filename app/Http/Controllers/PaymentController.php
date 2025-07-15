<?php

namespace App\Http\Controllers;

use App\Models\Input;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notification;
use Midtrans\Config;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Midtrans\Snap;

class PaymentController extends Controller
{
    public function create(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:inputorder,id',
            'customer_first_name' => 'required|string|max:100',
            'customer_email' => 'required|email',
            'item_name' => 'required|string|max:255',
        ]);

        $order = Input::findOrFail($request->id);
        $price = $order->biaya;

        if (!$price || $price <= 0) {
            return response()->json(['error' => 'Harga dari inputorder tidak valid.'], 400);
        }

        $orderId = Str::uuid();

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $price
            ],
            'item_details' => [
                [
                    'price' => $price,
                    'quantity' => 1,
                    'name' => $request->item_name,
                ]
            ],
            'customer_details' => [
                'first_name' => $request->customer_first_name,
                'email' => $request->customer_email,
            ],
            'enabled_payment' => ['credit_card', 'bca_va', 'bni_va', 'bri_va']
        ];

        $auth = base64_encode(env('MIDTRANS_SERVER_KEY') . ':');

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => "Basic $auth",
        ])->post('https://app.sandbox.midtrans.com/snap/v1/transactions', $params);

        if ($response->failed()) {
            return response()->json([
                'error' => 'Gagal memproses ke Midtrans',
                'midtrans_response' => json_decode($response->body(), true)
            ], 500);
        }

        $data = json_decode($response->body());

        $payment = new Payment();
        $payment->order_id = $orderId;
        $payment->status = 'pending';
        $payment->price = $price;
        $payment->customer_first_name = $request->customer_first_name;
        $payment->customer_email = $request->customer_email;
        $payment->item_name = $request->item_name;
        $payment->checkout_link = $data->redirect_url ?? null;
        $payment->save();

        return response()->json($data);
    }

    public function webhook(Request $request)
    {
        $auth = base64_encode(env('MIDTRANS_SERVER_KEY') . ':');

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => "Basic $auth",
        ])->get("https://api.sandbox.midtrans.com/v2/$request->order_id/status");

        $response = json_decode($response->body());

        $payment = Payment::where('order_id', $response->order_id)->firstOrFail();

        if (in_array($payment->status, ['settlement', 'capture'])) {
            return response()->json('Payment has been already processed');
        }

        $payment->status = $response->transaction_status;
        $payment->save();

        return response()->json('success');
    }
    public function store(Request $request)
    {
        $transaksii = $request->validate([
            'penerima' => 'required|string',
            'pengirim' => 'required|string',
            'no_hp_penerima' => 'required|string',
            'tujuan_provinsi_id' => 'required|exists:provinces,id',
            'tujuan_kota_id' => 'required|exists:cities,id',
            'alamat_tujuan' => 'required|string',
            'nama_barang' => 'required|string',
            'berat_barang' => 'required|numeric|min:0.01',
            'ekspedisi' => 'required|string',
            'layanan' => 'required|string',
            'biaya' => 'required|integer',
            'asal_provinsi_id' => 'required|exists:provinces,id',
            'asal_kota_id' => 'required|exists:cities,id',
            'alamat_asal' => 'required|string',
            'waktu' => 'nullable|date|before_or_equal:now',
            'rating' => 'nullable|integer|min:1|max:5',
            'status' => 'nullable|string',
            'komentar' => 'nullable|string',
            'kurir_id' => 'nullable|exists:kurir,kurir_id'
        ]);

        $noResi = 'ABC-' . strtoupper(uniqid());

        // Buat payload pembayaran ke Midtrans
        $payload = [
            'transaction_details' => [
                'order_id' => $noResi,
                'gross_amount' => $request->biaya,
            ],
            'customer_details' => [
                'first_name' => $request->pengirim,
                'phone' => $request->no_hp_penerima,
            ],
            'enabled_payment' => ['gopay', 'bank_transfer'],
        ];

        // Kirim request ke Midtrans
        $midtransResponse = Http::withBasicAuth(env('MIDTRANS_SERVER_KEY'), '')
            ->post('https://api.sandbox.midtrans.com/v2/charge', array_merge($payload, ['payment_type' => 'bank_transfer', 'bank_transfer' => ['bank' => 'bca']]));

        if (!$midtransResponse->ok()) {
            return response()->json(['message' => 'Gagal membuat transaksi di Midtrans'], 500);
        }

        $midtransData = $midtransResponse->json();

        // Tentukan status pembayaran berdasarkan response Midtrans
        $statusPembayaran = $midtransData['transaction_status'] ?? 'pending';

        // Simpan transaksi ke database
        $input = Input::create([
            'no_resi' => $noResi,
            'nama_barang' => $request->nama_barang,
            'berat_barang' => $request->berat_barang,
            'alamat_asal' => $request->alamat_asal,
            'alamat_tujuan' => $request->alamat_tujuan,
            'penerima' => $request->penerima,
            'pengirim' => $request->pengirim,
            'no_hp_penerima' => $request->no_hp_penerima,
            'status' => $request->status,
            'ekspedisi' => $request->ekspedisi,
            'layanan' => $request->layanan,
            'biaya' => $request->biaya,
            'rating' => $request->rating,
            'komentar' => $request->komentar,
            'waktu' => now(),
            'asal_provinsi_id' => $request->asal_provinsi_id,
            'asal_kota_id' => $request->asal_kota_id,
            'tujuan_provinsi_id' => $request->tujuan_provinsi_id,
            'tujuan_kota_id' => $request->tujuan_kota_id,
            'status_pembayaran' => $statusPembayaran,
            'kurir_id' => $request->kurir_id,
        ]);

        return response()->json([
            'message' => 'Input berhasil dibuat',
            'data' => $input,
            'midtrans' => $midtransData // Kirim data Midtrans ke frontend jika perlu
        ]);
    }
    // public function getSnapToken($id)
    // {
    //     // $input = Input::find($id);
    //     $input = Input::where('id', $id)->firstOrFail();
    //     $input->status_pembayaran = 'belum dibayar';
    //     $input->save();

    //     if (!$input) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Input tidak ditemukan'
    //         ], 404);
    //     }



    //     try {
    //         \Midtrans\Config::$serverKey = env('MIDTRANS_SERVER_KEY');
    //         \Midtrans\Config::$isProduction = false;
    //         \Midtrans\Config::$isSanitized = true;
    //         \Midtrans\Config::$is3ds = true;

    //         $params = [
    //             'transaction_details' => [
    //                 'order_id' => $input->id,
    //                 'gross_amount' => (int) $input->biaya ?? 0,
    //             ],
    //             'customer_details' => [
    //                 'first_name' => $input->name,
    //                 'email' => $input->email,
    //             ]
    //         ];

    //         $snapToken = \Midtrans\Snap::getSnapToken($params);

    //         return response()->json(['snap_token' => $snapToken]);
    //     } catch (\Exception $e) {
    //         \Log::error('Midtrans Snap Error: ' . $e->getMessage());
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Gagal membuat payment token: ' . $e->getMessage(),
    //         ], 500);
    //     }
    // }

public function getSnapToken($id)
{
    $input = Input::findOrFail($id);

    // Kalau status pending dan sudah ada token => pakai token lama
    if ($input->status_pembayaran === 'pending' && $input->snap_token) {
        return response()->json([
            'snap_token' => $input->snap_token
        ]);
    }

    // Konfigurasi Midtrans
    Config::$serverKey = config('midtrans.server_key');
    Config::$isProduction = config('midtrans.is_production');
    Config::$is3ds = true;

    // Payload Snap
    $payload = [
        'transaction_details' => [
            'order_id' => $input->id,
            'gross_amount' => $input->biaya, // contoh: total biaya
        ],
        'customer_details' => [
            'first_name' => $input->pengirim,
            'email' => 'dummy@mail.com', // optional
        ],
    ];

    $snapToken = Snap::getSnapToken($payload);

    // Simpan token ke DB
    $input->snap_token = $snapToken;
    $input->save();

    return response()->json([
        'snap_token' => $snapToken
    ]);
}


    public function show($id)
    {
        // Ambil data transaksi
        $input = Input::with(['kurir'])->findOrFail($id);

        // Konfigurasi Midtrans
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = false; // Ganti ke true jika production
        Config::$isSanitized = true;
        Config::$is3ds = true;

        // Buat parameter Snap
        $params = [
            'transaction_details' => [
                'order_id' => $input->id,
                'gross_amount' => $input->biaya, // pastikan ini integer, tanpa titik/koma
            ],
            'customer_details' => [
                'first_name' => $input->name ?? 'User',
                'email' => $input->email ?? 'user@example.com',
            ]
        ];

        // Buat Snap Token
        $snapToken = Snap::getSnapToken($params);

        // Kirim ke view atau langsung redirect
        return view('payment.show', compact('input', 'snapToken'));
    }
    public function handleNotification(Request $request)
    {
        $notification = new Notification();

        $transactionStatus = $notification->transaction_status;
        $orderId = $notification->order_id;

        $input = Input::find($orderId);
        if (!$input) {
            return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);
        }

        $input->status_pembayaran = $transactionStatus;
        $input->save();

        return response()->json(['message' => 'Notifikasi diproses']);
    }
    public function manualUpdateStatus(Request $request)
    {
        $input = Input::find($request->order_id);
        if ($input) {
            $input->status_pembayaran = $request->transaction_status ?? 'settlement';
            // $input->payment_type = $request->payment_type ?? 'manual';
            $input->save();

            Log::info("Manual update: order_id={$request->order_id}, status={$input->status_pembayaran}");
            return response()->json(['message' => 'Status pembayaran berhasil diperbarui']);
        }

        return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);
    }

}
