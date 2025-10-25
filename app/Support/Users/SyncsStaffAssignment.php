<?php

namespace App\Support\Users;

use App\Models\ActivityLog;
use App\Models\Staff;
use App\Models\User;

trait SyncsStaffAssignment
{
    protected function syncStaffAssignment(User $user, ?int $staffId): void
    {
        $previousStaff = $user->staff;
        $previousStaffId = $previousStaff?->id;

        if ($previousStaff && ($staffId === null || $previousStaffId !== $staffId)) {
            $previousStaff->user()->dissociate();
            $previousStaff->save();
        }

        $currentStaff = null;

        if ($staffId) {
            if ($previousStaffId === $staffId) {
                $currentStaff = $previousStaff;
            } else {
                $currentStaff = Staff::find($staffId);

                if ($currentStaff) {
                    $originalUserId = $currentStaff->user_id;

                    if ($originalUserId && $originalUserId !== $user->id) {
                        $currentStaff->user()->dissociate();
                        $currentStaff->save();
                    }

                    $currentStaff->user()->associate($user);
                    $currentStaff->save();

                    ActivityLog::record(
                        auth()->id(),
                        $currentStaff,
                        'user_link.updated',
                        'User link updated',
                        [
                            'before' => ['user_id' => $originalUserId],
                            'after' => ['user_id' => $user->id],
                        ]
                    );
                }
            }
        }

        if ($previousStaff && $previousStaffId !== $staffId) {
            ActivityLog::record(
                auth()->id(),
                $previousStaff,
                'user_link.updated',
                'User link removed',
                [
                    'before' => ['user_id' => $user->id],
                    'after' => ['user_id' => null],
                ]
            );
        }

        $user->unsetRelation('staff');

        if ($staffId && $staffId === $previousStaffId) {
            $user->setRelation('staff', $previousStaff);
        } elseif ($currentStaff) {
            $user->setRelation('staff', $currentStaff);
        }

        $currentStaffId = $user->staff?->id;

        if ($previousStaffId !== $currentStaffId) {
            ActivityLog::record(
                auth()->id(),
                $user,
                'staff_link.updated',
                'Linked staff profile updated',
                [
                    'before' => ['staff_id' => $previousStaffId],
                    'after' => ['staff_id' => $currentStaffId],
                ]
            );
        }
    }
}
