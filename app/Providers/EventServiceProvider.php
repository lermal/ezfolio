<?php

namespace App\Providers;

use App\Events\FrontendVisited;
use App\Events\NewMessage;
use App\Listeners\NewMessageListener;
use App\Listeners\StoreVisitorData;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        FrontendVisited::class => [
            StoreVisitorData::class
        ],
        NewMessage::class => [
            NewMessageListener::class
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
