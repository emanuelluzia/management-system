<?php

namespace App\Services;

use App\Models\Task;
use Illuminate\Support\Facades\DB;

class TaskService
{

    public function createTask(array $data): Task
    {
        return DB::transaction(function () use ($data) {
            /** @var Task $task */
            $task = Task::create($data);
            return $task->fresh();
        });
    }

    public function updateTask(Task $task, array $data): Task
    {
        return DB::transaction(function () use ($task, $data) {
            $task->update($data);
            return $task->fresh();
        });
    }

    public function deleteTask(Task $task): void
    {
        DB::transaction(function () use ($task) {
            $task->delete();
        });
    }

    public function restoreTask(int $id): Task
    {
        /** @var Task $task */
        $task = Task::onlyTrashed()->findOrFail($id);
        $task->restore();

        return $task->fresh();
    }
}
