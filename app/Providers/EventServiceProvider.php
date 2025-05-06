<?php

namespace App\Providers;

use Laravel\Lumen\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\OrderShipped' => [
    'App\Listeners\SendShipmentNotification',
	],
	  'App\Events\ExampleEvent' => [
        'App\Listeners\ExampleListener',
    ],
    ];
}
