<?php

namespace App\Http\Controllers;

use App\Services\BinderByteService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SmsController extends Controller
{
    protected $binderByte;

    public function __construct(BinderByteService $binderByte)
    {
        $this->binderByte = $binderByte;
    }

    /**
     * Send SMS
     */
    public function sendSms(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'required|string|regex:/^(\+62|62|0)8[1-9][0-9]{6,9}$/',
            'message' => 'required|string|max:160'
        ]);

        try {
            $result = $this->binderByte->sendSms(
                $request->phone,
                $request->message
            );

            return response()->json([
                'success' => true,
                'message' => 'SMS sent successfully',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send SMS',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send WhatsApp Message
     */
    public function sendWhatsApp(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'required|string|regex:/^(\+62|62|0)8[1-9][0-9]{6,9}$/',
            'message' => 'required|string'
        ]);

        try {
            $result = $this->binderByte->sendWhatsApp(
                $request->phone,
                $request->message
            );

            return response()->json([
                'success' => true,
                'message' => 'WhatsApp message sent successfully',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send WhatsApp message',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}