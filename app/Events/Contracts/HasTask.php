<?php

namespace App\Events\Contracts;

use App\Models\Task;

interface HasTask
{
    public function getTask(): Task;
}
