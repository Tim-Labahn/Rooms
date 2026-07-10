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
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('color')->nullable();

            // Layout fields
            $table->integer('grid_row')->default(1);
            $table->integer('grid_column')->default(1);
            $table->integer('grid_width')->default(1);
            $table->integer('grid_height')->default(1);

            // Hallway fields
            $table->integer('hallway_column')->default(1);
            $table->integer('hallway_row')->default(4);
            $table->integer('hallway_width')->default(6);
            $table->integer('hallway_height')->default(1);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};
