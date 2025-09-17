<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DiaryEventResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'date' => $this->date,
            'start' => $this->start,
            'end' => $this->end,
            'type' => $this->type,
            'user_id' => $this->user_id,
            'property_id' => $this->property_id,
            'contact_id' => $this->contact_id,
            'color' => $this->color,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
