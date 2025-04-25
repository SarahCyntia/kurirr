<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Traits\HasRoles;

class Kurir extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'penilaian', // ✅ Perbaikan nama field
        'alamat',
        'status',
        'jenis_kendaraan',
    ];

   protected $table = 'kurir';
//    protected $table = 'pengiriman';

public function user()
{
    return $this->belongsTo(User::class);
}  

}