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
        $per = $request->per ?? 10;

        // Ambil parameter 'page', dikurangi 1 agar bisa digunakan untuk penomoran (offset)
        $page = $request->page ? $request->page - 1 : 0;

        // Inisialisasi variabel SQL untuk nomor urut
        DB::statement('SET @no := ' . ($page * $per));

        // Ambil data dari tabel Input dengan filter pencarian dan status jika ada
        $data = Input::when($request->search, function (Builder $query, string $search) {
            // Jika parameter 'search' ada, filter berdasarkan kolom-kolom tertentu
            $query->where('nama_barang', 'like', "%$search%")
                ->orWhere('alamat_asal', 'like', "%$search%")
                ->orWhere('alamat_tujuan', 'like', "%$search%")
                ->orWhere('penerima', 'like', "%$search%")
                ->orWhere('metode_pengiriman', 'like', "%$search%")
                ->orWhere('biaya_pengiriman', 'like', "%$search%")
                ->orWhere('tanggal_input', 'like', "%$search%")
                ->orWhere('tanggal_penerimaan', 'like', "%$search%")
                ->orWhere('nilai', 'like', "%$search%")
                ->orWhere('ulasan', 'like', "%$search%")
                ->orWhere('status', 'like', "%$search%");
        })
            ->when($request->status, function ($query, $status) {
                // Jika parameter 'status' ada, filter berdasarkan status tersebut
                $query->where('status', $status);
            })
            ->when($request->has('exclude_status'), function ($query) use ($request) {
                // Jika parameter 'exclude_status' ada, ambil data yang status-nya bukan nilai itu
                $query->where('status', '!=', $request->exclude_status);
            })
            ->latest() // Urutkan berdasarkan kolom 'created_at' secara descending
            ->paginate($per); // Paginasi berdasarkan nilai $per

        // Tambahkan nomor urut manual berdasarkan halaman saat ini
        $no = ($data->currentPage() - 1) * $per + 1;
        foreach ($data as $item) {
            $item->no = $no++;
        }

        // Kembalikan hasil sebagai JSON
        return response()->json($data);
    }

    public function store(Request $request)
    {
        $Input = $request->validate([
            'nama_barang' => 'required|string|max:255',
            'alamat_asal' => 'required|string|max:255',
            'alamat_tujuan' => 'required|string|max:255',
            'jarak' => 'nullable|numeric|max:255',
            'penerima' => 'required|string|max:255',
            'metode_pengiriman' => 'required|string|max:255',
            'biaya_pengiriman' => 'nullable|string|max:255',
            'nilai' => 'nullable|string|max:255',
            'ulasan' => 'nullable|string|max:255',
            'tanggal_order' => 'nullable|date|before_or_equal:now',
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
            'nilai' => $request->nilai,
            'jarak' => $request->jarak,
            'ulasan' => $request->ulasan,
            'tanggal_order' => now()->format('Y-m-d H:i:s'),
            'id_pelanggan' => $id_pelanggan->id,
        ]);

        // return redirect()->route('input.index')->with('success', 'Input Order berhasil ditambahkan.');
        return response()->json(['message' => 'data berhasil ditambahkan', 'data' => $Input]);
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
            'jarak' => $Input->jarak,
            'biaya_pengiriman' => $Input->biaya_pengiriman,
            'nilai' => $Input->nilai,
            'ulasan' => $Input->ulasan,
            // 'status' => $Input->status,
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

        // if ($transaksi->kurir_id && $transaksi->status !== 'Terkirim') {
        //     return response()->json([
        //         'message' => 'Kurir sudah memiliki order yang sedang diproses.'
        //     ], 400);
        // }
        $user = auth()->user();
        $kurirId = $user->kurir->kurir_id ?? null;

        // Cegah kurir mengambil pesanan yang sudah diambil kurir lain
        // if ($user->role === 'kurir') {
        //     if ($transaksi->kurir_id && $transaksi->kurir_id != $kurirId) {
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
        // $waktuLama = $input->waktu ?? '';
        $input->nilai = $request->nilai;
        $input->ulasan = $request->ulasan;

        // $waktuBaru = now()->format('d-m-Y H:i:s');
        // $statusString = $request->status . ' (' . $waktuBaru . ')';
        // switch ($request->status) {
        //     case 'dalam proses':
        //         $input->tanggal_dikemas = now();
        //         break;
        //     case 'pengambilan paket':
        //         $input->tanggal_pengambilan = now();
        //         break;
        //     case 'dikirim':
        //         $input->tanggal_dikirim = now();
        //         break;
        //     case 'selesai':
        //         $input->tanggal_penerimaan = now();
        //         break;
        // }

        // Ambil waktu saat ini dalam format 'hari-bulan-tahun jam:menit:detik'
        $waktuBaru = now()->format('d-m-Y H:i:s');
        // Gabungkan status permintaan dengan waktu sebagai string keterangan status
        $statusString = $request->status . ' (' . $waktuBaru . ')';
        // Cek status pengiriman yang dikirim dari request
        switch ($request->status) {
            // Jika status adalah 'dalam proses', set tanggal_dikemas dengan waktu saat ini
            case 'dalam proses':
                $input->tanggal_dikemas = now();
                break;

            // Jika status adalah 'pengambilan paket', set tanggal_pengambilan dengan waktu saat ini
            case 'pengambilan paket':
                $input->tanggal_pengambilan = now();
                break;

            // Jika status adalah 'dikirim', set tanggal_dikirim dengan waktu saat ini
            case 'dikirim':
                $input->tanggal_dikirim = now();
                break;

            // Jika status adalah 'selesai', set tanggal_penerimaan dengan waktu saat ini
            case 'selesai':
                $input->tanggal_penerimaan = now();
                break;
        }




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

    // public function updatePenilaian(Request $request, $id)
    // {
    //     $request->validate([
    //         'nilai' => 'nullable|string',
    //         'ulasan' => 'nullable|string',
    //     ]);

    //     $input = Input::findOrFail($id);
    //     $input->nilai = $request->nilai;
    //     $input->ulasan = $request->ulasan;
    //     $input->save();

    //     return response()->json(['message' => 'Penilaian disimpan.']);
    // }
    public function get()
    {
        return response()->json([
            'success' => true,
            'data' => Input::select('nama_barang', 'alamat_asal', 'alamat_tujuan', 'penerima', 'jarak', 'metode_pembayaran', 'biaya_pengiriman', 'status')->get()
        ]);
    }

    public function storePenilaian(Request $request)
    {
        $request->validate([
            'id' => 'nullable|integer|exists:inputorder,id',
            'nilai' => 'nullable|string',
            'ulasan' => 'nullable|string',
        ]);

        $input = Input::find($request->id);
        if (!$input) {
            return response()->json(['message' => 'Input tidak ditemukan'], 404);
        }

        $input->nilai = $request->nilai;
        $input->ulasan = $request->ulasan;
        $input->save();

        return response()->json(['message' => 'Penilaian disimpan.']);
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
