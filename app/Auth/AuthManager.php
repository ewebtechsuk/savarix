<?php

namespace App\Auth;

use App\Models\User;

class AuthManager
{
    private ?User $user = null;

    public function user(): ?User
    {
        return $this->user;
    }

    public function check(): bool
    {
        return $this->user !== null;
    }

    public function login(User $user): void
    {
        $this->user = $user;
    }

    public function logout(): void
    {
        $this->user = null;
    }
}
