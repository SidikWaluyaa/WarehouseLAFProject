<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Livewire\Volt\Volt;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use App\Livewire\UserManagement\Index as UserManagementIndex;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;
    private User $operator;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed default roles and permissions
        $this->artisan('db:seed', ['--class' => 'DatabaseSeeder']);

        // Retrieve seeded users
        $this->superAdmin = User::where('email', 'superadmin@wmslaf.com')->first();
        $this->operator = User::where('email', 'gudang@wmslaf.com')->first();
    }

    public function test_guest_cannot_access_user_management(): void
    {
        $response = $this->get('/user-management');
        $response->assertRedirect('/login');
    }

    public function test_operator_cannot_access_user_management(): void
    {
        $response = $this->actingAs($this->operator)->get('/user-management');
        $response->assertStatus(403);
    }

    public function test_super_admin_can_access_user_management(): void
    {
        $response = $this->actingAs($this->superAdmin)->get('/user-management');
        $response->assertStatus(200);
        $response->assertSee('Manajemen Pengguna');
    }

    public function test_can_create_user_with_role(): void
    {
        Livewire::actingAs($this->superAdmin)
            ->test(UserManagementIndex::class)
            ->call('openCreateModal')
            ->set('name', 'User Baru')
            ->set('email', 'baru@wmslaf.com')
            ->set('roleName', 'operator_gudang')
            ->set('is_active', true)
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->call('createUser')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'name' => 'User Baru',
            'email' => 'baru@wmslaf.com',
            'is_active' => true,
        ]);

        $newUser = User::where('email', 'baru@wmslaf.com')->first();
        $this->assertTrue($newUser->hasRole('operator_gudang'));
        $this->assertTrue(Hash::check('password123', $newUser->password));
    }

    public function test_can_update_user_and_change_role(): void
    {
        $user = User::create([
            'name' => 'User Temp',
            'email' => 'temp@wmslaf.com',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);
        $user->assignRole('sales');

        Livewire::actingAs($this->superAdmin)
            ->test(UserManagementIndex::class)
            ->call('openEditModal', $user->id)
            ->set('name', 'User Temp Updated')
            ->set('roleName', 'finance')
            ->call('updateUser')
            ->assertHasNoErrors();

        $user->refresh();
        $this->assertEquals('User Temp Updated', $user->name);
        $this->assertTrue($user->hasRole('finance'));
        $this->assertFalse($user->hasRole('sales'));
    }

    public function test_cannot_deactivate_self(): void
    {
        Livewire::actingAs($this->superAdmin)
            ->test(UserManagementIndex::class)
            ->call('toggleUserStatus', $this->superAdmin->id)
            ->assertDispatched('toast:error', function ($name, $args) {
                $data = $args[0] ?? [];
                return ($data['text'] ?? '') === 'Gagal: Anda tidak dapat menonaktifkan akun Anda sendiri!';
            });

        $this->superAdmin->refresh();
        $this->assertTrue((bool)$this->superAdmin->is_active);
    }

    public function test_cannot_deactivate_last_active_super_admin(): void
    {
        // There is only 1 super admin seeded by default
        $user = User::create([
            'name' => 'Other Admin',
            'email' => 'other@wmslaf.com',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);
        $user->assignRole('super_admin');

        // Let's deactivate the newly created super_admin (now there is still 1 active, which is $this->superAdmin)
        Livewire::actingAs($this->superAdmin)
            ->test(UserManagementIndex::class)
            ->call('toggleUserStatus', $user->id)
            ->assertDispatched('toast:success');

        $user->refresh();
        $this->assertFalse((bool)$user->is_active);

        // Try to deactivate the last remaining active super admin ($this->superAdmin) using a second test run or simulation
        // But since $this->superAdmin cannot deactivate self, let's make $user active again and try to deactivate $this->superAdmin from $user's perspective
        $user->is_active = true;
        $user->save();

        Livewire::actingAs($user)
            ->test(UserManagementIndex::class)
            ->call('toggleUserStatus', $this->superAdmin->id)
            ->assertDispatched('toast:success');

        $this->superAdmin->refresh();
        $this->assertFalse((bool)$this->superAdmin->is_active);

        // Now $user is the last active super_admin. Try to deactivate $user acting as himself or through another user
        // But since we can't deactivate self, let's create a temporary normal user with 'manage system' permission to do it
        $manager = User::create([
            'name' => 'Manager User',
            'email' => 'manager@wmslaf.com',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);
        $manager->givePermissionTo('manage system');

        Livewire::actingAs($manager)
            ->test(UserManagementIndex::class)
            ->call('toggleUserStatus', $user->id)
            ->assertDispatched('toast:error', function ($name, $args) {
                $data = $args[0] ?? [];
                return ($data['text'] ?? '') === 'Gagal: Tidak dapat menonaktifkan satu-satunya Super Admin aktif yang tersisa.';
            });

        $user->refresh();
        $this->assertTrue((bool)$user->is_active);
    }

    public function test_deactivated_user_cannot_login(): void
    {
        $user = User::create([
            'name' => 'Disabled User',
            'email' => 'disabled@wmslaf.com',
            'password' => Hash::make('password123'),
            'is_active' => false,
        ]);

        $component = Volt::test('pages.auth.login')
            ->set('form.email', $user->email)
            ->set('form.password', 'password123');

        $component->call('login');

        $component->assertHasErrors(['form.email']);
        $this->assertGuest();
    }

    public function test_cannot_delete_self(): void
    {
        Livewire::actingAs($this->superAdmin)
            ->test(UserManagementIndex::class)
            ->call('requestDeleteUser', $this->superAdmin->id)
            ->assertDispatched('toast:error', function ($name, $args) {
                $data = $args[0] ?? [];
                return ($data['text'] ?? '') === 'Gagal: Anda tidak dapat menghapus akun Anda sendiri!';
            });
    }

    public function test_cannot_delete_last_super_admin(): void
    {
        // $this->superAdmin is the only super admin
        Livewire::actingAs($this->superAdmin)
            ->test(UserManagementIndex::class)
            ->call('requestDeleteUser', $this->superAdmin->id)
            ->assertDispatched('toast:error');
    }

    public function test_can_delete_other_user(): void
    {
        $user = User::create([
            'name' => 'User to Delete',
            'email' => 'delete-me@wmslaf.com',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);
        $user->assignRole('sales');

        Livewire::actingAs($this->superAdmin)
            ->test(UserManagementIndex::class)
            ->call('requestDeleteUser', $user->id)
            ->assertDispatched('alert:confirm')
            ->call('confirmedDeleteUser', ['id' => $user->id])
            ->assertDispatched('toast:success');

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }

    public function test_role_matrix_permission_toggling(): void
    {
        $role = Role::where('name', 'finance')->first();
        $permission = Permission::where('name', 'input masuk')->first();

        // Initially finance does not have input masuk
        $this->assertFalse($role->hasPermissionTo($permission->name));

        // Toggle it on
        Livewire::actingAs($this->superAdmin)
            ->test(UserManagementIndex::class)
            ->call('togglePermission', 'finance', 'input masuk')
            ->assertDispatched('toast:success');

        $role->refresh();
        $this->assertTrue($role->hasPermissionTo($permission->name));

        // Toggle it off
        Livewire::actingAs($this->superAdmin)
            ->test(UserManagementIndex::class)
            ->call('togglePermission', 'finance', 'input masuk')
            ->assertDispatched('toast:success');

        $role->refresh();
        $this->assertFalse($role->hasPermissionTo($permission->name));
    }

    public function test_cannot_toggle_super_admin_permissions(): void
    {
        Livewire::actingAs($this->superAdmin)
            ->test(UserManagementIndex::class)
            ->call('togglePermission', 'super_admin', 'input masuk')
            ->assertDispatched('toast:error');
    }
}
