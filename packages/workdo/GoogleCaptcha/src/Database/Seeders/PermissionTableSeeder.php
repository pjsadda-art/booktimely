<?php

namespace Workdo\GoogleCaptcha\Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;

class PermissionTableSeeder extends Seeder
{
     public function run()
    {
        Model::unguard();
        Artisan::call('cache:clear');
        $module = 'GoogleCaptcha';

        $permissions  = [
            'recaptcha manage',
        ];

        $superAdminRole  = Role::where('name', 'super admin')->first();
        foreach ($permissions as $key => $value) {
            $table = Permission::where('name', $value)->where('module', 'GoogleCaptcha')->exists();
            if (!$table) {
                $data = Permission::create(
                    [
                        'name' => $value,
                        'guard_name' => 'web',
                        'module' => 'GoogleCaptcha',
                        'created_by' => 0,
                        "created_at" => date('Y-m-d H:i:s'),
                        "updated_at" => date('Y-m-d H:i:s')
                    ]
                );
                if (!$superAdminRole->hasPermission($value)) {
                    $superAdminRole->givePermission($data);
                }
            }
        }
    }
}
