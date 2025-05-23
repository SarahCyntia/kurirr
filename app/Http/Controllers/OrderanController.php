<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Input;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class InputController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $per = $request->input('per', 10);
        $page = $request->input('page', 1);

        DB::statement('SET @no := ' . (($page - 1) * $per));

        $data = Input::when($request->search, function (Builder $query, string $search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_pengirim', 'like', "%$search%")
                    ->orWhere('alamat_pengirim', 'like', "%$search%")
                    ->orWhere('no_telp_pengirim', 'like', "%$search%")
                    ->orWhere('nama_penerima', 'like', "%$search%")
                    ->orWhere('alamat_penerima', 'like', "%$search%")
                    ->orWhere('no_telp_penerima', 'like', "%$search%")
                    ->orWhere('jenis_barang', 'like', "%$search%")
                    ->orWhere('jenis_layanan', 'like', "%$search%")
                    ->orWhere('berat_barang', 'like', "%$search%");
            });
        })
        ->orderBy('created_at', 'desc')
        ->paginate($per);

        return response()->json($data);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_pengirim' => 'required|string|max:255',
            'alamat_pengirim' => 'required|string|max:255',
            'no_telp_pengirim' => 'required|string|max:20',
            'nama_penerima' => 'required|string|max:255',
            'alamat_penerima' => 'required|string|max:255',
            'no_telp_penerima' => 'required|string|max:20',
            'jenis_barang' => 'required|string|max:255',
            'jenis_layanan' => 'required|string|max:255',
            'berat_barang' => 'required|numeric|min:1',
        ]);

        $input = new Input($validated);
        $input->no_resi = 'RESI-' . now()->timestamp . '-' . rand(1000, 9999);
        $input->status = 'menunggu';
        $input->save();

        return response()->json([
            'message' => 'Input berhasil disimpan',
            'data' => $input,
        ]);
    }

    public function show(Input $input)
    {
        return response()->json($input);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|string',
            'berat_barang' => 'required|numeric|min:1',
            'jarak' => 'nullable|numeric|min:1',
            'biaya_pengiriman' => 'required|numeric|min:0',
            'kurir_id' => 'nullable|integer|exists:kurirs,id',
        ]);

        $input = Input::findOrFail($id);

        $user = auth()->user();
        $kurirId = $user->kurir->id ?? null; // Asumsi relasi user->kurir

        $input->status = $validated['status'];
        $input->berat_barang = $validated['berat_barang'];
        $input->jarak = $validated['jarak'] ?? $input->jarak;
        $input->biaya_pengiriman = $validated['biaya_pengiriman'];
        $input->kurir_id = $validated['kurir_id'] ?? $kurirId;
        $input->save();

        return response()->json([
            'message' => 'Status berhasil diperbarui',
            'status' => $input->status,
        ]);
    }

    public function get()
    {
        $data = Input::select(
            'nama_pengirim',
            'alamat_pengirim',
            'no_telp_pengirim',
            'nama_penerima',
            'alamat_penerima',
            'no_telp_penerima',
            'jenis_barang',
            'jenis_layanan',
            'berat_barang'
        )->get();

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function destroy(Input $input)
    {
        $input->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data pesanan berhasil dihapus',
        ]);
    }
}
