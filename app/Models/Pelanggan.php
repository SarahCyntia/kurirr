<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pelanggan extends Model
{
    use HasFactory;

    
    protected $fillable = ['user_id', 'alamat', 'keluhan'];
    
    // public function complaints()
    // {
        //     return $this->hasMany(Komplain::class);
        // }
        protected $table = 'pelanggan';

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
