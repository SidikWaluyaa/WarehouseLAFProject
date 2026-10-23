<?php

namespace App\Livewire\UserManagement;

use App\Models\User;
use App\Traits\HasAlerts;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;
    use HasAlerts;

    // View state
    public string $activeTab = 'users'; // 'users' or 'roles'
    public string $search = '';

    // Modal state
    public bool $isCreatingUser = false;
    public bool $isEditingUser = false;

    // Form fields
    public ?int $userId = null;
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public bool $is_active = true;
    public string $roleName = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'activeTab' => ['except' => 'users'],
    ];

    public function mount(): void
    {
        Gate::authorize('manage system');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    // Modal Actions
    public function openCreateModal(): void
    {
        Gate::authorize('manage system');
        $this->resetForm();
        $this->isCreatingUser = true;
    }

    public function closeCreateModal(): void
    {
        $this->isCreatingUser = false;
        $this->resetForm();
    }

    public function openEditModal(int $id): void
    {
        Gate::authorize('manage system');
        $this->resetForm();
        
        $user = User::findOrFail($id);
        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->is_active = (bool)$user->is_active;
        
        $firstRole = $user->roles->first();
        $this->roleName = $firstRole ? $firstRole->name : '';

        $this->isEditingUser = true;
    }

    public function closeEditModal(): void
    {
        $this->isEditingUser = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->userId = null;
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->is_active = true;
        $this->roleName = '';
        $this->resetValidation();
    }

    // CRUD Actions
    public function createUser(): void
    {
        Gate::authorize('manage system');

        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'roleName' => 'required|exists:roles,name',
            'is_active' => 'required|boolean',
        ], [
            'name.required' => 'Nama wajib diisi.',
            'name.max' => 'Nama maksimal 255 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email maksimal 255 karakter.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'roleName.required' => 'Role wajib dipilih.',
            'roleName.exists' => 'Role tidak valid.',
            'is_active.required' => 'Status aktif wajib diisi.',
        ]);

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'is_active' => $this->is_active,
        ]);

        $user->assignRole($this->roleName);

        $this->closeCreateModal();
        $this->toastSuccess("Pengguna '{$user->name}' berhasil dibuat.");
    }

    public function updateUser(): void
    {
        Gate::authorize('manage system');
        
        $user = User::findOrFail($this->userId);

        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'roleName' => 'required|exists:roles,name',
            'is_active' => 'required|boolean',
        ], [
            'name.required' => 'Nama wajib diisi.',
            'name.max' => 'Nama maksimal 255 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email maksimal 255 karakter.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'roleName.required' => 'Role wajib dipilih.',
            'roleName.exists' => 'Role tidak valid.',
            'is_active.required' => 'Status aktif wajib diisi.',
        ]);

        // Safety Guard: Cannot deactivate self
        if ($user->id === Auth::id() && !$this->is_active) {
            $this->addError('is_active', 'Anda tidak dapat menonaktifkan akun Anda sendiri!');
            return;
        }

        // Safety Guard: Cannot change own role away from super_admin if it is the only one
        if ($user->id === Auth::id() && $this->roleName !== 'super_admin' && $user->hasRole('super_admin')) {
            // Count super admins
            $superAdminCount = User::role('super_admin')->where('is_active', true)->count();
            if ($superAdminCount <= 1) {
                $this->addError('roleName', 'Anda adalah satu-satunya Super Admin aktif. Anda tidak dapat mengubah peran Anda sendiri!');
                return;
            }
        }

        $user->name = $this->name;
        $user->email = $this->email;
        $user->is_active = $this->is_active;

        if (!empty($this->password)) {
            $user->password = Hash::make($this->password);
        }

        $user->save();
        $user->syncRoles([$this->roleName]);

        $this->closeEditModal();
        $this->toastSuccess("Pengguna '{$user->name}' berhasil diperbarui.");
    }

    public function toggleUserStatus(int $id): void
    {
        Gate::authorize('manage system');

        $user = User::findOrFail($id);

        // Safety Guard: Cannot deactivate self
        if ($user->id === Auth::id()) {
            $this->toastError("Gagal: Anda tidak dapat menonaktifkan akun Anda sendiri!");
            return;
        }

        // Safety Guard: Check if deactivating the last active super_admin
        if ($user->hasRole('super_admin') && $user->is_active) {
            $activeSuperAdmins = User::role('super_admin')->where('is_active', true)->count();
            if ($activeSuperAdmins <= 1) {
                $this->toastError("Gagal: Tidak dapat menonaktifkan satu-satunya Super Admin aktif yang tersisa.");
                return;
            }
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $statusStr = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        $this->toastSuccess("Akun '{$user->name}' berhasil {$statusStr}.");
    }

    /**
     * Satu sumber kebenaran untuk guard hapus pengguna.
     * Dipanggil oleh requestDeleteUser() DAN confirmedDeleteUser(), karena event
     * 'confirmed-delete-user' bisa dikirim langsung dari browser tanpa melewati dialog.
     *
     * @return string|null Pesan penolakan, atau null bila boleh dihapus.
     */
    private function deleteUserGuardError(User $user): ?string
    {
        if ($user->id === Auth::id()) {
            return "Gagal: Anda tidak dapat menghapus akun Anda sendiri!";
        }

        if ($user->hasRole('super_admin') && User::role('super_admin')->count() <= 1) {
            return "Gagal: Tidak dapat menghapus satu-satunya akun Super Admin.";
        }

        return null;
    }

    public function requestDeleteUser(int $id): void
    {
        Gate::authorize('manage system');
        $user = User::findOrFail($id);

        if ($error = $this->deleteUserGuardError($user)) {
            $this->toastError($error);
            return;
        }

        $this->alertConfirm(
            action: 'confirmed-delete-user',
            params: ['id' => $id],
            title: 'Hapus Pengguna?',
            text: "Apakah Anda yakin ingin menghapus akun '{$user->name}'? Data ini tidak dapat dikembalikan.",
            destructive: true
        );
    }

    #[On('confirmed-delete-user')]
    public function confirmedDeleteUser(array|int|null $params = null, ?int $id = null): void
    {
        Gate::authorize('manage system');

        if (is_array($params)) {
            $id = $params['id'] ?? $id;
        } elseif (is_numeric($params)) {
            $id = (int)$params;
        }

        if (!$id) {
            $this->toastError("Gagal: ID pengguna tidak valid.");
            return;
        }

        $user = User::findOrFail($id);

        if ($error = $this->deleteUserGuardError($user)) {
            $this->toastError($error);
            return;
        }

        $name = $user->name;
        $user->delete();

        $this->toastSuccess("Pengguna '{$name}' berhasil dihapus.");
    }

    // Role Matrix toggles
    public function togglePermission(string $roleName, string $permissionName): void
    {
        Gate::authorize('manage system');

        if ($roleName === 'super_admin') {
            $this->toastError("Gagal: Hak akses Super Admin tidak dapat dimodifikasi untuk mencegah lockout.");
            return;
        }

        $role = Role::findByName($roleName);
        $permission = Permission::findByName($permissionName);

        if ($role->hasPermissionTo($permission)) {
            $role->revokePermissionTo($permission);
            $this->toastSuccess("Izin '{$permissionName}' dicabut dari peran '{$roleName}'.");
        } else {
            $role->givePermissionTo($permission);
            $this->toastSuccess("Izin '{$permissionName}' diberikan kepada peran '{$roleName}'.");
        }

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function render()
    {
        Gate::authorize('manage system');

        // Fetch user list with roles
        $userQuery = User::with('roles');

        if (!empty($this->search)) {
            $userQuery->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%')
                  ->orWhereHas('roles', function ($qr) {
                      $qr->where('name', 'like', '%' . $this->search . '%');
                  });
            });
        }

        $users = $userQuery->orderBy('name')->paginate(10);

        // Fetch Roles & Permissions for Tab 2 Matrix
        $roles = Role::orderBy('name')->get();
        $permissions = Permission::orderBy('name')->get();

        return view('livewire.user-management.index', [
            'users' => $users,
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }
}
