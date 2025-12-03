<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Contact;
use App\Models\User;
use App\Support\AgencyRoles;

class ContactPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole(AgencyRoles::tenantOwnerRole())) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $this->canManage($user);
    }

    public function view(User $user, Contact $contact): bool
    {
        return $this->canManage($user);
    }

    public function create(User $user): bool
    {
        return $this->canManage($user);
    }

    public function update(User $user, Contact $contact): bool
    {
        return $this->canManage($user);
    }

    public function delete(User $user, Contact $contact): bool
    {
        return $this->canManage($user);
    }

    protected function canManage(User $user): bool
    {
        return $user->hasAnyRole(AgencyRoles::propertyManagers());
    }
}
