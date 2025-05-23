<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class RajaOngkirService
{
    protected $key;
    protected $baseUrl;

    public function __construct()
    {
        $this->key = config('services.rajaongkir.key');
        $this->baseUrl = config('services.rajaongkir.base_url');
    }

    public function getProvinces()
    {
        return Http::withHeaders([
            'key' => $this->key
        ])->get($this->baseUrl . '/province')->json('rajaongkir.results');
    }

    public function getCities($provinceId = null)
    {
        $url = $this->baseUrl . '/city';
        if ($provinceId) {
            $url .= '?province=' . $provinceId;
        }

        return Http::withHeaders([
            'key' => $this->key
        ])->get($url)->json('rajaongkir.results');
    }
}