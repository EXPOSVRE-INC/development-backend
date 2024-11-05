<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\Song;
use Illuminate\Console\Command;

class ClearLastDayViewsCount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'day_views:count';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear last day views count';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $posts = Post::all();
        foreach ($posts as $post) {
            $post->views_by_last_day = 0;
            $post->save();
        }

        $songs = Song::all();
        foreach ($songs as $song) {
            $song->views_by_last_day = 0;
            $song->save();
        }
    }
}
