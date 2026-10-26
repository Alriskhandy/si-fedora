<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $superadmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->withRole('admin_peran')->create();
        $this->superadmin = User::factory()->withRole('superadmin')->create();
    }

    public function test_admin_grants_permission_to_verifikator_and_access_changes_immediately(): void
    {
        $verifikator = User::factory()->withRole('verifikator')->create();
        $this->actingAs($verifikator)->get(route('master-urusan.index'))->assertForbidden();

        $permissions = Role::findByName('verifikator')->permissions->pluck('name')->push('master.urusan.manage')->all();

        $this->actingAs($this->admin)
            ->put(route('admin.roles.update', Role::findByName('verifikator')), ['permissions' => $permissions])
            ->assertRedirect(route('admin.roles.index'));

        $this->actingAs($verifikator)->get(route('master-urusan.index'))->assertOk();

        $activity = Activity::query()->where('description', 'Mengubah hak akses role')->sole();
        $this->assertTrue($activity->causer->is($this->admin));
        $this->assertSame(['master.urusan.manage'], $activity->properties['ditambah']);
        $this->assertSame([], $activity->properties['dicabut']);
    }

    public function test_unchecking_a_permission_revokes_it_and_is_logged(): void
    {
        $role = Role::findByName('verifikator');

        $this->actingAs($this->admin)
            ->put(route('admin.roles.update', $role), ['permissions' => ['permohonan.view-assigned']])
            ->assertRedirect(route('admin.roles.index'));

        $this->assertSame(['permohonan.view-assigned'], $role->fresh()->permissions->pluck('name')->all());
        $this->assertContains('permohonan.verify', Activity::query()->sole()->properties['dicabut']);
    }

    public function test_admin_cannot_change_superadmin_role(): void
    {
        $role = Role::findByName('superadmin');
        $before = $role->permissions->pluck('name')->all();

        $this->actingAs($this->admin)->get(route('admin.roles.edit', $role))->assertForbidden();
        $this->actingAs($this->admin)
            ->put(route('admin.roles.update', $role), ['permissions' => ['permohonan.view-all']])
            ->assertForbidden();

        $this->assertEqualsCanonicalizing($before, $role->fresh()->permissions->pluck('name')->all());
    }

    public function test_superadmin_can_change_superadmin_role(): void
    {
        $role = Role::findByName('superadmin');
        $permissions = $role->permissions->pluck('name')->reject(fn (string $name) => $name === 'user.export')->values()->all();

        $this->actingAs($this->superadmin)
            ->put(route('admin.roles.update', $role), ['permissions' => $permissions])
            ->assertRedirect(route('admin.roles.index'));

        $this->assertEqualsCanonicalizing($permissions, $role->fresh()->permissions->pluck('name')->all());
    }

    public function test_admin_changes_own_role_but_keeps_its_role_manage_permission(): void
    {
        $role = Role::findByName('admin_peran');
        $permissions = $role->permissions->pluck('name')
            ->reject(fn (string $name) => $name === 'role.manage')
            ->push('user.export')
            ->values()
            ->all();

        $this->actingAs($this->admin)
            ->put(route('admin.roles.update', $role), ['permissions' => $permissions])
            ->assertRedirect(route('admin.roles.index'));

        $this->assertTrue($role->fresh()->hasPermissionTo('user.export'));
        $this->assertTrue($role->fresh()->hasPermissionTo('role.manage'));
    }

    #[DataProvider('protectedPermissions')]
    public function test_admin_cannot_grant_protected_permission(string $permission): void
    {
        $role = Role::findByName('verifikator');

        $this->actingAs($this->admin)
            ->put(route('admin.roles.update', $role), ['permissions' => ['permohonan.view-assigned', $permission]])
            ->assertForbidden();

        $this->assertFalse($role->fresh()->hasPermissionTo($permission));
    }

    /**
     * @return array<string, array{string}>
     */
    public static function protectedPermissions(): array
    {
        return [
            'role.manage' => ['role.manage'],
            'role.manage-protected' => ['role.manage-protected'],
            'user.manage-superadmin' => ['user.manage-superadmin'],
        ];
    }

    public function test_protected_permission_already_on_a_role_survives_admin_edit(): void
    {
        $role = Role::create(['name' => 'koordinator', 'guard_name' => 'web']);
        $role->givePermissionTo(['role.manage', 'activity-log.view']);

        $this->actingAs($this->admin)
            ->put(route('admin.roles.update', $role), ['permissions' => ['permohonan.view-all']])
            ->assertRedirect(route('admin.roles.index'));

        $this->assertEqualsCanonicalizing(['role.manage', 'permohonan.view-all'], $role->fresh()->permissions->pluck('name')->all());
    }

    public function test_permission_outside_the_catalog_is_rejected(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.roles.update', Role::findByName('verifikator')), ['permissions' => ['permohonan.hapus-semua']])
            ->assertSessionHasErrors(['permissions.0' => 'Hak akses yang dipilih tidak valid.']);
    }

    public function test_reset_restores_catalog_default_mapping(): void
    {
        $role = Role::findByName('verifikator');
        $role->syncPermissions(['activity-log.view']);

        $this->actingAs($this->admin)
            ->post(route('admin.roles.reset', $role))
            ->assertRedirect(route('admin.roles.index'));

        $defaults = collect(config('permissions.permissions'))
            ->filter(fn (array $item) => in_array('verifikator', $item['roles'], true))
            ->keys()
            ->all();
        $this->assertEqualsCanonicalizing($defaults, $role->fresh()->permissions->pluck('name')->all());
    }

    public function test_admin_cannot_create_or_delete_roles(): void
    {
        $custom = Role::create(['name' => 'koordinator', 'guard_name' => 'web']);

        $this->actingAs($this->admin)->get(route('admin.roles.create'))->assertForbidden();
        $this->actingAs($this->admin)->post(route('admin.roles.store'), ['name' => 'pengawas'])->assertForbidden();
        $this->actingAs($this->admin)->delete(route('admin.roles.destroy', $custom))->assertForbidden();

        $this->assertDatabaseMissing('roles', ['name' => 'pengawas']);
        $this->assertDatabaseHas('roles', ['name' => 'koordinator']);
    }

    public function test_superadmin_creates_role_with_permissions(): void
    {
        $this->actingAs($this->superadmin)
            ->post(route('admin.roles.store'), ['name' => 'pengawas', 'permissions' => ['permohonan.view-all']])
            ->assertRedirect(route('admin.roles.index'));

        $this->assertSame(['permohonan.view-all'], Role::findByName('pengawas')->permissions->pluck('name')->all());
        $this->assertSame('Menambah role', Activity::query()->sole()->description);
    }

    public function test_superadmin_cannot_delete_catalog_role_but_can_delete_custom_role(): void
    {
        $custom = Role::create(['name' => 'koordinator', 'guard_name' => 'web']);

        $this->actingAs($this->superadmin)->delete(route('admin.roles.destroy', Role::findByName('pemohon')))->assertForbidden();
        $this->actingAs($this->superadmin)->delete(route('admin.roles.destroy', $custom))->assertRedirect(route('admin.roles.index'));

        $this->assertDatabaseHas('roles', ['name' => 'pemohon']);
        $this->assertDatabaseMissing('roles', ['name' => 'koordinator']);
    }

    public function test_role_without_role_manage_is_forbidden(): void
    {
        $verifikator = User::factory()->withRole('verifikator')->create();

        $this->actingAs($verifikator)->get(route('admin.roles.index'))->assertForbidden();
    }

    /**
     * @return array<string, array{string}>
     */
    public static function rolePages(): array
    {
        return [
            'daftar role' => ['admin.roles.index'],
            'atur hak akses' => ['admin.roles.edit'],
            'katalog hak akses' => ['admin.permissions.index'],
        ];
    }

    #[DataProvider('rolePages')]
    public function test_role_pages_hide_superadmin_from_admin(string $routeName): void
    {
        $response = $this->actingAs($this->admin)->get(route($routeName, Role::findByName('verifikator')));

        $response->assertOk();
        $this->assertStringNotContainsStringIgnoringCase('superadmin', strip_tags($response->getContent()));
    }

    public function test_superadmin_sees_superadmin_role(): void
    {
        $this->actingAs($this->superadmin)->get(route('admin.roles.index'))->assertOk()->assertSee('superadmin');
    }

    public function test_checklist_shows_access_labels_without_permission_names(): void
    {
        $this->actingAs($this->admin)->get(route('admin.roles.edit', Role::findByName('verifikator')))
            ->assertOk()
            ->assertSee('Kelola penugasan tim')
            ->assertDontSee('tim-assignment.manage</', false)
            ->assertDontSee('<code>', false);
    }
}
