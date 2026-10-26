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
        ]);

        $response->assertRedirect($this->panelUrl());
        $this->assertSame('2026-10-08', $verifikasi->fresh()->tanggal_selesai->toDateString());

        $riwayat = PermohonanTahapanRiwayat::sole();
        $this->assertSame(PermohonanTahapanRiwayat::JENIS_TANGGAL_SELESAI, $riwayat->jenis);
        $this->assertSame('2026-10-05', $riwayat->nilai_lama->toDateString());
        $this->assertSame('2026-10-08', $riwayat->nilai_baru->toDateString());
        $this->assertTrue($riwayat->user->is($this->admin));
        $this->assertNull($riwayat->alasan);

        $step = collect($this->permohonan->fresh()->getProgressSteps())->firstWhere('name', 'Verifikasi');
        $this->assertSame('2026-10-08', $step['date']->toDateString());
    }

    /**
     * @return array<string, array{array<string, string>, string, string}>
     */
    public static function invalidPayloads(): array
    {
        return [
            'masa depan' => [['tanggal_selesai' => '2026-10-21'], 'tanggal_selesai', 'Tanggal selesai tidak boleh di masa depan.'],
            'sebelum permohonan dibuat' => [['tanggal_selesai' => '2026-09-01'], 'tanggal_selesai', 'Tanggal selesai tidak boleh sebelum permohonan dibuat.'],
            'lebih awal dari tahapan sebelumnya' => [['tanggal_selesai' => '2026-09-29'], 'tanggal_selesai', 'Tanggal selesai tidak boleh lebih awal dari tahapan Permohonan (30 September 2026).'],
            'lebih akhir dari tahapan sesudahnya' => [['tanggal_selesai' => '2026-10-11'], 'tanggal_selesai', 'Tanggal selesai tidak boleh lebih akhir dari tahapan Penetapan Jadwal (10 Oktober 2026).'],
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
        ])->assertSessionHasErrors(['tanggal_selesai' => 'Hanya tahapan yang sudah selesai yang dapat diubah tanggal selesainya.']);
    }

    public function test_tahapan_of_another_permohonan_is_not_found(): void
    {
        $lain = PermohonanTahapan::factory()->selesai(now()->subDays(3))->create();

        $this->actingAs($this->admin)->put(
            route('permohonan.tahapan.update-tanggal-selesai', [$this->permohonan, $lain]),
            ['tanggal_selesai' => '2026-10-18'],
        )->assertNotFound();
    }

    public function test_role_without_permission_is_forbidden(): void
    {
        $fasilitator = User::factory()->withRole('fasilitator')->create();

        $this->actingAs($fasilitator)->put($this->url($this->tahapan[2]), [
            'tanggal_selesai' => '2026-10-06',
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

    public function test_changed_date_is_shown_in_tahapan_timeline_keeping_the_time(): void
    {
        $verifikasi = $this->tahapan[2];
        $verifikasi->update(['tanggal_selesai' => now()->setDate(2026, 10, 5)->setTime(14, 30)]);

        $this->actingAs($this->admin)->put($this->url($verifikasi), ['tanggal_selesai' => '2026-10-08'])
            ->assertRedirect($this->panelUrl());

        $this->assertSame('2026-10-08 14:30', $verifikasi->fresh()->tanggal_selesai->format('Y-m-d H:i'));
        $this->actingAs($this->admin)->get(route('permohonan.show', $this->permohonan))
            ->assertOk()
            ->assertSee('Selesai: <span', false)
            ->assertSee('08 Oct 2026, 14:30');
    }

    public function test_panel_does_not_show_change_history(): void
    {
        $this->createRiwayat();

        $this->actingAs($this->admin)->get(route('permohonan.show', $this->permohonan))
            ->assertOk()
            ->assertSee('id="tanggal-selesai-tahapan"', false)
            ->assertDontSee('Riwayat perubahan Verifikasi')
            ->assertDontSee('berita acara');
    }

    /**
     * @return array<string, array{string}>
     */
    public static function rolesWithoutPanel(): array
    {
        return [
            'kaban' => ['kaban'],
            'pemohon' => ['pemohon'],
            'auditor' => ['auditor'],
        ];
    }

    #[DataProvider('rolesWithoutPanel')]
    public function test_panel_is_hidden_from_other_roles(string $role): void
    {
        $this->createRiwayat();
        $user = User::factory()->withRole($role)->create(['kabupaten_kota_id' => $this->permohonan->kab_kota_id]);

        $this->actingAs($user)->get(route('permohonan.show', $this->permohonan))
            ->assertOk()
            ->assertDontSee('id="tanggal-selesai-tahapan"', false);
    }

    private function createRiwayat(): void
    {
        $this->tahapan[2]->riwayat()->create([
            'jenis' => PermohonanTahapanRiwayat::JENIS_TANGGAL_SELESAI,
            'nilai_lama' => now()->subDays(15),
            'nilai_baru' => now()->subDays(14),
            'alasan' => 'Penyesuaian <b>berita acara</b>.',
            'user_id' => $this->admin->id,
        ]);
    }

    public function test_admin_sees_inline_edit_form(): void
    {
        $this->actingAs($this->admin)->get(route('permohonan.show', $this->permohonan))
            ->assertOk()
            ->assertSee('js-form-ubah', false)
            ->assertSee('3/3 tahapan selesai')
            ->assertSee('30 Sep 2026');
    }

    public function test_panel_lists_all_tahapan_and_limits_date_picker_to_valid_range(): void
    {
        $tambahan = MasterTahapan::factory()->create(['nama_tahapan' => 'Pelaksanaan', 'urutan' => 4]);

        $this->actingAs($this->admin)->get(route('permohonan.show', $this->permohonan))
            ->assertOk()
            ->assertSee('3/4 tahapan selesai')
            ->assertSee('4. '.$tambahan->nama_tahapan)
            ->assertSee('min="2026-09-30" max="2026-10-10"', false)
            ->assertSee('Rentang: 30 Sep 2026 – 10 Okt 2026');
    }

    public function test_validation_error_is_shown_on_the_edited_row(): void
    {
        $verifikasi = $this->tahapan[2];

        $this->actingAs($this->admin)
            ->from(route('permohonan.show', $this->permohonan))
            ->followingRedirects()
            ->put($this->url($verifikasi), ['permohonan_tahapan_id' => $verifikasi->id, 'tanggal_selesai' => '2026-10-11'])
            ->assertOk()
            ->assertSee('Tanggal selesai tidak boleh lebih akhir dari tahapan Penetapan Jadwal (10 Oktober 2026).')
            ->assertSee('value="2026-10-11"', false);
    }

    private function panelUrl(): string
    {
        return route('permohonan.show', $this->permohonan).'#tanggal-selesai-tahapan';
    }

    private function url(PermohonanTahapan $tahapan): string
    {
        return route('permohonan.tahapan.update-tanggal-selesai', [$this->permohonan, $tahapan]);
    }
}
