<?php

namespace App\Http\Controllers;

use App\Models\Input;
use Illuminate\Http\Request;

class PaketController extends Controller
{
    public function lepasKeGudang(Request $request)
    {
        $request->validate([
            'no_resi' => 'required|string|exists:inputs,no_resi',
        ]);

        $paket = Input::where('no_resi', $request->no_resi)->firstOrFail();

        $paket->update([
            'status' => 'di_gudang',
            'lokasi_sekarang' => 'gudang_sby',
            'kurir_id' => null,
            'waktu_diterima_gudang' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Paket disimpan di gudang']);
    }

    public function ambilDariGudang(Request $request)
    {
        $request->validate([
            'no_resi' => 'required|string|exists:inputs,no_resi',
        ]);

        $paket = Input::where('no_resi', $request->no_resi)
            ->where('status', 'di_gudang')
            ->firstOrFail();

        $paket->update([
            'status' => 'dalam_perjalanan',
            'kurir_id' => $request->user()->id,
            'lokasi_sekarang' => 'dalam_perjalanan',
            'waktu_diambil_kurir' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Paket diambil untuk pengiriman']);
    }

    public function daftarPaketDiGudang()
    {
        $pakets = Input::where('status', 'di_gudang')->get();
        return response()->json($pakets);
    }

    public function updateLokasi(Request $request)
    {
        $request->validate([
            'no_resi' => 'required|string|exists:inputs,no_resi',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $paket = Input::where('no_resi', $request->no_resi)->firstOrFail();

        $paket->update([
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        return response()->json(['success' => true, 'message' => 'Lokasi diperbarui']);
    }

    public function laporanGudang()
    {
        $masuk = Input::whereDate('waktu_diterima_gudang', now()->toDateString())->count();
        $keluar = Input::whereDate('waktu_diambil_kurir', now()->toDateString())->count();
        $sisa = Input::where('status', 'di_gudang')->count();

        return response()->json([
            'masuk' => $masuk,
            'keluar' => $keluar,
            'total_di_gudang' => $sisa,
        ]);
    }

    public function pindahGudang(Request $request)
{
    $request->validate([
        'no_resi' => 'required|string|exists:inputs,no_resi',
        'gudang_tujuan' => 'required|string',
    ]);

    $paket = Input::where('no_resi', $request->no_resi)->firstOrFail();

    if ($paket->status !== 'di_gudang') {
        return response()->json(['message' => 'Paket tidak berada di gudang'], 400);
    }

    $paket->update([
        'gudang_asal' => $paket->lokasi_sekarang,
        'gudang_tujuan' => $request->gudang_tujuan,
        'lokasi_sekarang' => $request->gudang_tujuan,
    ]);

    return response()->json(['success' => true, 'message' => 'Paket berhasil dipindah ke gudang tujuan']);
}


}
