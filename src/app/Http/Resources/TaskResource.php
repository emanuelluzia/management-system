<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Task
 */
class TaskResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                  => $this->id,
            'title'               => $this->title,
            'description'         => $this->description,
            'status'              => $this->status,     
            'priority'            => $this->priority,   

            'due_date'            => optional($this->due_date)->toDateString(),
            'due_date_formatted'  => $this->due_date_formatted, 
            'is_overdue'          => $this->is_overdue,        

            'category' => $this->whenLoaded('category', function () {
                return [
                    'id'   => $this->category->id,
                    'name' => $this->category->name,
                ];
            }),

            
            'deleted_at'          => optional($this->deleted_at)?->toISOString(),
            'created_at'          => optional($this->created_at)?->toISOString(),
            'updated_at'          => optional($this->updated_at)?->toISOString(),
        ];
    }
}
