<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pelanggan;

class OrderController extends Controller
{
    // Menampilkan semua data pelanggan
    public function index()
    {
        return Pelanggan::all();
    }

    // Menyimpan pesanan baru dari pelanggan
    public function store(Request $request)
    {
        $request->validate([
            'nama_pelanggan' => 'required|string|max:255',
            // 'nama_toko' => 'required|string|max:255',
            'produk' => 'required|string|max:255',
            'total_harga' => 'required|string|max:255',
            'pengirim' => 'required|string|max:255',
            'penerima' => 'required|string|max:255',
            'alamat' => 'required|string|max:255',
            'status' => 'required|in:dikirim,selesai,dalam proses,dibatalkan',
        ]);

        $pelanggan = Pelanggan::create($request->all());

        return response()->json([
            'message' => 'Pesanan berhasil disimpan',
            'data' => $pelanggan
        ], 201);
    }

    // Optional: Menampilkan 1 data pelanggan tertentu
    public function show($id)
    {
        $data = Pelanggan::findOrFail($id);
        return response()->json($data);
    }

    // Optional: Update data status oleh kurir
    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:dikirim,selesai,dalam proses,dibatalkan',
        ]);

        $pelanggan = Pelanggan::findOrFail($id);
        $pelanggan->update(['status' => $request->status]);

        return response()->json([
            'message' => 'Status pesanan diperbarui',
            'data' => $pelanggan
        ]);
    }

    // Optional: Hapus data
    public function destroy($id)
    {
        Pelanggan::destroy($id);
        return response()->json(['message' => 'Data berhasil dihapus']);
    }
}
