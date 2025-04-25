<?php

namespace App\Http\Controllers;

use App\Models\Komplain;
use Illuminate\Http\Request;

class KomplainController extends Controller
{
    public function index()
    {
        return response()->json(Komplain::with('pelanggan')->get(), 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'pelanggan_id' => 'required|exists:pelanggans,id',
            'komplain' => 'required|string',
            'status' => 'in:Pending,In Progress,Resolved',
        ]);

        $Komplain = Komplain::create($request->all());
        return response()->json($Komplain, 201);
    }

    public function show(Komplain $Komplain)
    {
        return response()->json($Komplain->load('pelanggan'), 200);
    }

    public function update(Request $request, Komplain $Komplain)
    {
        $request->validate([
            'status' => 'in:Pending,In Progress,Resolved',
        ]);

        $Komplain->update($request->all());
        return response()->json($Komplain, 200);
    }

    public function destroy(Komplain $Komplain)
    {
        $Komplain->delete();
        return response()->json(['message' => 'Komplain deleted successfully'], 200);
    }
}
