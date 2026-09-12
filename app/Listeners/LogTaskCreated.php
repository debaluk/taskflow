<?php

namespace App\Listeners;

use App\Events\TaskCreated;
use App\Models\Activity;

class LogTaskCreated
{
    /**
     * Handle the event.
     */
    public function handle(TaskCreated $event): void
    {
        $task = $event->task;

        Activity::log(
            $task->workspace_id,
            'created',
            'Membuat task "' . $task->title . '"',
            $task
        );
    }
}