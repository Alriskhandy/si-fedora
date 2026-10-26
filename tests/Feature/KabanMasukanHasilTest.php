<?php

namespace Tests\Feature;

use App\Models\HasilFasilitasi;
use App\Models\HasilFasilitasiForm;
use App\Models\HasilFasilitasiRekomendasi;
use App\Models\HasilFasilitasiUrusan;
use App\Models\MasterTahapan;
use App\Models\MasterUrusan;
use App\Models\Opd;
use App\Models\Permohonan;
use App\Models\PermohonanTahapan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KabanMasukanHasilTest extends TestCase
{
    use RefreshDatabase;

    private User $kaban;

    private Permohonan $permohonan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kaban = User::factory()->withRole('kaban')->create();
        $this->permohonan = Permohonan::factory()->create();
        PermohonanTahapan::factory()
            ->for($this->permohonan)
            ->for(MasterTahapan::factory()->create(['nama_tahapan' => 'Hasil Fasilitasi']), 'masterTahapan')
            ->create(['status' => 'proses']);
    }

    public function test_kaban_sees_input_summary_while_hasil_is_still_being_prepared(): void
    {
        $this->createHasilWithOpdInput('draft');

        $this->actingAs($this->kaban)->get(route('permohonan.tahapan.hasil', $this->permohonan))
            ->assertOk()
            ->assertSee('Masukan Hasil Fasilitasi')
            ->assertSee('Urusan Pemerintahan: 1')
            ->assertSee('Dari OPD: 1')
            ->assertSee(route('hasil-fasilitasi.show', $this->permohonan))
            ->assertDontSee('Review &amp; Setujui', false);
    }

    public function test_review_button_appears_when_waiting_for_kaban_approval(): void
    {
        $this->createHasilWithOpdInput('menunggu_persetujuan_kaban', 'hasil-fasilitasi/draft.pdf');

        $this->actingAs($this->kaban)->get(route('permohonan.tahapan.hasil', $this->permohonan))
            ->assertOk()
            ->assertSee(route('approval.show', $this->permohonan));
    }

    public function test_kaban_reads_input_with_opd_source_without_edit_actions(): void
    {
        $this->createHasilWithOpdInput('draft');

        $this->actingAs($this->kaban)->get(route('hasil-fasilitasi.show', $this->permohonan))
            ->assertOk()
            ->assertSee('Perkuat target SPM.')
            ->assertSee('DINKES')
            ->assertDontSee(route('hasil-fasilitasi.generate', $this->permohonan->id))
            ->assertDontSee(route('hasil-fasilitasi.submit-to-kaban', $this->permohonan->id));
    }

    public function test_kaban_sees_consistency_and_recommendation_sections(): void
    {
        $this->createHasilWithOpdInput('draft');
        $hasil = $this->permohonan->hasilFasilitasi()->sole();
        HasilFasilitasiForm::create(['hasil_fasilitasi_id' => $hasil->id, 'catatan' => 'Selaraskan target RPJMD.', 'user_id' => $this->kaban->id]);
        HasilFasilitasiRekomendasi::create(['hasil_fasilitasi_id' => $hasil->id, 'catatan' => 'Rekomendasi perbaikan indikator.', 'user_id' => $this->kaban->id]);

        $this->actingAs($this->kaban)->get(route('hasil-fasilitasi.show', $this->permohonan))
            ->assertOk()
            ->assertSee('Konsistensi & Keselarasan', false)
            ->assertSee('Selaraskan target RPJMD.')
            ->assertSee('Rekomendasi perbaikan indikator.');

        $opdUser = User::factory()->withRole('opd')->create(['opd_id' => Opd::factory()->create()->id]);
        $this->actingAs($opdUser)->get(route('hasil-fasilitasi.show', $this->permohonan))
            ->assertOk()
            ->assertDontSee('Selaraskan target RPJMD.')
            ->assertDontSee('Rekomendasi perbaikan indikator.');
    }

    public function test_kaban_lists_all_hasil_without_team_assignment(): void
    {
        $this->createHasilWithOpdInput('draft');

        $this->actingAs($this->kaban)->get(route('hasil-fasilitasi.index'))
            ->assertOk()
            ->assertSee(route('hasil-fasilitasi.show', $this->permohonan))
            ->assertDontSee(route('hasil-fasilitasi.create', $this->permohonan));
    }

    public function test_kaban_opening_missing_hasil_is_redirected_to_tahapan_page(): void
    {
        $this->actingAs($this->kaban)->get(route('hasil-fasilitasi.show', $this->permohonan))
            ->assertRedirect(route('permohonan.tahapan.hasil', $this->permohonan));
    }

    public function test_opd_sees_input_button_on_hasil_list(): void
    {
        $opdUser = User::factory()->withRole('opd')->create(['opd_id' => Opd::factory()->create()->id]);
        $this->createHasilWithOpdInput('draft');

        $this->actingAs($opdUser)->get(route('hasil-fasilitasi.index'))
            ->assertOk()
            ->assertSee(route('hasil-fasilitasi.create', $this->permohonan));
    }

    private function createHasilWithOpdInput(string $status, ?string $draftFinal = null): void
    {
        $hasil = HasilFasilitasi::factory()->for($this->permohonan)->create(['status_draft' => $status, 'draft_final_file' => $draftFinal]);
        $opdUser = User::factory()->withRole('opd')->create([
            'opd_id' => Opd::factory()->create(['singkatan' => 'DINKES'])->id,
        ]);

        HasilFasilitasiUrusan::create([
            'hasil_fasilitasi_id' => $hasil->id,
            'master_urusan_id' => MasterUrusan::factory()->create(['kode_urusan' => '1.02', 'nama_urusan' => 'Kesehatan'])->id,
            'catatan_masukan' => 'Perkuat target SPM.',
            'user_id' => $opdUser->id,
        ]);
    }
}
