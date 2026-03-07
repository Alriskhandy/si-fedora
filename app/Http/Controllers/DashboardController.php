<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Models\Permohonan;
use App\Models\User;
use App\Models\JadwalFasilitasi;
use App\Models\Notifikasi;

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
        } elseif ($user->hasRole('auditor')) {
            return $this->auditorDashboard($user);
        } elseif ($user->hasRole('pemohon')) {  // <-- Ganti jadi ini
            return $this->kabKotaDashboard($user);
        }

        // Fallback
        return view('pages.dashboard.default', [
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

        return view('pages.dashboard.superadmin', compact('stats'));
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

        return view('pages.dashboard.kaban', compact('stats'));
    }

    private function adminPeranDashboard($user)
    {
        // Get jenis dokumen list for filter
        $jenisDokumenList = \App\Models\MasterJenisDokumen::pluck('nama', 'id')->toArray();
        
        // Get kab/kota list for filter
        $kabupatenKotaList = \App\Models\KabupatenKota::pluck('nama', 'id')->toArray();
        
        // Get permohonan list dengan relasi
        $permohonanList = Permohonan::with(['jenisDokumen', 'kabupatenKota'])
            ->latest()
            ->get()
            ->map(function ($permohonan) {
                return [
                    'id' => $permohonan->id,
                    'nomor_permohonan' => 'PRM-' . str_pad($permohonan->id, 6, '0', STR_PAD_LEFT),
                    'tahun' => $permohonan->tahun ?? date('Y'),
                    'jenis_dokumen_id' => $permohonan->jenis_dokumen_id ?? '',
                    'jenis_dokumen_nama' => $permohonan->jenisDokumen?->nama ?? '-',
                    'kabupaten_kota_id' => $permohonan->kab_kota_id ?? '',
                    'kabupaten_kota_nama' => $permohonan->kabupatenKota?->nama ?? '-',
                    'tanggal' => $permohonan->created_at,
                    'status' => $permohonan->status_akhir ?? 'draft',
                ];
            })
            ->toArray();
        
        $stats = [
            'pending_verifikasi' => Permohonan::where('status_akhir', 'belum')->count(),
            'in_evaluation' => Permohonan::where('status_akhir', 'proses')->count(),
            'pending_approval' => Permohonan::where('status_akhir', 'revisi')->count(),
            'total_permohonan' => Permohonan::count(),
            'recent_activities' => DB::table('activity_log')
                ->leftJoin('users', 'activity_log.causer_id', '=', 'users.id')
                ->select(
                    'activity_log.*',
                    'users.name as causer_name'
                )
                ->where('activity_log.created_at', '>=', now()->subDays(7))
                ->orderBy('activity_log.created_at', 'desc')
                ->limit(10)
                ->get(),
            'recent_permohonan' => Permohonan::with(['kabupatenKota', 'jenisDokumen'])
                ->latest()
                ->limit(5)
                ->get(),
            
            // Data untuk table daftar permohonan
            'permohonan_list' => $permohonanList,
            'jenis_dokumen_list' => $jenisDokumenList,
            'kabupaten_kota_list' => $kabupatenKotaList,
            
            // Jadwal aktif untuk card jadwal pelaksanaan - 5 terbaru
            'jadwal_aktif' => JadwalFasilitasi::where('status', 'published')
                ->orderBy('created_at', 'desc')
                ->limit(3)
                ->get(),
            
            // Master Data Statistics
            'master_data' => [
                'kabupaten_kota' => \App\Models\KabupatenKota::count(),
                'jenis_dokumen' => \App\Models\MasterJenisDokumen::count(),
                'tahapan' => \App\Models\MasterTahapan::count(),
                'bab' => \App\Models\MasterBab::count(),
                'urusan' => \App\Models\MasterUrusan::count(),
                'kelengkapan' => \App\Models\MasterKelengkapanVerifikasi::count(),
            ],
            // Activity chart data (daily/weekly/monthly)
            'activity_chart' => $this->prepareActivityChartData(),
            
            // User Accounts Statistics
            'users' => [
                'total' => User::count(),
                'superadmin' => User::role('superadmin')->count(),
                'kaban' => User::role('kaban')->count(),
                'admin_peran' => User::role('admin_peran')->count(),
                'verifikator' => User::role('verifikator')->count(),
                'fasilitator' => User::role('fasilitator')->count(),
                'pemohon' => User::role('pemohon')->count(),
                'auditor' => User::role('auditor')->count(),
            ],
            
            // Team Assignments Statistics
            'tim_assignments' => [
                'total' => DB::table('user_kabkota_assignments')
                    ->selectRaw("COUNT(DISTINCT (kabupaten_kota_id || '_' || COALESCE(jenis_dokumen_id::text, 'null') || '_' || tahun::text)) as total")
                    ->value('total'),
                'active' => DB::table('user_kabkota_assignments')
                    ->where('is_active', true)
                    ->selectRaw("COUNT(DISTINCT (kabupaten_kota_id || '_' || COALESCE(jenis_dokumen_id::text, 'null') || '_' || tahun::text)) as total")
                    ->value('total'),
                'total_members' => \App\Models\UserKabkotaAssignment::count(),
                'verifikator' => \App\Models\UserKabkotaAssignment::where('role_type', 'verifikator')->count(),
                'fasilitator' => \App\Models\UserKabkotaAssignment::where('role_type', 'fasilitator')->count(),
            ],
            // Notification counts for current user (custom notifikasi table)
            'notifications' => [
                'total' => Notifikasi::where('user_id', $user->id)->count(),
                'unread' => Notifikasi::where('user_id', $user->id)
                    ->where('is_read', false)
                    ->count(),
                'read' => Notifikasi::where('user_id', $user->id)
                    ->where('is_read', true)
                    ->count(),
            ],
        ];

        return view('pages.dashboard.admin_peran', compact('stats'));
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

        return view('pages.dashboard.verifikator', compact('stats'));
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

        return view('pages.dashboard.pokja', compact('stats'));
    }

    private function prepareActivityChartData()
    {
        // generate labels/data for last 7 days, 8 weeks, and 6 months
        $dailyLabels = [];
        $dailyData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dailyLabels[] = now()->subDays($i)->format('d M');
            $dailyData[] = DB::table('activity_log')
                ->whereDate('created_at', $date)
                ->count();
        }

        $weeklyLabels = [];
        $weeklyData = [];
        for ($i = 7; $i >= 0; $i--) {
            $start = now()->subWeeks($i)->startOfWeek();
            $end = (clone $start)->endOfWeek();
            $weeklyLabels[] = $start->format('d M');
            $weeklyData[] = DB::table('activity_log')
                ->whereBetween('created_at', [$start, $end])
                ->count();
        }

        $monthlyLabels = [];
        $monthlyData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthlyLabels[] = $month->format('M Y');
            $monthlyData[] = DB::table('activity_log')
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
        }

        return [
            'daily' => ['labels' => $dailyLabels, 'data' => $dailyData],
            'weekly' => ['labels' => $weeklyLabels, 'data' => $weeklyData],
            'monthly' => ['labels' => $monthlyLabels, 'data' => $monthlyData],
        ];
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

        return view('pages.dashboard.kab_kota', compact('stats'));
    }

    private function auditorDashboard($user)
    {
        $stats = [
            'total_permohonan' => Permohonan::count(),
            'in_process' => Permohonan::whereIn('status_akhir', ['proses', 'revisi'])->count(),
            'completed_this_month' => Permohonan::where('status_akhir', 'selesai')
                ->whereMonth('updated_at', now()->month)
                ->count(),
            'total_activities' => DB::table('activity_log')
                ->where('created_at', '>=', now()->subDays(7))
                ->count(),
            'recent_activities' => DB::table('activity_log')
                ->leftJoin('users', 'activity_log.causer_id', '=', 'users.id')
                ->select('activity_log.*', 'users.name as causer_name')
                ->orderBy('activity_log.created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($activity) {
                    $activity->causer = (object) ['name' => $activity->causer_name];
                    return $activity;
                }),
            'total_users' => User::count(),
            'total_kabkota' => \App\Models\KabupatenKota::count(),
            'recent_permohonan' => Permohonan::with(['kabupatenKota', 'jenisDokumen'])
                ->latest()
                ->limit(10)
                ->get()
        ];

        return view('pages.dashboard.auditor', compact('stats'));
    }
}
