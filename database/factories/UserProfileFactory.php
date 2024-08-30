<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class UserProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'firstName' => $this->faker->firstName,
            'lastName' => $this->faker->lastName,
            'birthDate' => $this->faker->dateTimeBetween('-20 years', 'today'),
            'phone' => $this->faker->phoneNumber,
            'jobTitle' => $this->faker->jobTitle,
            'jobDescription' => $this->faker->text(100),
        ];
    }
}
