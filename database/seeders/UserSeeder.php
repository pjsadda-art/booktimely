<?php

namespace Database\Seeders;

use App\Events\DefaultData;
use App\Models\User;
use App\Models\Business;
use App\Models\Warehouse;
use App\Events\GivePermissionToRole;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Super Admin
        $admin = User::where('type','super admin')->first();

        // Company
        $user = User::where('type','company')->first();
        if(empty($user))
        {
            $company = new User();
            $company->name = 'WorkDo';
            $company->email = 'company@example.com';
            $company->password = Hash::make('1234');
            $company->email_verified_at = date('Y-m-d H:i:s');
            $company->type = 'company';
            $company->active_status = 1;
            $company->active_business = 1;
            $company->avatar = 'uploads/users-avatar/avatar.png';
            $company->dark_mode = 0;
            $company->lang = 'en';
            $company->business_id = 1;
            $company->created_by = $admin->id;
            $company->save();


            $role_r = Role::where('name','company')->first();
            $company->addRole($role_r);

            $data= $company->MakeRole();

            // create  Business
            $business = new Business();
            $business->name = 'WorkDo';
            $business->slug = 'workdo';
            $business->form_type = 'form-layout';
            $business->layouts = 'Formlayout1';
            $business->theme_color = 'color1-Formlayout1';
            $business->created_by = $company->id;
            $business->save();


            $company = User::find($company->id);

            $company->business_id = $business->id;
            $company->active_business = $business->id;
            $company->save();

            // company setting save

            User::CompanySetting($company->id);
        }
        // Warehouse::defaultdata();
    }
}
