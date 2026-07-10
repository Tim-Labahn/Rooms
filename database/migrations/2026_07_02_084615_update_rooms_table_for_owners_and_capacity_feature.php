<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->string('owner_name')->nullable()->after('name');
            $table->boolean('is_flexible')->default(true)->after('owner_name');
        });

        // Ensure "Capacity" feature exists
        $capacityFeature = DB::table('features')->where('name', 'Capacity')->first();
        if (!$capacityFeature) {
            $capacityFeatureId = DB::table('features')->insertGetId([
                'name' => 'Capacity',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $capacityFeatureId = $capacityFeature->id;
        }

        // Migrate data
        $rooms = DB::table('rooms')->get();
        foreach ($rooms as $room) {
            $exists = DB::table('feature_room')
                ->where('room_id', $room->id)
                ->where('feature_id', $capacityFeatureId)
                ->exists();

            if (!$exists && isset($room->capacity)) {
                DB::table('feature_room')->insert([
                    'room_id' => $room->id,
                    'feature_id' => $capacityFeatureId,
                    'value' => (string)$room->capacity,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn('capacity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->integer('capacity')->nullable()->after('section_id');
        });

        // Try to recover data
        $capacityFeature = DB::table('features')->where('name', 'Capacity')->first();
        if ($capacityFeature) {
            $features = DB::table('feature_room')->where('feature_id', $capacityFeature->id)->get();
            foreach ($features as $f) {
                DB::table('rooms')->where('id', $f->room_id)->update(['capacity' => (int)$f->value]);
            }
        }

        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn(['owner_name', 'is_flexible']);
        });
    }
};
