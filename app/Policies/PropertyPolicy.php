<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Property;
use App\Models\User;
use App\Support\AgencyRoles;

class PropertyPolicy
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

    public function view(User $user, Property $property): bool
    {
        return $this->canManage($user);
    }

    public function create(User $user): bool
    {
        return $this->canManage($user);
    }

    public function update(User $user, Property $property): bool
    {
        return $this->canManage($user);
    }

    public function delete(User $user, Property $property): bool
    {
        return $this->canManage($user);
    }

    protected function canManage(User $user): bool
    {
        return $user->hasAnyRole(AgencyRoles::propertyManagers());
    }
}
