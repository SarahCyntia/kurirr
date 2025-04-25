<?php

namespace App\Listeners;

use App\Events\UserRoleUpdated;
use App\Models\Kurir;
use App\Models\Pelanggan;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class UserRoleUpdatedListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(UserRoleUpdated $event): void
    {
        $user = $event->user;

        $currentRole = $user->roles()->pluck('name')->first();

        Log::info('Trigger');
        // Jika user menjadi Dokter dan belum ada di tabel dokter, tambahkan
        if ($currentRole === 'kurir' && !$user->kurir) {
            Kurir::create([
                'user_id' => $user->id,
                // 'status' => 'nonaktif', // atur
            ]);  
        }
        if ($currentRole === 'pelanggan' && !$user->pelanggan) {
            Pelanggan::create([
                'user_id' => $user->id,
                // 'status' => 'nonaktif', // atur
            ]);  
        }

        
        // Jika user tidak lagi menjadi Dokter, hapus dari tabel dokter
        if ($currentRole !== 'kurir' && $user->kurir) {
            $user->kurir->delete();
        }
        if ($currentRole !== 'pelanggan' && $user->pelanggan) {
            $user->pelanggan->delete();
        }
    }
}