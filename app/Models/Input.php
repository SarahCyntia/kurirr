<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Input extends Model
{
    use HasFactory;

    // Nama tabel jika tidak default "inputs"
    protected $table = 'inputorder';

    // Kolom yang bisa diisi secara massal
    protected $fillable = [
        'id_pelanggan',
        'nama_barang',
        'alamat_asal',
        'alamat_tujuan',
        'penerima',
        'berat_paket',
        'tanggal_order',
        'tanggal_dikemas',
        'tanggal_pengambilan',
        'tanggal_dikirim',
        'tanggal_penerimaan',
        'metode_pengiriman',
        'biaya_pengiriman',
        'status',
    ];

}
