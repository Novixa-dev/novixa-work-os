<?php

namespace App\Modules\Projects\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, Project $project): bool
    {
        if ($user->isAdmin() || $user->isManager()) {
            return true;
        }

        // Guest can only view projects they are a member of
        if ($user->isGuest()) {
            return $project->members()->where('user_id', $user->id)->exists();
        }

        return true;
    }

    public function create(User $user): bool
    {
        return $user->isManager(); // owner, admin, manager
    }

    public function update(User $user, Project $project): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isManager()) {
            return $project->lead_id === $user->id || $project->members()->where('user_id', $user->id)->exists();
        }

        return false;
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->isAdmin();
    }
}
