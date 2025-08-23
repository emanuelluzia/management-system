<?php

namespace App\Services;

use App\Models\Task;

class TaskService
{
    public function all()
    {
        return Task::latest()->get();
    }

    public function store(array $data): Task
    {
        return Task::create($data);
    }

    public function update(Task $task, array $data): Task
    {
        $task->update($data);
        return $task;
    }

    public function delete(Task $task): void
    {
        $task->delete();
    }

    public function restore(int $id): ?Task
    {
        $task = Task::withTrashed()->find($id);
        if ($task && $task->trashed()) {
            $task->restore();
        }
        return $task;
    }
}
