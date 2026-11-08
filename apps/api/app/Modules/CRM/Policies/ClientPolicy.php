<?php

namespace App\Modules\CRM\Policies;

use App\Models\Client;
use App\Models\User;

class ClientPolicy
{
    public function viewAny(User $user): bool
    {
        // Guests cannot access CRM
        return $user->is_active && ! $user->isGuest();
    }

    public function view(User $user, Client $client): bool
    {
        return $user->is_active && ! $user->isGuest();
    }

    public function create(User $user): bool
    {
        return $user->isManager();
    }

    public function update(User $user, Client $client): bool
    {
        return $user->isManager() || $client->account_manager_id === $user->id;
    }

    public function delete(User $user, Client $client): bool
    {
        return $user->isAdmin();
    }
}
