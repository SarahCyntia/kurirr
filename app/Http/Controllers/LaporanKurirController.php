<?php

namespace App\Http\Controllers;

use App\Models\Input;
use Illuminate\Http\Request;
// use App\Models\Paket;

class LaporanKurirController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user(); // kurir login
        $data = Input::where('kurir_id', $user->id)->get();
        return response()->json($data);
    }
}


