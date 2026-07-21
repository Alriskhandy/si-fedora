<?php

namespace App\Policies;

use App\Models\JadwalFasilitasi;
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
     *
     * $target berupa Permohonan (perpanjangan batas upload dokumen) atau
     * JadwalFasilitasi (perpanjangan batas pembuatan permohonan, untuk
     * pemohon yang belum sempat membuat permohonan sebelum batas lewat).
     */
    public function create(User $user, Permohonan|JadwalFasilitasi $target): bool
    {
        if (!$user->hasRole('pemohon')) {
            return false;
        }

        if ($target instanceof Permohonan) {
            if ($target->user_id !== $user->id) {
                return false;
            }

            // Hanya boleh mengajukan 1 kali per permohonan agar tidak
            // menimbulkan request ganda/redundan di sisi admin.
            return !$target->perpanjanganWaktu()->exists();
        }

        return JadwalFasilitasi::availableForExtensionRequest($user->id)
            ->whereKey($target->id)
            ->exists();
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
                && is_null($perpanjanganWaktu->diproses_at);
        }

        // Admin bisa delete semua
        return $user->hasAnyRole(['admin_peran', 'superadmin']);
    }
}
