<?php

namespace App\Http\Controllers;

use App\Services\BinderByteService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $binderByte;

    public function __construct(BinderByteService $binderByte)
    {
        $this->binderByte = $binderByte;
    }

    /**
     * Show dashboard with BinderByte integration
     */
    public function index()
    {
        $provinces = $this->binderByte->getProvinces();
        $couriers = $this->binderByte->getCouriers();
        
        return view('dashboard', compact('provinces', 'couriers'));
    }

    /**
     * Show tracking page
     */
    public function tracking()
    {
        $couriers = $this->binderByte->getCouriers();
        return view('tracking', compact('couriers'));
    }

    /**
     * Show SMS page
     */
    public function sms()
    {
        return view('sms');
    }


    //UBAH SEMUA MENJADI VUE DAN CONTROLLERNYA ARAHKAN MENGGUNAKAN API 
    // TEMPLATE.BLADE UAH MENJADI VUE BESERTA CONTROLLERNYA
}