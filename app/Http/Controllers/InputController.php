<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Input;
use App\Models\Pelanggan;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Input\Input as InputInput;

class InputController extends Controller
{
    // Menampilkan semua data  input
    // public function index()
    // {
    //     return  input::all();
    // }

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
                      ->orWhere('metode_pengiriman', 'like', "%$search%")
                      ->orWhere('biaya_pengiriman', 'like', "%$search%")
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
            'metode_pengiriman' => 'required|string|max:255',
            'biaya_pengiriman' => 'nullable|string|max:255',
        ]);

        $id_pelanggan = Pelanggan::where('user_id', $request->id_user)->first('id');
        Log::info($id_pelanggan->id);
        Input::create([
            'nama_barang' => $request->nama_barang,
            'alamat_asal' => $request->alamat_asal,
            'alamat_tujuan' => $request->alamat_tujuan,
            'penerima' => $request->penerima,
            'metode_pengiriman' => $request->metode_pengiriman,
            'biaya_pengiriman' => $request->biaya_pengiriman,
            'id_pelanggan' =>$id_pelanggan->id,
        ]);
    
        // return redirect()->route('input.index')->with('success', 'Input Order berhasil ditambahkan.');
        return response()->json(['message' => 'data berhasil ditambahkan', 'data' => $Input]);

        // $input = Input::create($request->all());

        // return response()->json([
        //     'message' => 'Pesanan berhasil disimpan',
        //     'data' => $input
        // ], 201);
    }

    // Optional: Menampilkan 1 data  input tertentu
    public function show(Input $Input)
    {
        // $data = Input::findOrFail($id);
        return response()->json([
            'nama_barang' => $Input->nama_barang,
            'alamat_asal' => $Input->alamat_asal,
            'alamat_tujuan' => $Input->alamat_tujuan,
            'penerima' => $Input->penerima,
            'metode_pengiriman' => $Input->metode_pengiriman,
            'biaya_pengiriman' => $Input->biaya_pengiriman,
            // 'status' => $Input->status,
        ]);
    }

    // Optional: Update data status oleh kurir
    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:dikirim,selesai,dalam proses,dibatalkan,pengambilan paket, menunggu',
        ]);

        $input = Input::findOrFail($id);
        $input->update(['status' => $request->status]);

        return response()->json([
            'message' => 'Status pesanan diperbarui',
            'data' => $input
        ]);
    }
    public function get()
    {
        return response()->json([
            'success' => true,
            'data' => Input::select('nama_barang', 'alamat_asal', 'alamat_tujuan', 'penerima', 'metode_pembayaran', 'biaya_pengiriman', 'status')->get()
        ]);
    }

    // Optional: Hapus data
//     public function destroy($id)
//     {
//         Input::destroy($id);
//         return response()->json(['message' => 'Data berhasil dihapus']);
//     }
// }
public function destroy(Input $input)
{

    // Hapus data user yang terkait
    // if ($input->user) {
    //     $input->user->delete();
    // }

    // Hapus data kurir
    $input->delete();

    return response()->json([
        'success' => true,
        'message' => 'Data pesanan berhasil dihapus'
    ]);
}
}
