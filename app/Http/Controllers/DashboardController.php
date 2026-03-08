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
use App\Models\UndanganPenerima;
use App\Models\KabupatenKota;
use App\Models\MasterJenisDokumen;
use App\Models\MasterUrusan;
use App\Models\HasilFasilitasi;
use App\Models\PenetapanJadwalFasilitasi;
use App\Models\TimFasilitasiAssignment;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Redirect berdasarkan role
        return match (true) {
            $user->hasRole('superadmin') => $this->superadminDashboard($user),
            $user->hasRole('kaban') => $this->kabanDashboard($user),
            $user->hasRole('admin_peran') => $this->adminPeranDashboard($user),
            $user->hasRole('verifikator') => $this->verifikatorDashboard($user),
            $user->hasRole('fasilitator') => $this->pokjaDashboard($user),
            $user->hasRole('auditor') => $this->auditorDashboard($user),
            $user->hasRole('pemohon') => $this->kabKotaDashboard($user),
            default => view('pages.dashboard.default', ['user' => $user])
        };
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
            'pending_approval' => HasilFasilitasi::where('status_draft', 'menunggu_persetujuan_kaban')->count(),
            'total_permohonan' => Permohonan::count(),
            'completed_this_month' => $this->countCompletedThisMonth(),
            'hasil_fasilitasi_approval' => $this->getHasilFasilitasiForApproval(),
            'permohonan_list' => $this->getPermohonanListData(),
            'jenis_dokumen_list' => $this->getJenisDokumenList(),
            'kabupaten_kota_list' => $this->getKabupatenKotaList(),
            'penetapan_jadwal' => $this->getPenetapanJadwal(),
            'activity_chart' => $this->prepareActivityChartData(),
            'notifications' => $this->getNotificationCounts($user->id),
        ];

        return view('pages.dashboard.kaban', compact('stats'));
    }

    private function adminPeranDashboard($user)
    {
        $stats = [
            'permohonan_list' => $this->getPermohonanListData(),
            'jenis_dokumen_list' => $this->getJenisDokumenList(),
            'kabupaten_kota_list' => $this->getKabupatenKotaList(),
            'jadwal_aktif' => $this->getActiveJadwal(3),
            'master_data' => $this->getMasterDataCounts(),
            'activity_chart' => $this->prepareActivityChartData(),
            'users' => ['total' => User::count()],
            'notifications' => $this->getNotificationCounts($user->id),
        ];

        return view('pages.dashboard.admin_peran', compact('stats'));
    }

    private function verifikatorDashboard($user)
    {
        $permohonanList = $this->getPermohonanListData(['status_akhir' => 'proses']);

        $stats = [
            'my_verifikasi' => Permohonan::where('status_akhir', 'proses')->count(),
            'completed_verifikasi' => $this->countCompletedThisMonth(),
            'pending_verifikasi' => Permohonan::where('status_akhir', 'proses')->count(),
            'permohonan_list' => $permohonanList,
            'my_tasks' => $this->getVerifikatorTasks(),
            'undangan' => $this->getUndanganForUser($user->id),
            'notifications' => $this->getNotificationCounts($user->id),
        ];

        return view('pages.dashboard.verifikator', compact('stats'));
    }

    private function pokjaDashboard($user)
    {
        $permohonanIds = TimFasilitasiAssignment::where('user_id', $user->id)
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
            'my_evaluasi_tasks' => $this->getEvaluasiTasks($permohonanIds),
            'undangan' => $this->getUndanganForUser($user->id),
            'notifications' => $this->getNotificationCounts($user->id),
        ];

        return view('pages.dashboard.pokja', compact('stats'));
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
            'permohonan_list' => $this->getPermohonanListData(['user_id' => $user->id]),
            'jadwal_aktif' => $this->getPublishedJadwal(),
            'activity_chart' => $this->prepareActivityChartData(),
            'notifications' => $this->getNotificationCounts($user->id),
        ];

        return view('pages.dashboard.kab_kota', compact('stats'));
    }

    private function auditorDashboard($user)
    {
        $stats = [
            'total_permohonan' => Permohonan::count(),
            'in_process' => Permohonan::whereIn('status_akhir', ['proses', 'revisi'])->count(),
            'completed_this_month' => $this->countCompletedThisMonth(),
            'total_activities' => DB::table('activity_log')
                ->where('created_at', '>=', now()->subDays(7))
                ->count(),
            'recent_activities' => $this->getRecentActivities(),
            'total_users' => User::count(),
            'total_kabkota' => KabupatenKota::count(),
            'recent_permohonan' => $this->getRecentPermohonan(10),
            'activity_chart' => $this->prepareActivityChartData(),
            'notifications' => $this->getNotificationCounts($user->id),
        ];

        return view('pages.dashboard.auditor', compact('stats'));
    }

    // ============================================
    // HELPER METHODS
    // ============================================

    /**
     * Get notification counts for user
     */
    private function getNotificationCounts(int $userId): array
    {
        return [
            'total' => Notifikasi::where('user_id', $userId)->count(),
            'unread' => Notifikasi::where('user_id', $userId)->where('is_read', false)->count(),
            'read' => Notifikasi::where('user_id', $userId)->where('is_read', true)->count(),
        ];
    }

    /**
     * Get permohonan list data with mapping
     */
    private function getPermohonanListData(array $conditions = []): array
    {
        $query = Permohonan::with(['jenisDokumen', 'kabupatenKota'])->latest();

        foreach ($conditions as $key => $value) {
            if (is_array($value)) {
                $query->whereIn($key, $value);
            } else {
                $query->where($key, $value);
            }
        }

        return $query->get()->map(fn($permohonan) => [
            'id' => $permohonan->id,
            'nomor_permohonan' => $this->generateNomorPermohonan($permohonan->id),
            'tahun' => $permohonan->tahun ?? date('Y'),
            'jenis_dokumen_id' => $permohonan->jenis_dokumen_id ?? '',
            'jenis_dokumen_nama' => $permohonan->jenisDokumen?->nama ?? '-',
            'kabupaten_kota_id' => $permohonan->kab_kota_id ?? '',
            'kabupaten_kota_nama' => $permohonan->kabupatenKota?->nama ?? '-',
            'status' => $permohonan->status_akhir ?? 'draft',
        ])->toArray();
    }

    /**
     * Generate nomor permohonan
     */
    private function generateNomorPermohonan(int $id): string
    {
        return 'PRM-' . str_pad($id, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Get jenis dokumen list for dropdown
     */
    private function getJenisDokumenList(): array
    {
        return MasterJenisDokumen::pluck('nama', 'id')->toArray();
    }

    /**
     * Get kabupaten kota list for dropdown
     */
    private function getKabupatenKotaList(): array
    {
        return KabupatenKota::pluck('nama', 'id')->toArray();
    }

    /**
     * Count completed permohonan this month
     */
    private function countCompletedThisMonth(): int
    {
        return Permohonan::where('status_akhir', 'selesai')
            ->whereMonth('updated_at', now()->month)
            ->count();
    }

    /**
     * Get hasil fasilitasi waiting for kaban approval
     */
    private function getHasilFasilitasiForApproval()
    {
        return HasilFasilitasi::with([
            'permohonan.kabupatenKota',
            'permohonan.jenisDokumen',
            'pembuat'
        ])
            ->where('status_draft', 'menunggu_persetujuan_kaban')
            ->orderBy('tanggal_diajukan_kaban', 'asc')
            ->limit(5)
            ->get();
    }

    /**
     * Get penetapan jadwal
     */
    private function getPenetapanJadwal()
    {
        return PenetapanJadwalFasilitasi::with([
            'permohonan.kabupatenKota',
            'permohonan.jenisDokumen',
            'jadwalFasilitasi.jenisDokumen',
            'penetap'
        ])
            ->orderBy('tanggal_penetapan', 'desc')
            ->limit(5)
            ->get();
    }

    /**
     * Get active jadwal
     */
    private function getActiveJadwal(int $limit = 3)
    {
        return JadwalFasilitasi::where('status', 'published')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get published jadwal for pemohon
     */
    private function getPublishedJadwal(int $limit = 5)
    {
        return JadwalFasilitasi::where('status', 'published')
            ->where('batas_permohonan', '>=', now())
            ->with(['jenisDokumen'])
            ->orderBy('batas_permohonan', 'asc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get master data counts
     */
    private function getMasterDataCounts(): array
    {
        return [
            'kabupaten_kota' => KabupatenKota::count(),
            'jenis_dokumen' => MasterJenisDokumen::count(),
            'urusan' => MasterUrusan::count(),
        ];
    }

    /**
     * Get verifikator tasks
     */
    private function getVerifikatorTasks()
    {
        return Permohonan::with(['kabupatenKota', 'jenisDokumen'])
            ->where('status_akhir', 'proses')
            ->latest()
            ->limit(5)
            ->get();
    }

    /**
     * Get evaluasi tasks for fasilitator
     */
    private function getEvaluasiTasks($permohonanIds)
    {
        return Permohonan::with(['kabupatenKota', 'jenisDokumen'])
            ->whereIn('id', $permohonanIds)
            ->where('status_akhir', 'proses')
            ->latest()
            ->limit(5)
            ->get();
    }

    /**
     * Get undangan for user
     */
    private function getUndanganForUser(int $userId, int $limit = 5)
    {
        return UndanganPenerima::with([
            'undangan.permohonan.kabupatenKota',
            'undangan.permohonan.jenisDokumen',
            'undangan.penetapanJadwal'
        ])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get recent activities for auditor
     */
    private function getRecentActivities(int $limit = 5)
    {
        return DB::table('activity_log')
            ->leftJoin('users', 'activity_log.causer_id', '=', 'users.id')
            ->select('activity_log.*', 'users.name as causer_name')
            ->orderBy('activity_log.created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($activity) {
                $activity->causer = (object) ['name' => $activity->causer_name];
                return $activity;
            });
    }

    /**
     * Get recent permohonan
     */
    private function getRecentPermohonan(int $limit = 10)
    {
        return Permohonan::with(['kabupatenKota', 'jenisDokumen'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Prepare activity chart data
     */
    private function prepareActivityChartData(): array
    {
        return [
            'daily' => $this->getActivityData('daily', 7),
            'weekly' => $this->getActivityData('weekly', 8),
            'monthly' => $this->getActivityData('monthly', 6),
        ];
    }

    /**
     * Get activity data for chart
     */
    private function getActivityData(string $period, int $count): array
    {
        $labels = [];
        $data = [];

        for ($i = $count - 1; $i >= 0; $i--) {
            switch ($period) {
                case 'daily':
                    $date = now()->subDays($i);
                    $labels[] = $date->format('d M');
                    $data[] = DB::table('activity_log')
                        ->whereDate('created_at', $date->format('Y-m-d'))
                        ->count();
                    break;

                case 'weekly':
                    $start = now()->subWeeks($i)->startOfWeek();
                    $end = (clone $start)->endOfWeek();
                    $labels[] = $start->format('d M');
                    $data[] = DB::table('activity_log')
                        ->whereBetween('created_at', [$start, $end])
                        ->count();
                    break;

                case 'monthly':
                    $month = now()->subMonths($i);
                    $labels[] = $month->format('M Y');
                    $data[] = DB::table('activity_log')
                        ->whereYear('created_at', $month->year)
                        ->whereMonth('created_at', $month->month)
                        ->count();
                    break;
            }
        }

        return ['labels' => $labels, 'data' => $data];
    }
}
