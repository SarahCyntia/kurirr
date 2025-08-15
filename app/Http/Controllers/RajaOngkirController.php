<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\District;
use App\Models\Province;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RajaOngkirController extends Controller
{
    /**
     * Menampilkan daftar provinsi dari API Raja Ongkir
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Mengambil data provinsi dari API Raja Ongkir
        $response = Http::withHeaders([

            //headers yang diperlukan untuk API Raja Ongkir
            'Accept' => 'application/json',
            'key' => config('rajaongkir.api_key'),

        ])->get('https://rajaongkir.komerce.id/api/v1/destination/province');

        // Memeriksa apakah permintaan berhasil
        if ($response->successful()) {

            // Mengambil data provinsi dari respons JSON
            // Jika 'data' tidak ada, inisialisasi dengan array kosong
            $provinces = $response->json()['data'] ?? [];
        }

        // returning the view with provinces data
        return view('rajaongkir', compact('provinces'));
    }

    /**
     * Mengambil data kota berdasarkan ID provinsi
     *
     * @param int $provinceId
     * @return \Illuminate\Http\JsonResponse
     */

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

    // Ambil daftar kota berdasarkan provinsi

    public function getCities($provinceId)
    {
        $cities = City::where('province_id', $provinceId)->pluck('name', 'id');
        return response()->json($cities);
        
    }

    // public function getCities($provinceId)
    // {
    //     // $response = Http::withHeaders([
    //     //     'key' => env('RAJAONGKIR_API_KEY'),
    //     // ])->get('https://api.rajaongkir.com/starter/city?province=' . $provinceId);

    //     // $cities = collect($response['rajaongkir'])
    //     //     ->pluck('name', 'id');
    //     // $cities = City::all()->pluck('name','id');
    //     $cities = City::where('province_id', $provinceId)->pluck('name', 'id');


    //     return response()->json($cities);
    // }

    public function getDistricts($cityId)
    {
        // $response = Http::withHeaders([
        //     'key' => env('RAJAONGKIR_API_KEY'),
        // ])->get('https://pro.rajaongkir.com/api/subdistrict?city=' . $cityId);

        // $districts = collect($response['rajaongkir']['results'])
        //     ->pluck('name', 'id');
        // $districts = District::all()->pluck('name','id');
        $districts = District::where('city_id', $cityId)->pluck('name', 'id');
        return response()->json($districts);
    }
    // public function getCities($provinceId)
    // {
    //     // Mengambil data kota berdasarkan ID provinsi dari API Raja Ongkir
    //     $response = Http::withHeaders([

    //         //headers yang diperlukan untuk API Raja Ongkir
    //         'Accept' => 'application/json',
    //         'key' => config('rajaongkir.api_key'),

    //     ])->get("https://rajaongkir.komerce.id/api/v1/destination/city/{$provinceId}");

    //     if ($response->successful()) {

    //         // Mengambil data kota dari respons JSON
    //         // Jika 'data' tidak ada, inisialisasi dengan array kosong
    //         return response()->json($response->json()['data'] ?? []);
    //     }
    // }
}