<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Input extends Model
{
    
    use HasFactory;
    

    // Nama tabel jika tidak default "inputs"
    protected $table = 'inputorder';
    protected $primaryKey = 'id';
    public $timestamps = false;
    // Kolom yang bisa diisi secara massal
    protected $fillable = [
        'nama_pengirim',
        'alamat_pengirim',
        'no_telp_pengirim',
        'nama_penerima',
        'alamat_penerima',
        'no_telp_penerima',
        'jenis_barang',
        'jenis_layanan',
        'berat_barang',
        'no_resi',
        'ekspedisi', 
        'layanan', 
        'biaya', 
        'waktu',
        'asal_provinsi_id', 
        'asal_kota_id', 
        'tujuan_provinsi_id', 
        'tujuan_kota_id',
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

    // Input.php

// RiwayatPengiriman.php
public function riwayat()
{
    return $this->hasMany(Riwayat::class, 'id');
}
    // public function riwayat(): HasMany
    // {
    //     return $this->hasMany(Riwayat::class, 'id_riwayat', 'id');
    // }
    // public function index()
    // {
    //     $data = Input::with('riwayat')->get(); // eager load relasi 'riwayat'
    //     return response()->json($data);
    // }
}
    // public function pengguna() {
    //     return $this->belongsTo(User::class, 'pengguna_id');
    // }

    // protected $fillable = [
    //     'id_pelanggan',
    //     'nama_barang',
    //     'alamat_asal',
    //     'alamat_tujuan',
    //     'penerima',
    //     'berat_paket',
    //     'tanggal_order',
    //     'tanggal_dikemas',
    //     'tanggal_pengambilan',
    //     'tanggal_dikirim',
    //     'tanggal_penerimaan',
    //     'metode_pengiriman',
    //     'biaya_pengiriman',
    //     'status',
    //     'nilai',
    //     'ulasan',
    //     'jarak',
    // ];

