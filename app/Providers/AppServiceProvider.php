<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Jobs\updateStatus;
use DB;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        


    }
     /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        DB::statement("SET time_zone = '+05:30'");
	    $this->app->bindMethod([updateStatus::class, 'handle'], function ($job, $app) {
            return $job->handle();
        });
    }
}
