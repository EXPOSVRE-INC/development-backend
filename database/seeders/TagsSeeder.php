<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Foundation\Testing\WithFaker;

class TagsSeeder extends Seeder
{
    use WithFaker;

    /**
     * Run the database seeds.
     *
     * @return void
     */

    public function __construct()
    {
        $this->setUpFaker();
        $this->faker->addProvider(new \Xvladqt\Faker\LoremFlickrProvider($this->faker));

    }
    protected $tagsExample = [
        'music',
        'song',
        'guitar',
        'art',
        'culture',
        'modern',
        'sport',
        'football',
        'basketball',
        'swimming'
    ];
    public function run()
    {
        foreach ($this->tagsExample as $tag) {
            $newTag = Tag::findOrCreate($tag);
            $newTag->addMediaFromUrl($this->faker->imageUrl($this->faker->numberBetween($min = 100, $max = 500), $this->faker->numberBetween($min = 100, $max = 500), ['animals']))->toMediaCollection('preview');
            dump($newTag);
        }
    }
}
