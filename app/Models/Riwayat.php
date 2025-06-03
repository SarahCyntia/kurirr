<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Riwayat extends Model
{
    use HasFactory;

    protected $table = 'riwayat';

    protected $fillable = [
        'id',
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
}
