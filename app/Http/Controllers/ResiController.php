<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Input;

class ResiController extends Controller
{
    /**
     * Cetak struk berdasarkan No Resi
     */
    public function cekResi($nomorResi, Request $request)
{
    $ekspedisi = $request->query('kurir'); // contoh: "jne"

    $data = Input::where('no_resi', $nomorResi)
        ->where('kurir', $ekspedisi) // ← ini peka huruf besar kecil
        ->first();
    
    if (!$data) {
        return response()->json(['message' => 'Resi tidak ditemukan.'], 404);
    }

    return response()->json($data);
}

//     public function cek($nomorResi, Request $request)
// {
//     $kurir = $request->query('kurir');

//     if (!$kurir) {
//         return response()->json(['message' => 'kurir tidak ditemukan.'], 400);
//     }

//     $data = Input::where('no_resi', $nomorResi)
//         ->where('kurir', strtolower($kurir)) // pastikan lowercase juga di DB
//         ->first();

//     if (!$data) {
//         return response()->json(['message' => 'Resi tidak ditemukan.'], 404);
//     }

//     return response()->json($data);
// }

    // public function cetak($noResi)
    // {
    //     $input = Input::where('no_resi', $noResi)->firstOrFail();

    //     return view('resi.cetak', compact('input'));
    // }
}
