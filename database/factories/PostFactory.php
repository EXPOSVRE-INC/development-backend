<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {

        return [
            'title' => $this->faker->company,
            'description' => $this->faker->realText,
            'collection_post' => $this->faker->boolean,
            'post_for_sale' => $this->faker->boolean,
            'collection_id' => null,
            'unlimited_edition' => $this->faker->boolean,
            'limited_addition_number' => $this->faker->randomDigit,
            'physical_item' => $this->faker->boolean,
            'time_sale_from_date' => $this->faker->dateTimeBetween('-1 month', 'yesterday'),
            'time_sale_to_date' => $this->faker->dateTimeBetween('today', '+1 month'),
            'fixed_price' => $this->faker->numberBetween(1, 100),
            'royalties_percentage' => $this->faker->numberBetween(1, 100),
            'allow_to_comment' => $this->faker->boolean,
            'allow_views' => $this->faker->boolean,
            'exclusive_content' => $this->faker->boolean,
            'owner_id' => null,
            'views_count' => $this->faker->randomDigit,
            'likes_count' => $this->faker->randomDigit,
            'order_priority' =>$this->faker->randomDigit,
            'parent_id' => null,
        ];
    }
}
