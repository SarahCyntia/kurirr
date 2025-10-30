<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Input;
use App\Models\Order;
use App\Models\Riwayat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RiwayattController extends Controller
{

    // public function index(Request $request)
    // {
    //     $user = auth()->user();

    //     $data = Input::with('riwayat')
    //         ->when($user->role === 'kurir', function ($query) use ($user) {
    //             $query->where('kurir_id', $user->id);
    //         })
    //         ->get();

    //     return response()->json($data);
    // }

    // public function store(Request $request, $id)
    // {
    //     $request->validate([
    //         'status' => 'required|string|max:255',
    //         'deskripsi' => 'required|string',
    //     ]);

    //     $input = Input::findOrFail($id);

    //     $input->riwayats()->create([
    //         'status' => $request->status,
    //         'deskripsi' => $request->deskripsi,
    //     ]);

    //     return redirect()->back()->with('success', 'Riwayat berhasil ditambahkan.');
    // }

    public function tambahRiwayat(Request $request, Input $id)
    {
        $request->validate([
            'status' => 'required',
            'riwayat' => 'required'
        ]);

        $id->update([
            'status' => $request->status
        ]);
        $kurir = auth()->user()->kurir; // kurir yang login
        

        Log::info($id);

        $data = Riwayat::create([
            'id' => $id->id,
            // 'id' => $input->id,
            'deskripsi' => $request->riwayat,
            'kurir_id'   => $kurir->id,

        ]);

        return response()->json($data);

    }

    public function store(Request $request, $id)
    {
        $request->validate([
            'riwayat' => 'required|string',
            'status' => 'required|string',
        ]);

        $order = Order::findOrFail($id);

        // Tambahkan entri baru ke riwayat_pengiriman (asumsinya array atau teks log)
        $existingRiwayat = $order->riwayat_pengiriman ?? [];

        if (!is_array($existingRiwayat)) {
            $existingRiwayat = explode("\n", $existingRiwayat); // fallback kalau disimpan sebagai string
        }

        // Tambahkan riwayat baru ke array
        $existingRiwayat[] = $request->riwayat;

        // Simpan ke database, bisa berupa JSON atau string tergantung field-nya
        $order->riwayat_pengiriman = json_encode($existingRiwayat); // jika pakai JSON di kolom DB
        $order->status = $request->status;
        $order->save();

        return response()->json(['message' => 'Riwayat pengiriman berhasil ditambahkan.']);
    }

    public function resi()
    {
        return $this->belongsTo(Input::class, 'no_resi', 'no_resi');
    }
    public function show($no_resi)
    {
        $input = Input::where('no_resi', $no_resi)->firstOrFail();
        $riwayat = Riwayat::where('no_resi', $no_resi)->orderBy('created_at')->get();

        return response()->json([
            'input' => $input,
            'riwayat' => $riwayat,
        ]);
    }

}
$data = Input::with('riwayat')->get();
return response()->json($data);
