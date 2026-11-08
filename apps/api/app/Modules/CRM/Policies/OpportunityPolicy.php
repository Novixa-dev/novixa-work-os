<?php

namespace App\Modules\CRM\Policies;

use App\Models\Opportunity;
use App\Models\User;

class OpportunityPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active && !$user->isGuest();
    }

    public function view(User $user, Opportunity $opportunity): bool
    {
        return $user->is_active && !$user->isGuest();
    }

    public function create(User $user): bool
    {
        return $user->isManager();
    }

    public function update(User $user, Opportunity $opportunity): bool
    {
        return $user->isManager() || $opportunity->owner_id === $user->id;
    }

    public function delete(User $user, Opportunity $opportunity): bool
    {
        return $user->isAdmin();
    }
}
