<?php

namespace Tests\Feature;

use App\Models\MasterTahapan;
use App\Models\Permohonan;
use App\Models\PermohonanTahapan;
use App\Models\PermohonanTahapanRiwayat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TanggalSelesaiTahapanTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Permohonan $permohonan;

    /** @var array<int, PermohonanTahapan> */
    private array $tahapan;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(now()->setDate(2026, 10, 20)->startOfDay());

        $this->admin = User::factory()->withRole('admin_peran')->create();
        $this->permohonan = Permohonan::factory()->create(['created_at' => now()->subDays(30)]);

        foreach ([1 => 'Permohonan', 2 => 'Verifikasi', 3 => 'Penetapan Jadwal'] as $urutan => $nama) {
            $master = MasterTahapan::factory()->create(['nama_tahapan' => $nama, 'urutan' => $urutan]);
            $this->tahapan[$urutan] = PermohonanTahapan::factory()
                ->for($this->permohonan)
                ->for($master, 'masterTahapan')
                ->selesai(now()->subDays(25 - $urutan * 5))
                ->create();
        }
    }

    public function test_admin_changes_tanggal_selesai_and_change_is_recorded_in_history(): void
    {
        $verifikasi = $this->tahapan[2];

        $response = $this->actingAs($this->admin)->put($this->url($verifikasi), [
            'tanggal_selesai' => '2026-10-08',
            'alasan' => 'Penyesuaian sesuai berita acara verifikasi.',
        ]);

        $response->assertRedirect(route('permohonan.show', $this->permohonan));
        $this->assertSame('2026-10-08', $verifikasi->fresh()->tanggal_selesai->toDateString());

        $riwayat = PermohonanTahapanRiwayat::sole();
        $this->assertSame(PermohonanTahapanRiwayat::JENIS_TANGGAL_SELESAI, $riwayat->jenis);
        $this->assertSame('2026-10-05', $riwayat->nilai_lama->toDateString());
        $this->assertSame('2026-10-08', $riwayat->nilai_baru->toDateString());
        $this->assertTrue($riwayat->user->is($this->admin));

        $step = collect($this->permohonan->fresh()->getProgressSteps())->firstWhere('name', 'Verifikasi');
        $this->assertSame('2026-10-08', $step['date']->toDateString());
    }

    public function test_first_correction_of_estimated_date_is_not_recorded_in_history(): void
    {
        $verifikasi = $this->tahapan[2];
        $verifikasi->update(['tanggal_selesai_perkiraan' => true]);

        $this->actingAs($this->admin)->put($this->url($verifikasi), [
            'tanggal_selesai' => '2026-10-07',
            'alasan' => 'Koreksi data lama hasil migrasi.',
        ])->assertRedirect(route('permohonan.show', $this->permohonan));

        $this->assertFalse($verifikasi->fresh()->tanggal_selesai_perkiraan);
        $this->assertSame(0, PermohonanTahapanRiwayat::count());
    }

    /**
     * @return array<string, array{array<string, string>, string, string}>
     */
    public static function invalidPayloads(): array
    {
        return [
            'masa depan' => [['tanggal_selesai' => '2026-10-21', 'alasan' => 'Alasan yang cukup panjang.'], 'tanggal_selesai', 'Tanggal selesai tidak boleh di masa depan.'],
            'sebelum permohonan dibuat' => [['tanggal_selesai' => '2026-09-01', 'alasan' => 'Alasan yang cukup panjang.'], 'tanggal_selesai', 'Tanggal selesai tidak boleh sebelum permohonan dibuat.'],
            'lebih awal dari tahapan sebelumnya' => [['tanggal_selesai' => '2026-09-29', 'alasan' => 'Alasan yang cukup panjang.'], 'tanggal_selesai', 'Tanggal selesai tidak boleh lebih awal dari tahapan Permohonan (30 September 2026).'],
            'lebih akhir dari tahapan sesudahnya' => [['tanggal_selesai' => '2026-10-11', 'alasan' => 'Alasan yang cukup panjang.'], 'tanggal_selesai', 'Tanggal selesai tidak boleh lebih akhir dari tahapan Penetapan Jadwal (10 Oktober 2026).'],
            'tanpa alasan' => [['tanggal_selesai' => '2026-10-06'], 'alasan', 'Alasan perubahan wajib diisi.'],
        ];
    }

    #[DataProvider('invalidPayloads')]
    public function test_invalid_tanggal_selesai_is_rejected(array $payload, string $field, string $message): void
    {
        $this->actingAs($this->admin)->put($this->url($this->tahapan[2]), $payload)
            ->assertSessionHasErrors([$field => $message]);

        $this->assertSame('2026-10-05', $this->tahapan[2]->fresh()->tanggal_selesai->toDateString());
    }

    public function test_unfinished_tahapan_cannot_be_changed(): void
    {
        $this->tahapan[3]->update(['status' => 'proses']);

        $this->actingAs($this->admin)->put($this->url($this->tahapan[3]), [
            'tanggal_selesai' => '2026-10-10',
            'alasan' => 'Alasan yang cukup panjang.',
        ])->assertSessionHasErrors(['tanggal_selesai' => 'Hanya tahapan yang sudah selesai yang dapat diubah tanggal selesainya.']);
    }

    public function test_tahapan_of_another_permohonan_is_not_found(): void
    {
        $lain = PermohonanTahapan::factory()->selesai(now()->subDays(3))->create();

        $this->actingAs($this->admin)->put(
            route('permohonan.tahapan.update-tanggal-selesai', [$this->permohonan, $lain]),
            ['tanggal_selesai' => '2026-10-18', 'alasan' => 'Alasan yang cukup panjang.'],
        )->assertNotFound();
    }

    public function test_role_without_permission_is_forbidden(): void
    {
        $fasilitator = User::factory()->withRole('fasilitator')->create();

        $this->actingAs($fasilitator)->put($this->url($this->tahapan[2]), [
            'tanggal_selesai' => '2026-10-06',
            'alasan' => 'Alasan yang cukup panjang.',
        ])->assertForbidden();
    }

    public function test_status_change_fills_and_clears_tanggal_selesai_automatically(): void
    {
        $tahapan = PermohonanTahapan::factory()->for($this->permohonan)->create(['status' => 'proses']);
        $this->assertNull($tahapan->tanggal_selesai);

        $tahapan->update(['status' => 'selesai']);
        $this->assertSame(now()->toDateString(), $tahapan->fresh()->tanggal_selesai->toDateString());

        $tahapan->update(['status' => 'revisi']);
        $this->assertNull($tahapan->fresh()->tanggal_selesai);
    }

    public function test_deadline_change_is_recorded_in_history(): void
    {
        $this->actingAs($this->admin)->put(route('permohonan.tahapan.update-deadline', $this->permohonan), [
            'tahapan' => 'Verifikasi',
            'deadline' => '2026-11-01 10:00',
            'keterangan' => 'Perpanjangan karena dokumen pendukung terlambat.',
        ])->assertSessionHasNoErrors();

        $riwayat = PermohonanTahapanRiwayat::sole();
        $this->assertSame(PermohonanTahapanRiwayat::JENIS_DEADLINE, $riwayat->jenis);
        $this->assertSame('2026-11-01 10:00', $riwayat->nilai_baru->format('Y-m-d H:i'));
    }

    public function test_history_is_visible_to_applicant_who_can_view_the_permohonan(): void
    {
        $pemohon = User::factory()->withRole('pemohon')->create(['kabupaten_kota_id' => $this->permohonan->kab_kota_id]);
        $this->tahapan[2]->riwayat()->create([
            'jenis' => PermohonanTahapanRiwayat::JENIS_TANGGAL_SELESAI,
            'nilai_lama' => now()->subDays(15),
            'nilai_baru' => now()->subDays(14),
            'alasan' => 'Penyesuaian <b>berita acara</b>.',
            'user_id' => $this->admin->id,
        ]);

        $this->actingAs($pemohon)->get(route('permohonan.show', $this->permohonan))
            ->assertOk()
            ->assertSee('Riwayat Perubahan Tanggal')
            ->assertSee('Penyesuaian &lt;b&gt;berita acara&lt;/b&gt;.', false)
            ->assertDontSee('modalTanggalSelesai', false);
    }

    public function test_admin_sees_edit_form_and_estimated_badge(): void
    {
        $this->tahapan[1]->update(['tanggal_selesai_perkiraan' => true]);

        $this->actingAs($this->admin)->get(route('permohonan.show', $this->permohonan))
            ->assertOk()
            ->assertSee('modalTanggalSelesai', false)
            ->assertSee('Perkiraan')
            ->assertSee('30 September 2026');
    }

    private function url(PermohonanTahapan $tahapan): string
    {
        return route('permohonan.tahapan.update-tanggal-selesai', [$this->permohonan, $tahapan]);
    }
}
