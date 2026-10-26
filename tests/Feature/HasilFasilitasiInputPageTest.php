<?php

namespace Tests\Feature;

use App\Models\HasilFasilitasi;
use App\Models\HasilFasilitasiSistematika;
use App\Models\HasilFasilitasiUrusan;
use App\Models\MasterBab;
use App\Models\MasterUrusan;
use App\Models\Opd;
use App\Models\Permohonan;
use App\Models\User;
use App\Models\UserKabkotaAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HasilFasilitasiInputPageTest extends TestCase
{
    use RefreshDatabase;

    private Permohonan $permohonan;

    private HasilFasilitasi $hasil;

    private MasterUrusan $kesehatan;

    private MasterUrusan $sosial;

    private MasterUrusan $pertanian;

    private User $opdUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->permohonan = Permohonan::factory()->create();
        $this->hasil = HasilFasilitasi::factory()->for($this->permohonan)->create(['status_draft' => 'draft']);
        $this->kesehatan = MasterUrusan::factory()->create(['kode_urusan' => '1.02', 'nama_urusan' => 'Kesehatan']);
        $this->sosial = MasterUrusan::factory()->create(['kode_urusan' => '1.06', 'nama_urusan' => 'Sosial']);
        $this->pertanian = MasterUrusan::factory()->create(['kode_urusan' => '3.27', 'nama_urusan' => 'Pertanian']);

        $opd = Opd::factory()->create(['singkatan' => 'DINKES']);
        $opd->urusan()->attach([$this->kesehatan->id, $this->sosial->id]);
        $this->opdUser = User::factory()->withRole('opd')->create(['opd_id' => $opd->id]);
    }

    private function createUrusanInput(MasterUrusan $urusan, User $user, string $catatan = 'Perkuat target SPM.'): HasilFasilitasiUrusan
    {
        return HasilFasilitasiUrusan::create([
            'hasil_fasilitasi_id' => $this->hasil->id,
            'master_urusan_id' => $urusan->id,
            'catatan_masukan' => $catatan,
            'user_id' => $user->id,
        ]);
    }

    private function createKoordinator(): User
    {
        $koordinator = User::factory()->withRole('fasilitator')->create();
        UserKabkotaAssignment::create([
            'user_id' => $koordinator->id,
            'kabupaten_kota_id' => $this->permohonan->kabupaten_kota_id,
            'jenis_dokumen_id' => $this->permohonan->jenis_dokumen_id,
            'tahun' => $this->permohonan->tahun,
            'role_type' => 'fasilitator',
            'is_pic' => true,
            'is_active' => true,
        ]);

        return $koordinator;
    }

    public function test_opd_sees_unfilled_urusan_of_its_field_without_other_sections(): void
    {
        $this->createUrusanInput($this->kesehatan, $this->opdUser);

        $this->actingAs($this->opdUser)->get(route('hasil-fasilitasi.create', $this->permohonan))
            ->assertSee('Urusan bidang OPD Anda yang belum diberi masukan (1 dari 2)')
            ->assertSee('data-urusan-id="'.$this->sosial->id.'"', false)
            ->assertDontSee('data-urusan-id="'.$this->kesehatan->id.'"', false)
            ->assertDontSee('Sistematika &amp; Rancangan Akhir', false)
            ->assertDontSee('id="dokumen-hasil"', false);
    }

    public function test_opd_is_told_when_all_urusan_of_its_field_have_input(): void
    {
        $this->createUrusanInput($this->kesehatan, $this->opdUser);
        $this->createUrusanInput($this->sosial, $this->opdUser);

        $this->actingAs($this->opdUser)->get(route('hasil-fasilitasi.create', $this->permohonan))
            ->assertSee('Semua urusan bidang OPD Anda sudah diberi masukan.');
    }

    public function test_koordinator_sees_summary_and_document_steps_without_team_card(): void
    {
        $this->createUrusanInput($this->kesehatan, $this->opdUser);

        $this->actingAs($this->createKoordinator())->get(route('hasil-fasilitasi.create', $this->permohonan))
            ->assertSee('Penyusunan Masukan')
            ->assertSee('id="dokumen-hasil"', false)
            ->assertSee(route('hasil-fasilitasi.generate', $this->permohonan->id))
            ->assertSee('Dari OPD')
            ->assertSee('DINKES')
            ->assertDontSee('Tim Fasilitasi / Evaluasi');
    }

    public function test_locked_page_hides_add_forms_and_row_actions(): void
    {
        $this->createUrusanInput($this->kesehatan, $this->opdUser);
        $this->hasil->update(['status_draft' => 'menunggu_persetujuan_kaban']);

        $this->actingAs($this->opdUser)->get(route('hasil-fasilitasi.create', $this->permohonan))
            ->assertSee('Kunci terbuka kembali bila Kepala Badan mengembalikan hasil untuk diperbaiki.')
            ->assertSee('Menunggu Persetujuan Kaban')
            ->assertDontSee('Tambah Masukan')
            ->assertDontSee('dropdown-item text-danger js-hapus', false);
    }

    public function test_owner_sees_row_actions_on_own_input_only(): void
    {
        $this->createUrusanInput($this->kesehatan, User::factory()->withRole('fasilitator')->create(), 'Masukan fasilitator.');

        $this->actingAs($this->opdUser)->get(route('hasil-fasilitasi.create', $this->permohonan))
            ->assertSee('Masukan fasilitator.')
            ->assertDontSee('dropdown-item text-danger js-hapus', false);
    }

    public function test_opd_moves_own_input_to_another_urusan_of_its_field(): void
    {
        $item = $this->createUrusanInput($this->kesehatan, $this->opdUser);

        $response = $this->actingAs($this->opdUser)->putJson(route('hasil-fasilitasi.urusan.update', [$this->permohonan, $item->id]), [
            'master_urusan_id' => $this->sosial->id,
            'catatan_masukan' => 'Perkuat layanan sosial.',
        ]);

        $response->assertOk()->assertJsonPath('success', true);
        $this->assertSame($this->sosial->id, $item->fresh()->master_urusan_id);
    }

    public function test_opd_cannot_move_input_to_urusan_outside_its_field(): void
    {
        $item = $this->createUrusanInput($this->kesehatan, $this->opdUser);

        $response = $this->actingAs($this->opdUser)->putJson(route('hasil-fasilitasi.urusan.update', [$this->permohonan, $item->id]), [
            'master_urusan_id' => $this->pertanian->id,
            'catatan_masukan' => 'Pindah ke luar bidang.',
        ]);

        $response->assertForbidden()
            ->assertJsonPath('error', 'Anda hanya dapat memberi masukan pada urusan pemerintahan bidang OPD Anda.');
        $this->assertSame($this->kesehatan->id, $item->fresh()->master_urusan_id);
    }

    public function test_owner_changes_bab_of_own_sistematika_input(): void
    {
        $koordinator = $this->createKoordinator();
        $babLama = MasterBab::create(['nama_bab' => 'Bab I Pendahuluan', 'jenis_dokumen_id' => $this->permohonan->jenis_dokumen_id, 'urutan' => 1]);
        $babBaru = MasterBab::create(['nama_bab' => 'Bab II Gambaran Umum', 'jenis_dokumen_id' => $this->permohonan->jenis_dokumen_id, 'urutan' => 2]);
        $item = HasilFasilitasiSistematika::create([
            'hasil_fasilitasi_id' => $this->hasil->id,
            'master_bab_id' => $babLama->id,
            'catatan_penyempurnaan' => 'Lengkapi dasar hukum.',
            'user_id' => $koordinator->id,
        ]);

        $response = $this->actingAs($koordinator)->putJson(route('hasil-fasilitasi.sistematika.update', [$this->permohonan, $item->id]), [
            'master_bab_id' => $babBaru->id,
            'sub_bab' => '2.1 Aspek Geografi',
            'catatan_penyempurnaan' => 'Perbarui data geografi.',
        ]);

        $response->assertOk();
        $item->refresh();
        $this->assertSame($babBaru->id, $item->master_bab_id);
        $this->assertSame('2.1 Aspek Geografi', $item->sub_bab);
    }

    public function test_sistematika_update_rejects_unknown_bab(): void
    {
        $koordinator = $this->createKoordinator();
        $bab = MasterBab::create(['nama_bab' => 'Bab I Pendahuluan', 'jenis_dokumen_id' => $this->permohonan->jenis_dokumen_id, 'urutan' => 1]);
        $item = HasilFasilitasiSistematika::create([
            'hasil_fasilitasi_id' => $this->hasil->id,
            'master_bab_id' => $bab->id,
            'catatan_penyempurnaan' => 'Lengkapi dasar hukum.',
            'user_id' => $koordinator->id,
        ]);

        $response = $this->actingAs($koordinator)->putJson(route('hasil-fasilitasi.sistematika.update', [$this->permohonan, $item->id]), [
            'master_bab_id' => 999999,
            'catatan_penyempurnaan' => 'Perbarui.',
        ]);

        $response->assertJsonValidationErrors(['master_bab_id']);
        $this->assertSame($bab->id, $item->fresh()->master_bab_id);
    }

    public function test_detail_page_links_team_member_to_input_while_unlocked(): void
    {
        $this->createUrusanInput($this->kesehatan, $this->opdUser);

        $this->actingAs($this->opdUser)->get(route('hasil-fasilitasi.show', $this->permohonan))
            ->assertSee(route('hasil-fasilitasi.create', $this->permohonan))
            ->assertSee('Perkuat target SPM.')
            ->assertSee('Masukan Saya')
            ->assertDontSee('dropdown-item text-danger js-hapus', false)
            ->assertDontSee('Tim Fasilitasi / Evaluasi');
    }

    public function test_detail_page_hides_input_link_while_locked(): void
    {
        $this->hasil->update(['status_draft' => 'disetujui_kaban']);

        $this->actingAs($this->opdUser)->get(route('hasil-fasilitasi.show', $this->permohonan))
            ->assertSee('Disetujui Kaban')
            ->assertDontSee(route('hasil-fasilitasi.create', $this->permohonan));
    }

    public function test_kaban_gets_review_link_on_detail_page_while_waiting_for_approval(): void
    {
        $this->hasil->update(['status_draft' => 'menunggu_persetujuan_kaban', 'draft_final_file' => 'hasil-fasilitasi/draft.pdf']);
        $kaban = User::factory()->withRole('kaban')->create();

        $this->actingAs($kaban)->get(route('hasil-fasilitasi.show', $this->permohonan))
            ->assertSee(route('approval.show', $this->permohonan))
            ->assertDontSee('Masukan Saya')
            ->assertDontSee(route('hasil-fasilitasi.create', $this->permohonan));
    }
}
