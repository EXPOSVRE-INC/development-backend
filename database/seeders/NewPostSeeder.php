<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Foundation\Testing\WithFaker;

class NewPostSeeder extends Seeder
{
    use WithFaker;

    public function __construct()
    {
        $this->setUpFaker();
        $this->faker->addProvider(new \Xvladqt\Faker\LoremFlickrProvider($this->faker));

    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
    }
}
