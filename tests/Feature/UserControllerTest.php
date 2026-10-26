<?php

namespace Tests\Feature;

use App\Models\Opd;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    private const ROLES = ['superadmin', 'kaban', 'admin_peran', 'verifikator', 'fasilitator', 'pemohon', 'auditor', 'opd'];

    protected function setUp(): void
    {
        parent::setUp();

        foreach (self::ROLES as $role) {
            Role::findOrCreate($role, 'web');
        }
    }

    /**
     * @return array<string, array{string}>
     */
    public static function nonCreatableRoles(): array
    {
        return [
            'superadmin' => ['superadmin'],
            'kaban' => ['kaban'],
            'admin_peran' => ['admin_peran'],
        ];
    }

    #[DataProvider('nonCreatableRoles')]
    public function test_admin_peran_cannot_create_user_with_protected_role(string $role): void
    {
        $admin = User::factory()->withRole('admin_peran')->create();

        $response = $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Akun Baru',
            'email' => 'baru@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => $role,
        ]);

        $response->assertSessionHasErrors(['role' => 'Role yang dipilih tidak valid.']);
        $this->assertDatabaseMissing('users', ['email' => 'baru@example.com']);
    }

    public function test_admin_peran_creates_user_with_allowed_role(): void
    {
        $admin = User::factory()->withRole('admin_peran')->create();

        $response = $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Akun Baru',
            'email' => 'baru@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'pemohon',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertTrue(User::where('email', 'baru@example.com')->firstOrFail()->hasRole('pemohon'));
    }

    public function test_admin_peran_cannot_promote_user_to_superadmin(): void
    {
        $admin = User::factory()->withRole('admin_peran')->create();
        $target = User::factory()->withRole('pemohon')->create();

        $response = $this->actingAs($admin)->put(route('users.update', $target), [
            'name' => $target->name,
            'email' => $target->email,
            'role' => 'superadmin',
        ]);

        $response->assertSessionHasErrors(['role' => 'Role yang dipilih tidak valid.']);
        $this->assertFalse($target->fresh()->hasRole('superadmin'));
    }

    public function test_role_change_is_recorded_in_activity_log(): void
    {
        $admin = User::factory()->withRole('admin_peran')->create();
        $target = User::factory()->withRole('pemohon')->create();

        $response = $this->actingAs($admin)->put(route('users.update', $target), [
            'name' => $target->name,
            'email' => $target->email,
            'role' => 'verifikator',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertTrue($target->fresh()->hasRole('verifikator'));
        $this->assertDatabaseHas('activity_log', [
            'description' => 'Mengubah role pengguna',
            'causer_id' => $admin->id,
            'subject_id' => $target->id,
        ]);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function superadminAccountRequests(): array
    {
        return [
            'show' => ['get', 'users.show'],
            'edit' => ['get', 'users.edit'],
            'update' => ['put', 'users.update'],
            'destroy' => ['delete', 'users.destroy'],
            'reset password' => ['post', 'users.reset-password'],
        ];
    }

    #[DataProvider('superadminAccountRequests')]
    public function test_admin_peran_is_forbidden_from_managing_superadmin_account(string $method, string $routeName): void
    {
        $admin = User::factory()->withRole('admin_peran')->create();
        $superadmin = User::factory()->withRole('superadmin')->create();

        $response = $this->actingAs($admin)->{$method}(route($routeName, $superadmin), [
            'name' => 'Diambil Alih',
            'email' => $superadmin->email,
            'role' => 'pemohon',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertForbidden();
        $this->assertNotSoftDeleted($superadmin);
        $this->assertTrue($superadmin->fresh()->hasRole('superadmin'));
    }

    public function test_superadmin_can_update_another_superadmin_and_keep_the_role(): void
    {
        $superadmin = User::factory()->withRole('superadmin')->create();
        $target = User::factory()->withRole('superadmin')->create();

        $response = $this->actingAs($superadmin)->put(route('users.update', $target), [
            'name' => 'Nama Baru',
            'email' => $target->email,
            'role' => 'superadmin',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertSame('Nama Baru', $target->fresh()->name);
        $this->assertTrue($target->fresh()->hasRole('superadmin'));
    }

    /**
     * @return array<string, array{string}>
     */
    public static function protectedNonSuperadminRoles(): array
    {
        return [
            'kaban' => ['kaban'],
            'admin_peran' => ['admin_peran'],
        ];
    }

    #[DataProvider('protectedNonSuperadminRoles')]
    public function test_admin_peran_cannot_change_user_role_to_protected_role(string $role): void
    {
        $admin = User::factory()->withRole('admin_peran')->create();
        $target = User::factory()->withRole('pemohon')->create();

        $response = $this->actingAs($admin)->put(route('users.update', $target), [
            'name' => $target->name,
            'email' => $target->email,
            'role' => $role,
        ]);

        $response->assertSessionHasErrors(['role' => 'Role yang dipilih tidak valid.']);
        $this->assertTrue($target->fresh()->hasRole('pemohon'));
    }

    #[DataProvider('protectedNonSuperadminRoles')]
    public function test_admin_peran_cannot_change_role_of_user_with_protected_role(string $role): void
    {
        $admin = User::factory()->withRole('admin_peran')->create();
        $target = User::factory()->withRole($role)->create();

        $response = $this->actingAs($admin)->put(route('users.update', $target), [
            'name' => $target->name,
            'email' => $target->email,
            'role' => 'pemohon',
        ]);

        $response->assertSessionHasErrors(['role' => 'Role akun ini hanya dapat diubah Superadmin.']);
        $this->assertTrue($target->fresh()->hasRole($role));
    }

    public function test_admin_peran_can_update_data_of_user_with_protected_role_keeping_the_role(): void
    {
        $admin = User::factory()->withRole('admin_peran')->create();
        $target = User::factory()->withRole('kaban')->create();

        $this->actingAs($admin)->get(route('users.edit', $target))
            ->assertOk()
            ->assertSee('Role akun ini hanya dapat diubah Superadmin.');

        $response = $this->actingAs($admin)->put(route('users.update', $target), [
            'name' => 'Nama Kaban Baru',
            'email' => $target->email,
            'role' => 'kaban',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertSame('Nama Kaban Baru', $target->fresh()->name);
        $this->assertTrue($target->fresh()->hasRole('kaban'));
    }

    #[DataProvider('nonCreatableRoles')]
    public function test_superadmin_can_create_user_with_protected_role(string $role): void
    {
        $superadmin = User::factory()->withRole('superadmin')->create();

        $response = $this->actingAs($superadmin)->post(route('users.store'), [
            'name' => 'Akun Baru',
            'email' => 'baru@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => $role,
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertTrue(User::where('email', 'baru@example.com')->firstOrFail()->hasRole($role));
    }

    public function test_admin_peran_creates_opd_account_linked_to_active_opd(): void
    {
        $admin = User::factory()->withRole('admin_peran')->create();
        $opd = Opd::factory()->create();

        $response = $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Akun OPD',
            'email' => 'opd@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'opd',
            'opd_id' => $opd->id,
        ]);

        $response->assertRedirect(route('users.index'));
        $user = User::where('email', 'opd@example.com')->firstOrFail();
        $this->assertTrue($user->hasRole('opd'));
        $this->assertTrue($user->opd->is($opd));
    }

    public function test_opd_account_requires_an_active_opd(): void
    {
        $admin = User::factory()->withRole('admin_peran')->create();
        $payload = [
            'name' => 'Akun OPD',
            'email' => 'opd@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'opd',
        ];

        $this->actingAs($admin)->post(route('users.store'), $payload)
            ->assertSessionHasErrors(['opd_id' => 'OPD wajib dipilih untuk akun OPD.']);

        $this->actingAs($admin)->post(route('users.store'), $payload + ['opd_id' => Opd::factory()->inactive()->create()->id])
            ->assertSessionHasErrors(['opd_id' => 'OPD yang dipilih tidak valid atau nonaktif.']);

        $this->assertDatabaseMissing('users', ['email' => 'opd@example.com']);
    }

    public function test_create_form_offers_active_opd_and_hides_protected_roles_from_admin(): void
    {
        $admin = User::factory()->withRole('admin_peran')->create();
        Opd::factory()->create(['nama_opd' => 'Dinas Kesehatan']);
        Opd::factory()->inactive()->create(['nama_opd' => 'Dinas Lama']);

        $this->actingAs($admin)->get(route('users.create'))
            ->assertOk()
            ->assertSee('Dinas Kesehatan')
            ->assertDontSee('Dinas Lama')
            ->assertDontSee('value="kaban"', false)
            ->assertSee('value="opd"', false);
    }
}
