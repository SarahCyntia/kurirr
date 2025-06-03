<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\D_penggunaController;
use App\Http\Controllers\KurirController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingController;
use App\Models\Kurir;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PesananController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\KomplainController;
// use App\Http\Controllers\RatingController;
use App\Http\Controllers\PengirimanController;
use App\Http\Controllers\OrderController;
// use App\Models\Komplain;
use App\Models\Pelanggan;
use App\Http\Controllers\AkunController;
use App\Http\Controllers\InputController;
use App\Http\Controllers\OrderedController;
use App\Models\Input;
use App\Http\Controllers\LaporanKurirController;
use App\Http\Controllers\RajaOngkirController;
use App\Http\Controllers\CheckOngkirController;
use App\Http\Controllers\ResiController;
use App\Http\Controllers\OrderanController;
use App\Http\Controllers\CekResiController;
use App\Http\Controllers\RiwayatController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Authentication Route
Route::middleware(['auth', 'json'])->prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->withoutMiddleware('auth');
    Route::delete('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);
});

Route::prefix('setting')->group(function () {
    Route::get('', [SettingController::class, 'index']);
});

Route::middleware(['auth', 'verified', 'json'])->group(function () {
    Route::prefix('setting')->middleware('can:setting')->group(function () {
        Route::post('', [SettingController::class, 'update']);
    });

    Route::prefix('master')->group(function () {
        Route::middleware('can:master-user')->group(function () {
            Route::get('users', [UserController::class, 'get']);
            Route::post('users', [UserController::class, 'index']);
            Route::post('users/store', [UserController::class, 'store']);
            Route::apiResource('users', UserController::class)
                ->except(['index', 'store'])->scoped(['user' => 'uuid']);
        });

        Route::middleware('can:master-role')->group(function () {
            Route::get('roles', [RoleController::class, 'get'])->withoutMiddleware('can:master-role');
            Route::post('roles', [RoleController::class, 'index']);
            Route::post('roles/store', [RoleController::class, 'store']);
            Route::apiResource('roles', RoleController::class)
                ->except(['index', 'store']);
        });

    });

    Route::middleware('can:kurir')->group(function () {
        Route::get('kurir', [KurirController::class, 'get'])->withoutMiddleware('can:kurir');
        Route::post('kurir', [KurirController::class, 'index']);
        Route::post('kurir/store', [KurirController::class, 'store']);
        Route::apiResource('kurir', KurirController::class)
            ->except(['index', 'store']);
        // Route::delete('/kurirs', [KurirController::class, 'destroy']);

    });
    // Route::middleware('auth:sanctum')->get('/kurirs/by-user', [KurirController::class, 'byUser']);

    Route::middleware('can:pelanggan')->group(function () {
        Route::get('pelanggan', [PelangganController::class, 'get'])->withoutMiddleware('can:pelanggan');
        Route::post('pelanggan', [PelangganController::class, 'index']);
        Route::post('pelanggan/store', [PelangganController::class, 'store']);
        Route::apiResource('pelanggan', PelangganController::class)
            ->except(['index', 'store']);
        // Route::get('pelanggan', [PelangganController::class, 'show']);
        // Route::put('pelanggan', [PelangganController::class, 'update']);
        // Route::delete('pelanggan', [PelangganController::class, 'destroy']);
    });

    Route::middleware('can:pesanan')->group(function () {
        Route::get('pesanan', [PesananController::class, 'get'])->withoutMiddleware('can:pesanan');
        Route::post('pesanan', [PesananController::class, 'index']); // Ambil semua pesanan
        Route::post('pesanan/store', [PesananController::class, 'store']); // Tambah pesanan baru
        Route::get('pesanan', [PesananController::class, 'show']); // Detail pesanan
        Route::put('pesanan', [PesananController::class, 'update']); // Update pesanan
        Route::apiResource('pesanan', PesananController::class)
            ->except(['index', 'store']);
        // Route::delete('/pesanan', [PesananController::class, 'destroy']); // Hapus pesanan
    });
    // Route::middleware('can:order')->group(function () {
    //     Route::get('/order', [OrderController::class, 'index']);
    //     Route::post('/order', [OrderController::class, 'store']);
    //     Route::get('/order', [OrderController::class, 'show']);
    //     Route::put('/order', [OrderController::class, 'update']);
    //     Route::delete('/order', [OrderController::class, 'destroy']);
    // });
    Route::middleware('can:input')->group(function () {
        // Route::get('/input', [InputController::class, 'get'])->withoutMiddleware('can:input');
        Route::post('/input', [InputController::class, 'index'])->withoutMiddleware('can:input');
        Route::get('/input', [InputController::class, 'get']);
        // Route::post('/input', [InputController::class, 'index']);
        Route::post('/input/store', [InputController::class, 'store']);
        Route::put('/input', [InputController::class, 'update']);
        // Route::put('/input', [InputController::class, 'update'])->withoutMiddleware('can:input');
        Route::post('/input/stores', [InputController::class, 'storePenilaian']);
        Route::put('/riwayat/store/{id}',[RiwayatController::class,'tambahRiwayat'])->withoutMiddleware('can:input');
        Route::get('/cetak-resi/{no_resi}', [inputController::class, 'cetakResi']);
        Route::get('/provinces', [InputController::class, 'getProvinces']);
        // Route::get('/cities/{provinceId}', [InputController::class, 'getCities']);
        Route::post('/cost', [InputController::class, 'hitungOngkir']);
        Route::get('/Input/{input}',[InputController::class,'show'])->withoutMiddleware('can:input');
        Route::get('/input/{id}', [InputController::class, 'show'])->name('input.show');

    });

    Route::middleware('can:ordered')->group(function () {
        Route::get('/ordered', [OrderedController::class, 'get'])->withoutMiddleware('can:ordered');
        Route::post('/ordered', [OrderedController::class, 'index']);
        Route::post('/ordered/store', [OrderedController::class, 'store']);
        Route::get('/ordered/{Input}', [OrderedController::class, 'show']);
        // Route::put('/ordered', [OrderedController::class, 'update']);
        Route::put('/input/{id}', [InputController::class, 'update']);
    //     Route::get('/orders/all', [OrderedController::class, 'allOrders']);
    //    Route::post('/orders/all', [OrderedController::class, 'allOrders']);
        Route::put('/ordered', [OrderedController::class, 'update'])->withoutMiddleware('can:ordered');
        Route::apiResource('/Ordered', OrderedController::class)
            ->except(['index', 'store']);
    });

    Route::apiResource('pengiriman', PengirimanController::class);
    Route::put('pengiriman/{id}/terkirim', [PengirimanController::class, 'setTerkirim']);
    Route::put('pengiriman/{id}/dalam-perjalanan', [PengirimanController::class, 'setDalamPerjalanan']);

    // Route::middleware('auth:sanctum')->get('/kurirs/by-user', [AkunController::class, 'byUser']);
// Route::middleware('auth:sanctum')->put('/kurirs/by-user', [AkunController::class, 'updateByUser']);

Route::middleware('auth:sanctum')->get('/laporan-kurir', [LaporanKurirController::class, 'index']);
        Route::get('/ordered', [LaporanKurirController::class, 'get'])->withoutMiddleware('can:ordered');



Route::get('/rajaongkir/provinces', [RajaOngkirController::class, 'getProvinces']);
Route::get('/rajaongkir/cities', [RajaOngkirController::class, 'getCities']);

Route::get('/ongkir', 'CheckOngkirController@index');
Route::post('/ongkir', 'CheckOngkirController@check_ongkir');
Route::get('/cities/{province_id}', 'CheckOngkirController@getCities');

Route::get('/provinces', [CheckOngkirController::class, 'getProvinces']);
    Route::get('/cities/{province_id}', [CheckOngkirController::class, 'getCities']);
    Route::post('/ongkir', [CheckOngkirController::class, 'checkOngkir']);


    Route::get('/cetak-resi/{noResi}', [ResiController::class, 'cetak']);


    // Route::middleware('can:orderan')->group(function () {
    //     Route::get('/orderan', [OrderanController::class, 'get'])->withoutMiddleware('can:orderan');
    //     Route::post('/orderan', [OrderanController::class, 'index'])->withoutMiddleware('can:input');;
    //     Route::post('/orderan/store', [OrderanController::class, 'store']);
    //     Route::get('/orderan/{Input}', [OrderanController::class, 'show']);
    //     Route::put('/orderan', [OrderanController::class, 'update']);
    //     Route::put('/orderan', [OrderanController::class, 'update'])->withoutMiddleware('can:orderan');
    //     Route::apiResource('/orderan', OrderanController::class)
    //         ->except(['index', 'store']);
    // });

    

Route::get('/cek-resi/{nomorResi}', [CekResiController::class, 'cekResi']);
Route::get('/resi/{nomorResi}', [CekResiController::class, 'show']);
Route::get('/cek-resi/{noResi}', [CekResiController::class, 'cek']);
Route::get('/cek-resi/{no_resi}', [CekResiController::class, 'cekResi']);
Route::get('/cek-resi', [CekResiController::class, 'cekResi']);
Route::post('/cek-resi', [ResiController::class, 'cek'])->withoutMiddleware(['auth']);



// Route::get('/input/{id}/riwayat', [RiwayatController::class, 'index']);
// Route::post('/input/{id}/riwayat', [RiwayatController::class, 'store']);
Route::post('/input/{id}/riwayat', [RiwayatController::class, 'store'])->name('riwayat.store');




    // Route::middleware('can:kurir')->group(function () {
    // ✅ Menampilkan daftar kurir (GET)
    // Route::get('kurir', [KurirController::class, 'index']);

    // ✅ Menyimpan kurir baru (POST)
    // Route::post('kurir', [KurirController::class, 'store']);

    // ✅ Menampilkan detail kurir berdasarkan ID (GET)
    // Route::get('kurir/{kurir}', [KurirController::class, 'show']);

    // ✅ Memperbarui data kurir berdasarkan ID (PUT)
    // Route::put('kurir/{kurir}', [KurirController::class, 'update']);

    // Route::apiResource('kurir', KurirController::class);
    // ✅ Menghapus kurir berdasarkan ID (DELETE)
    // Route::delete('kurir/{kurir}', [KurirController::class, 'destroy']);
    // });
});
