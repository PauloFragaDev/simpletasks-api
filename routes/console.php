<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('tasks:send-due-reminders')->daily();
