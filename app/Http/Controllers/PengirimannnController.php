<?php

namespace App\Http\Controllers;

use App\Models\Pengiriman;
use Illuminate\Http\Request;

class PengirimanController extends Controller
{
    /**
     * Tampilkan semua data pengiriman.
     */
    public function index()
    {
        $pengiriman = Pengiriman::all();
        return response()->json($pengiriman);
    }

    /**
     * Simpan data pengiriman baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_resi' => 'required|string|unique:pengiriman',
            'id_pengirim' => 'required|exists:users,id',
            // 'id_penerima' => 'required|exists:users,id',
            'alamat_asal' => 'required|string',
            'alamat_tujuan' => 'required|string',
            'kapasitan' => 'required|string',
            // 'prioritas' => 'required|in:Tinggi,Sedang,Rendah',
            'kapasitas' => 'required|numeric|min:0',
            'tanggal_kirim' => 'required|date',
            'id_kurir' => 'required|exists:users,id',
        ]);

        $pengiriman = Pengiriman::create($validated);
        return response()->json($pengiriman, 201);
    }

    /**
     * Tampilkan detail pengiriman tertentu.
     */
    public function show($id)
    {
        $pengiriman = Pengiriman::findOrFail($id);
        return response()->json($pengiriman);
    }

    /**
     * Perbarui data pengiriman.
     */
    public function update(Request $request, $id)
    {
        $pengiriman = Pengiriman::findOrFail($id);

        $validated = $request->validate([
            'kode_resi' => 'string|unique:pengiriman,kode_resi,' . $id,
            'id_pengirim' => 'exists:users,id',
            'alamat_asal' => 'string',
            'alamat_tujuan' => 'string',
            // 'prioritas' => 'in:Tinggi,Sedang,Rendah',
            'kapasitas' => 'numeric|min:0',
            'status_pengiriman' => 'in:dijadwalkan,dalam_perjalanan,terkirim',
            'tanggal_kirim' => 'date',
            'tanggal_terima' => 'nullable|date',
            'id_kurir' => 'exists:users,id',
        ]);

        $pengiriman->update($validated);
        return response()->json($pengiriman);
    }

    /**
     * Hapus data pengiriman.
     */
    public function destroy($id)
    {
        $pengiriman = $pengiriman::findOrFail($id);
        $pengiriman->delete();

        return response()->json(['message' => 'Data pengiriman berhasil dihapus']);
    }

    /**
     * Ubah status pengiriman menjadi 'terkirim'.
     */
    public function setTerkirim($id)
    {
        $pengiriman = Pengiriman::findOrFail($id);

        $pengiriman->status_pengiriman = 'terkirim';
        $pengiriman->tanggal_terima = now(); // otomatis set tanggal_terima ke hari ini
        $pengiriman->save();

        return response()->json([
            'message' => 'Status pengiriman berhasil diubah menjadi terkirim.',
            'data' => $pengiriman
        ]);
    }
    /**
     * Ubah status pengiriman menjadi 'dalam_perjalanan'.
     */
    public function setDalamPerjalanan($id)
    {
        $pengiriman = Pengiriman::findOrFail($id);

        $pengiriman->status_pengiriman = 'dalam_perjalanan';
        $pengiriman->save();

        return response()->json([
            'message' => 'Status pengiriman berhasil diubah menjadi dalam_perjalanan.',
            'data' => $pengiriman
        ]);
    }


}
