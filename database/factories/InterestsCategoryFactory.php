<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class InterestsCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $name = $this->faker->firstName,
            'slug' => Str::slug($name),
//            'type' => $this->faker->randomElement(['interest', 'not-interest'])
        ];
    }
}
