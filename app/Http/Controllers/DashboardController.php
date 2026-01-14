<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Models\Permohonan;
use App\Models\User;
use App\Models\JadwalFasilitasi;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Redirect berdasarkan role
        if ($user->hasRole('superadmin')) {
            return $this->superadminDashboard($user);
        } elseif ($user->hasRole('kaban')) {
            return $this->kabanDashboard($user);
        } elseif ($user->hasRole('admin_peran')) {
            return $this->adminPeranDashboard($user);
        } elseif ($user->hasRole('verifikator')) {
            return $this->verifikatorDashboard($user);
        } elseif ($user->hasRole('fasilitator')) {
            return $this->pokjaDashboard($user);
        } elseif ($user->hasRole('pemohon')) {  // <-- Ganti jadi ini
            return $this->kabKotaDashboard($user);
        }

        // Fallback
        return view('dashboard.default', [
            'user' => $user
        ]);
    }

    private function superadminDashboard($user)
    {
        $stats = [
            'total_users' => User::count(),
            'total_permohonan' => Permohonan::count(),
            'active_jadwal' => JadwalFasilitasi::count(),
            'recent_permohonan' => Permohonan::with(['kabupatenKota'])
                ->latest()
                ->limit(5)
                ->get()
        ];

        return view('dashboard.superadmin', compact('stats'));
    }

    private function kabanDashboard($user)
    {
        $stats = [
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

        return view('dashboard.kaban', compact('stats'));
    }

    private function adminPeranDashboard($user)
    {
        $stats = [
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

        return view('dashboard.admin_peran', compact('stats'));
    }

    private function verifikatorDashboard($user)
    {
        // Untuk sementara, verifikator bisa melihat semua permohonan dengan status proses
        // Karena belum ada sistem assignment verifikator
        $stats = [
            'my_verifikasi' => Permohonan::where('status_akhir', 'proses')
                ->count(),
            'completed_verifikasi' => Permohonan::where('status_akhir', 'selesai')
                ->whereMonth('updated_at', now()->month)
                ->count(),
            'pending_verifikasi' => Permohonan::where('status_akhir', 'proses')
                ->count(),
            'my_tasks' => Permohonan::with(['kabupatenKota'])
                ->where('status_akhir', 'proses')
                ->latest()
                ->limit(5)
                ->get()
        ];

        return view('dashboard.verifikator', compact('stats'));
    }

    private function pokjaDashboard($user)
    {
        // Fasilitator sekarang menggunakan tim_fasilitasi_assignment
        $permohonanIds = \App\Models\TimFasilitasiAssignment::where('user_id', $user->id)
            ->pluck('permohonan_id');

        $stats = [
            'my_evaluasi' => Permohonan::whereIn('id', $permohonanIds)
                ->where('status_akhir', 'proses')
                ->count(),
            'completed_evaluasi' => Permohonan::whereIn('id', $permohonanIds)
                ->where('status_akhir', 'selesai')
                ->whereMonth('updated_at', now()->month)
                ->count(),
            'pending_submissions' => Permohonan::whereIn('id', $permohonanIds)
                ->where('status_akhir', 'proses')
                ->count(),
            'my_evaluasi_tasks' => Permohonan::with(['kabupatenKota'])
                ->whereIn('id', $permohonanIds)
                ->where('status_akhir', 'proses')
                ->latest()
                ->limit(5)
                ->get()
        ];

        return view('dashboard.pokja', compact('stats'));
    }

    private function kabKotaDashboard($user)
    {
        $stats = [
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

        return view('dashboard.kab_kota', compact('stats'));
    }
}
