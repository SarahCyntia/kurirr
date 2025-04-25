<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Komplain;

class KomplainSeeder extends Seeder
{
    public function run()
    {
        Komplain::insert([
            [
                'customer_id' => 1,
                'komplain' => 'Barang rusak saat diterima',
                'status' => 'Pending',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'customer_id' => 2,
                'komplain' => 'Pesanan terlambat dikirim',
                'status' => 'In Progress',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}

