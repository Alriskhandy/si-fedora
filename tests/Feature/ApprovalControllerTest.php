<?php

namespace Tests\Feature;

use App\Enums\StatusHasilFasilitasi;
use App\Models\HasilFasilitasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ApprovalControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_kaban_approves_draft_waiting_for_approval(): void
    {
        Http::preventStrayRequests();
        Http::fake(['https://api.fonnte.com/*' => Http::response(['status' => true])]);
        $this->freezeSecond();
        $kaban = User::factory()->withRole('kaban')->create();
        $hasil = HasilFasilitasi::factory()->create(['status_draft' => 'menunggu_persetujuan_kaban']);

        $response = $this->actingAs($kaban)->post(route('approval.approve', $hasil->permohonan_id), [
            'keterangan_kaban' => 'Sudah sesuai.',
        ]);

        $response->assertRedirect(route('approval.index'));
        $hasil->refresh();
        $this->assertSame(StatusHasilFasilitasi::DisetujuiKaban, $hasil->status_draft);
        $this->assertSame('Sudah sesuai.', $hasil->keterangan_kaban);
        $this->assertTrue($hasil->tanggal_disetujui_kaban->equalTo(now()));
    }

    public function test_draft_not_waiting_for_approval_cannot_be_approved(): void
    {
        $kaban = User::factory()->withRole('kaban')->create();
        $hasil = HasilFasilitasi::factory()->create(['status_draft' => 'draft']);

        $response = $this->actingAs($kaban)->post(route('approval.approve', $hasil->permohonan_id));

        $response->assertSessionHas('error', 'Dokumen tidak dalam status menunggu persetujuan.');
        $this->assertSame(StatusHasilFasilitasi::Draft, $hasil->fresh()->status_draft);
    }

    public function test_admin_peran_is_forbidden_from_approving(): void
    {
        $admin = User::factory()->withRole('admin_peran')->create();
        $hasil = HasilFasilitasi::factory()->create(['status_draft' => 'menunggu_persetujuan_kaban']);

        $this->actingAs($admin)->post(route('approval.approve', $hasil->permohonan_id))->assertForbidden();

        $this->assertSame(StatusHasilFasilitasi::MenungguPersetujuanKaban, $hasil->fresh()->status_draft);
    }
}
