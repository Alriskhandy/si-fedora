<?php

namespace Tests\Feature;

use App\Models\JadwalFasilitasi;
use App\Models\KabupatenKota;
use App\Models\Notifikasi;
use App\Models\Permohonan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RemindPermohonanDeadlineTest extends TestCase
{
    use RefreshDatabase;

    public function test_reminds_pemohon_who_has_not_submitted_before_deadline(): void
    {
        Http::preventStrayRequests();
        Http::fake(['https://api.fonnte.com/*' => Http::response(['status' => true])]);
        $this->travelTo(now()->startOfDay()->setHour(8));
        $jadwal = JadwalFasilitasi::factory()->create(['status' => 'published', 'batas_permohonan' => today()->addDays(3)]);
        $belumMengajukan = $this->createPemohon('6281111111111');
        $sudahMengajukan = $this->createPemohon('6282222222222');
        Permohonan::factory()->for($sudahMengajukan, 'pemohon')->for($jadwal, 'jadwalFasilitasi')->create();

        $this->artisan('permohonan:remind-deadline')->assertSuccessful();

        $this->assertDatabaseHas('notifikasi', ['user_id' => $belumMengajukan->id, 'model_type' => JadwalFasilitasi::class, 'model_id' => $jadwal->id]);
        $this->assertDatabaseMissing('notifikasi', ['user_id' => $sudahMengajukan->id]);
        Http::assertSent(fn (Request $request) => $request['target'] === '6281111111111' && str_contains($request['message'], '3 hari lagi'));
    }

    public function test_does_not_remind_twice_on_the_same_day(): void
    {
        Http::preventStrayRequests();
        Http::fake(['https://api.fonnte.com/*' => Http::response(['status' => true])]);
        $this->travelTo(now()->startOfDay()->setHour(8));
        JadwalFasilitasi::factory()->create(['status' => 'published', 'batas_permohonan' => today()->addDay()]);
        $pemohon = $this->createPemohon('6281111111111');

        $this->artisan('permohonan:remind-deadline')->assertSuccessful();
        $this->artisan('permohonan:remind-deadline')->assertSuccessful();

        $this->assertSame(1, Notifikasi::where('user_id', $pemohon->id)->count());
    }

    public function test_ignores_schedule_with_deadline_beyond_three_days(): void
    {
        $this->travelTo(now()->startOfDay()->setHour(8));
        JadwalFasilitasi::factory()->create(['status' => 'published', 'batas_permohonan' => today()->addDays(4)]);
        $this->createPemohon('6281111111111');

        $this->artisan('permohonan:remind-deadline')->assertSuccessful();

        $this->assertSame(0, Notifikasi::count());
    }

    private function createPemohon(string $noHp): User
    {
        return User::factory()->withRole('pemohon')->create([
            'no_hp' => $noHp,
            'kabupaten_kota_id' => KabupatenKota::factory()->create()->id,
        ]);
    }
}
