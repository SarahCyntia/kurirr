<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Input;
use App\Models\Riwayat;

class ResiController extends Controller
{
    /**
     * Cetak struk berdasarkan No Resi
     */
    public function cekResi(Request $request)
{
    $request->validate([
        'no_resi' => 'required|string',
        'ekspedisi' => 'required|string',
    ]);

    $data = Input::where('no_resi', $request->no_resi)
        ->where('ekspedisi', $request->ekspedisi)
        ->first();

    if (!$data) {
        return response()->json(['message' => 'Resi tidak ditemukan.'], 404);
    }

    return response()->json(['data' => $data]);
}

public function show($no_resi)
{
    $inputorder = Input::where('no_resi', $no_resi)->first();

    if (!$inputorder) {
        return response()->json([
            'message' => 'Data tidak ditemukan.'
        ], 404);
    }

    // Ambil riwayat berdasarkan no_resi
    $riwayat = Riwayat::where('no_resi', $no_resi)->orderBy('created_at', 'asc')->get();

    return response()->json([
        'input' => $inputorder,
        'riwayat' => $riwayat,
    ]);
}
public function riwayat()
{
    return $this->hasMany(Riwayat::class, 'no_resi', 'no_resi');
}


//  public function show($id)
// {
//     $inputorder = Input::findOrFail($id);
//     $riwayat = Riwayat::where('id_riwayat', $id)->orderBy('created_at')->get();

//     return response()->json([
//         'input' => $inputorder,
//         'riwayat' => $riwayat,
//     ]);
// }
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
// }
