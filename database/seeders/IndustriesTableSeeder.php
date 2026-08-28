<?php

namespace Database\Seeders;

use App\Models\Industry;
use Illuminate\Database\Seeder;

class IndustriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $industries = [
            [
                'id' => 1,
                'name' => 'General Booking',
                'description' => 'Default industry for all generic businesses',
            ],
            [
                'id' => 2,
                'name' => 'Auto Repair',
                'description' => 'Industry for workshops, mechanics, vehicle service centers',
            ],
            [
                'id' => 3,
                'name' => 'Tuitions Academy',
                'description' => 'Industry for coaching centers, tutors, academies',
            ],
        ];

        foreach ($industries as $data) {
            $industry = Industry::where('name', $data['name'])->first();

            if ($industry) {
                // Descriptions may be edited by Super Admin later; only refresh
                // the protection flag and active state for the default three.
                $industry->is_system_default = true;
                $industry->is_active = true;
                $industry->save();
                continue;
            }

            Industry::create([
                'id' => $data['id'],
                'name' => $data['name'],
                'description' => $data['description'],
                'is_active' => true,
                'is_system_default' => true,
                'created_by' => 0,
            ]);
        }
    }
}
