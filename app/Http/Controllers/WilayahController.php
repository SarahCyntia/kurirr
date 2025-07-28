<?php

namespace App\Http\Controllers;

use App\Services\BinderByteService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

class WilayahController extends Controller
{
    protected $binderByte;

    public function __construct(BinderByteService $binderByte)
    {
        $this->binderByte = $binderByte;
    }

     public function getProvinsi()
    {
        $response = Http::get('https://api.binderbyte.com/wilayah/provinsi', [
            'api_key' => env('BINDERBYTE_API_KEY'),
        ]);

        if ($response->successful()) {
            return response()->json($response->json());
        } else {
            return response()->json([
                'error' => 'Gagal mengambil data provinsi',
                'detail' => $response->json(),
            ], $response->status());
        }
    }

    // public function getProvinces(): JsonResponse
    // {
    //     try {
    //         $provinces = $this->binderByte->getProvinces();
            
    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Provinces retrieved successfully',
    //             'data' => $provinces
    //         ]);
            
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to retrieve provinces',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }


    public function getCities($provinceId): JsonResponse
    {
        try {
            $cities = $this->binderByte->getCities($provinceId);
            
            return response()->json([
                'success' => true,
                'message' => 'Cities retrieved successfully',
                'data' => $cities
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve cities',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getDistricts($cityId): JsonResponse
    {
        try {
            $districts = $this->binderByte->getDistricts($cityId);
            
            return response()->json([
                'success' => true,
                'message' => 'Districts retrieved successfully',
                'data' => $districts
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve districts',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function getVillages($districtId): JsonResponse
    {
        try {
            $villages = $this->binderByte->getVillages($districtId);
            
            return response()->json([
                'success' => true,
                'message' => 'Villages retrieved successfully',
                'data' => $villages
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve villages',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}