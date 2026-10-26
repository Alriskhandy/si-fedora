<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SyncPermissionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_role_gets_full_default_mapping_without_touching_ui_changes_on_existing_roles(): void
    {
        $this->artisan('permission:sync', ['--reset' => true])->assertSuccessful();

        Role::findByName('opd')->delete();
        Role::findByName('verifikator')->revokePermissionTo('permohonan.verify');
        Permission::findByName('permohonan.view-all')->roles()->detach(Role::findByName('auditor'));

        $this->artisan('permission:sync')->assertSuccessful();

        $this->assertTrue(Role::findByName('opd')->hasPermissionTo('permohonan.view-all'));
        $this->assertFalse(Role::findByName('verifikator')->hasPermissionTo('permohonan.verify'));
        $this->assertFalse(Role::findByName('auditor')->hasPermissionTo('permohonan.view-all'));
    }
}
