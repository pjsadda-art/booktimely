<?php

namespace Workdo\Purchases\Database\Seeders;

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
        $module = 'Purchases';

        $permissions = [
            'purchases manage',
            'purchases create',
            'purchases edit',
            'purchases delete',
            'purchase manage',
            'purchase create',
            'purchase edit',
            'purchase delete',
            'purchase show',
            'purchase send',
            'purchase payment create',
            'purchase payment delete',
            'purchase product delete',
            'purchase debitnote create',
            'purchase debitnote edit',
            'purchase debitnote delete',
        ];

        $company_role = Role::where('name', 'company')->first();
        foreach ($permissions as $key => $value) {
            $check = Permission::where('name', $value)->where('module', $module)->exists();
            if ($check == false) {
                $permission = Permission::create(
                    [
                        'name' => $value,
                        'guard_name' => 'web',
                        'module' => $module,
                        'created_by' => 0,
                        "created_at" => date('Y-m-d H:i:s'),
                        "updated_at" => date('Y-m-d H:i:s')
                    ]
                );
                if (!$company_role->hasPermission($value)) {
                    $company_role->givePermission($permission);
                }
            }
        }
    }
}
