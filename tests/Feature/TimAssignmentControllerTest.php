<?php

namespace Tests\Feature;

use App\Models\KabupatenKota;
use App\Models\User;
use App\Models\UserKabkotaAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimAssignmentControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_edit_returns_team_data_as_json_for_the_index_modal(): void
    {
        $admin = User::factory()->withRole('admin_peran')->create();
        $assignment = $this->createAssignment();

        $response = $this->actingAs($admin)->getJson(route('tim-assignment.edit', $assignment));

        $response->assertOk()->assertJson([
            'id' => $assignment->id,
            'kabupaten_kota_id' => $assignment->kabupaten_kota_id,
            'verifikator_id' => $assignment->user_id,
        ]);
    }

    public function test_opening_edit_url_directly_redirects_to_team_list(): void
    {
        $admin = User::factory()->withRole('admin_peran')->create();
        $assignment = $this->createAssignment();

        $this->actingAs($admin)->get(route('tim-assignment.edit', $assignment))
            ->assertRedirect(route('tim-assignment.index'));
    }

    private function createAssignment(): UserKabkotaAssignment
    {
        return UserKabkotaAssignment::create([
            'user_id' => User::factory()->withRole('verifikator')->create()->id,
            'kabupaten_kota_id' => KabupatenKota::factory()->create()->id,
            'tahun' => 2026,
            'role_type' => 'verifikator',
            'is_pic' => true,
            'is_active' => true,
        ]);
    }
}
