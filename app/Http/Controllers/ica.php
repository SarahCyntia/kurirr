<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Province;
use App\Models\Input;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InputController extends Controller
{
    public function getProvinces()
    {
        // $response = Http::withHeaders([
        //     'key' => env('RAJAONGKIR_API_KEY'),
        // ])->get('https://api.rajaongkir.com/starter/province');

        // $provinces = collect($response['rajaongkir'])
        //     ->pluck('province', 'province_id');
        $provinces = Province::all()->pluck('name', 'id');

        return response()->json($provinces);
    }

    // public function getCities($provinceId)
    // {
    //     $response = Http::withHeaders([
    //         'key' => env('RAJAONGKIR_API_KEY'),
    //     ])->get('https://api.rajaongkir.com/starter/city', [
    //                 'province' => $provinceId
    //             ]);

    //     $cities = collect($response['rajaongkir']['results'])
    //         ->pluck('city_name', 'city_id');

    //     return response()->json($cities);
    // }

    public function getCities($provinceId)
    {
        $response = Http::withHeaders([
            'key' => env('RAJAONGKIR_API_KEY'),
        ])->get('https://api.rajaongkir.com/starter/city?province=' . $provinceId);

        $cities = collect($response['rajaongkir'])
            ->pluck('city_name', 'city_id');

        return response()->json($cities);
    }


//     public function hitungOngkir(Request $request)
// {
//     $response = Http::withHeaders([
//         'key' => config('services.rajaongkir.key')
//     ])->post('https://api.rajaongkir.com/starter/cost', [
//         'origin' => $request->origin,
//         'destination' => $request->destination,
//         'weight' => $request->weight,
//         'courier' => $request->courier,
//     ]);

//     if ($response->successful()) {
//         $results = $response['rajaongkir']['results'] ?? [];

//         if (!empty($results) && isset($results[0]['costs'])) {
//             $costs = $results[0]['costs'];

//             $services = collect($costs)->map(function ($item) {
//                 return [
//                     'service' => $item['service'],
//                     'description' => $item['description'],
//                     'cost' => $item['cost'][0]['value'] ?? 0,
//                     'etd' => $item['cost'][0]['etd'] ?? '',
//                 ];
//             });

//             return response()->json($services);
//         } else {
//             return response()->json(['message' => 'Data layanan tidak tersedia'], 404);
//         }
//     } else {
//         return response()->json(['message' => 'Gagal mengambil data ongkir'], $response->status());
//     }
// }


    public function hitungOngkir(Request $request)
    {
        $response = Http::withHeaders([
            'key' => config('services.rajaongkir.key')
        ])->post('https://api.rajaongkir.com/starter/cost', [
                    'origin' => $request->origin,
                    'destination' => $request->destination,
                    'weight' => $request->weight, // dalam gram
                    'courier' => $request->courier, // jne / tiki / pos
                ]);

        Log::info("Cost", $response['rajaongkir']);

        $cost = $response['rajaongkir']['results'][0]['costs'];

        return response()->json($cost);
    }


    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $per = $request->input('per', 10);
        $page = $request->input('page', 1);

        DB::statement('SET @no := ' . (($page - 1) * $per));
        
        $data = Input::with(['asalProvinsi', 'asalKota', 'tujuanProvinsi', 'tujuanKota',])
        ->when($request->search, function (Builder $query, string $search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_pengirim', 'like', "%$search%")
                    ->orWhere('alamat_pengirim', 'like', "%$search%")
                    ->orWhere('no_telp_pengirim', 'like', "%$search%")
                    ->orWhere('nama_penerima', 'like', "%$search%")
                    ->orWhere('alamat_penerima', 'like', "%$search%")
                    ->orWhere('no_telp_penerima', 'like', "%$search%")
                    ->orWhere('jenis_barang', 'like', "%$search%")
                    ->orWhere('ekspedisi', 'like', "%$search%")
                    ->orWhere('jenis_layanan', 'like', "%$search%")
                    ->orWhere('berat_barang', 'like', "%$search%");
            });
        })
            // ->orderBy('created_at', 'desc')
            // ->paginate($per);
            ->latest()

            // Paginate hasil query
            ->paginate($per);

        $no = ($data->currentPage() - 1) * $per + 1;
        foreach ($data as $item) {
            $item->no = $no++;
        }

        return response()->json($data);
    }

    public function get($id)
    {
        $input = Input::with(['asalProvinsi', 'asalKota', 'tujuanProvinsi', 'tujuanKota', 'pengguna'])
            ->findOrFail($id);

        return response()->json($input);
    }


    public function store(Request $request, $id = null)
    {
        $validated = $request->validate([
            'nama_pengirim' => 'required|string|max:255',
            'tujuan_provinsi_id' => 'required|exists:provinces,id',
            'tujuan_kota_id' => 'required|exists:cities,id',
            'alamat_pengirim' => 'required|string|max:255',
            'no_telp_pengirim' => 'required|string|max:20',

            'nama_penerima' => 'required|string|max:255',
            'asal_provinsi_id' => 'required|exists:provinces,id',
            'asal_kota_id' => 'required|exists:cities,id',
            'alamat_penerima' => 'required|string|max:255',
            'no_telp_penerima' => 'required|string|max:20',
            'jenis_barang' => 'required|string|max:255',
            'ekspedisi' => 'required|string',
            'jenis_layanan' => 'required|string|max:255',
            'biaya' => 'required|integer',
            'berat_barang' => 'required|numeric|min:1',

            'waktu' => 'nullable|date',
            'nilai' => 'nullable|integer|min:1|max:5',
            'status' => 'required|in:menunggu,dalam proses,dikirim,selesai',
            'ulasan' => 'nullable|string',
        ]);

        $input = $id ? Input::findOrFail($id) : new Input();

        $input->fill($validated);

        $input = new Input($validated);
        $input->no_resi = 'RESI-' . now()->timestamp . '-' . rand(1000, 9999);
        $input->status = 'menunggu';
        $input->save();

        if (!$id && empty($input->waktu)) {
            $input->waktu = now()->format('Y-m-d H:i:s');
        }

        $input->save();

        return response()->json(['message' => 'Berhasil menambahkan input', 'data' => $input]);
        // return response()->json([
        //     'message' => 'Input berhasil disimpan',
        //     'data' => $input,
        // ]);
    }

    public function show(Input $input)
    {
        return response()->json($input);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'nullable|string',
            'riwayat_pengiriman' => 'nullable|string',
            // 'berat_barang' => 'required|numeric|min:1',
            // 'jarak' => 'nullable|numeric|min:1',
            // 'biaya_pengiriman' => 'required|numeric|min:0',
            // 'kurir_id' => 'nullable|integer|exists:kurirs,id',
        ]);

        $input = Input::findOrFail($id);

        $user = auth()->user();
        $kurirId = $user->kurir->id ?? null; // Asumsi relasi user->kurir

        $input->status = $validated['status'];
        $input->riwayat_pengiriman = $validated['riwayat_pengiriman'];
        // $input->berat_barang = $validated['berat_barang'];
        // $input->jarak = $validated['jarak'] ?? $input->jarak;
        // $input->biaya_pengiriman = $validated['biaya_pengiriman'];
        // $input->kurir_id = $validated['kurir_id'] ?? $kurirId;
        $input->save();

        return response()->json([
            'message' => 'Status berhasil diperbarui',
            'status' => $input->status,
        ]);
    }

    // public function get()
    // {
    //     $data = Input::select(
    //         'nama_pengirim',
    //         'alamat_pengirim',
    //         'no_telp_pengirim',
    //         'nama_penerima',
    //         'alamat_penerima',
    //         'no_telp_penerima',
    //         'jenis_barang',
    //         'jenis_layanan',
    //         'berat_barang'
    //     )->get();

    //     return response()->json([
    //         'success' => true,
    //         'data' => $data
    //     ]);
    // }

    public function destroy(Input $input)
    {
        $input->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data pesanan berhasil dihapus',
        ]);
    }
}
