<?php

use App\Http\Controllers\Controller;
use App\Models\Input;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class PaymentsController extends Controller
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
            'item_details' => [[
                'price' => $price,
                'quantity' => 1,
                'name' => $request->item_name,
            ]],
            'customer_details' => [
                'first_name' => $request->customer_first_name,
                'email' => $request->customer_email,
            ],
            'enabled_payments' => ['credit_card', 'bca_va', 'bni_va', 'bri_va']
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
}
