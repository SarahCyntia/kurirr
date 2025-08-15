<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $guarded = [];

    protected $table = 'cities';

    protected $fillable = [
        'id',
        'name',
        'province_id',
    ];
}
