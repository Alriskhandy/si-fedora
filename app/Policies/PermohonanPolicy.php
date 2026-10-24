<?php

namespace App\Policies;

use App\Models\Permohonan;
use App\Models\User;
use App\Models\UserKabkotaAssignment;

class PermohonanPolicy
{
    /**
     * permohonan.view-all: semua permohonan.
     * permohonan.view-own: permohonan miliknya atau kab/kota-nya.
     * permohonan.view-assigned: kab/kota, tahun, dan jenis dokumen yang di-assign aktif;
     * pemegang permohonan.verify juga boleh melihat permohonan selesai yang pernah diverifikasinya.
     */
    public function view(User $user, Permohonan $permohonan): bool
    {
        if ($user->can('permohonan.view-all')) {
            return true;
        }

        if ($user->can('permohonan.view-own') && $this->isOwnedBy($user, $permohonan)) {
            return true;
        }

        if ($user->can('permohonan.view-assigned')) {
            return $this->isAssigned($user, $permohonan)
                || ($user->can('permohonan.verify') && $this->hasVerified($user, $permohonan));
        }

        return false;
    }

    /**
     * Mengajukan atau menghapus permohonan: hanya akun pembuatnya.
     */
    public function update(User $user, Permohonan $permohonan): bool
    {
        return $user->can('permohonan.create') && $permohonan->user_id === $user->id;
    }

    /**
     * Mengunduh dokumen mengikuti hak melihat permohonannya.
     */
    public function downloadDokumen(User $user, Permohonan $permohonan): bool
    {
        return $this->view($user, $permohonan);
    }

    private function isOwnedBy(User $user, Permohonan $permohonan): bool
    {
        return $permohonan->user_id === $user->id
            || ($user->kabupaten_kota_id !== null && (int) $permohonan->kab_kota_id === (int) $user->kabupaten_kota_id);
    }

    private function isAssigned(User $user, Permohonan $permohonan): bool
    {
        return UserKabkotaAssignment::where('user_id', $user->id)
            ->where('kabupaten_kota_id', $permohonan->kab_kota_id)
            ->where('tahun', $permohonan->tahun)
            ->where('is_active', true)
            ->where(function ($query) use ($permohonan) {
                $query->whereNull('jenis_dokumen_id')
                    ->orWhere('jenis_dokumen_id', $permohonan->jenis_dokumen_id);
            })
            ->exists();
    }

    /**
     * Verifikator yang pernah memverifikasi permohonan selesai tetap boleh melihatnya (pergantian PIC).
     */
    private function hasVerified(User $user, Permohonan $permohonan): bool
    {
        return $permohonan->status_akhir === 'selesai'
            && $permohonan->permohonanDokumen()
                ->where('verified_by', $user->id)
                ->whereNotNull('verified_at')
                ->exists();
    }
}
