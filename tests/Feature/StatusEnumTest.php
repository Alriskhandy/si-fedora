<?php

namespace Tests\Feature;

use App\Enums\StatusJadwal;
use App\Enums\StatusPermohonan;
use App\Models\JadwalFasilitasi;
use App\Models\MasterKelengkapanVerifikasi;
use App\Models\MasterTahapan;
use App\Models\Permohonan;
use App\Models\PermohonanDokumen;
use App\Models\PermohonanTahapan;
use App\Models\User;
use App\Services\FonteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Halaman yang membaca kolom status (sudah di-cast ke enum, Fase 4 butir C) tetap tampil untuk setiap nilai status.
 */
class StatusEnumTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{string, string}>
     */
    public static function statusPermohonan(): array
    {
        return [
            'belum' => ['belum', 'pending'],
            'proses' => ['proses', 'pending'],
            'revisi' => ['revisi', 'revision'],
            'selesai' => ['selesai', 'verified'],
        ];
    }

    #[DataProvider('statusPermohonan')]
    public function test_permohonan_pages_render_for_each_status(string $status, string $statusVerifikasi): void
    {
        $admin = User::factory()->withRole('admin_peran')->create();
        $permohonan = $this->createPermohonan($status, $statusVerifikasi);

        foreach (['permohonan.show', 'permohonan.tahapan.permohonan', 'permohonan.tahapan.verifikasi', 'permohonan.tahapan.pelaksanaan', 'permohonan.tahapan.hasil'] as $route) {
            $this->actingAs($admin)->get(route($route, $permohonan))->assertOk();
        }
        $this->actingAs($admin)->get(route('permohonan.index'))->assertOk();
    }

    /**
     * @return array<string, array{string}>
     */
    public static function roleDashboard(): array
    {
        return [
            'kaban' => ['kaban'],
            'admin peran' => ['admin_peran'],
            'verifikator' => ['verifikator'],
            'fasilitator' => ['fasilitator'],
            'auditor' => ['auditor'],
            'pemohon' => ['pemohon'],
        ];
    }

    #[DataProvider('roleDashboard')]
    public function test_dashboard_renders_permohonan_status_for_each_role(string $role): void
    {
        $permohonan = $this->createPermohonan('proses', 'pending');
        $user = User::factory()->withRole($role)->create(['kabupaten_kota_id' => $permohonan->kabupaten_kota_id]);
        JadwalFasilitasi::factory()->create(['status' => 'published']);

        $this->actingAs($user)->get(route('dashboard'))->assertOk();
    }

    public function test_jadwal_pages_render_status_label(): void
    {
        $admin = User::factory()->withRole('admin_peran')->create();
        $jadwal = JadwalFasilitasi::factory()->create(['status' => 'published']);

        $this->actingAs($admin)->get(route('jadwal.index'))->assertSee('Published');
        $this->actingAs($admin)->get(route('jadwal.show', $jadwal))->assertSee('Published');
        $this->actingAs($admin)->get(route('jadwal.edit', $jadwal))->assertSeeInOrder(['value="published"', 'selected', 'Published'], false);
    }

    public function test_saving_published_jadwal_again_does_not_resend_whatsapp(): void
    {
        $admin = User::factory()->withRole('admin_peran')->create();
        User::factory()->withRole('pemohon')->create(['no_hp' => '081234567890']);
        $jadwal = JadwalFasilitasi::factory()->create(['status' => 'published']);
        $this->mock(FonteService::class)->shouldNotReceive('sendBulkMessage');

        $response = $this->actingAs($admin)->put(route('jadwal.update', $jadwal), $this->jadwalPayload($jadwal, 'published'));

        $response->assertRedirect();
        $this->assertSame(StatusJadwal::Published, $jadwal->fresh()->status);
    }

    public function test_publishing_draft_jadwal_sends_whatsapp(): void
    {
        $admin = User::factory()->withRole('admin_peran')->create();
        User::factory()->withRole('pemohon')->create(['no_hp' => '081234567890']);
        $jadwal = JadwalFasilitasi::factory()->create(['status' => 'draft']);
        $this->mock(FonteService::class)->shouldReceive('sendBulkMessage')->once()->andReturn(['success' => true]);

        $response = $this->actingAs($admin)->put(route('jadwal.update', $jadwal), $this->jadwalPayload($jadwal, 'published'));

        $response->assertRedirect();
        $this->assertSame(StatusJadwal::Published, $jadwal->fresh()->status);
    }

    /**
     * @return array<string, mixed>
     */
    private function jadwalPayload(JadwalFasilitasi $jadwal, string $status): array
    {
        return [
            'tahun_anggaran' => $jadwal->tahun_anggaran,
            'jenis_dokumen' => $jadwal->jenis_dokumen,
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addMonth()->toDateString(),
            'status' => $status,
        ];
    }

    private function createPermohonan(string $status, string $statusVerifikasi): Permohonan
    {
        $permohonan = Permohonan::factory()->create(['status_akhir' => $status, 'submitted_at' => $status === 'belum' ? null : now()]);
        foreach (['Permohonan', 'Verifikasi', 'Penetapan Jadwal', 'Pelaksanaan', 'Hasil Fasilitasi / Evaluasi'] as $urutan => $nama) {
            PermohonanTahapan::factory()->for($permohonan)->create([
                'tahapan_id' => MasterTahapan::factory()->create(['nama_tahapan' => $nama, 'urutan' => $urutan + 1])->id,
                'status' => $urutan === 0 ? StatusPermohonan::Selesai : StatusPermohonan::from($status),
            ]);
        }
        PermohonanDokumen::create([
            'permohonan_id' => $permohonan->id,
            'master_kelengkapan_id' => MasterKelengkapanVerifikasi::create(['nama_dokumen' => 'Surat Permohonan'])->id,
            'is_ada' => true,
            'status_verifikasi' => $statusVerifikasi,
        ]);

        return $permohonan;
    }
}
