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

    // ✅ Middleware auth (jika hanya user login boleh akses)
    public function __construct()
    {
        $this->middleware('auth'); // Hapus jika tidak pakai login
    }
    // Menyimpan pesanan baru dari  input

    public function index(Request $request)
    {
        // Ambil parameter 'per' dari request, default 10 jika tidak ada
        $per = $request->input('per', 10);

        // Ambil parameter 'page', default 1 jika tidak ada
        $page = $request->input('page', 1);

        // Hitung offset untuk penomoran baris SQL
        DB::statement('SET @no := ' . (($page - 1) * $per));

        // Ambil data dengan filter pencarian
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
                    ->orWhere('berat_paket', 'like', "%$search%");
            });
        })
            ->orderBy('created_at', 'desc') // Bisa diganti sesuai kebutuhan
            ->paginate($per);

        return response()->json($data);
    }


    public function store(Request $request)
    {
        $Input = $request->validate([
            'nama_pengirim' => 'required|string|max:255',
            'alamat_pengirim' => 'required|string|max:255',
            'no_telp_pengirim' => 'required|numeric|255',
            'nama_penerima' => 'required|string|max:255',
            'alamat_penerima' => 'required|string|max:255',
            'no_telp_penerima' => 'required|numeric|255',
            'jenis_barang' => 'required|string|max:255',
            'jenis_layanan' => 'required|string|max:255',
            'berat_paket' => 'required|numeric|255',
           
        ]);
        $input = new Input($validated);

    // Generate no_resi otomatis
    $input->no_resi = 'RESI-' . now()->timestamp . '-' . rand(1000, 9999);

    $input->status = 'menunggu'; // Set status default jika perlu
    $input->save();

    return response()->json(['message' => 'Input berhasil disimpan', 'data' => $input]);
        // $id_pelanggan = Pelanggan::where('user_id', $request->id_user)->first('id');
        // Log::info($id_pelanggan->id);
        // Input::create([
        //     'nama_pengirim' => $request->nama_pengirim,
        //     'alamat_pengirim' => $request->alamat_pengirim,
        //     'no_telp_pengirim' => $request->no_telp_pengirim,
        //     'nama_penerima' => $request->nama_penerima,
        //     'alamat_penerima' => $request->alamat_penerima,
        //     'no_telp_penerima' => $request->no_telp_penerima,
        //     'jenis_barang' => $request->jenis_barang,
        //     'jenis_layanan' => $request->jenis_layanan,
        //     'berat_paket' => $request->berat_paket,
        // ]);

        // return redirect()->route('input.index')->with('success', 'Input Order berhasil ditambahkan.');
        return response()->json(['message' => 'data berhasil ditambahkan', 'data' => $Input]);
    }

    // Optional: Menampilkan 1 data  input tertentu
    public function show(Input $Input)
    {
        // $data = Input::findOrFail($id);
        return response()->json([
            'nama_pengirim' => $Input->nama_pengirim,
            'alamat_pengirim' => $Input->alamat_pengirim,
            'no_telp_pengirim' => $Input->no_telp_pengirim,
            'nama_penerima' => $Input->nama_penerima,
            'alamat_penerima' => $Input->alamat_penerima,
            'no_telp_penerima' => $Input->no_telp_penerima,
            'jenis_barang' => $Input->jenis_barang,
            'jenis_layanan' => $Input->jenis_layanan,
            'berat_paket' => $Input->berat_paket,
        ]);
    }

    // Optional: Update data status oleh kurir
    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string',
            'berat_paket' => 'required|numeric|min:1',
            'jarak' => 'nullable|numeric|min:1',
            'biaya_pengiriman' => 'required|numeric|min:0',
        ]);

        $input = Input::where('id', $id)->firstOrFail();

        $user = auth()->user();
        $kurirId = $user->kurir->kurir_id ?? null;


        $input->update([
            'status' => $request->status,
            'berat_barang' => $request->berat_barang,
            'biaya_pengiriman' => $request->biaya_pengiriman,
            'jarak' => $request->jarak,
            'biaya' => $request->biaya,
            'kurir_id' => $request->kurir_id,
            // 'kurir_id' => $request->kurir->kurir_id,
            // 'waktu' => $waktuLama ? $waktuLama . '<br>' . $statusString : $statusString,
        ]);

        $input->save();
        return response()->json([
            'message' => 'Status berhasil diperbarui',
            'status' => $input->status,
        ]);
    }

    public function get()
    {
        return response()->json([
            'success' => true,
            'data' => Input::select('nama_pengirim', 'alamat_pengirim', 'no_telp_pengirim', 'nama_penerima', 'alamat_penerima', 'no_telp_penerima', 'jenis_barang', 'jenis_layanan', 'berat_paket')->get()
        ]);
    }
    public function destroy(Input $input)
    {
        $input->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data pesanan berhasil dihapus'
        ]);
    }
}
