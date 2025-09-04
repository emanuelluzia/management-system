<?php

namespace App\Observers;

use App\Models\Category;
use App\Support\CategoryStatsCache;

class CategoryObserver
{
    public bool $afterCommit = true;

    public function created(Category $category): void   { CategoryStatsCache::forget(); }
    public function updated(Category $category): void   { CategoryStatsCache::forget(); }
    public function deleted(Category $category): void   { CategoryStatsCache::forget(); }
    public function restored(Category $category): void  { CategoryStatsCache::forget(); }
    public function forceDeleted(Category $category): void { CategoryStatsCache::forget(); }
}
