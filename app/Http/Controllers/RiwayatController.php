<?php

namespace App\Http\Controllers;

use App\Models\Input;
use App\Models\RiwayatPengiriman;
use Illuminate\Http\Request;

class RiwayatController extends Controller
{
    // Tambahkan riwayat baru ke pengiriman tertentu
    public function store(Request $request, $pengirimanId)
    {
        $request->validate([
            'status' => 'required|string|max:255',
            'keterangan' => 'required|string',
        ]);

        $pengiriman = Pengiriman::findOrFail($pengirimanId);

        $pengiriman->riwayat()->create([
            'status' => $request->status,
            'keterangan' => $request->keterangan,
        ]);

        return response()->json(['message' => 'Riwayat pengiriman berhasil ditambahkan.']);
    }

    // Ambil semua riwayat untuk satu pengiriman
    public function index($inputorderId)
    {
        $pengiriman = Pengiriman::with('riwayat')->findOrFail($pengirimanId);
        return response()->json($pengiriman->riwayat);
    }
}
