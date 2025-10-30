<?php

namespace App\Http\Controllers;

use App\Models\Kurir;
use App\Http\Requests\StoreKurirRequest;
use App\Http\Requests\UpdateKurirRequest;
use App\Models\Input;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use function Laravel\Prompts\select;

class KurirController extends Controller
{
    /**
     * Get paginated list of kurir
     */
    public function index(Request $request)
    {
        $per = $request->per ?? 10;
        $page = $request->page ? $request->page - 1 : 0;

        DB::statement('set @no=0+' . $page * $per);
        $data = Kurir::select('id', 'name', 'email', 'phone', 'alamat', 'penilaian', 'photo');
        // $data = Kurir::select('id', 'status','alamat', 'penilaian', 'jenis_kendaraan');
        $data = Kurir::with('user')->select('id', 'user_id', 'status', 'alamat', 'penilaian', 'jenis_kendaraan') // Tambahkan relasi user
            ->when($request->search, function ($query, $search) {
                // $query->where('name', 'like', "%$search%")
                $query->where('id', 'like', "%$search%")
                    ->orWhere('status', 'like', "%$search%")
                    ->orWhere('jenis_kendaraan', 'like', "%$search%")
                    ->orWhere('alamat', 'like', "%$search%")
                    ->orWhere('penilaian', 'like', "%$search%");
            })->latest()->paginate($per);

        $no = ($data->currentPage() - 1) * $per + 1;
        foreach ($data as $item) {
            $item->no = $no++;
        }

        return response()->json($data);
    }

    /**
     * Store a newly created kurir
     */
    public function store(StoreKurirRequest $request)
    {
        $validatedData = $request->validated();
        // $validatedData['password'] = Hash::make($validatedData['password']);
        // $validatedData['penilaian'] = $validatedData['penilaian'] ?? 5;

        if ($request->hasFile('photo')) {
            if ($kurir->user->photo) {
                Storage::disk('public')->delete($kurir->user->photo);
            }
            $validatedData['photo'] = $request->file('photo')->store('photo', 'public');
        }

        $kurir = Kurir::create($validatedData);
        $kurir->load('user'); // load relasi user

        return response()->json([
            'success' => true,
            'kurir' => [
                'id' => $kurir->id,
                'alamat' => $kurir->alamat,
                'penilaian' => $kurir->penilaian,
                'jenis_kendaraan' => $kurir->jenis_kendaraan,
                'status' => $kurir->status,
                'user' => [
                    'name' => $kurir->user->name,
                    'email' => $kurir->user->email,
                    'phone' => $kurir->user->phone,
                    'photo' => $kurir->user->photo,
                ],
            ],
        ]);
    }

    /**
     * Show a specific kurir
     */
    public function show(Kurir $kurir)
    {
        $kurir->load('user');

        return response()->json([
            // 'kurir'=> ['alamat' => $kurir->alamat],
            'user' => [
                'name' => $kurir->user->name,
                'email' => $kurir->user->email,
                'phone' => $kurir->user->phone,
                'photo' => $kurir->user->photo,
                'alamat' => $kurir->alamat,
                'jenis_kendaraan' => $kurir->jenis_kendaraan,
                'penilaian' => $kurir->penilaian,
                'status' => $kurir->status,
                // 'password' => $kurir->user->password,
            ],
        ]);
    }

