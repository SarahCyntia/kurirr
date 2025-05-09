<?php

namespace App\Http\Controllers;

use App\Models\Input;
use Illuminate\Http\Request;
use App\Models\Ordered;
use App\Models\Pelanggan;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Input\Input as InputInput;

class OrderedController extends Controller
{

    // ✅ Middleware auth (jika hanya user login boleh akses)
    public function __construct()
    {
        $this->middleware('auth'); // Hapus jika tidak pakai login
    }
    // Menyimpan pesanan baru dari  input

    public function index(Request $request)
    {
        $per = $request->per ?? 10;
        $page = $request->page ? $request->page - 1 : 0;

        DB::statement('SET @no := ' . ($page * $per));
        // $data = Input::select('nama_barang', 'alamat_asal','alamat_tujuan', 'penerima', 'biaya_pengiriman', 'ststus');
        $data = Input::when($request->search, function (Builder $query, string $search) {
                $query->where('nama_barang', 'like', "%$search%")
                      ->orWhere('alamat_asal', 'like', "%$search%")
                      ->orWhere('alamat_tujuan', 'like', "%$search%")
                      ->orWhere('penerima', 'like', "%$search%")
                      ->orWhere('berat_paket', 'like', "%$search%")
                      ->orWhere('biaya_pengiriman', 'like', "%$search%")
                      ->orWhere('metode_pengiriman', 'like', "%$search%")
                      ->orWhere('penerima', 'like', "%$search%")
                      ->orWhere('jarak', 'like', "%$search%")
                      ->orWhere('status', 'like', "%$search%");
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->has('exclude_status'), function ($query) use ($request) {
                $query->where('status', '!=', $request->exclude_status);
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
    public function store(Request $request)
    {
        $Input = $request->validate([
            'nama_barang' => 'required|string|max:255',
            'alamat_asal' => 'required|string|max:255',
            'alamat_tujuan' => 'required|string|max:255',
            'penerima' => 'required|string|max:255',
            'jarak' => 'nullable|numeric|max:255',
            'biaya_pengiriman' => 'nullable|string|max:255',
            'metode_pengiriman' => 'nullable|string|max:255',
            'berat_paket' => 'nullable|string|max:255',
            'waktu' => 'nullable|date|before_or_equal:now',
            'status' => 'required|string',
        ]);

        $id_pelanggan = Pelanggan::where('user_id', $request->id_user)->first('id');
        Log::info($id_pelanggan->id);
        Input::create([
            'nama_barang' => $request->nama_barang,
            'alamat_asal' => $request->alamat_asal,
            'alamat_tujuan' => $request->alamat_tujuan,
            'penerima' => $request->penerima,
            'biaya_pengiriman' => $request->biaya_pengiriman,
            'metode_pengiriman' => $request->metode_pengiriman,
            'berat_paket' => $request->berat_paket,
            'jarak' => $request->jarak,
            'status' => $request->status,
            'id_pelanggan' =>$id_pelanggan->id,
        ]);
        return response()->json(['message' => 'data berhasil ditambahkan', 'data' => $Input]);
    }
    public function show($Input)
    {
        $data = Input::findOrFail($Input);
        return response()->json([
            'nama_barang' => $data->nama_barang,
            'alamat_asal' => $data->alamat_asal,
            'alamat_tujuan' => $data->alamat_tujuan,
            'penerima' => $data->penerima,
            'biaya_pengiriman' => $data->biaya_pengiriman,
            'metode_pengiriman' => $data->metode_pengiriman,
            'berat_paket' => $data->berat_paket,
            'jarak' => $data->jarak,
            'status' => $data->status,
        ]);
    }

    // Optional: Update data status oleh kurir

    public function update(Request $request, $id)
{
    $request->validate([
        'status' => 'required|string',
        'berat_paket' => 'nullable|string|min:1',
        'jarak' => 'nullable|numeric|min:1',
        'biaya_pengiriman' => 'nullable|numeric|min:0',
    ]);

    $input = Input::where('id', $id)->firstOrFail();

    // if ($transaksi->kurir_id && $transaksi->status !== 'Terkirim') {
    //     return response()->json([
    //         'message' => 'Kurir sudah memiliki order yang sedang diproses.'
    //     ], 400);
    // }
    $user = auth()->user();
    $kurirId = $user->kurir->kurir_id ?? null;

    // Cegah kurir mengambil pesanan yang sudah diambil kurir lain
    // if ($user->role === 'kurir') {
    //     if ($input->kurir_id && $input->kurir_id != $kurirId) {
    //         return response()->json([
    //             'message' => 'Pesanan sudah diambil oleh kurir lain.'
    //         ], 403);
    //     }
    // }
    
    // $kurir = auth()->user()->kurir;

    // // Cek apakah kurir sudah pegang 1 order yang belum selesai
    // $masihAktif = Transaksi::where('kurir_id', $kurir->kurir_id)
    //     ->whereIn('status', ['Penjemputan Barang', 'Sedang Dikirim']) // sesuaikan dengan status aktifmu
    //     ->exists();

    // if ($masihAktif) {
    //     return response()->json([
    //         'message' => 'Kurir hanya bisa mengambil 1 order dalam satu waktu.'
    //     ], 404);
    // }


    // Update kolom waktu dengan status baru dan timestamp
    // $waktuLama = $transaksi->waktu ?? '';
    // $input->penilaian = $request->penilaian;
    // $input->komentar = $request->komentar;
    
    $waktuBaru = now()->format('d-m-Y H:i:s');
    $statusString = $request->status . ' (' . $waktuBaru . ')';
    switch ($request->status) {
        case 'dalam proses':
            $input->tanggal_dikemas = now();
            break;
        case 'pengambilan paket':
            $input->tanggal_pengambilan = now();
            break;
        case 'dikirim':
            $input->tanggal_dikirim = now();
            break;
        case 'selesai':
            $input->tanggal_penerimaan = now();
            break;
    }
    
    
    $input->update([
        'status' => $request->status,
        'berat_paket' => $request->berat_paket,
        'jarak' => $request->jarak,
        'biaya_pengiriman' => $request->biaya_pengiriman,
        // 'kurir_id' => $request->kurir_id,
        // 'kurir_id' => $request->kurir->kurir_id,
        // 'waktu' => $waktuLama ? $waktuLama . '<br>' . $statusString : $statusString,
    ]);
    
    $input->save();
    return response()->json([
        'message' => 'Status berhasil diperbarui',
        'status' => $input->status,
        
    ]);
}

public function destroy(Input $Input)
{

    // Hapus data user yang terkait
    // if ($input->user) {
    //     $input->user->delete();
    // }

    // Hapus data kurir
    $Input->delete();

    return response()->json([
        'success' => true,
        'message' => 'Data pesanan berhasil dihapus'
    ]);
}
}
