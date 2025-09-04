<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Task;
use App\Support\CategoryStatsCache;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CategoryService
{
    public function getRootCategories(): Collection
    {
        return Category::query()
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();
    }

    public function getAllCategoriesWithRelations()
    {
        return Category::query()
            ->select(['id', 'name', 'parent_id'])
            ->with([
                'parent:id,name',
                'children:id,name,parent_id',
            ])
            ->orderBy('name')
            ->get();
    }

    public function getAllCategoriesWithCountsAndParents(): Collection
    {
        return Category::query()
            ->with(['parent:id,name', 'children:id,name,parent_id'])
            ->withCount([
                'tasks as tasks_total_count',
                'tasks as tasks_pending_count'      => fn ($q) => $q->where('status', 'pending'),
                'tasks as tasks_in_progress_count'  => fn ($q) => $q->where('status', 'in_progress'),
                'tasks as tasks_completed_count'    => fn ($q) => $q->where('status', 'completed'),
            ])
            ->orderBy('name')
            ->get();
    }

    public function create(array $data): Category
    {
        $this->validateParentDepth($data['parent_id'] ?? null);

        return DB::transaction(function () use ($data) {
            $cat = Category::create($data);
            return $cat->fresh(['parent', 'children']);
        });
    }

    public function update(Category $category, array $data): Category
    {
        $parentId = $data['parent_id'] ?? null;

        if (!is_null($parentId) && (int)$parentId === (int)$category->id) {
            throw ValidationException::withMessages([
                'parent_id' => 'Uma categoria não pode ser pai de si mesma.',
            ]);
        }

        $this->validateParentDepth($parentId);
        $this->validateNoCycle($category, $parentId);

        return DB::transaction(function () use ($category, $data) {
            $category->update($data);
            return $category->fresh(['parent', 'children']);
        });
    }

    public function delete(Category $category): void
    {
        DB::transaction(fn () => $category->delete());
    }

    public function restore(int $id): Category
    {
        $category = Category::onlyTrashed()->findOrFail($id);
        $category->restore();

        return $category->fresh(['parent', 'children']);
    }

    public function getStatistics(): array
    {
        return CategoryStatsCache::remember(function () {
            $stats = \App\Models\Category::query()
                ->select(['id', 'name', 'parent_id'])
                ->withCount([
                    'tasks as tasks_total_count',
                    'tasks as tasks_pending_count'     => fn ($q) => $q->where('status', 'pending'),
                    'tasks as tasks_in_progress_count' => fn ($q) => $q->where('status', 'in_progress'),
                    'tasks as tasks_completed_count'   => fn ($q) => $q->where('status', 'completed'),
                ])
                ->orderBy('name')
                ->get();

            $totals = [
                'total'        => $stats->sum('tasks_total_count'),
                'pending'      => $stats->sum('tasks_pending_count'),
                'in_progress'  => $stats->sum('tasks_in_progress_count'),
                'completed'    => $stats->sum('tasks_completed_count'),
            ];

            return [$stats, $totals];
        });
    }

    
    protected function validateParentDepth(?int $parentId): void
    {
        if (is_null($parentId)) return;

        $parent = Category::findOrFail($parentId);

        if (!is_null($parent->parent_id)) {
            throw ValidationException::withMessages([
                'parent_id' => 'A hierarquia de categorias permite no máximo 2 níveis.',
            ]);
        }
    }

    protected function validateNoCycle(Category $category, ?int $parentId): void
    {
        if (is_null($parentId)) return;

        $childrenIds = $category->children()->pluck('id')->all();
        if (in_array($parentId, $childrenIds, true)) {
            throw ValidationException::withMessages([
                'parent_id' => 'Ciclo detectado: o pai não pode ser um filho da categoria.',
            ]);
        }
    }
}
