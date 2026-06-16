<?php

namespace App\Providers;

use App\Events\TaskCompleted;
use App\Events\TaskCreated;
use App\Events\TaskDeleted;
use App\Events\TaskUpdated;
use App\Events\UserRegistered;
use App\Listeners\Auth\SendWelcomeNotification;
use App\Listeners\Task\LogTaskActivity;
use App\Listeners\Task\SendTaskCompletedNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        TaskCreated::class    => [LogTaskActivity::class],
        TaskUpdated::class    => [LogTaskActivity::class],
        TaskDeleted::class    => [LogTaskActivity::class],
        TaskCompleted::class  => [LogTaskActivity::class, SendTaskCompletedNotification::class],
        UserRegistered::class => [SendWelcomeNotification::class],
    ];
}
