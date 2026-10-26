<?php

namespace Tests\Feature;

use App\Models\MasterUrusan;
use App\Models\Opd;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class OpdControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_creates_opd_and_its_input_urusan_follow_the_codes(): void
    {
        $admin = User::factory()->withRole('admin_peran')->create();
        $urusan = MasterUrusan::factory()->create(['kode_urusan' => '1.01']);

        $response = $this->actingAs($admin)->post(route('opd.store'), $this->payload(['kode_urusan' => '1.01']));

        $response->assertRedirect(route('opd.index'));
        $opd = Opd::where('kode_opd', '82.04.01')->firstOrFail();
        $this->assertSame('Badan Perencanaan Pembangunan Daerah', $opd->nama_opd);
        $this->assertTrue($opd->isActive());
        $this->assertSame('2026-10-25', $opd->tanggal_update->toDateString());
        $this->assertSame([$urusan->id], $opd->urusan->pluck('id')->all());
    }

    public function test_empty_payload_reports_all_required_fields(): void
    {
        $admin = User::factory()->withRole('admin_peran')->create();

        $this->actingAs($admin)->post(route('opd.store'), [])->assertSessionHasErrors([
            'kode_wilayah' => 'Kode wilayah wajib diisi.',
            'kode_opd' => 'Kode OPD wajib diisi.',
            'nama_opd' => 'Nama OPD wajib diisi.',
            'singkatan' => 'Singkatan wajib diisi.',
            'jenis_opd' => 'Jenis OPD wajib dipilih.',
            'rumpun_urusan' => 'Rumpun urusan wajib dipilih.',
            'status' => 'Status wajib dipilih.',
            'tanggal_update' => 'Tanggal pemutakhiran wajib diisi.',
        ]);
    }

    /**
     * @return array<string, array{array<string, string>, string, string}>
     */
    public static function invalidFormats(): array
    {
        return [
            'format kode OPD' => [['kode_opd' => '82.4.1'], 'kode_opd', 'Format kode OPD harus 82.XX.XX atau 82.XX.XX.XX.'],
            'kode OPD tidak diawali kode wilayah' => [['kode_opd' => '81.04.01'], 'kode_opd', 'Kode OPD harus diawali kode wilayah.'],
            'kode wilayah bukan 2 digit' => [['kode_wilayah' => '820'], 'kode_wilayah', 'Kode wilayah harus 2 digit, misalnya 82.'],
            'singkatan terlalu panjang' => [['singkatan' => str_repeat('A', 21)], 'singkatan', 'Singkatan maksimal 20 karakter.'],
            'jenis di luar daftar' => [['jenis_opd' => 'Kantor'], 'jenis_opd', 'Jenis OPD tidak valid.'],
            'rumpun di luar daftar' => [['rumpun_urusan' => 'Lainnya'], 'rumpun_urusan', 'Rumpun urusan tidak valid.'],
            'format kode urusan' => [['kode_urusan' => '1.01; 5.1'], 'kode_urusan', 'Format kode urusan harus X.XX (lebih dari satu dipisah ;): 5.1.'],
            'kode urusan tidak terdaftar' => [['kode_urusan' => '7.01'], 'kode_urusan', 'Kode urusan tidak terdaftar di master urusan: 7.01.'],
            'induk tidak terdaftar' => [['induk_opd' => '82.99.99'], 'induk_opd', 'Kode OPD induk tidak terdaftar.'],
        ];
    }

    /**
     * @param  array<string, string>  $override
     */
    #[DataProvider('invalidFormats')]
    public function test_invalid_field_is_rejected(array $override, string $field, string $message): void
    {
        $admin = User::factory()->withRole('admin_peran')->create();

        $this->actingAs($admin)->post(route('opd.store'), $this->payload($override))
            ->assertSessionHasErrors([$field => $message]);
    }

    public function test_duplicate_kode_and_name_are_rejected(): void
    {
        $admin = User::factory()->withRole('admin_peran')->create();
        Opd::factory()->create(['kode_opd' => '82.04.01', 'nama_opd' => 'Badan Perencanaan Pembangunan Daerah']);

        $this->actingAs($admin)->post(route('opd.store'), $this->payload())->assertSessionHasErrors([
            'kode_opd' => 'Kode OPD sudah terdaftar.',
            'nama_opd' => 'Nama OPD sudah terdaftar.',
        ]);
    }

    public function test_child_opd_with_four_segment_code_refers_to_registered_parent(): void
    {
        $admin = User::factory()->withRole('admin_peran')->create();
        Opd::factory()->create(['kode_opd' => '82.01.01']);
        MasterUrusan::factory()->create(['kode_urusan' => '4.01']);
        MasterUrusan::factory()->create(['kode_urusan' => '5.01']);

        $this->actingAs($admin)->post(route('opd.store'), $this->payload([
            'kode_opd' => '82.01.01.02',
            'induk_opd' => '82.01.01',
            'jenis_opd' => 'Biro',
            'kode_urusan' => '4.01; 5.01',
        ]))->assertRedirect(route('opd.index'));

        $opd = Opd::where('kode_opd', '82.01.01.02')->sole();
        $this->assertSame('82.01.01', $opd->induk->kode_opd);
        $this->assertSame('4.01; 5.01', $opd->kode_urusan);
    }

    public function test_opd_with_user_accounts_cannot_be_deleted(): void
    {
        $admin = User::factory()->withRole('admin_peran')->create();
        $opd = Opd::factory()->create();
        User::factory()->withRole('opd')->create(['opd_id' => $opd->id]);

        $this->actingAs($admin)->delete(route('opd.destroy', $opd))->assertRedirect(route('opd.index'));

        $this->assertModelExists($opd);
    }

    public function test_opd_without_accounts_is_deleted(): void
    {
        $admin = User::factory()->withRole('admin_peran')->create();
        $opd = Opd::factory()->create();

        $this->actingAs($admin)->delete(route('opd.destroy', $opd))->assertRedirect(route('opd.index'));

        $this->assertModelMissing($opd);
    }

    public function test_index_renders_for_admin(): void
    {
        $admin = User::factory()->withRole('admin_peran')->create();
        Opd::factory()->create(['nama_opd' => 'Dinas Pendidikan']);

        $this->actingAs($admin)->get(route('opd.index'))->assertOk()->assertSee('Dinas Pendidikan');
    }

    public function test_search_matches_code_name_or_abbreviation_case_insensitively(): void
    {
        $admin = User::factory()->withRole('admin_peran')->create();
        Opd::factory()->create(['kode_opd' => '82.03.01', 'nama_opd' => 'Badan Perencanaan Pembangunan Daerah', 'singkatan' => 'BAPPEDA']);
        Opd::factory()->create(['kode_opd' => '82.05.02', 'nama_opd' => 'Dinas Kesehatan', 'singkatan' => 'DINKES']);

        $this->actingAs($admin)->get(route('opd.index', ['q' => 'bappeda']))
            ->assertOk()->assertSee('Badan Perencanaan Pembangunan Daerah')->assertDontSee('Dinas Kesehatan');
        $this->actingAs($admin)->get(route('opd.index', ['q' => '82.05']))
            ->assertOk()->assertSee('Dinas Kesehatan')->assertDontSee('Badan Perencanaan Pembangunan Daerah');
        $this->actingAs($admin)->get(route('opd.index', ['q' => 'kesehatan']))
            ->assertOk()->assertSee('Dinas Kesehatan')->assertDontSee('Badan Perencanaan Pembangunan Daerah');
    }

    public function test_filters_by_jenis_status_and_urusan_setting(): void
    {
        $admin = User::factory()->withRole('admin_peran')->create();
        Opd::factory()->create(['nama_opd' => 'Badan Aktif Tanpa Urusan', 'jenis_opd' => 'Badan']);
        Opd::factory()->inactive()->create(['nama_opd' => 'Dinas Nonaktif', 'jenis_opd' => 'Dinas']);
        Opd::factory()->create(['nama_opd' => 'Dinas Dengan Urusan', 'jenis_opd' => 'Dinas'])
            ->urusan()->attach(MasterUrusan::factory()->create());

        $this->actingAs($admin)->get(route('opd.index', ['jenis' => 'Dinas']))
            ->assertOk()->assertSee('Dinas Nonaktif')->assertSee('Dinas Dengan Urusan')->assertDontSee('Badan Aktif Tanpa Urusan');
        $this->actingAs($admin)->get(route('opd.index', ['status' => 'Tidak Aktif']))
            ->assertOk()->assertSee('Dinas Nonaktif')->assertDontSee('Dinas Dengan Urusan');
        $this->actingAs($admin)->get(route('opd.index', ['urusan' => 'belum', 'status' => 'Aktif']))
            ->assertOk()->assertSee('Badan Aktif Tanpa Urusan')->assertDontSee('Dinas Dengan Urusan')->assertDontSee('Dinas Nonaktif');
    }

    public function test_unknown_filter_value_is_ignored(): void
    {
        $admin = User::factory()->withRole('admin_peran')->create();
        Opd::factory()->create(['nama_opd' => 'Dinas Pendidikan']);

        $this->actingAs($admin)->get(route('opd.index', ['jenis' => "Dinas' OR 1=1", 'status' => 'Lainnya']))
            ->assertOk()->assertSee('Dinas Pendidikan');
    }

    public function test_pagination_keeps_filters(): void
    {
        $admin = User::factory()->withRole('admin_peran')->create();
        Opd::factory()->count(16)->create(['jenis_opd' => 'Dinas']);

        $this->actingAs($admin)->get(route('opd.index', ['jenis' => 'Dinas']))
            ->assertOk()
            ->assertSee('Menampilkan 16 dari 16 OPD')
            ->assertSee('jenis=Dinas&amp;page=2', false);
    }

    public function test_changing_codes_resyncs_input_urusan(): void
    {
        $admin = User::factory()->withRole('admin_peran')->create();
        $pendidikan = MasterUrusan::factory()->create(['kode_urusan' => '1.01']);
        $kebudayaan = MasterUrusan::factory()->create(['kode_urusan' => '2.22']);
        $opd = Opd::factory()->create(['kode_opd' => '82.05.01', 'kode_urusan' => '1.01']);

        $this->actingAs($admin)->put(route('opd.update', $opd), $this->payload([
            'kode_opd' => '82.05.01',
            'kode_urusan' => '2.22;1.01',
        ]))->assertRedirect(route('opd.index'));

        $opd->refresh();
        $this->assertSame('2.22; 1.01', $opd->kode_urusan);
        $this->assertEqualsCanonicalizing([$pendidikan->id, $kebudayaan->id], $opd->urusan->pluck('id')->all());

        $this->actingAs($admin)->put(route('opd.update', $opd), $this->payload(['kode_opd' => '82.05.01', 'kode_urusan' => '']))
            ->assertRedirect(route('opd.index'));
        $this->assertSame([], $opd->fresh()->urusan->pluck('id')->all());
    }

    public function test_urusan_added_later_is_synced_to_opd_that_lists_its_code(): void
    {
        $opd = Opd::factory()->create(['kode_urusan' => '3.27']);
        $this->assertSame([], $opd->urusan->pluck('id')->all());

        $pertanian = MasterUrusan::factory()->create(['kode_urusan' => '3.27']);

        $this->assertSame([$pertanian->id], $opd->fresh()->urusan->pluck('id')->all());
    }

    public function test_edit_form_shows_synced_urusan_without_checkboxes(): void
    {
        $admin = User::factory()->withRole('admin_peran')->create();
        MasterUrusan::factory()->create(['kode_urusan' => '1.02', 'nama_urusan' => 'Kesehatan']);
        $opd = Opd::factory()->create(['kode_urusan' => '1.02']);

        $this->actingAs($admin)->get(route('opd.edit', $opd))
            ->assertOk()
            ->assertSee('1.02 Kesehatan')
            ->assertDontSee('name="urusan[]"', false);
    }

    public function test_create_and_edit_forms_render(): void
    {
        $admin = User::factory()->withRole('admin_peran')->create();
        $opd = Opd::factory()->create(['kode_opd' => '82.04.01', 'jenis_opd' => 'Badan']);

        $this->actingAs($admin)->get(route('opd.create'))->assertOk()->assertSee('Rumpun Urusan');
        $this->actingAs($admin)->get(route('opd.edit', $opd))->assertOk()->assertSee('82.04.01');
    }

    public function test_role_without_master_opd_permission_is_forbidden(): void
    {
        $verifikator = User::factory()->withRole('verifikator')->create();

        $this->actingAs($verifikator)->get(route('opd.index'))->assertForbidden();
    }

    /**
     * Data OPD sah sesuai contoh kamus data.
     *
     * @param  array<string, mixed>  $override
     * @return array<string, mixed>
     */
    private function payload(array $override = []): array
    {
        return $override + [
            'kode_wilayah' => '82',
            'kode_opd' => '82.04.01',
            'nama_opd' => 'Badan Perencanaan Pembangunan Daerah',
            'singkatan' => 'BAPPEDA',
            'jenis_opd' => 'Badan',
            'rumpun_urusan' => 'Penunjang',
            'status' => 'Aktif',
            'tanggal_update' => '2026-10-25',
            'urusan_pemerintahan' => 'Perencanaan',
        ];
    }
}
