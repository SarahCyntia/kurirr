<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class BinderByteService
{
    protected $client;
    protected $apiKey;
    protected $baseUrl;
    protected $timeout;
    protected $cacheTtl;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiKey = config('binderbyte.api_key');
        $this->baseUrl = config('binderbyte.base_url');
        $this->timeout = config('binderbyte.timeout');
        $this->cacheTtl = config('binderbyte.cache_ttl');
    }

    /**
     * Get all provinces in Indonesia
     */
    public function getProvinces()
    {
        return Cache::remember('binderbyte_provinces', $this->cacheTtl, function () {
            return $this->makeRequest('GET', '/wilayah/provinsi', [
                'api_key' => $this->apiKey
            ]);
        });
    }

    /**
     * Get cities by province ID
     */
    public function getCities($provinceId)
    {
        return Cache::remember("binderbyte_cities_{$provinceId}", $this->cacheTtl, function () use ($provinceId) {
            return $this->makeRequest('GET', '/wilayah/kabupaten', [
                'api_key' => $this->apiKey,
                'id_provinsi' => $provinceId
            ]);
        });
    }

    /**
     * Get districts by city ID
     */
    public function getDistricts($cityId)
    {
        return Cache::remember("binderbyte_districts_{$cityId}", $this->cacheTtl, function () use ($cityId) {
            return $this->makeRequest('GET', '/wilayah/kecamatan', [
                'api_key' => $this->apiKey,
                'id_kabupaten' => $cityId
            ]);
        });
    }

    /**
     * Get villages by district ID
     */
    public function getVillages($districtId)
    {
        return Cache::remember("binderbyte_villages_{$districtId}", $this->cacheTtl, function () use ($districtId) {
            return $this->makeRequest('GET', '/wilayah/kelurahan', [
                'api_key' => $this->apiKey,
                'id_kecamatan' => $districtId
            ]);
        });
    }

    /**
     * Track package/resi
     */
    public function trackPackage($courier, $resi)
    {
        return $this->makeRequest('GET', '/cek-resi', [
            'api_key' => $this->apiKey,
            'courier' => $courier,
            'resi' => $resi
        ]);
    }

    /**
     * Get shipping cost
     */
    public function getShippingCost($origin, $destination, $weight, $courier)
    {
        return $this->makeRequest('GET', '/ongkir', [
            'api_key' => $this->apiKey,
            'origin' => $origin,
            'destination' => $destination,
            'weight' => $weight,
            'courier' => $courier
        ]);
    }

    /**
     * Send SMS
     */
    public function sendSms($phone, $message)
    {
        return $this->makeRequest('POST', '/sms/send', [
            'api_key' => $this->apiKey,
            'phone' => $phone,
            'message' => $message
        ]);
    }

    /**
     * Send WhatsApp Message
     */
    public function sendWhatsApp($phone, $message)
    {
        return $this->makeRequest('POST', '/whatsapp/send', [
            'api_key' => $this->apiKey,
            'phone' => $phone,
            'message' => $message
        ]);
    }

    /**
     * Get list of supported couriers
     */
    public function getCouriers()
    {
        return Cache::remember('binderbyte_couriers', $this->cacheTtl, function () {
            return $this->makeRequest('GET', '/couriers', [
                'api_key' => $this->apiKey
            ]);
        });
    }

    /**
     * Make HTTP request to BinderByte API
     */
    protected function makeRequest($method, $endpoint, $params = [])
    {
        try {
            $url = $this->baseUrl . $endpoint;
            
            $options = [
                'timeout' => $this->timeout,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'User-Agent' => 'Laravel-BinderByte/1.0'
                ]
            ];

            if ($method === 'GET') {
                $options['query'] = $params;
            } else {
                $options['json'] = $params;
            }

            $response = $this->client->request($method, $url, $options);
            $body = json_decode($response->getBody()->getContents(), true);
            
            // Log successful requests
            Log::info('BinderByte API Request Success', [
                'method' => $method,
                'endpoint' => $endpoint,
                'status_code' => $response->getStatusCode()
            ]);
            
            return $body;
            
        } catch (RequestException $e) {
            Log::error('BinderByte API Error', [
                'method' => $method,
                'endpoint' => $endpoint,
                'error' => $e->getMessage()
            ]);
            
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $body = json_decode($response->getBody()->getContents(), true);
                
                return [
                    'success' => false,
                    'error' => $body['message'] ?? 'API Error',
                    'status_code' => $response->getStatusCode(),
                    'data' => null
                ];
            }
            
            return [
                'success' => false,
                'error' => 'Connection Error',
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Clear cache for specific or all cached data
     */
    public function clearCache($type = null)
    {
        if ($type) {
            Cache::forget("binderbyte_{$type}");
        } else {
            Cache::flush();
        }
    }
}