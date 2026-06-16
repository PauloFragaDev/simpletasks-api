<?php

namespace App\Providers;

use App\Events\TaskCompleted;
use App\Events\TaskCreated;
use App\Events\TaskDeleted;
use App\Events\TaskUpdated;
use App\Listeners\Task\LogTaskActivity;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(TaskCreated::class,   LogTaskActivity::class);
        Event::listen(TaskUpdated::class,   LogTaskActivity::class);
        Event::listen(TaskDeleted::class,   LogTaskActivity::class);
        Event::listen(TaskCompleted::class, LogTaskActivity::class);
    }
}
