<?php

namespace Tests\Feature;

use App\Models\KabupatenKota;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KabupatenKotaControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_adds_kabupaten_kota(): void
    {
        $admin = User::factory()->withRole('admin_peran')->create();

        $response = $this->actingAs($admin)->post(route('kabupaten-kota.store'), [
            'kode' => '8201',
            'nama' => 'Kabupaten Halmahera Barat',
            'jenis' => 'kabupaten',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('kabupaten-kota.index'));
        $this->assertDatabaseHas('kabupaten_kota', ['kode' => '8201', 'nama' => 'Kabupaten Halmahera Barat', 'jenis' => 'kabupaten', 'is_active' => true]);
    }

    public function test_admin_updates_kabupaten_kota(): void
    {
        $admin = User::factory()->withRole('admin_peran')->create();
        $kabupatenKota = KabupatenKota::factory()->create(['jenis' => 'kabupaten']);

        $response = $this->actingAs($admin)->put(route('kabupaten-kota.update', $kabupatenKota), [
            'kode' => $kabupatenKota->kode,
            'nama' => 'Kota Ternate',
            'jenis' => 'kota',
        ]);

        $response->assertRedirect(route('kabupaten-kota.index'));
        $kabupatenKota->refresh();
        $this->assertSame('Kota Ternate', $kabupatenKota->nama);
        $this->assertSame('kota', $kabupatenKota->jenis);
        $this->assertFalse($kabupatenKota->is_active);
    }
}
