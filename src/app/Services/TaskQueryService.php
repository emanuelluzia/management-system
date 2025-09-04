<?php

namespace App\Services;

use App\Models\Task;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class TaskQueryService
{
      public function getAllTasks(array $filters, array $sorting, int $perPage = 10): LengthAwarePaginator
    {
        return Task::query()
            ->slim()
            ->with([
                'category:id,name', 
            ])
            ->trashedMode($filters['with'] ?? '')
            ->applyFilters($filters)
            ->applySorting($sorting)
            ->paginate($perPage)
            ->withQueryString();
    }

    public function getTaskById(int $id): ?Task
    {
        return Task::query()
            ->with(['category:id,name'])
            ->find($id);
    }

     public function getTaskStatistics(): array
    {
            $now = now()->toDateString();

            $base = Task::query();
            $total     = (clone $base)->count();
            $pending   = (clone $base)->where('status', 'pending')->count();
            $inProg    = (clone $base)->where('status', 'in_progress')->count();
            $completed = (clone $base)->where('status', 'completed')->count();
            $overdue   = (clone $base)->whereDate('due_date', '<', $now)->whereNull('deleted_at')->where('status', '!=', 'completed')->count();

            return [
                'total'     => $total,
                'pending'   => $pending,
                'inProgress'=> $inProg,
                'completed' => $completed,
                'overdue'   => $overdue,
            ];
    }
    
    public function getTasksByCategory(int $categoryId, array $filters = [], array $sorting = [], int $perPage = 10): LengthAwarePaginator
    {
        $filters['category_id'] = $categoryId;
        return $this->getAllTasks($filters, $sorting, $perPage);
    }

    public function listAllLight(?string $search = null): Collection
    {
        return Task::query()
            ->select('id', 'title', 'status', 'priority', 'due_date', 'category_id')
            ->when($search, fn($q) => $q->search($search))
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();
    }
}
