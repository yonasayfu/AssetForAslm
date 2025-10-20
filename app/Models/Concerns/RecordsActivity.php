<?php

namespace App\Models\Concerns;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

trait RecordsActivity
{
    public static function bootRecordsActivity(): void
    {
        foreach (['created', 'updated', 'deleted'] as $event) {
            static::$event(function (Model $model) use ($event) {
                $model->recordModelActivity($event);
            });
        }
    }

    protected function recordModelActivity(string $event): void
    {
        if (app()->runningInConsole()) {
            return;
        }

        $changes = null;

        if ($event === 'updated') {
            $changes = $this->gatherUpdatedChanges();

            if (empty($changes['before']) && empty($changes['after'])) {
                return;
            }
        }

        if ($event === 'created') {
            $changes = [
                'after' => $this->only($this->activityLogAttributes()),
            ];
        }

        if ($event === 'deleted') {
            $changes = [
                'before' => $this->only($this->activityLogAttributes()),
            ];
        }

        ActivityLog::record(
            auth()->id(),
            $this,
            $event,
            $this->activityLogDescription($event),
            $changes
        );
    }

    protected function gatherUpdatedChanges(): array
    {
        $before = [];
        $after = [];

        foreach ($this->activityLogAttributes() as $attribute) {
            if ($this->wasChanged($attribute)) {
                $before[$attribute] = $this->getOriginal($attribute);
                $after[$attribute] = $this->{$attribute};
            }
        }

        return [
            'before' => $before,
            'after' => $after,
        ];
    }

    protected function activityLogDescription(string $event): string
    {
        $label = property_exists($this, 'activityLogLabel')
            ? $this->activityLogLabel
            : class_basename($this);

        return match ($event) {
            'created' => "{$label} created",
            'updated' => "{$label} updated",
            'deleted' => "{$label} deleted",
            default => "{$label} {$event}",
        };
    }

    protected function activityLogAttributes(): array
    {
        return property_exists($this, 'activityLogAttributes')
            ? $this->activityLogAttributes
            : array_keys($this->getAttributes());
    }
}
