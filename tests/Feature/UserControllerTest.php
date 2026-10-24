<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    private const ROLES = ['superadmin', 'kaban', 'admin_peran', 'verifikator', 'fasilitator', 'pemohon', 'auditor'];

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
}
