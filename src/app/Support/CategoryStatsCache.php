<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

class CategoryStatsCache
{
    /** Single key for this dataset (version it when the shape changes) */
    public const KEY = 'cat_stats_v1';

    /** Default TTL (you can tweak): e.g. 5 minutes */
    public const TTL = 300; // seconds

    /**
     * Remember helper: accepts a callback that computes [stats, totals].
     * Usage: CategoryStatsCache::remember(fn() => computeStats());
     */
    public static function remember(callable $callback): array
    {
        /** @var array{0:mixed,1:mixed} $value */
        $value = Cache::remember(self::KEY, now()->addSeconds(self::TTL), function () use ($callback) {
            return $callback();
        });

        // Ensure we always return an array [stats, totals]
        return is_array($value) ? $value : [[], []];
    }

    /** Invalidate the cached stats */
    public static function forget(): void
    {
        Cache::forget(self::KEY);
    }
}
