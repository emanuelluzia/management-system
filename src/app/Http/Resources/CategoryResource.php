<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'parent_id' => $this->parent_id,
            'parent_name' => $this->parent->name ?? null,
            'children' => CategoryResource::collection($this->whenLoaded('children')),
            'tasks_count' => $this->whenCounted('tasks', $this->tasks_count),
            'pending_tasks_count' => $this->when(isset($this->pending_tasks_count), $this->pending_tasks_count),
            'in_progress_tasks_count' => $this->when(isset($this->in_progress_tasks_count), $this->in_progress_tasks_count),
            'completed_tasks_count' => $this->when(isset($this->completed_tasks_count), $this->completed_tasks_count),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'deleted_at' => $this->deleted_at?->toDateTimeString(),
        ];
    }
}