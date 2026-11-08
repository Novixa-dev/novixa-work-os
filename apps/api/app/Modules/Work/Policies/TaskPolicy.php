<?php

namespace App\Modules\Work\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, Task $task): bool
    {
        if ($user->isAdmin() || $user->isManager()) {
            return true;
        }

        // Guest can only view tasks assigned to them or in a project they belong to
        if ($user->isGuest()) {
            if ($task->assignee_id === $user->id) {
                return true;
            }
            if ($task->project_id && $task->project->members()->where('user_id', $user->id)->exists()) {
                return true;
            }

            return false;
        }

        return true;
    }

    public function create(User $user): bool
    {
        return $user->is_active && ! $user->isGuest();
    }

    public function update(User $user, Task $task): bool
    {
        if ($user->isAdmin() || $user->isManager()) {
            return true;
        }

        // Member can update tasks they are assigned to, or reported
        if ($user->isMember()) {
            return $task->assignee_id === $user->id || $task->reporter_id === $user->id;
        }

        return false;
    }

    public function delete(User $user, Task $task): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isManager()) {
            return $task->reporter_id === $user->id || ($task->project && $task->project->lead_id === $user->id);
        }

        return false;
    }
}
