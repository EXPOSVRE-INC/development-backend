<?php

namespace Database\Seeders;

use App\Models\InterestsCategory;
use App\Models\Post;
use App\Models\PostCollection;
use App\Models\User;
use App\Models\UserProfile;
use Database\Factories\PostFactory;
use Illuminate\Database\Seeder;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\Tag;

class PostSeeder extends Seeder
{
    use WithFaker;

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

    public function __construct()
    {
        $this->setUpFaker();
        $this->faker->addProvider(new \Xvladqt\Faker\LoremFlickrProvider($this->faker));

    }

    public function run()
    {
        $users = User::factory()->count(10)->create()->each(function ($user) {
            $user->profile()->save(UserProfile::factory()->make(['user_id' => $user->id]));
            $user->profile->addMediaFromUrl($this->faker->imageUrl($this->faker->numberBetween($min = 500, $max = 1000), $this->faker->numberBetween($min = 500, $max = 1000), ['girl']))->toMediaCollection('preview');
            $user->addMediaFromUrl($this->faker->imageUrl($this->faker->numberBetween($min = 100, $max = 500), $this->faker->numberBetween($min = 100, $max = 500), ['girl']))->toMediaCollection('preview');

            $interests = InterestsCategory::inRandomOrder()->limit(rand(1, 3))->get();

            foreach ($interests as $interest) {
                $user->assignInterest($interest->id);
            }

            $notInterests = InterestsCategory::inRandomOrder()->limit(rand(1, 3))->get();

            foreach ($notInterests as $interest) {
                $user->assignNotInterest($interest->id);
            }

            for ($i = 0; $i < 3; $i++) {

                $userTagName = $this->tagsExample[rand(0, 9)];
                $userTag = Tag::findOrCreate($userTagName);
                $userTag->addMediaFromUrl($this->faker->imageUrl($this->faker->numberBetween($min = 100, $max = 500), $this->faker->numberBetween($min = 100, $max = 500), ['animals']))->toMediaCollection('preview');
                $user->attachTag($userTag);
            }
            $collection = PostCollection::factory()->create();

            $user->posts()->saveMany(Post::factory()->count(10)->create(['owner_id' => $user->id, 'collection_id' => $collection->id])->each(function ($post) {
                $post->addMediaFromUrl($this->faker->imageUrl($this->faker->numberBetween($min = 100, $max = 500), $this->faker->numberBetween($min = 100, $max = 500), ['girl']))->toMediaCollection('preview');
                for ($i = 0; $i < 5; $i++) {
                    $tagName = $this->tagsExample[rand(0, 9)];
                    $tag = Tag::findOrCreate($tagName);
                    $post->attachTag($tag);
                }

                $interests = InterestsCategory::inRandomOrder()->limit(rand(1, 3))->get();

                foreach ($interests as $interest) {
                    $interest->addMediaFromUrl($this->faker->imageUrl($this->faker->numberBetween($min = 100, $max = 500), $this->faker->numberBetween($min = 100, $max = 500), ['animals']))->toMediaCollection('preview');
                    $post->assignInterest($interest->id);
                }
            }));

        });
    }
}
