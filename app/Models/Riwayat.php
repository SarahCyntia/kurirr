<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Riwayat extends Model
{
    use HasFactory;

    protected $table = 'riwayat';
    protected $primaryKey = 'id_riwayat'; // <- kasih tau PK

    protected $fillable = [
        'id_riwyayat',
        'id',
        'kurir_id',
        'status',
        'deskripsi',
    ];

    /**
     * Relasi ke model Pengiriman.
     */
    public function input()
    {
        return $this->belongsTo(Input::class, 'id');
    }
    public function kurir()
    {
        return $this->belongsTo(Kurir::class, 'id');
    }
}

