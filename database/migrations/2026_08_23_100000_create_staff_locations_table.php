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
        if (!Schema::hasTable('staff_locations')) {
            Schema::create('staff_locations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('staff_id');
                $table->unsignedBigInteger('location_id');
                $table->unsignedBigInteger('business_id');
                $table->unsignedBigInteger('created_by')->default(0);
                $table->timestamps();

                $table->foreign('staff_id')->references('id')->on('staff')->onDelete('cascade');
                $table->foreign('location_id')->references('id')->on('locations')->onDelete('cascade');
                $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');

                $table->unique(['staff_id', 'location_id']);
            });
        }

        // Backfill from the legacy CSV `staff.location_id` column so the new
        // pivot starts in sync with existing assignments.
        $validLocationIds = DB::table('locations')->pluck('id')->all();

        DB::table('staff')->select('id', 'location_id', 'business_id', 'created_by')
            ->orderBy('id')
            ->chunk(200, function ($rows) use ($validLocationIds) {
                foreach ($rows as $row) {
                    $ids = array_filter(array_map('trim', explode(',', (string) $row->location_id)), function ($id) use ($validLocationIds) {
                        return $id !== '' && is_numeric($id) && in_array((int) $id, $validLocationIds, true);
                    });

                    foreach (array_unique($ids) as $locationId) {
                        DB::table('staff_locations')->insertOrIgnore([
                            'staff_id' => $row->id,
                            'location_id' => (int) $locationId,
                            'business_id' => $row->business_id,
                            'created_by' => $row->created_by,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_locations');
    }
};
