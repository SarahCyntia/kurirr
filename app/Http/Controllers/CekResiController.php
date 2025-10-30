<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Input;
use Milon\Barcode\DNS2D;
use Milon\Barcode\DNS1D;


class CekResiController extends Controller
{
    // Cek resi berdasarkan nomor dan kurir
    public function cekResi(Request $request)
{
    $request->validate([
        'no_resi' => 'required|string',
        'ekspedisi' => 'nullable|string',
    ]);

    $data = Input::where('no_resi', $request->no_resi)
        // ->where('ekspedisi', $request->ekspedisi)
        ->with('riwayat')
        ->first();

    if (!$data) {
        return response()->json(['message' => 'Resi tidak ditemukan.'], 404);
    }

    return response()->json(['data' => $data]);
}
//   public function cekResi(Request $request, $nomorResi)
// {
//     $jenisLayanan = strtolower($request->query('kurir')); // ambil query param kurir
    
//     $validKurir = ['jne', 'pos', 'tiki'];
//     if (!in_array($jenisLayanan, $validKurir)) {
//         return response()->json(['message' => 'Kurir tidak valid.'], 400);
//     }

//     $data = Input::where('no_resi', $nomorResi)
//         ->where('jenis_layanan', $jenisLayanan)
//         ->first();

//     if (!$data) {
//         return response()->json(['message' => 'Resi tidak ditemukan.'], 404);
//     }

//     return response()->json($data);
// }




    // Cek resi hanya berdasarkan nomor resi
    public function show(string $nomorResi)
{
    $data = Input::where('no_resi', $nomorResi)->first();

    if (!$data) {
        return response()->json(['message' => 'Resi tidak ditemukan.'], 404);
    }

    // Kirim data yang diinginkan saja
    return response()->json([
        'nama_barang' => $data->nama_barang,
        'nama_pengirim' => $data->nama_pengirim,
        'alamat_pengirim' => $data->no_telepon,
        'no_telepon_pengirim' => $data->no_telepon,
        'nama_penerima' => $data->no_telepon,
        'alamat_penerima' => $data->no_telepon,
        'no_telepon_penerima' => $data->no_telepon,
        'jenis_barang' => $data->no_telepon,
        'jenis_layanan' => $data->no_telepon,
        'berat_barang' => $data->no_telepon,
        'status' => $data->no_telepon,
        // tambahkan field lain jika perlu
    ]);
}

}

    // public function show(string $nomorResi)
    // {
    //     $data = Input::where('nomor_resi', $nomorResi)->first();

    //     if (!$data) {
    //         return response()->json(['message' => 'Resi tidak ditemukan.'], 404);
    //     }

    //     return response()->json($data);
    // }
