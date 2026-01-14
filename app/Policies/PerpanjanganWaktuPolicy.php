<?php

namespace App\Policies;

use App\Models\PerpanjanganWaktu;
use App\Models\Permohonan;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PerpanjanganWaktuPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['pemohon', 'admin_peran', 'superadmin']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PerpanjanganWaktu $perpanjanganWaktu): bool
    {
        // Pemohon hanya bisa lihat miliknya sendiri
        if ($user->hasRole('pemohon')) {
            return $perpanjanganWaktu->user_id === $user->id;
        }

        // Admin bisa lihat semua
        return $user->hasAnyRole(['admin_peran', 'superadmin']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Permohonan $permohonan): bool
    {
        // Hanya pemohon yang membuat permohonan yang bisa mengajukan perpanjangan
        if (!$user->hasRole('pemohon')) {
            return false;
        }

        return $permohonan->user_id === $user->id;
    }

    /**
     * Determine whether the user can update the model status.
     */
    public function updateStatus(User $user, PerpanjanganWaktu $perpanjanganWaktu): bool
    {
        // Hanya admin yang bisa approve/reject
        return $user->hasAnyRole(['admin_peran', 'superadmin']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PerpanjanganWaktu $perpanjanganWaktu): bool
    {
        // Pemohon hanya bisa delete miliknya sendiri yang masih pending
        if ($user->hasRole('pemohon')) {
            return $perpanjanganWaktu->user_id === $user->id 
                && $perpanjanganWaktu->status === 'pending';
        }

        // Admin bisa delete semua
        return $user->hasAnyRole(['admin_peran', 'superadmin']);
    }
}
