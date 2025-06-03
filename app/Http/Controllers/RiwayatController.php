<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Input;
use App\Models\Riwayat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RiwayatController extends Controller
{
    public function store(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|max:255',
            'deskripsi' => 'required|string',
        ]);

        $input = Input::findOrFail($id);

        $input->riwayats()->create([
            'status' => $request->status,
            'deskripsi' => $request->deskripsi,
        ]);

        return redirect()->back()->with('success', 'Riwayat berhasil ditambahkan.');
    }

    public function tambahRiwayat(Request $request,Input $id){
        $request->validate([
            'status' => 'required',
            'riwayat' => 'required'
        ]);

        $id->update([
            'status' => $request->status
        ]);

        Log::info($id );

        $data = Riwayat::create([
            'id' => $id->id,
            'deskripsi' => $request->riwayat
        ]);

        return response()->json($data);

    }

    public function store(Request $request, $id)
    {
        $request->validate([
            'riwayat' => 'required|string',
            'status' => 'required|string',
        ]);

        $order = Order::findOrFail($id);

        // Tambahkan entri baru ke riwayat_pengiriman (asumsinya array atau teks log)
        $existingRiwayat = $order->riwayat_pengiriman ?? [];

        if (!is_array($existingRiwayat)) {
            $existingRiwayat = explode("\n", $existingRiwayat); // fallback kalau disimpan sebagai string
        }

        // Tambahkan riwayat baru ke array
        $existingRiwayat[] = $request->riwayat;

        // Simpan ke database, bisa berupa JSON atau string tergantung field-nya
        $order->riwayat_pengiriman = json_encode($existingRiwayat); // jika pakai JSON di kolom DB
        $order->status = $request->status;
        $order->save();

        return response()->json(['message' => 'Riwayat pengiriman berhasil ditambahkan.']);
    }
}