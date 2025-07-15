<?php

namespace App\Http\Controllers;

use App\Services\BinderByteService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TrackingController extends Controller
{
    protected $binderByte;

    public function __construct(BinderByteService $binderByte)
    {
        $this->binderByte = $binderByte;
    }

    /**
     * Track package by courier and resi number
     */
    public function trackPackage(Request $request): JsonResponse
    {
        $request->validate([
            'courier' => 'required|string',
            'resi' => 'required|string'
        ]);

        try {
            $result = $this->binderByte->trackPackage(
                $request->courier,
                $request->resi
            );

            return response()->json([
                'success' => true,
                'message' => 'Package tracking retrieved successfully',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to track package',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get shipping cost
     */
    public function getShippingCost(Request $request): JsonResponse
    {
        $request->validate([
            'origin' => 'required|string',
            'destination' => 'required|string',
            'weight' => 'required|numeric',
            'courier' => 'required|string'
        ]);

        try {
            $result = $this->binderByte->getShippingCost(
                $request->origin,
                $request->destination,
                $request->weight,
                $request->courier
            );

            return response()->json([
                'success' => true,
                'message' => 'Shipping cost retrieved successfully',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get shipping cost',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get list of supported couriers
     */
    public function getCouriers(): JsonResponse
    {
        try {
            $couriers = $this->binderByte->getCouriers();
            
            return response()->json([
                'success' => true,
                'message' => 'Couriers retrieved successfully',
                'data' => $couriers
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve couriers',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}