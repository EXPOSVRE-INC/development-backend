<?php

namespace Database\Seeders;

use App\Models\InterestsCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class InterestsCategorySeeder extends Seeder
{
    protected $generalCategories = [
        'Art',
        'Entertainment',
        'Film',
        'Music',
        'Photography',
        'PodCast',
        'TV',
        'Video',
        'Words',
    ];

    protected $artCategories = [
        'Abstract Expressionism',
        'Architecture',
        'Art Nouveau',
        'Conceptual Art',
        'Contemporary Art',
        'Cubism',
        'Digital Art',
        'Drawing',
        'Expressionism',
        'Fine Art',
        'Impressionism',
        'Landscape Painting',
        'Art Music',
        'Nudity Art',
        'Painting',
        'Photo Realism',
        'Pop Art',
        'Sculpture',
        'Sports Art',
        'Surrealism',
        'Video Art',
    ];
    protected $entertainmentCategories = [
        'Entertainment Film',
        'Entertainment TV',
        'Entertainment Music',
        'Entertainment Nudity',
        'PodCast',
        'Entertainment Video',
        'Entertainment Sports',
    ];

    protected $filmCategories = [
        'Action',
        'Animated',
        'Biographical',
        'Comedy',
        'Documentary',
        'Drama',
        'Fantasy',
        'Feature',
        'Film Adaptations',
        'Historical',
        'Horror',
        'Mystery',
        'Nudity Film',
        'Romance',
        'Short',
        'Silent',
        'Sports Film',
        'Thriller',
    ];

    protected $musicCategories = [
        'Alternative',
        'Country',
        'Disco',
        'EDM',
        'Funk',
        'Hip Hop',
        'Indie',
        'Jazz',
        'Latin',
        'Pop',
        'Rap',
        'R&B',
        'World',
        'Reggae',
        'Rock',
    ];

    protected $photoCategories = [
        'Abstract',
        'Aerial',
        'Architectural',
        'Black and White',
        'Conceptual',
        'Fashion & Beauty',
        'Landscape',
        'Mobile',
        'Nudity Photo',
        'Portrait',
        'Sports Photo',
        'Still Life',
        'Travel',
        'Wildlife',
    ];

    protected $entertainmentPodcastCategories = [
        'Art PodCast',
        'Business',
        'Education',
        'Entertainment PodCast',
        'Fashion PodCast',
        'Fiction PodCast',
        'News PodCast',
        'Pop Culture PodCast',
        'Society PodCast',
        'Spirituality PodCast',
        'Sports PodCast',
        'Technology PodCast',
        'The Arts PodCast',
        'True Crime PodCast',
    ];

    protected $TVCategories = [
        'Art TV',
        'Business TV',
        'Comedy TV',
        'Education TV',
        'Entertainment',
        'Fashion TV',
        'Fiction TV',
        'Music TV',
        'News TV',
        'Nudity TV',
        'Pop Culture TV',
        'Society TV',
        'Spirituality TV',
        'Sports TV',
        'Technology TV',
        'The Arts TV',
        'True Crime TV',
    ];

    protected $videoCategories = [
        'Animation',
        'Film Video',
        'Live Action',
        'Live Streaming',
        'Music Video',
        'Nudity Video',
        'Sports Video',
    ];

    protected $wordsCategories = [
        'Biography',
        'Coffee Table',
        'Fantasy Words',
        'Fashion Words',
        'Fiction Words',
        'Historical Words',
        'Horror Words',
        'Memoir',
        'Music Words',
        'Mystery Words',
        'Poetry',
        'Romance Words',
        'Science Fiction',
        'Script',
        'Short Story',
        'Sports Words',
        'Thriller Words',
        'True Crime Words'
    ];










    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $generalCategories = [
            ['name' => 'Art', 'sub' => $this->artCategories],
            ['name' => 'Entertainment', 'sub' => $this->entertainmentCategories],
            ['name' => 'Film', 'sub' => $this->filmCategories],
            ['name' => 'Music', 'sub' => $this->musicCategories],
            ['name' => 'Photography', 'sub' => $this->photoCategories],
            ['name' => 'PodCast', 'sub' => $this->entertainmentPodcastCategories],
            ['name' => 'TV', 'sub' => $this->TVCategories],
            ['name' => 'Video', 'sub' => $this->videoCategories],
            ['name' => 'Words', 'sub' => $this->wordsCategories],
        ];

        foreach ($generalCategories as $generalKey => $generalCategory) {
            $findGenCat = InterestsCategory::where(['slug' => Str::slug($generalCategory['name'])])->first();
//            dump($findGenCat);
                InterestsCategory::factory(1)->create(['name' => $generalCategory['name'], 'slug' => (!$findGenCat) ? Str::slug($generalCategory['name']) : Str::slug($generalCategory['name']).'-'.$generalKey])
                    ->each(function(InterestsCategory $category) use ($generalCategory) {
                        dump('FOREACH');
                        dump($category);
                        foreach ($generalCategory['sub'] as $subKey => $subcategory) {
                            dump('FOREACH 2');
                            dump($subcategory);
                            $findSubCat = InterestsCategory::where(['slug' => Str::slug($subcategory)])->first();
//                            if (!$findSubCat) {
                                $category->children()->saveMany(InterestsCategory::factory(1)->create(['name' => $subcategory, 'slug' => (!$findSubCat) ? Str::slug($subcategory) : Str::slug($subcategory).'-'.$subKey]));
//                            }
                        }
                });

        }

//        InterestsCategory::factory(10)->create()->each(function(InterestsCategory $category) {
//            $counts = [1, random_int(3, 7)];
//            $category->children()->saveMany(InterestsCategory::factory($counts[array_rand($counts)])->create());
//        });
    }
}
