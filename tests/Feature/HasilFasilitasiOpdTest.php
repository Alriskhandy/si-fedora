<?php

namespace Tests\Feature;

use App\Enums\StatusHasilFasilitasi;
use App\Models\HasilFasilitasi;
use App\Models\HasilFasilitasiUrusan;
use App\Models\MasterUrusan;
use App\Models\Opd;
use App\Models\Permohonan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class HasilFasilitasiOpdTest extends TestCase
{
    use RefreshDatabase;

    private Permohonan $permohonan;

    private HasilFasilitasi $hasil;

    private MasterUrusan $urusanBidang;

    private MasterUrusan $urusanLain;

    private User $opdUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->permohonan = Permohonan::factory()->create();
        $this->hasil = HasilFasilitasi::factory()->for($this->permohonan)->create(['status_draft' => 'draft']);
        $this->urusanBidang = MasterUrusan::factory()->create(['kode_urusan' => '1.02', 'nama_urusan' => 'Kesehatan']);
        $this->urusanLain = MasterUrusan::factory()->create(['kode_urusan' => '3.27', 'nama_urusan' => 'Pertanian Lahan Kering']);

        $opd = Opd::factory()->create(['nama_opd' => 'Dinas Kesehatan Provinsi', 'singkatan' => 'DINKES']);
        $opd->urusan()->attach($this->urusanBidang);
        $this->opdUser = User::factory()->withRole('opd')->create(['opd_id' => $opd->id]);
    }

    public function test_opd_adds_sanitized_input_on_urusan_of_its_field(): void
    {
        $response = $this->actingAs($this->opdUser)->postJson(route('hasil-fasilitasi.urusan.store', $this->permohonan), [
            'master_urusan_id' => $this->urusanBidang->id,
            'catatan_masukan' => '<p>Perkuat target SPM</p><script>alert(1)</script>',
        ]);

        $response->assertOk()->assertJsonPath('data.user.opd.nama_opd', 'Dinas Kesehatan Provinsi');
        $item = HasilFasilitasiUrusan::sole();
        $this->assertTrue($item->user->is($this->opdUser));
        $this->assertStringNotContainsString('<script>', (string) $item->getRawOriginal('catatan_masukan'));
    }

    public function test_opd_cannot_add_input_on_urusan_outside_its_field(): void
    {
        $this->actingAs($this->opdUser)->postJson(route('hasil-fasilitasi.urusan.store', $this->permohonan), [
            'master_urusan_id' => $this->urusanLain->id,
            'catatan_masukan' => 'Masukan di luar bidang.',
        ])->assertForbidden();

        $this->assertSame(0, HasilFasilitasiUrusan::count());
    }

    public function test_opd_without_field_urusan_cannot_add_input(): void
    {
        $opdTanpaUrusan = User::factory()->withRole('opd')->create(['opd_id' => Opd::factory()->create()->id]);

        $this->actingAs($opdTanpaUrusan)->postJson(route('hasil-fasilitasi.urusan.store', $this->permohonan), [
            'master_urusan_id' => $this->urusanBidang->id,
            'catatan_masukan' => 'Masukan tanpa bidang.',
        ])->assertForbidden();
    }

    /**
     * @return array<string, array{string}>
     */
    public static function nonUrusanStoreRoutes(): array
    {
        return [
            'sistematika' => ['hasil-fasilitasi.sistematika.store'],
            'form' => ['hasil-fasilitasi.form.store'],
            'rekomendasi' => ['hasil-fasilitasi.rekomendasi.store'],
        ];
    }

    #[DataProvider('nonUrusanStoreRoutes')]
    public function test_opd_cannot_input_other_sections(string $routeName): void
    {
        $this->actingAs($this->opdUser)->postJson(route($routeName, $this->permohonan), [])->assertForbidden();
    }

    public function test_opd_cannot_change_input_of_another_user(): void
    {
        $item = HasilFasilitasiUrusan::create([
            'hasil_fasilitasi_id' => $this->hasil->id,
            'master_urusan_id' => $this->urusanBidang->id,
            'catatan_masukan' => 'Masukan fasilitator.',
            'user_id' => User::factory()->withRole('fasilitator')->create()->id,
        ]);

        $this->actingAs($this->opdUser)->putJson(route('hasil-fasilitasi.urusan.update', [$this->permohonan, $item->id]), [
            'catatan_masukan' => 'Diubah OPD.',
        ])->assertForbidden();
    }

    /**
     * @return array<string, array{string}>
     */
    public static function lockedStatuses(): array
    {
        return [
            'menunggu persetujuan Kaban' => ['menunggu_persetujuan_kaban'],
            'disetujui Kaban' => ['disetujui_kaban'],
        ];
    }

    #[DataProvider('lockedStatuses')]
    public function test_input_is_locked_for_opd_and_fasilitator_while_reviewed_or_approved_by_kaban(string $status): void
    {
        $this->hasil->update(['status_draft' => $status]);
        $fasilitator = User::factory()->withRole('fasilitator')->create();

        $this->actingAs($this->opdUser)->postJson(route('hasil-fasilitasi.urusan.store', $this->permohonan), [
            'master_urusan_id' => $this->urusanBidang->id,
            'catatan_masukan' => 'Masukan terlambat.',
        ])->assertStatus(423);

        $this->actingAs($fasilitator)->postJson(route('hasil-fasilitasi.sistematika.store', $this->permohonan), [
            'master_bab_id' => 1,
            'catatan_penyempurnaan' => 'Masukan terlambat.',
        ])->assertStatus(423);

        $this->assertSame(0, HasilFasilitasiUrusan::count());
    }

    public function test_input_reopens_after_kaban_rejects(): void
    {
        $this->hasil->update(['status_draft' => 'ditolak_kaban']);

        $this->actingAs($this->opdUser)->postJson(route('hasil-fasilitasi.urusan.store', $this->permohonan), [
            'master_urusan_id' => $this->urusanBidang->id,
            'catatan_masukan' => 'Masukan revisi.',
        ])->assertOk();
    }

    public function test_input_page_for_opd_shows_only_urusan_of_its_field(): void
    {
        $this->actingAs($this->opdUser)->get(route('hasil-fasilitasi.create', $this->permohonan))
            ->assertOk()
            ->assertSee('Kesehatan')
            ->assertDontSee('Pertanian Lahan Kering');
    }

    public function test_locked_input_page_shows_notice(): void
    {
        $this->hasil->update(['status_draft' => 'menunggu_persetujuan_kaban']);

        $this->actingAs($this->opdUser)->get(route('hasil-fasilitasi.create', $this->permohonan))
            ->assertOk()
            ->assertSee('Masukan tidak dapat ditambah, diubah, atau dihapus.');
    }

    public function test_opd_can_view_hasil_and_its_dashboard(): void
    {
        $this->actingAs($this->opdUser)->get(route('hasil-fasilitasi.show', $this->permohonan))->assertOk();
        $this->actingAs($this->opdUser)->get(route('dashboard'))->assertOk();
    }

    public function test_kaban_sees_opd_input_with_its_source_on_approval_pages(): void
    {
        $this->hasil->update(['status_draft' => 'menunggu_persetujuan_kaban', 'draft_final_file' => 'hasil-fasilitasi/draft.pdf', 'tanggal_diajukan_kaban' => now()]);
        HasilFasilitasiUrusan::create([
            'hasil_fasilitasi_id' => $this->hasil->id,
            'master_urusan_id' => $this->urusanBidang->id,
            'catatan_masukan' => 'Perkuat target SPM.',
            'user_id' => $this->opdUser->id,
        ]);
        $kaban = User::factory()->withRole('kaban')->create();

        $this->actingAs($kaban)->get(route('approval.index'))->assertOk()->assertSee('1 masukan OPD');
        $this->actingAs($kaban)->get(route('approval.show', $this->permohonan))
            ->assertOk()
            ->assertSee('Masukan Urusan Pemerintahan')
            ->assertSee('DINKES')
            ->assertSee('Perkuat target SPM.');
    }

    public function test_team_can_submit_hasil_for_validation(): void
    {
        $fasilitator = User::factory()->withRole('fasilitator')->create();

        $this->actingAs($fasilitator)->post(route('hasil-fasilitasi.submit', $this->permohonan))->assertRedirect();

        $this->assertSame(StatusHasilFasilitasi::Diajukan, $this->hasil->fresh()->status_draft);
    }
}
