<?php

namespace Tests\Feature;

use App\Models\MasterUrusan;
use App\Models\Opd;
use Database\Seeders\MasterUrusanSeeder;
use Database\Seeders\OpdSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterCsvSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeders_load_all_rows_from_csv(): void
    {
        $this->seed([OpdSeeder::class, MasterUrusanSeeder::class]);

        $this->assertSame(46, Opd::count());
        $this->assertSame(44, MasterUrusan::count());

        $rsud = Opd::where('kode_opd', '82.05.02.01')->sole();
        $this->assertSame('RSUD CB', $rsud->singkatan);
        $this->assertSame('82.05.02', $rsud->induk->kode_opd);
        $this->assertSame('1.01; 2.22', Opd::where('kode_opd', '82.05.01')->value('kode_urusan'));

        $kesehatan = MasterUrusan::where('kode_urusan', '1.02')->sole();
        $this->assertSame('82.05.02; 82.05.02.01', $kesehatan->kode_opd_pengampu);
        $this->assertSame('DINKES; RSUD CB', $kesehatan->opd_pengampu);
        $this->assertTrue($kesehatan->tampil_hasil_fasilitasi);
        $this->assertFalse(MasterUrusan::where('kode_urusan', '5.01')->sole()->tampil_hasil_fasilitasi);
        $this->assertSame(32, MasterUrusan::untukHasilFasilitasi()->count());
        $this->assertSame(['1.01', '2.22'], Opd::where('kode_opd', '82.05.01')->sole()->urusan->pluck('kode_urusan')->sort()->values()->all());
    }

    public function test_existing_rows_are_only_completed_not_overwritten(): void
    {
        $existing = MasterUrusan::factory()->create([
            'kode_urusan' => '2.17',
            'nama_urusan' => 'Koperasi, UKM',
            'kode_opd_pengampu' => null,
            'tampil_hasil_fasilitasi' => false,
        ]);

        $this->seed([OpdSeeder::class, MasterUrusanSeeder::class]);
        $this->seed([OpdSeeder::class, MasterUrusanSeeder::class]);

        $existing->refresh();
        $this->assertSame('Koperasi, UKM', $existing->nama_urusan);
        $this->assertSame('82.05.14', $existing->kode_opd_pengampu);
        $this->assertSame('DISKOP UKM', $existing->opd_pengampu);
        $this->assertFalse($existing->tampil_hasil_fasilitasi);
        $this->assertSame(44, MasterUrusan::count());
        $this->assertSame(46, Opd::count());
    }
}
