<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;

class BackfillEditorialPriority extends Command
{
    protected $signature = 'editorial:backfill-priority';
    protected $description = 'Backfill order_priority for published editorial posts';

    public function handle()
    {
        DB::transaction(function () {

            $posts = Post::where('ad', 1)
                ->whereNotNull('publish_date')
                ->where('is_archived', 0)
                ->orderBy('publish_date', 'desc')
                ->lockForUpdate()
                ->get();

            $priority = 1;

            foreach ($posts as $post) {
                $post->order_priority = $priority++;
                $post->save();
            }
        });

        $this->info('Editorial priorities backfilled successfully.');
    }
}
