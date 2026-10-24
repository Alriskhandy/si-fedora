<?php

namespace Tests\Feature;

use App\Models\KabupatenKota;
use App\Models\Permohonan;
use App\Models\User;
use App\Models\UserKabkotaAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PermohonanPolicyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{string}>
     */
    public static function fullAccessRoles(): array
    {
        return [
            'superadmin' => ['superadmin'],
            'admin_peran' => ['admin_peran'],
            'kaban' => ['kaban'],
            'auditor' => ['auditor'],
        ];
    }

    #[DataProvider('fullAccessRoles')]
    public function test_full_access_roles_can_view_any_permohonan(string $role): void
    {
        $user = User::factory()->withRole($role)->create();

        $this->assertTrue($user->can('view', Permohonan::factory()->create()));
    }

    public function test_pemohon_can_view_permohonan_of_own_kabupaten_kota(): void
    {
        $kabKota = KabupatenKota::factory()->create();
        $pemohon = User::factory()->withRole('pemohon')->create(['kabupaten_kota_id' => $kabKota->id]);
        $permohonanRekan = Permohonan::factory()->create(['kab_kota_id' => $kabKota->id]);

        $this->assertTrue($pemohon->can('view', $permohonanRekan));
    }

    public function test_pemohon_cannot_view_permohonan_of_other_kabupaten_kota(): void
    {
        $pemohon = User::factory()->withRole('pemohon')->create(['kabupaten_kota_id' => KabupatenKota::factory()->create()->id]);

        $this->assertFalse($pemohon->can('view', Permohonan::factory()->create()));
    }

    /**
     * @return array<string, array{string}>
     */
    public static function assignedRoles(): array
    {
        return [
            'verifikator' => ['verifikator'],
            'fasilitator' => ['fasilitator'],
        ];
    }

    #[DataProvider('assignedRoles')]
    public function test_assigned_team_member_can_view_permohonan_of_assigned_kabupaten_kota_and_year(string $role): void
    {
        $user = User::factory()->withRole($role)->create();
        $permohonan = Permohonan::factory()->create();
        $this->assign($user, $permohonan->kab_kota_id, $permohonan->tahun, $role);

        $this->assertTrue($user->can('view', $permohonan));
    }

    #[DataProvider('assignedRoles')]
    public function test_team_member_cannot_view_permohonan_outside_assignment(string $role): void
    {
        $user = User::factory()->withRole($role)->create();
        $permohonan = Permohonan::factory()->create();
        $this->assign($user, $permohonan->kab_kota_id, $permohonan->tahun - 1, $role);

        $this->assertFalse($user->can('view', $permohonan));
    }

    public function test_inactive_assignment_does_not_grant_access(): void
    {
        $verifikator = User::factory()->withRole('verifikator')->create();
        $permohonan = Permohonan::factory()->create();
        $this->assign($verifikator, $permohonan->kab_kota_id, $permohonan->tahun, 'verifikator', isActive: false);

        $this->assertFalse($verifikator->can('view', $permohonan));
    }

    public function test_user_without_role_cannot_view_permohonan(): void
    {
        $this->assertFalse(User::factory()->create()->can('view', Permohonan::factory()->create()));
    }

    private function assign(User $user, int $kabKotaId, int $tahun, string $roleType, bool $isActive = true): void
    {
        UserKabkotaAssignment::create([
            'user_id' => $user->id,
            'kabupaten_kota_id' => $kabKotaId,
            'tahun' => $tahun,
            'role_type' => $roleType,
            'is_active' => $isActive,
        ]);
    }
}
