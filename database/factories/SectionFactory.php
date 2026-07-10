<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SectionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Section ' . $this->faker->unique()->lexify('?'),
        ];
    }
}
