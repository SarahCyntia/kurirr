<?php

namespace App\Http\Controllers;

use App\Services\RajaOngkirService;
use Illuminate\Http\Request;

class RajaOngkirController extends Controller
{
    protected $rajaOngkir;

    public function __construct(RajaOngkirService $rajaOngkir)
    {
        $this->rajaOngkir = $rajaOngkir;
    }

    public function getProvinces()
    {
        return response()->json($this->rajaOngkir->getProvinces());
    }

    public function getCities(Request $request, RajaOngkirService $rajaOngkir)
{
    $provinceId = $request->query('province_id');
    $cities = $rajaOngkir->getCities($provinceId);
    return response()->json($cities);
}
}