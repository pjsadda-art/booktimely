<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(NotificationsTableSeeder::class);
        $this->call(EmailTemplates::class);
        $this->call(DepositEmailTemplates::class);
        $this->call(Plans::class);
        $this->call(PermissionTableSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(DefultSetting::class);
        $this->call(LanguageTableSeeder::class);
        $this->call(PackagesName::class);
        // Last: it needs businesses to exist before it can seed per-business
        // settings and the standard status spine.
        $this->call(ModuleDefaultSettings::class);
        // if(module_is_active('AIAssistant')){
        //     $this->call(AIAssistantTemplateListTableSeeder::class);
        // }
    }
}
