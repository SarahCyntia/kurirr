<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('role_has_permissions')->delete();

        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $menuMaster = ['master', 'master-user', 'master-role'];
        $menuOrder = ['order', 'input', 'data', 'ordered', 'riwayat', 'riwayatt'];
        $menuWebsite = ['website', 'setting','kurir','pesanan','pelanggan', 'transaksi', 'akun', 'orderan', 'cekresi', 'd_pengguna', 'gudang', 'pengiriman'];

        $permissionsByRole = [
            'admin' => ['dashboard', ...$menuMaster, ...$menuWebsite,...$menuOrder],
            'kurir' => ['dashboard', ...$menuWebsite,...$menuOrder],
            'pelanggan' => ['dashboard', ...$menuWebsite,...$menuOrder],
            'gudang' => ['dashboard','input']
        ];

        $insertPermissions = fn ($role) => collect($permissionsByRole[$role])
            ->map(function ($name) {
                $check = Permission::whereName($name)->first();

                if (!$check) {
                    return Permission::create([
                        'name' => $name,
                        'guard_name' => 'api',
                    ])->id;
                }

                return $check->id;
            })
            ->toArray();

        $permissionIdsByRole = [
            'admin' => $insertPermissions('admin'),
            'pelanggan' =>$insertPermissions('pelanggan'),
            'kurir' =>$insertPermissions('kurir'),
            'gudang' =>$insertPermissions('gudang')
        ];

        foreach ($permissionIdsByRole as $role => $permissionIds) {
            $role = Role::whereName($role)->first();

            DB::table('role_has_permissions')
                ->insert(
                    collect($permissionIds)->map(fn ($id) => [
                        'role_id' => $role->id,
                        'permission_id' => $id
                    ])->toArray()
                );
        }
    }
}
