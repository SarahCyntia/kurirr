<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengiriman extends Model
{
    use HasFactory;

    protected $table = 'pengiriman';

    protected $fillable = [
        'kode_resi',
        'id_pengirim',
        'id_kurir',
        'alamat_asal',
        'alamat_tujuan',
        'kapasitas',
        'status_pengiriman',
        'tanggal_kirim',
        'tanggal_terima',
    ];
}
