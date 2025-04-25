<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Pesanan;
use App\Models\Komplain;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class D_penggunaController extends Controller
{
    public function index()
    {
        $user = Auth::user(); // Ambil pengguna yang login

        // Statistik Hari Ini
        $today = Carbon::today();

        $pemesananHariIni = Pesanan::where('user_id', $user->id)
            ->whereDate('created_at', $today)
            ->count();

        $totalHariIni = Pesanan::where('user_id', $user->id)
            ->whereDate('created_at', $today)
            ->sum('total_harga');

        // Statistik Total
        $totalPemesanan = Pesanan::where('user_id', $user->id)->count();
        $totalBelanja = Pesanan::where('user_id', $user->id)->sum('total_harga');

        // Daftar Komplain Pengguna
        $komplains = Komplain::with('pelanggan')
            ->where('pelanggan_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        return response()->json([
            'user' => $user,
            'statistik_hari_ini' => [
                'jumlah_pesanan' => $pemesananHariIni,
                'total' => $totalHariIni,
            ],
            'statistik_total' => [
                'total_pesanan' => $totalPemesanan,
                'total_belanja' => $totalBelanja,
            ],
            'komplains_terbaru' => $komplains,
        ]);
    }
}
