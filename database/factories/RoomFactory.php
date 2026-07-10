<?php

namespace Database\Factories;

use App\Models\Section;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoomFactory extends Factory
{
    public function definition()
    {
        return [
            'name' => 'Room ' . $this->faker->unique()->numberBetween(100, 999),
            'floor' => $this->faker->numberBetween(1, 3),
            'section_id' => Section::inRandomOrder()->first()?->id ?? Section::create(['name' => 'Default Section'])->id,
            'capacity' => 2,
            'grid_column' => 1,
            'grid_row' => 1,
            'width' => 1,
            'height' => 1,
        ];
    }
}
