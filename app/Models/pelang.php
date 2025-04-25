<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pelanggan extends Model
{
    use HasFactory;

    protected $table = 'pelanggan';
    
    protected $fillable = ['nama', 'phone', 'email', 'alamat', 'keluhan'];

    public function complaints()
    {
        return $this->hasMany(Komplain::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }
}
