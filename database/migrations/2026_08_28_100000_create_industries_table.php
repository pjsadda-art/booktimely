<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('industries')) {
            Schema::create('industries', function (Blueprint $table) {
                $table->id();
                $table->string('name', 150)->unique();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(1);
                $table->boolean('is_system_default')->default(0);
                $table->unsignedBigInteger('created_by')->default(0);
                $table->timestamps();
            });
        }

        // Bootstrap the three mandatory default industries here so they exist
        // right after migration, even before any seeder runs.
        $defaults = [
            ['id' => 1, 'name' => 'General Booking', 'description' => 'Default industry for all generic businesses'],
            ['id' => 2, 'name' => 'Auto Repair', 'description' => 'Industry for workshops, mechanics, vehicle service centers'],
            ['id' => 3, 'name' => 'Tuitions Academy', 'description' => 'Industry for coaching centers, tutors, academies'],
        ];

        foreach ($defaults as $default) {
            $existing = DB::table('industries')->where('name', $default['name'])->first();

            if ($existing) {
                DB::table('industries')->where('id', $existing->id)->update([
                    'is_system_default' => 1,
                    'is_active' => 1,
                    'updated_at' => now(),
                ]);
                continue;
            }

            DB::table('industries')->insert([
                'id' => $default['id'],
                'name' => $default['name'],
                'description' => $default['description'],
                'is_active' => 1,
                'is_system_default' => 1,
                'created_by' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('industries');
    }
};