    /**
     * Update an existing kurir
     */
    public function update(UpdateKurirRequest $request, Kurir $kurir)
    {
        $validatedData = $request->validated();

        $validatedData['id'] = $request->input('id');

        if ($request->filled('password')) {
            $validatedData['password'] = Hash::make($validatedData['password']);
        } else {
            unset($validatedData['password']);
        }

        // if ($request->filled('penilaian')) {
        //     $validatedData['penilaian'] = max(1, min(5, $validatedData['penilaian']));
        // }

        if ($request->hasFile('photo')) {
            if ($kurir->user->photo) {
                Storage::disk('public')->delete($kurir->user->photo);
            }
            $validatedData['photo'] = $request->file('photo')->store('photo', 'public');
        }


        $kurir->user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            // 'password' => $request->password,
            'photo' => $validatedData['photo'] ?? $kurir->user->photo,
        ]);

        $kurir->update($validatedData);
        return response()->json([
            'success' => true,
            'kurir' => [
                'id' => $kurir->id,
                // 'name' => $kurir->name,
                // 'email' => $kurir->email,
                // 'phone' => $kurir->phone, tiga tiganya sudah ada di user
                'alamat' => $kurir->alamat,
                'status' => $kurir->status,
                'penilaian' => $kurir->penilaian,
                'jenis_kendaraan' => $kurir->jenis_kendaraan,
            ]
        ]);
    }
    public function get()
    {
        return response()->json([
            'success' => true,
            'data' => Kurir::select('id', 'alamat', 'penilaian', 'jenis_kendaraan', 'status')->get()
            // 'data' => Kurir::select('id', 'name', 'email', 'phone', 'alamat', 'penilaian', 'photo')->get()
        ]);
    }


    public function list()
    {
        $kurir = Kurir::with('user:id,name')->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'text' => $item->user->name,
            ];
        });

        return response()->json([
            'kurir' => $kurir,
        ]);
    }

    public function inputCountByUser()
    {
        $user = auth()->user();
        $kurir = $user->kurir;

        if (!$kurir) {
            return response()->json([
                'menunggu' => 0,
                'dalam proses' => 0,
                'diproses' => 0,
                'dikirm' => 0,
                'selesaai' => 0,
            ]);
        }

        $kurirId = $kurir->kurir_id;

        $menungguCount = Input::where('kurir_id', $kurirId)
            ->where('status', 'menunggu')
            ->whereDate('tanggal_order', Carbon::menunggu())
            ->count();

        $dalamProsesCount = Input::where('kurir_id', $kurirId)
            ->where('status', 'dalam proses')
            ->whereDate('tanggal_dikemas', Carbon::dalamProses())
            ->count();

        $diprosesCount = Input::where('kurir_id', $kurirId)
            ->where('status', 'pengambilan paket')
            ->whereDate('tanggal_diprosespengambilan', Carbon::pengambilanPaket())
            ->count();

        $dikirimCount = Input::where('kurir_id', $kurirId)
            ->where('status', 'dikirim')
            ->whereDate('tanggal_dikirim', Carbon::dikirim())
            ->count();


        $selesaiCount = Input::where('kurir_id', $kurirId)
            ->where('status', 'selesai')
            ->whereMonth('tanggal_penerimaan', Carbon::now()->selesai)
            ->count();


        return response()->json([
            'menunggu' => $menungguCount,
            'dalam proses' => $dalamProsesCount,
            'diproses' => $diprosesCount,
            'dikirim' => $dikirimCount,
            'selesai' => $selesaiCount,
        ]);
    }


    public function destroy(Kurir $kurir)
    {
        // Hapus foto dari storage jika user memiliki foto
        if ($kurir->user && $kurir->user->photo) {
            Storage::disk('public')->delete($kurir->user->photo);
        }

        // Hapus data user yang terkait
        if ($kurir->user) {
            $kurir->user->delete();
        }

        // Hapus data kurir
        $kurir->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data kurir berhasil dihapus'
        ]);
    }


    public function paketRiwayat(Request $request)
    {
        // misalnya pakai auth, ambil id kurir yang sedang login
        $kurirId = $request->user()->kurir->id ?? null;

        if (!$kurirId) {
            return response()->json([
                'message' => 'Kurir tidak ditemukan'
            ], 404);
        }

        // Ambil paket yang ada riwayat sesuai kurir
        $input = Input::with(['riwayat' => function($q) use ($kurirId) {
            $q->where('kurir_id', $kurirId);
        }])->get();

        return response()->json($input);
    }

//     public function paketPernahDitangani(Request $request)
// {
//     $kurirId = auth()->id(); // ambil id user yang login

//     $data = Riwayat::with('paket')
//         ->where('kurir_id', $kurirId)
//         ->get();

//     return response()->json($data);
// }

    public function paketPernahDitangani()
{
    $user = auth()->user();
    $kurir = $user->kurir;

    // Ambil semua paket yang pernah ditangani kurir ini lewat tabel riwayat
    $input = Input::whereHas('riwayat', function($q) use ($kurir) {
            $q->where('kurir_id', $kurir->id);
        })
        ->with(['riwayat' => function($q) use ($kurir) {
            $q->where('kurir_id', $kurir->id)
              ->with('kurir.user');
        }])
        ->orderBy('created_at', 'desc')
        ->get();

    return response()->json($input);
}


//  index podokno index per
//      public function riwayatKurir()
// {
//     $user = auth()->user(); // user yang login
//     $kurir = $user->kurir; // relasi ke tabel kurir

//     $input = Input::with(['riwayat' => function($q) use ($kurir) {
//                     $q->where('kurir_id', $kurir->id);
//                 }, 'riwayat.user.kurir'])
//                 ->whereHas('riwayat', function($q) use ($kurir) {
//                     $q->where('kurir_id', $kurir->id);
//                 })
//                 ->get();

//     return response()->json($input);
// }
}