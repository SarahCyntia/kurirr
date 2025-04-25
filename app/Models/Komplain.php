<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Komplain extends Model
{
    use HasFactory;

    protected $fillable = ['customer_id', 'komplain', 'status'];

    public function customer()
    {
        return $this->belongsTo(Pelanggan::class);
    }
}
