<?php

namespace Tests\Feature;

use App\Models\MasterBab;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterBabControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_bab_is_updated_through_the_form_on_the_index_page(): void
    {
        $admin = User::factory()->withRole('admin_peran')->create();
        $bab = MasterBab::create(['nama_bab' => 'BAB I PENDAHULUAN', 'urutan' => 1]);

        $response = $this->actingAs($admin)->put(route('master-bab.update', $bab), [
            'nama_bab' => 'BAB I PENDAHULUAN (REVISI)',
            'urutan' => 2,
        ]);

        $response->assertRedirect(route('master-bab.index'));
        $this->assertSame('BAB I PENDAHULUAN (REVISI)', $bab->fresh()->nama_bab);
    }

    public function test_index_page_renders(): void
    {
        $admin = User::factory()->withRole('admin_peran')->create();

        $this->actingAs($admin)->get(route('master-bab.index'))->assertOk();
    }
}
