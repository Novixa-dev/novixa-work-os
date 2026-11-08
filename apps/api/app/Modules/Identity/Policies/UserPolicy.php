<?php

namespace App\Modules\Identity\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, User $model): bool
    {
        return $user->is_active;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, User $model): bool
    {
        if ($user->id === $model->id) {
            return true;
        }

        if ($user->isAdmin()) {
            // Cannot modify owner unless you are the owner
            if ($model->isOwner() && !$user->isOwner()) {
                return false;
            }
            return true;
        }

        return false;
    }

    public function delete(User $user, User $model): bool
    {
        if ($model->isOwner()) {
            return false;
        }

        return $user->isAdmin() && $user->id !== $model->id;
    }
}
