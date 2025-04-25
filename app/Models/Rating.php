<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;

    protected $fillable = ['customer_id', 'rating', 'respon'];

    public function customer()
    {
        return $this->belongsTo(Pelanggan::class);
    }
}
