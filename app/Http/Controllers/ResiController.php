<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Input;

class ResiController extends Controller
{
    /**
     * Cetak struk berdasarkan No Resi
     */
    public function cetak($noResi)
    {
        $input = Input::where('no_resi', $noResi)->firstOrFail();

        return view('resi.cetak', compact('pengiriman'));
    }
}
