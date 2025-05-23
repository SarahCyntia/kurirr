<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    ];

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

}
