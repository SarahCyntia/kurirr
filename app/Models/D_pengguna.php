<?php

namespace App\ViewModels;

class D_pengguna
{
    public $pegguna;
    public $statistikHariIni;
    public $statistikTotal;
    public $komplainsTerbaru;

    public function __construct($pengguna, $statistikHariIni, $statistikTotal, $komplainsTerbaru)
    {
        $this->pengguna = $pengguna;
        $this->statistikHariIni = $statistikHariIni;
        $this->statistikTotal = $statistikTotal;
        $this->komplainsTerbaru = $komplainsTerbaru;
    }

    public function toArray()
    {
        return [
            'pengguna' => $this->pengguna,
            'statistik_hari_ini' => $this->statistikHariIni,
            'statistik_total' => $this->statistikTotal,
            'komplains_terbaru' => $this->komplainsTerbaru,
        ];
    }
}
