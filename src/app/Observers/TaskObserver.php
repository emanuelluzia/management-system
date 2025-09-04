<?php

namespace App\Observers;

use App\Models\Task;
use App\Support\CategoryStatsCache;

class TaskObserver
{
    public bool $afterCommit = true;

    public function created(Task $task): void   { CategoryStatsCache::forget(); }
    public function updated(Task $task): void   { CategoryStatsCache::forget(); }
    public function deleted(Task $task): void   { CategoryStatsCache::forget(); }
    public function restored(Task $task): void  { CategoryStatsCache::forget(); }
    public function forceDeleted(Task $task): void { CategoryStatsCache::forget(); }
}
