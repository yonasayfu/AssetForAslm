<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityLogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'action' => $this->action,
            'description' => $this->description,
            'causer' => $this->whenLoaded('causer', function () {
                return [
                    'id' => $this->causer?->id,
                    'name' => $this->causer?->name,
                ];
            }),
            'changes' => $this->changes,
            'created_at' => optional($this->created_at)->toIso8601String(),
            'created_at_for_humans' => optional($this->created_at)?->diffForHumans(),
        ];
    }
}
