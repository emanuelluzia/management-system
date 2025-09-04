<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Task extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title', 'description', 'status', 'priority', 'due_date', 'category_id',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }


    public function scopeSlim(Builder $q): Builder
    {
        return $q->select([
            'id', 'title', 'description', 'status', 'priority',
            'due_date', 'created_at', 'deleted_at', 'category_id',
        ]);
    }

    public function scopeTrashedMode(Builder $q, ?string $mode): Builder
    {
        return match ($mode) {
            'with_trashed' => $q->withTrashed(),
            'only_trashed' => $q->onlyTrashed(),
            default        => $q,
        };
    }

    public function scopeApplyFilters(Builder $q, array $filters = []): Builder
    {
        return $q
            ->when(!empty($filters['status']), fn ($qq) =>
                $qq->where('status', $filters['status'])
            )
            ->when(!empty($filters['priority']), fn ($qq) =>
                $qq->where('priority', $filters['priority'])
            )
            ->when(!empty($filters['category_id']), fn ($qq) =>
                $qq->where('category_id', $filters['category_id'])
            )
            ->when(!empty($filters['search']), fn ($qq) =>
                $qq->where(function ($w) use ($filters) {
                    $term = '%' . trim($filters['search']) . '%';
                    $w->where('title', 'ILIKE', $term)
                      ->orWhere('description', 'ILIKE', $term);
                })
            )
            ->when(!empty($filters['due_from']), fn ($qq) =>
                $qq->whereDate('due_date', '>=', $filters['due_from'])
            )
            ->when(!empty($filters['due_to']), fn ($qq) =>
                $qq->whereDate('due_date', '<=', $filters['due_to'])
            );
    }

    public function scopeApplySorting(Builder $q, array $sorting = []): Builder
    {
        $sortBy   = $sorting['sort_by']   ?? 'created_at';
        $sortDir  = strtolower($sorting['sort_dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        $allowed = ['created_at', 'due_date', 'priority', 'status', 'title'];
        if (!in_array($sortBy, $allowed, true)) {
            $sortBy = 'created_at';
        }

        if ($sortBy === 'priority') {
            $expr = "CASE priority
                        WHEN 'low' THEN 1
                        WHEN 'medium' THEN 2
                        WHEN 'high' THEN 3
                        ELSE 4
                     END";
            return $q->orderByRaw("$expr $sortDir")->orderBy('id', 'desc');
        }

        if ($sortBy === 'status') {
            $expr = "CASE status
                        WHEN 'pending' THEN 1
                        WHEN 'in_progress' THEN 2
                        WHEN 'completed' THEN 3
                        ELSE 4
                     END";
            return $q->orderByRaw("$expr $sortDir")->orderBy('id', 'desc');
        }

        return $q->orderBy($sortBy, $sortDir)->orderBy('id', 'desc');
    }
}
