<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Akun superadmin hanya boleh dilihat dan dikelola oleh pemegang permission user.manage-superadmin.
     */
    public function manage(User $user, User $target): bool
    {
        if ($user->can('user.manage-superadmin')) {
            return true;
        }

        return ! $target->hasRole('superadmin');
    }
}
