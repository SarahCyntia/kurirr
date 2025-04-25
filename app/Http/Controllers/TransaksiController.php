<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\transaksi;

class TransaksiController extends Controller
{
    // Menampilkan semua data transaksi
    public function index()
    {
        return transaksi::all();
    }

    // Menyimpan pesanan baru dari transaksi
    public function store(Request $request)
    {
        $request->validate([
            'nama_barang' => 'required|string|max:255',
            'alamat_asal' => 'required|string|max:255',
            'alamat_tujuan' => 'required|string|max:255',
            'pengirim' => 'required|string|max:255',
            'penerima' => 'required|string|max:255',
            'kurir' => 'required|string|max:255',
            'penilaian' => 'required|string|max:255',
        ]);

        $transaksi = transaksi::create($request->all());

        return response()->json([
            'message' => 'Pesanan berhasil disimpan',
            'data' => $transaksi
        ], 201);
    }

    // Optional: Menampilkan 1 data transaksi tertentu
    public function show($id)
    {
        $data = transaksi::findOrFail($id);
        return response()->json($data);
    }

    // Optional: Update data status oleh kurir
    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:dikirim,selesai,dalam proses,dibatalkan',
        ]);

        $transaksi = transaksi::findOrFail($id);
        $transaksi->update(['status' => $request->status]);

        return response()->json([
            'message' => 'Status pesanan diperbarui',
            'data' => $transaksi
        ]);
    }

    // Optional: Hapus data
    public function destroy($id)
    {
        transaksi::destroy($id);
        return response()->json(['message' => 'Data berhasil dihapus']);
    }
}
