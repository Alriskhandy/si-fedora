<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        } elseif ($user->hasRole('pokja')) {
            return $this->pokjaDashboard($user);
        } elseif ($user->hasRole('kabkota')) {  // <-- Ganti jadi ini
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
            'active_jadwal' => JadwalFasilitasi::where('status', 'published')->count(),
            'recent_permohonan' => Permohonan::with(['kabupatenKota', 'jenisDokumen'])
                ->latest()
                ->limit(5)
                ->get()
        ];

        return view('dashboard.superadmin', compact('stats'));
    }

    private function kabanDashboard($user)
    {
        $stats = [
            'pending_approval' => Permohonan::where('status', 'draft_recommendation')
                ->whereHas('evaluasi', function($q) {
                    $q->where('status', 'submitted');
                })
                ->count(),
            'total_permohonan' => Permohonan::count(),
            'completed_this_month' => Permohonan::where('status', 'completed')
                ->whereMonth('completed_at', now()->month)
                ->count(),
            'recent_evaluasi' => Permohonan::with(['kabupatenKota', 'evaluasi'])
                ->whereHas('evaluasi', function($q) {
                    $q->where('status', 'submitted');
                })
                ->latest()
                ->limit(5)
                ->get()
        ];

        return view('dashboard.kaban', compact('stats'));
    }

    private function adminPeranDashboard($user)
    {
        $stats = [
            'pending_verifikasi' => Permohonan::whereIn('status', ['submitted', 'revision_required'])->count(),
            'in_evaluation' => Permohonan::where('status', 'in_evaluation')->count(),
            'pending_approval' => Permohonan::where('status', 'draft_recommendation')->count(),
            'total_permohonan' => Permohonan::count(),
            'recent_activities' => \DB::table('activity_log')
                ->where('created_at', '>=', now()->subDays(7))
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
        ];

        return view('dashboard.admin_peran', compact('stats'));
    }

    private function verifikatorDashboard($user)
    {
        $stats = [
            'my_verifikasi' => Permohonan::where('verifikator_id', $user->id)
                ->whereIn('status', ['submitted', 'revision_required'])
                ->count(),
            'completed_verifikasi' => Permohonan::where('verifikator_id', $user->id)
                ->where('status', 'verified')
                ->whereMonth('verified_at', now()->month)
                ->count(),
            'pending_verifikasi' => Permohonan::where('verifikator_id', $user->id)
                ->where('status', 'submitted')
                ->count(),
            'my_tasks' => Permohonan::with(['kabupatenKota', 'jenisDokumen'])
                ->where('verifikator_id', $user->id)
                ->whereIn('status', ['submitted', 'revision_required'])
                ->latest()
                ->limit(5)
                ->get()
        ];

        return view('dashboard.verifikator', compact('stats'));
    }

    private function pokjaDashboard($user)
    {
        $stats = [
            'my_evaluasi' => Permohonan::where('pokja_id', $user->id)
                ->where('status', 'in_evaluation')
                ->count(),
            'completed_evaluasi' => Permohonan::where('pokja_id', $user->id)
                ->where('status', 'draft_recommendation')
                ->whereNotNull('evaluated_at')
                ->whereMonth('evaluated_at', now()->month)
                ->count(),
            'pending_submissions' => Permohonan::where('pokja_id', $user->id)
                ->where('status', 'in_evaluation')
                ->count(),
            'my_evaluasi_tasks' => Permohonan::with(['kabupatenKota', 'jenisDokumen'])
                ->where('pokja_id', $user->id)
                ->where('status', 'in_evaluation')
                ->latest()
                ->limit(5)
                ->get()
        ];

        return view('dashboard.pokja', compact('stats'));
    }

    private function kabKotaDashboard($user)
    {
        $stats = [
            'my_permohonan' => Permohonan::where('created_by', $user->id)->count(),
            'submitted_permohonan' => Permohonan::where('created_by', $user->id)
                ->where('status', 'submitted')
                ->count(),
            'verified_permohonan' => Permohonan::where('created_by', $user->id)
                ->where('status', 'verified')
                ->count(),
            'my_permohonan_list' => Permohonan::with(['jenisDokumen', 'status'])
                ->where('created_by', $user->id)
                ->latest()
                ->limit(5)
                ->get()
        ];

        return view('dashboard.kab_kota', compact('stats'));
    }
}