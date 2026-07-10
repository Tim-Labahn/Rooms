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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('section_id')->constrained()->cascadeOnDelete();
            $table->integer('floor')->default(1);
            $table->integer('capacity')->default(1);
            $table->string('status')->default('available');
            $table->text('description')->nullable();

            // Layout and Ordering
            $table->integer('order')->default(0);
            $table->integer('grid_column')->default(0);
            $table->integer('grid_row')->default(0);
            $table->integer('width')->default(1);
            $table->integer('height')->default(1);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
