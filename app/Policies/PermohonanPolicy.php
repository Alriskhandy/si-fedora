<?php

namespace App\Policies;

use App\Models\Permohonan;
use App\Models\User;
use App\Models\UserKabkotaAssignment;

class PermohonanPolicy
{
    /**
     * Pemohon: permohonan miliknya atau kab/kota-nya.
     * Verifikator & fasilitator: kab/kota, tahun, dan jenis dokumen yang di-assign aktif.
     * Superadmin, Admin PERAN, Kaban, Auditor: semua.
     */
    public function view(User $user, Permohonan $permohonan): bool
    {
        if ($user->hasAnyRole(['superadmin', 'admin_peran', 'kaban', 'auditor'])) {
            return true;
        }

        if ($user->hasRole('pemohon')) {
            return $permohonan->user_id === $user->id
                || ($user->kabupaten_kota_id !== null && (int) $permohonan->kab_kota_id === (int) $user->kabupaten_kota_id);
        }

        if ($user->hasAnyRole(['verifikator', 'fasilitator'])) {
            return $this->isAssigned($user, $permohonan)
                || ($user->hasRole('verifikator') && $this->hasVerified($user, $permohonan));
        }

        return false;
    }

    /**
     * Mengunduh dokumen mengikuti hak melihat permohonannya.
     */
    public function downloadDokumen(User $user, Permohonan $permohonan): bool
    {
        return $this->view($user, $permohonan);
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
