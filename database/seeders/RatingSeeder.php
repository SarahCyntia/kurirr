<?php


namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rating;

class RatingSeeder extends Seeder
{
    public function run()
    {
        Rating::insert([
            [
                'customer_id' => 1,
                'rating' => 5,
                'respon' => 'Layanan sangat baik!',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'customer_id' => 2,
                'rating' => 3,
                'respon' => 'Pengiriman agak lama, tapi produk bagus.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
