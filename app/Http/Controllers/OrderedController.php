<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Input;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class OrderedController extends Controller
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
                    ->orWhere('berat_barang', 'like', "%$search%")
                    ->orWhere('riwayat_pengiriman', 'like', "%$search%");
            });
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
        // ->orderBy('created_at', 'desc')
        // ->paginate($per);

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
            'riwayat_pengiriman' => 'nullable|string|max:255',
        ]);

        $input = new Input($validated);
        $input->no_resi = 'RESI-' . now()->timestamp . '-' . rand(1000, 9999);
        $input->status = 'menunggu';
        $input->riwayat_pengiriman = '';
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
        $request->validate([
            'status' => 'required|string',
            'riwayat_pengiriman' => 'nullable|array',
            'riwayat_pengiriman.*' => 'string',
        ]);

        // $validated = $request->validate([
        //     'status' => 'required|string',
        //     'riwayat_pengiriman' => 'nullable|string', // hanya 1 pesan log baru
        // ]);

        $input = Input::findOrFail($id);

        // Ambil dan decode riwayat lama (jika ada)
        $riwayat = json_decode($input->riwayat_pengiriman ?? '[]', true);
          $input = Input::find($id);
$input->status = $request->status;

switch ($request->status) {
    case 'menunggu':
        $input->tanggal_menunggu = now();
        break;
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

        //  $waktuBaru = now()->format('d-m-Y H:i:s');
        // $statusString = $request->status . ' (' . $waktuBaru . ')';
        // switch ($request->status) {
        //     case 'dalam proses':
        //         $input->tanggal_dikemas = now();
        //         break;
        //     case 'dikirim':
        //         $input->tanggal_dikirim = now();
        //         break;
        //     case 'selesai':
        //         $input->tanggal_penerimaan = now();
        //         break;
        // }

        // Tambahkan pesan baru ke array
        if (!empty($validated['riwayat_pengiriman'])) {
            $riwayat[] = [
                'pesan' => $validated['riwayat_pengiriman'],
                'waktu' => now()->format('Y-m-d H:i'),
            ];
        }

        // Simpan status baru dan riwayat yang sudah diperbarui
        // $input->status = $validated['status'];
        $input->riwayat_pengiriman = json_encode($riwayat);
        $input->save();

        return response()->json([
            'message' => 'Status dan riwayat berhasil diperbarui',
            'status' => $input->status,
            'riwayat_pengiriman' => $riwayat
        ]);
    }




    //    public function update(Request $request, $id)
// {

    //     $validated = $request->validate([
//         'status' => 'required|string',
//         'riwayat_pengiriman' => 'nullable|string', // isi = pesan baru$input = Input::findOrFail($id);
//     $data = $request->all();

    //     $existingRiwayat = $input->riwayat_pengiriman ?? [];
//     $newRiwayat = array_merge($existingRiwayat, $data['riwayat_pengiriman'] ?? []);

    //     $data['riwayat_pengiriman'] = $newRiwayat;

    //     $input->update($data);

    //     return response()->json([
//         'message' => 'Data berhasil diperbarui',
//         'data' => $input,
//     ]);
// ]);

    //     $input = Input::findOrFail($id);

    //     // Ambil riwayat sebelumnya dan decode
//     $riwayat = json_decode($input->riwayat_pengiriman ?? '[]', true);

    //     // Tambahkan riwayat baru jika ada input pesan
//     if ($validated['riwayat_pengiriman']) {
//         $riwayat[] = [
//             'pesan' => $validated['riwayat_pengiriman'],
//             'waktu' => now()->format('Y-m-d H:i'),
//         ];
//     }

    //     // Simpan kembali status & riwayat ke database
//     $input->status = $validated['status'];
//     $input->riwayat_pengiriman = json_encode($riwayat);
//     $input->save();

    //     return response()->json([
//         'message' => 'Status dan riwayat berhasil diperbarui',
//         'status' => $input->status,
//         'riwayat_pengiriman' => $riwayat
//     ]);
// }


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
            'berat_barang',
            'riwayat_pengiriman'
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