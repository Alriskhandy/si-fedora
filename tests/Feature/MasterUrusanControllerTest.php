<?php

namespace Tests\Feature;

use App\Models\HasilFasilitasi;
use App\Models\HasilFasilitasiUrusan;
use App\Models\MasterUrusan;
use App\Models\Opd;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class MasterUrusanControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->withRole('admin_peran')->create();
    }

    public function test_store_derives_group_and_supporting_opd_from_codes(): void
    {
        Opd::factory()->create(['kode_opd' => '82.05.01', 'singkatan' => 'DIKBUD']);
        Opd::factory()->create(['kode_opd' => '82.04.01', 'singkatan' => 'BAPPEDA']);

        $this->actingAs($this->admin)->post(route('master-urusan.store'), $this->payload([
            'kode_opd_pengampu' => '82.05.01 ; 82.04.01',
        ]))->assertRedirect(route('master-urusan.index'));

        $urusan = MasterUrusan::where('kode_urusan', '1.01')->sole();
        $this->assertSame('1', $urusan->kode_kelompok);
        $this->assertSame('Urusan Pemerintahan Wajib yang Berkaitan dengan Pelayanan Dasar', $urusan->nama_kelompok);
        $this->assertSame('Wajib Pelayanan Dasar', $urusan->rumpun_urusan);
        $this->assertSame('82.05.01; 82.04.01', $urusan->kode_opd_pengampu);
        $this->assertSame('DIKBUD; BAPPEDA', $urusan->opd_pengampu);
    }

    /**
     * @return array<string, array{array<string, string>, string, string}>
     */
    public static function invalidPayloads(): array
    {
        return [
            'kelompok di luar 1–8' => [['kode_urusan' => '9.01'], 'kode_urusan', 'Format kode urusan harus X.XX dengan X = kelompok 1–8, misalnya 1.01.'],
            'format kode urusan' => [['kode_urusan' => '1.1'], 'kode_urusan', 'Format kode urusan harus X.XX dengan X = kelompok 1–8, misalnya 1.01.'],
            'nama terlalu panjang' => [['nama_urusan' => str_repeat('a', 151)], 'nama_urusan', 'Nama urusan maksimal 150 karakter.'],
            'format kode OPD pengampu' => [['kode_opd_pengampu' => '82.5.1'], 'kode_opd_pengampu', 'Format kode OPD pengampu harus 82.XX.XX atau 82.XX.XX.XX: 82.5.1.'],
            'OPD pengampu tidak terdaftar' => [['kode_opd_pengampu' => '82.99.99'], 'kode_opd_pengampu', 'Kode OPD pengampu tidak terdaftar: 82.99.99.'],
        ];
    }

    /**
     * @param  array<string, string>  $override
     */
    #[DataProvider('invalidPayloads')]
    public function test_invalid_field_is_rejected(array $override, string $field, string $message): void
    {
        $this->actingAs($this->admin)->post(route('master-urusan.store'), $this->payload($override))
            ->assertSessionHasErrors([$field => $message]);
    }

    public function test_empty_payload_reports_required_fields(): void
    {
        $this->actingAs($this->admin)->post(route('master-urusan.store'), [])->assertSessionHasErrors([
            'kode_urusan' => 'Kode urusan wajib diisi.',
            'nama_urusan' => 'Nama urusan wajib diisi.',
            'status' => 'Status wajib dipilih.',
            'tanggal_update' => 'Tanggal pemutakhiran wajib diisi.',
        ]);
    }

    public function test_duplicate_kode_is_rejected(): void
    {
        MasterUrusan::factory()->create(['kode_urusan' => '1.01']);

        $this->actingAs($this->admin)->post(route('master-urusan.store'), $this->payload())
            ->assertSessionHasErrors(['kode_urusan' => 'Kode urusan sudah terdaftar.']);
    }

    public function test_urusan_used_by_hasil_fasilitasi_cannot_be_deleted(): void
    {
        $urusan = MasterUrusan::factory()->create();
        HasilFasilitasiUrusan::create([
            'hasil_fasilitasi_id' => HasilFasilitasi::factory()->create()->id,
            'master_urusan_id' => $urusan->id,
            'catatan_masukan' => 'Masukan.',
            'user_id' => $this->admin->id,
        ]);

        $this->actingAs($this->admin)->delete(route('master-urusan.destroy', $urusan))->assertRedirect(route('master-urusan.index'));

        $this->assertModelExists($urusan);
    }

    public function test_unused_urusan_is_deleted(): void
    {
        $urusan = MasterUrusan::factory()->create();

        $this->actingAs($this->admin)->delete(route('master-urusan.destroy', $urusan))->assertRedirect(route('master-urusan.index'));

        $this->assertModelMissing($urusan);
    }

    public function test_index_groups_by_kelompok_and_filters(): void
    {
        MasterUrusan::factory()->create(['kode_urusan' => '1.01', 'nama_urusan' => 'Pendidikan']);
        MasterUrusan::factory()->create(['kode_urusan' => '5.01', 'nama_urusan' => 'Perencanaan']);

        $this->actingAs($this->admin)->get(route('master-urusan.index'))
            ->assertOk()
            ->assertSeeInOrder(['Urusan Pemerintahan Wajib yang Berkaitan dengan Pelayanan Dasar', 'Pendidikan', 'Unsur Penunjang Urusan Pemerintahan', 'Perencanaan']);

        $this->actingAs($this->admin)->get(route('master-urusan.index', ['kelompok' => 5]))
            ->assertOk()
            ->assertSee('Perencanaan')
            ->assertDontSee('Pendidikan');
    }

    public function test_search_matches_code_name_or_supporting_opd(): void
    {
        Opd::factory()->create(['kode_opd' => '82.05.02', 'singkatan' => 'DINKES']);
        MasterUrusan::factory()->create(['kode_urusan' => '1.01', 'nama_urusan' => 'Pendidikan']);
        MasterUrusan::factory()->create(['kode_urusan' => '1.02', 'nama_urusan' => 'Kesehatan', 'kode_opd_pengampu' => '82.05.02']);

        $this->actingAs($this->admin)->get(route('master-urusan.index', ['q' => 'pendidikan']))
            ->assertOk()->assertSee('Pendidikan')->assertDontSee('Kesehatan');
        $this->actingAs($this->admin)->get(route('master-urusan.index', ['q' => 'dinkes']))
            ->assertOk()->assertSee('Kesehatan')->assertDontSee('Pendidikan');
        $this->actingAs($this->admin)->get(route('master-urusan.index', ['q' => '1.01']))
            ->assertOk()->assertSee('Pendidikan')->assertDontSee('Kesehatan');
    }

    public function test_filters_by_status_hasil_fasilitasi_flag_and_supporting_opd(): void
    {
        Opd::factory()->create(['kode_opd' => '82.05.02']);
        MasterUrusan::factory()->create(['nama_urusan' => 'Urusan Berpengampu', 'kode_opd_pengampu' => '82.05.02']);
        MasterUrusan::factory()->create(['nama_urusan' => 'Urusan Tersembunyi', 'tampil_hasil_fasilitasi' => false]);
        MasterUrusan::factory()->inactive()->create(['nama_urusan' => 'Urusan Nonaktif']);

        $this->actingAs($this->admin)->get(route('master-urusan.index', ['status' => 'Tidak Aktif']))
            ->assertOk()->assertSee('Urusan Nonaktif')->assertDontSee('Urusan Berpengampu');
        $this->actingAs($this->admin)->get(route('master-urusan.index', ['hasil' => 'tidak']))
            ->assertOk()->assertSee('Urusan Tersembunyi')->assertDontSee('Urusan Berpengampu');
        $this->actingAs($this->admin)->get(route('master-urusan.index', ['pengampu' => 'ada']))
            ->assertOk()->assertSee('Urusan Berpengampu')->assertDontSee('Urusan Tersembunyi');
    }

    public function test_unknown_filter_value_is_ignored(): void
    {
        MasterUrusan::factory()->create(['nama_urusan' => 'Pendidikan']);

        $this->actingAs($this->admin)->get(route('master-urusan.index', ['kelompok' => '9', 'hasil' => 'semua']))
            ->assertOk()->assertSee('Pendidikan');
    }

    public function test_pagination_keeps_filters(): void
    {
        MasterUrusan::factory()->count(16)->create(['status' => 'Aktif']);

        $this->actingAs($this->admin)->get(route('master-urusan.index', ['status' => 'Aktif']))
            ->assertOk()
            ->assertSee('Menampilkan 16 dari 16 urusan')
            ->assertSee('status=Aktif&amp;page=2', false);
    }

    public function test_forms_render(): void
    {
        $urusan = MasterUrusan::factory()->create(['kode_urusan' => '1.01']);

        $this->actingAs($this->admin)->get(route('master-urusan.create'))->assertOk()->assertSee('Kode OPD Pengampu');
        $this->actingAs($this->admin)->get(route('master-urusan.edit', $urusan))->assertOk()->assertSee('1.01');
    }

    public function test_new_urusan_defaults_hasil_fasilitasi_flag_by_kelompok(): void
    {
        $this->actingAs($this->admin)->post(route('master-urusan.store'), $this->payload(['kode_urusan' => '5.01', 'nama_urusan' => 'Perencanaan']));
        $wajib = MasterUrusan::factory()->create(['kode_urusan' => '1.01']);

        $this->assertTrue($wajib->tampil_hasil_fasilitasi);
        $this->assertFalse(MasterUrusan::where('kode_urusan', '5.01')->sole()->tampil_hasil_fasilitasi);
    }

    public function test_changing_kode_urusan_moves_opd_input_scope(): void
    {
        $urusan = MasterUrusan::factory()->create(['kode_urusan' => '1.01']);
        $lama = Opd::factory()->create(['kode_urusan' => '1.01']);
        $baru = Opd::factory()->create(['kode_urusan' => '1.09']);

        $this->actingAs($this->admin)->put(route('master-urusan.update', $urusan), $this->payload(['kode_urusan' => '1.09']))
            ->assertRedirect(route('master-urusan.index'));

        $this->assertSame([], $lama->fresh()->urusan->pluck('id')->all());
        $this->assertSame([$urusan->id], $baru->fresh()->urusan->pluck('id')->all());
    }

    public function test_admin_toggles_urusan_in_hasil_fasilitasi(): void
    {
        $urusan = MasterUrusan::factory()->create(['kode_urusan' => '1.01']);

        $this->actingAs($this->admin)->post(route('master-urusan.toggle-hasil-fasilitasi', $urusan))->assertRedirect();
        $this->assertFalse($urusan->fresh()->tampil_hasil_fasilitasi);

        $this->actingAs($this->admin)->post(route('master-urusan.toggle-hasil-fasilitasi', $urusan))->assertRedirect();
        $this->assertTrue($urusan->fresh()->tampil_hasil_fasilitasi);
    }

    public function test_only_urusan_shown_in_hasil_fasilitasi_are_offered_on_input_page(): void
    {
        $opd = Opd::factory()->create();
        $opd->urusan()->attach(MasterUrusan::factory()->create(['nama_urusan' => 'Urusan Tampil']));
        $opd->urusan()->attach(MasterUrusan::factory()->create(['nama_urusan' => 'Urusan Disembunyikan', 'tampil_hasil_fasilitasi' => false]));
        $opdUser = User::factory()->withRole('opd')->create(['opd_id' => $opd->id]);
        $hasil = HasilFasilitasi::factory()->create();
        $opd->urusan()->attach(MasterUrusan::factory()->create(['kode_urusan' => '5.01', 'nama_urusan' => 'Unsur Penunjang Contoh']));

        $this->actingAs($opdUser)->get(route('hasil-fasilitasi.create', $hasil->permohonan))
            ->assertOk()
            ->assertSee('Urusan Tampil')
            ->assertDontSee('Urusan Disembunyikan')
            ->assertDontSee('Unsur Penunjang Contoh');
    }

    /**
     * @param  array<string, mixed>  $override
     * @return array<string, mixed>
     */
    private function payload(array $override = []): array
    {
        return $override + [
            'kode_urusan' => '1.01',
            'nama_urusan' => 'Pendidikan',
            'status' => 'Aktif',
            'tanggal_update' => '2026-10-25',
        ];
    }
}
