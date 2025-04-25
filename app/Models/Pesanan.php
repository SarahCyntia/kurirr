<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Pesanan extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'pesanan';

    protected $fillable = [
        'uuid', 'nama_pelanggan', 'produk', 'total_harga', 'tanggal_pesan', 'pengirim', 'penerima', 'alamat_pengiriman', 'status'
    ];

    // public function pelanggan(){

    // }
    public function pesanan()
    {
        return $this->belongsTo(Pesanan::class);
    }
}


