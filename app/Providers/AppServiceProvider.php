<?php

namespace App\Providers;

use App\Http\Channels\DatabaseChannel;
use Illuminate\Notifications\Channels\DatabaseChannel as IlluminateDatabaseChannel;;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
//        $this->app->instance(IlluminateDatabaseChannel::class, new DatabaseChannel());
    }
}
