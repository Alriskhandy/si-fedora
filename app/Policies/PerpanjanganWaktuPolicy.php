<?php

namespace App\Policies;

use App\Models\JadwalFasilitasi;
use App\Models\Permohonan;
use App\Models\PerpanjanganWaktu;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Aturan akses perpanjangan waktu.
 *
 * Pemegang perpanjangan-waktu.process (Admin) mengelola semua pengajuan; pemegang
 * perpanjangan-waktu.submit (pemohon) hanya pengajuannya sendiri.
 * Persetujuan (proses) diperiksa langsung dengan permission di PerpanjanganWaktuController::process.
 */
class PerpanjanganWaktuPolicy
{
    use HandlesAuthorization;

    /**
     * Boleh membuka daftar perpanjangan waktu.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('perpanjangan-waktu.view');
    }

    /**
     * Boleh melihat satu pengajuan: pemroses melihat semua, pengaju hanya miliknya.
     */
    public function view(User $user, PerpanjanganWaktu $perpanjanganWaktu): bool
    {
        // Pemroses (admin) bisa lihat semua
        if ($user->can('perpanjangan-waktu.process')) {
            return true;
        }

        // Pengaju (pemohon) hanya bisa lihat miliknya sendiri
        return $user->can('perpanjangan-waktu.submit') && $perpanjanganWaktu->user_id === $user->id;
    }

    /**
     * Boleh mengajukan perpanjangan untuk target tertentu.
     *
     * $target berupa Permohonan (perpanjangan batas upload dokumen) atau
     * JadwalFasilitasi (perpanjangan batas pembuatan permohonan, untuk
     * pemohon yang belum sempat membuat permohonan sebelum batas lewat).
     */
    public function create(User $user, Permohonan|JadwalFasilitasi $target): bool
    {
        if (! $user->can('perpanjangan-waktu.submit')) {
            return false;
        }

        if ($target instanceof Permohonan) {
            if ($target->user_id !== $user->id) {
                return false;
            }

            // Hanya boleh mengajukan 1 kali per permohonan agar tidak
            // menimbulkan request ganda/redundan di sisi admin.
            return ! $target->perpanjanganWaktu()->exists();
        }

        return JadwalFasilitasi::availableForExtensionRequest($user->id)
            ->whereKey($target->id)
            ->exists();
    }

    /**
     * Boleh menghapus pengajuan: pemroses semua, pengaju hanya miliknya yang belum diproses.
     */
    public function delete(User $user, PerpanjanganWaktu $perpanjanganWaktu): bool
    {
        // Pemroses (admin) bisa delete semua
        if ($user->can('perpanjangan-waktu.process')) {
            return true;
        }

        // Pengaju hanya bisa delete miliknya sendiri yang masih pending
        return $user->can('perpanjangan-waktu.submit')
            && $perpanjanganWaktu->user_id === $user->id
            && is_null($perpanjanganWaktu->diproses_at);
    }
}
