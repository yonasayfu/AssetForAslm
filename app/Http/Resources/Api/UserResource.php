<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\User */
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'account_status' => $this->account_status ?? null,
            'account_type' => $this->account_type ?? null,
            'approved_at' => optional($this->approved_at)->toIso8601String(),
            'approved_by' => $this->whenLoaded('approver', fn () => $this->approver?->name),
            'roles' => $this->whenLoaded('roles', fn () => $this->roles->pluck('name')),
            'permissions' => $this->whenLoaded('permissions', fn () => $this->permissions->pluck('name')),
            'staff' => $this->whenLoaded('staff', function () {
                if (! $this->staff) {
                    return null;
                }

                return [
                    'id' => $this->staff->id,
                    'first_name' => $this->staff->first_name,
                    'last_name' => $this->staff->last_name,
                    'full_name' => $this->staff->full_name,
                    'email' => $this->staff->email,
                    'status' => $this->staff->status,
                    'job_title' => $this->staff->job_title,
                    'phone' => $this->staff->phone,
                    'hire_date' => optional($this->staff->hire_date)->toDateString(),
                    'avatar_url' => $this->staff->avatar_url,
                ];
            }),
            'created_at' => optional($this->created_at)->toIso8601String(),
            'updated_at' => optional($this->updated_at)->toIso8601String(),
        ];
    }
}
