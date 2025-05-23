<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Transaksii extends Model
{
    protected $table = 'transaksii'; // sesuaikan nama tabel
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'no_resi', 'penerima', 'alamat_asal', 'alamat_tujuan',
        'berat_barang', 'ekspedisi', 'layanan', 'biaya', 'waktu',
        'status', 'penilaian', 'komentar',
        'asal_provinsi_id', 'asal_kota_id', 'tujuan_provinsi_id', 'tujuan_kota_id',
        'pengguna_id',
    ];

    public function asalProvinsi() {
        return $this->belongsTo(Province::class, 'asal_provinsi_id');
    }

    public function asalKota() {
        return $this->belongsTo(City::class, 'asal_kota_id');
    }

    public function tujuanProvinsi() {
        return $this->belongsTo(Province::class, 'tujuan_provinsi_id');
    }

    public function tujuanKota() {
        return $this->belongsTo(City::class, 'tujuan_kota_id');
    }

    public function pengguna() {
        return $this->belongsTo(User::class, 'pengguna_id');
    }
}


// class Transaksii extends Model
// {
//     public function provinceOrigin()
//     {
//         return $this->belongsTo(Province::class, 'province_origin_id');
//     }

//     public function cityOrigin()
//     {
//         return $this->belongsTo(City::class, 'city_origin_id');
//     }

//     public function provinceDestination()
//     {
//         return $this->belongsTo(Province::class, 'province_destination_id');
//     }

//     public function cityDestination()
//     {
//         return $this->belongsTo(City::class, 'city_destination_id');
//     }
// }