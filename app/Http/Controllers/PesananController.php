<?php

namespace App\Http\Controllers;

use App\Models\Pesanan;
use Illuminate\Contracts\Database\Eloquent\Builder;
// use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class PesananController extends Controller
{
    // ✅ Ambil semua pesanan
    public function index(Request $request)
    {
        $per = $request->per ?? 10;
        $page = $request->page ? $request->page - 1 : 0;

        DB::statement('SET @no := ' . ($page * $per));

        $data = Pesanan::when($request->search, function (Builder $query, string $search) {
                $query->where('nama_pelanggan', 'like', "%$search%")
                      ->orWhere('produk', 'like', "%$search%")
                      ->orWhere('tanggal_pesan', 'like', "%$search%")
                      ->orWhere('pengirim', 'like', "%$search%")
                      ->orWhere('penerima', 'like', "%$search%")
                      ->orWhere('alamat_pengiriman', 'like', "%$search%")
                      ->orWhere('total_harga', 'like', "%$search%")
                      ->orWhere('status', 'like', "%$search%");
            })
            ->latest()
            ->paginate($per);

        // Tambah nomor urut
        $no = ($data->currentPage() - 1) * $per + 1;
        foreach ($data as $item) {
            $item->no = $no++;
        }

        return response()->json($data);
    }

    // ✅ Tambah pesanan baru
    public function store(Request $request)
    {
        $request->validate([
            'nama_pelanggan' => 'required|string|max:255',
            'produk' => 'required|string',
            'total_harga' => 'required|numeric|min:0',
            'tanggal_pesan' => 'required|date',
            'status' => 'required|in:Dikemas,Dikirim,Selesai'
        ]);

        $pesanan = Pesanan::create([
            'uuid' => Str::uuid(),
            'nama_pelanggan' => $request->nama_pelanggan,
            'produk' => $request->produk,
            'total_harga' => $request->total_harga,
            'tanggal_pesan' => $request->tanggal_pesan,
            'pengirim' => $request->pengirim,
            'penerima' => $request->penerima,
            'alamat_pengiriman' => $request->alamat_pengiriman,
            'status' => $request->status
        ]);

        return response()->json(['message' => 'Pesanan berhasil dibuat', 'data' => $pesanan], 201);
    }

    // ✅ Ambil detail pesanan berdasarkan UUID
    public function show($uuid)
    {
        $pesanan = Pesanan::where('uuid', $uuid)->firstOrFail();
        // return response()->json($pesanan);
        return response()->json([
            // 'kurir'=> ['status' => $kurir->status],
            'pesana' => [
                    'name' => $kurir->user->name,
                    'email' => $kurir->user->email,
                    'phone' => $kurir->user->phone,
                    'photo' => $kurir->user->photo,
                    'status' => $kurir->status,
                    // 'password' => $kurir->user->password,
                ],
            ]);
    }

    // ✅ Update pesanan berdasarkan UUID
    public function update(Request $request, $uuid)
    {
        $request->validate([
            'nama_pelanggan' => 'sometimes|string|max:255',
            'produk' => 'sometimes|string',
            'total_harga' => 'sometimes|numeric|min:0',
            'tanggal_pesan' => 'sometimes|date',
            'pengirim' => 'sometimes|string|max:255',
            'penerima' => 'sometimes|string|max:255',
            'alamat_pengiriman' => 'sometimes|string|max:255',
            'status' => 'sometimes|in:Dikemas,Dikirim,Selesai'
        ]);

        $pesanan = Pesanan::where('uuid', $uuid)->firstOrFail();
        $pesanan->update($request->all());

        return response()->json(['message' => 'Pesanan berhasil diperbarui', 'data' => $pesanan]);
    }

    // ✅ Hapus pesanan berdasarkan UUID
    public function destroy($uuid)
    {
        $pesanan = Pesanan::where('uuid', $uuid)->firstOrFail();
        $pesanan->delete();

        return response()->json(['message' => 'Pesanan berhasil dihapus']);
    }

//     public function show(Pesanan $pesanan)
// {
//     // Contoh: jika kamu punya relasi seperti `pelanggan`, `kurir`, atau `produk`, bisa ambil id-nya
//     $pesanan['pelanggan_id'] = $pesanan->pelanggan?->id;
//     $pesanan['kurir_id'] = $pesanan->kurir?->id;
//     $pesanan['produk_id'] = $pesanan->produk?->id;

//     return response()->json([
//         'data' => $pesanan
//     ]);
// }

}

