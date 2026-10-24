<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Akun superadmin hanya boleh dilihat dan dikelola oleh superadmin.
     */
    public function manage(User $user, User $target): bool
    {
        if ($user->hasRole('superadmin')) {
            return true;
        }

        return ! $target->hasRole('superadmin');
    }
}
