<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Models\Permohonan;
use App\Models\User;
use App\Models\JadwalFasilitasi;
use App\Models\PermohonanAssignments;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Get stats based on role
        if ($user->hasRole('superadmin')) {
            $stats = $this->getSuperadminStats($user);
        } elseif ($user->hasRole('kaban')) {
            $stats = $this->getKabanStats($user);
        } elseif ($user->hasRole('admin_peran')) {
            $stats = $this->getAdminPeranStats($user);
        } elseif ($user->hasRole('verifikator')) {
            $stats = $this->getVerifikatorStats($user);
        } elseif ($user->hasRole('fasilitator')) {
            $stats = $this->getFasilitatorStats($user);
        } elseif ($user->hasRole('auditor')) {
            $stats = $this->getAuditorStats($user);
        } elseif ($user->hasRole('pemohon')) {
            $stats = $this->getKabKotaStats($user);
        } else {
            $stats = [];
        }

        // Return single dashboard view with stats
        return view('pages.dashboard.index', compact('stats'));
    }

    private function getSuperadminStats($user)
    {
        return [
            'total_users' => User::count(),
            'total_permohonan' => Permohonan::count(),
            'active_jadwal' => JadwalFasilitasi::count(),
            'recent_permohonan' => Permohonan::with(['kabupatenKota'])
                ->latest()
                ->limit(5)
                ->get()
        ];
    }

    private function getKabanStats($user)
    {
        return [
            'pending_approval' => Permohonan::where('status_akhir', 'proses')->count(),
            'total_permohonan' => Permohonan::count(),
            'completed_this_month' => Permohonan::where('status_akhir', 'selesai')
                ->whereMonth('updated_at', now()->month)
                ->count(),
            'recent_permohonan' => Permohonan::with(['kabupatenKota'])
                ->whereIn('status_akhir', ['proses', 'selesai'])
                ->latest()
                ->limit(5)
                ->get()
        ];
    }

    private function getAdminPeranStats($user)
    {
        return [
            'pending_verifikasi' => Permohonan::where('status_akhir', 'belum')->count(),
            'in_evaluation' => Permohonan::where('status_akhir', 'proses')->count(),
            'pending_approval' => Permohonan::where('status_akhir', 'revisi')->count(),
            'total_permohonan' => Permohonan::count(),
            'recent_activities' => DB::table('activity_log')
                ->where('created_at', '>=', now()->subDays(7))
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
        ];
    }

    private function getVerifikatorStats($user)
    {
        return [
            'my_verifikasi' => Permohonan::where('status_akhir', 'proses')
                ->count(),
            'completed_verifikasi' => Permohonan::where('status_akhir', 'selesai')
                ->whereMonth('updated_at', now()->month)
                ->count(),
            'pending_verifikasi' => Permohonan::where('status_akhir', 'proses')
                ->count(),
            'my_tasks' => Permohonan::with(['kabupatenKota', 'jenisDokumen'])
                ->where('status_akhir', 'proses')
                ->latest()
                ->limit(5)
                ->get()
        ];
    }

    private function getFasilitatorStats($user)
    {
        return [
            'my_evaluasi' => Permohonan::where('status_akhir', 'proses')->count(),
            'completed_evaluasi' => Permohonan::where('status_akhir', 'selesai')
                ->whereMonth('updated_at', now()->month)
                ->count(),
            'pending_submissions' => Permohonan::where('status_akhir', 'proses')->count(),
            'my_evaluasi_tasks' => Permohonan::with(['kabupatenKota'])
                ->where('status_akhir', 'proses')
                ->latest()
                ->limit(5)
                ->get()
        ];
    }

    private function getAuditorStats($user)
    {
        return [
            'total_permohonan' => Permohonan::count(),
            'in_progress' => Permohonan::whereIn('status_akhir', ['proses', 'revisi'])->count(),
            'completed' => Permohonan::where('status_akhir', 'selesai')->count(),
            'total_kabkota' => \App\Models\KabupatenKota::count(),
            'recent_permohonan' => Permohonan::with(['kabupatenKota'])
                ->latest()
                ->limit(10)
                ->get(),
            'monthly_stats' => Permohonan::selectRaw('status_akhir, COUNT(*) as count')
                ->whereMonth('created_at', now()->month)
                ->groupBy('status_akhir')
                ->get()
        ];
    }

    private function getKabKotaStats($user)
    {
        return [
            'my_permohonan' => Permohonan::where('user_id', $user->id)->count(),
            'draft_permohonan' => Permohonan::where('user_id', $user->id)
                ->where('status_akhir', 'belum')
                ->count(),
            'in_process_permohonan' => Permohonan::where('user_id', $user->id)
                ->whereIn('status_akhir', ['proses', 'revisi'])
                ->count(),
            'completed_permohonan' => Permohonan::where('user_id', $user->id)
                ->where('status_akhir', 'selesai')
                ->count(),
            'jadwal_aktif' => JadwalFasilitasi::where('status', 'published')
                ->where('batas_permohonan', '>=', now())
                ->orderBy('tanggal_mulai', 'asc')
                ->limit(3)
                ->get(),
            'my_permohonan_list' => Permohonan::with(['kabupatenKota'])
                ->where('user_id', $user->id)
                ->latest()
                ->limit(5)
                ->get()
        ];
    }
}
