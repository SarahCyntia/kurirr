<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Gudang extends Model
{
    use HasFactory;

    protected $table = 'gudang';

    protected $fillable = [
        'no_resi',
        'status',
        'lokasi_sekarang',
        'gudang_asal',
        'gudang_tujuan',
        'kurir_id',
        'waktu_diterima_gudang',
        'waktu_diambil_kurir',
        'latitude',
        'longitude'
    ];

    protected $casts = [
        'waktu_diterima_gudang' => 'datetime',
        'waktu_diambil_kurir' => 'datetime',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7'
    ];

    // Relasi ke User (Kurir)
    public function kurir(): BelongsTo
    {
        return $this->belongsTo(User::class, 'kurir_id');
    }

    // Scope untuk status tertentu
    public function scopeMenunggu($query)
    {
        return $query->where('status', 'menunggu');
    }

    public function scopeDiproses($query)
    {
        return $query->where('status', 'diproses');
    }

    public function scopeSelesai($query)
    {
        return $query->where('status', 'selesai');
    }

    // Accessor untuk format lokasi
    public function getLokasiLengkapAttribute()
    {
        return $this->lokasi_sekarang . ' (' . $this->latitude . ', ' . $this->longitude . ')';
    }

    // Mutator untuk uppercase no_resi
    public function setNoResiAttribute($value)
    {
        $this->attributes['no_resi'] = strtoupper($value);
    }

    // Method helper untuk update status
    public function updateStatus($status, $kurir_id = null)
    {
        $data = ['status' => $status];
        
        if ($status === 'diproses' && $kurir_id) {
            $data['kurir_id'] = $kurir_id;
            $data['waktu_diambil_kurir'] = now();
        }
        
        if ($status === 'diterima') {
            $data['waktu_diterima_gudang'] = now();
        }

        return $this->update($data);
    }

    // Method untuk update lokasi real-time
    public function updateLokasi($latitude, $longitude, $lokasi_sekarang = null)
    {
        return $this->update([
            'latitude' => $latitude,
            'longitude' => $longitude,
            'lokasi_sekarang' => $lokasi_sekarang ?? $this->lokasi_sekarang
        ]);
    }
}