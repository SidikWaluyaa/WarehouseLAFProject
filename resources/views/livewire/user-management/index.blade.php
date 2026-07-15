<div x-init="$dispatch('update-header-title', 'Manajemen Pengguna & Hak Akses')" class="flex-1 overflow-y-auto p-margin-desktop space-y-stack-lg custom-scrollbar">
    
    @if($activeTab === 'users')
    <template x-teleport="#header-search-placeholder">
        <div class="hidden md:flex items-center bg-surface-container-low rounded-full px-4 py-1.5 border border-white/5 ml-4">
            <span class="material-symbols-outlined text-on-surface-variant text-sm mr-2">search</span>
            <input wire:model.live.debounce.300ms="search" class="bg-transparent border-none text-sm focus:ring-0 text-on-surface w-64 placeholder:text-on-surface-variant/50" placeholder="Cari Pengguna atau Email..." type="text"/>
        </div>
    </template>
    @endif

    <!-- Tab Navigation -->
    <div class="flex border-b border-white/10 mb-6 gap-2">
        <button wire:click="$set('activeTab', 'users')" 
                class="flex items-center gap-2 px-6 py-3.5 font-bold text-sm transition-all focus:outline-none border-b-2 {{ $activeTab === 'users' ? 'border-primary text-primary' : 'border-transparent text-on-surface-variant/60 hover:text-on-surface' }}">
            <span class="material-symbols-outlined text-lg">group</span>
            Manajemen Pengguna
        </button>
        <button wire:click="$set('activeTab', 'roles')" 
                class="flex items-center gap-2 px-6 py-3.5 font-bold text-sm transition-all focus:outline-none border-b-2 {{ $activeTab === 'roles' ? 'border-primary text-primary' : 'border-transparent text-on-surface-variant/60 hover:text-on-surface' }}">
            <span class="material-symbols-outlined text-lg">admin_panel_settings</span>
            Peran & Hak Akses
        </button>
    </div>

    @if($activeTab === 'users')
        <!-- Actions Bar -->
        <section class="flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                <!-- Search for mobile -->
                <div class="md:hidden relative w-full">
                    <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-sm">search</span>
                    <input wire:model.live.debounce.300ms="search" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-2.5 pl-10 pr-4 text-sm text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all placeholder:text-on-surface-variant/30" placeholder="Cari pengguna..."/>
                </div>
            </div>
            <button wire:click="openCreateModal" class="flex items-center gap-2 px-8 py-3.5 bg-gradient-to-r from-primary-container to-[#ff9f00] text-on-primary-container rounded-2xl font-bold text-sm shadow-xl hover:shadow-primary/20 transition-all active:scale-95 duration-200 amber-glow-strong">
                <span class="material-symbols-outlined">person_add</span>
                Tambah Pengguna
            </button>
        </section>

        <!-- Users Table -->
        <section class="glass-card rounded-3xl overflow-hidden mb-8">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-white/5">
                            <th class="px-6 py-5 text-[11px] font-bold tracking-[0.2em] text-on-surface-variant/60 uppercase">Nama Pengguna</th>
                            <th class="px-6 py-5 text-[11px] font-bold tracking-[0.2em] text-on-surface-variant/60 uppercase">Email</th>
                            <th class="px-6 py-5 text-[11px] font-bold tracking-[0.2em] text-on-surface-variant/60 uppercase text-center">Peran</th>
                            <th class="px-6 py-5 text-[11px] font-bold tracking-[0.2em] text-on-surface-variant/60 uppercase text-center">Status</th>
                            <th class="px-6 py-5 text-[11px] font-bold tracking-[0.2em] text-on-surface-variant/60 uppercase text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5 text-xs">
                        @forelse ($users as $user)
                            <tr class="hover:bg-white/5 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold border border-primary/20">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <span class="text-sm font-semibold text-on-surface block">{{ $user->name }}</span>
                                            @if($user->id === auth()->id())
                                                <span class="text-[9px] text-primary/70 font-semibold uppercase tracking-wider bg-primary/10 border border-primary/20 px-2 py-0.5 rounded-full mt-0.5 inline-block">Anda</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-mono text-on-surface-variant/90">{{ $user->email }}</td>
                                <td class="px-6 py-4 text-center">
                                    @php
                                        $userRole = $user->roles->first();
                                    @endphp
                                    @if ($userRole)
                                        <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider 
                                            {{ $userRole->name === 'super_admin' ? 'bg-error/10 text-error border border-error/20' : 
                                               ($userRole->name === 'admin' ? 'bg-primary/10 text-primary border border-primary/20' : 'bg-white/5 text-on-surface-variant/80 border border-white/10') }}">
                                            {{ str_replace('_', ' ', $userRole->name) }}
                                        </span>
                                    @else
                                        <span class="text-on-surface-variant/40 italic">Tidak ada peran</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center">
                                        <button wire:click="toggleUserStatus({{ $user->id }})" 
                                                @disabled($user->id === auth()->id())
                                                class="flex items-center gap-1.5 transition-opacity disabled:opacity-50">
                                            @if($user->is_active)
                                                <span class="px-2 py-0.5 bg-green-500/10 text-green-400 border border-green-500/20 rounded text-[10px] font-bold">AKTIF</span>
                                            @else
                                                <span class="px-2 py-0.5 bg-white/5 text-on-surface-variant/40 border border-white/5 rounded text-[10px]">NONAKTIF</span>
                                            @endif
                                        </button>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex justify-center gap-2">
                                        <button wire:click="openEditModal({{ $user->id }})" class="w-8 h-8 rounded-lg flex items-center justify-center hover:bg-primary/20 text-primary transition-all" title="Ubah">
                                            <span class="material-symbols-outlined text-lg">edit</span>
                                        </button>
                                        <button wire:click="requestDeleteUser({{ $user->id }})" 
                                                @disabled($user->id === auth()->id())
                                                class="w-8 h-8 rounded-lg flex items-center justify-center hover:bg-error/20 text-error transition-all disabled:opacity-30 disabled:hover:bg-transparent" title="Hapus">
                                            <span class="material-symbols-outlined text-lg">delete</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-on-surface-variant">Tidak ada pengguna ditemukan</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Custom Styled Pagination -->
            <div class="px-6 py-4 border-t border-white/5 flex items-center justify-between">
                <p class="text-xs text-on-surface-variant">
                    Menampilkan {{ $users->firstItem() ?? 0 }} s.d. {{ $users->lastItem() ?? 0 }} dari {{ $users->total() }} hasil
                </p>
                <div class="flex gap-2">
                    @if ($users->onFirstPage())
                        <button class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-on-surface-variant/50 cursor-not-allowed" disabled>Sebelumnya</button>
                    @else
                        <button wire:click="previousPage" class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-on-surface hover:bg-white/10 transition-all">Sebelumnya</button>
                    @endif

                    @foreach ($users->getUrlRange(max(1, $users->currentPage() - 2), min($users->lastPage(), $users->currentPage() + 2)) as $page => $url)
                        @if ($page == $users->currentPage())
                            <button class="px-4 py-2 rounded-xl bg-primary-container text-on-primary-container text-xs font-bold">{{ $page }}</button>
                        @else
                            <button wire:click="gotoPage({{ $page }})" class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-on-surface hover:bg-white/10 transition-all">{{ $page }}</button>
                        @endif
                    @endforeach

                    @if ($users->hasMorePages())
                        <button wire:click="nextPage" class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-on-surface hover:bg-white/10 transition-all">Selanjutnya</button>
                    @else
                        <button class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-on-surface-variant/50 cursor-not-allowed" disabled>Selanjutnya</button>
                    @endif
                </div>
            </div>
        </section>
    @endif

    @if($activeTab === 'roles')
        <!-- Roles & Permissions Matrix -->
        <section class="glass-card rounded-3xl overflow-hidden mb-8 p-6 space-y-6">
            <div>
                <h3 class="text-base font-bold text-primary mb-1">Matrix Hak Akses</h3>
                <p class="text-xs text-on-surface-variant/70">Klik checkbox di bawah untuk mengubah izin akses yang diberikan ke masing-masing peran sistem. Perubahan akan langsung disimpan secara real-time.</p>
            </div>

            <!-- Caution / Safety Tip -->
            <div class="flex items-start gap-3 p-4 bg-error/5 border border-error/20 rounded-2xl">
                <span class="material-symbols-outlined text-error text-lg flex-shrink-0 mt-0.5">warning</span>
                <div>
                    <h4 class="text-xs font-bold text-error uppercase tracking-wider mb-0.5">Catatan Penting</h4>
                    <p class="text-[11px] text-on-surface-variant/80">Hak akses untuk peran <strong>Super Admin</strong> dikunci secara permanen dan tidak dapat dimodifikasi guna mencegah administrator terkunci dari sistem (lockout).</p>
                </div>
            </div>

            <div class="overflow-x-auto custom-scrollbar border border-white/5 rounded-2xl">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-white/5 border-b border-white/10">
                            <th class="px-6 py-4 text-xs font-bold tracking-[0.1em] text-on-surface uppercase w-1/3">Izin / Fitur</th>
                            @foreach($roles as $role)
                                <th class="px-6 py-4 text-xs font-bold tracking-[0.1em] text-on-surface uppercase text-center">
                                    {{ str_replace('_', ' ', $role->name) }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5 text-xs">
                        @foreach($permissions as $permission)
                            <tr class="hover:bg-white/5 transition-colors">
                                <td class="px-6 py-4">
                                    <span class="text-sm font-semibold text-on-surface block">{{ ucwords(str_replace('_', ' ', $permission->name)) }}</span>
                                    <span class="text-[10px] text-on-surface-variant/50 font-mono">{{ $permission->name }}</span>
                                </td>
                                @foreach($roles as $role)
                                    <td class="px-6 py-4 text-center">
                                        @if($role->name === 'super_admin')
                                            <!-- Super Admin Checked & Disabled -->
                                            <input type="checkbox" checked disabled 
                                                   class="h-5 w-5 rounded bg-white/10 border-white/20 text-error focus:ring-0 focus:ring-offset-0 opacity-60 cursor-not-allowed"/>
                                        @else
                                            <!-- Toggleable Checkbox -->
                                            <input type="checkbox" 
                                                   wire:click="togglePermission('{{ $role->name }}', '{{ $permission->name }}')"
                                                   @checked($role->hasPermissionTo($permission->name))
                                                   class="h-5 w-5 rounded bg-surface-container-low border-white/10 text-primary focus:ring-0 focus:ring-offset-0 cursor-pointer hover:border-primary transition-colors"/>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endif

    <!-- Create User Modal -->
    @if ($isCreatingUser)
    <div class="fixed inset-0 z-50 flex items-center justify-center overflow-x-hidden overflow-y-auto outline-none focus:outline-none">
        <div class="fixed inset-0 bg-background/80 backdrop-blur-md transition-opacity" wire:click="closeCreateModal"></div>
        <div class="relative w-full max-w-lg mx-auto my-6 z-50 px-4">
            <div class="relative flex flex-col w-full glass-card rounded-3xl shadow-2xl border border-white/10 outline-none focus:outline-none overflow-hidden bg-surface-dark/95">
                <!-- Modal Header -->
                <div class="flex items-center justify-between px-6 py-5 border-b border-white/5">
                    <h3 class="text-base font-bold text-primary flex items-center gap-2">
                        <span class="material-symbols-outlined">person_add</span>
                        Tambah Pengguna Baru
                    </h3>
                    <button wire:click="closeCreateModal" class="text-on-surface-variant hover:text-on-surface">
                        <span class="material-symbols-outlined text-lg">close</span>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto custom-scrollbar">
                    <!-- Nama -->
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant/80 uppercase tracking-wider mb-2">Nama Pengguna</label>
                        <input wire:model="name" type="text" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-3 px-4 text-sm text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all placeholder:text-on-surface-variant/30" placeholder="Masukkan nama lengkap...">
                        @error('name') <span class="text-xs text-error mt-1.5 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant/80 uppercase tracking-wider mb-2">Alamat Email</label>
                        <input wire:model="email" type="email" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-3 px-4 text-sm text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all placeholder:text-on-surface-variant/30" placeholder="Contoh: user@wmslaf.com">
                        @error('email') <span class="text-xs text-error mt-1.5 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <!-- Peran / Role -->
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant/80 uppercase tracking-wider mb-2">Peran Akses</label>
                        <select wire:model="roleName" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-3 px-4 text-sm text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                            <option value="">Pilih Peran...</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}">{{ ucwords(str_replace('_', ' ', $role->name)) }}</option>
                            @endforeach
                        </select>
                        @error('roleName') <span class="text-xs text-error mt-1.5 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant/80 uppercase tracking-wider mb-2">Status Akun</label>
                        <div class="flex items-center gap-3 p-3 bg-white/5 rounded-2xl border border-white/5">
                            <div>
                                <p class="text-sm font-semibold text-on-surface">Aktifkan Akun</p>
                                <p class="text-[10px] text-on-surface-variant/60">Jika dinonaktifkan, pengguna tidak dapat masuk ke sistem.</p>
                            </div>
                            <input wire:model="is_active" type="checkbox" class="h-5 w-5 rounded bg-surface-container-low border-white/10 text-primary focus:ring-0 focus:ring-offset-0 ml-auto cursor-pointer"/>
                        </div>
                        @error('is_active') <span class="text-xs text-error mt-1.5 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <div class="border-t border-white/5 pt-4 my-2"></div>

                    <!-- Password -->
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant/80 uppercase tracking-wider mb-2">Password</label>
                        <input wire:model="password" type="password" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-3 px-4 text-sm text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all placeholder:text-on-surface-variant/30" placeholder="Minimal 8 karakter...">
                        @error('password') <span class="text-xs text-error mt-1.5 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <!-- Password Confirmation -->
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant/80 uppercase tracking-wider mb-2">Konfirmasi Password</label>
                        <input wire:model="password_confirmation" type="password" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-3 px-4 text-sm text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all placeholder:text-on-surface-variant/30" placeholder="Ulangi password...">
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex items-center justify-end px-6 py-5 border-t border-white/5 gap-3 bg-surface-container-low/50">
                    <button wire:click="closeCreateModal" type="button" class="px-6 py-2.5 bg-white/5 border border-white/10 rounded-2xl text-sm font-medium hover:bg-white/10 transition-all text-on-surface">
                        Batal
                    </button>
                    <button wire:click="createUser" type="button" class="px-6 py-2.5 bg-gradient-to-r from-primary-container to-[#ff9f00] text-on-primary-container rounded-2xl font-bold text-sm shadow-xl hover:shadow-primary/20 transition-all active:scale-95 duration-200 amber-glow-strong">
                        Simpan Pengguna
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Edit User Modal -->
    @if ($isEditingUser)
    <div class="fixed inset-0 z-50 flex items-center justify-center overflow-x-hidden overflow-y-auto outline-none focus:outline-none">
        <div class="fixed inset-0 bg-background/80 backdrop-blur-md transition-opacity" wire:click="closeEditModal"></div>
        <div class="relative w-full max-w-lg mx-auto my-6 z-50 px-4">
            <div class="relative flex flex-col w-full glass-card rounded-3xl shadow-2xl border border-white/10 outline-none focus:outline-none overflow-hidden bg-surface-dark/95">
                <!-- Modal Header -->
                <div class="flex items-center justify-between px-6 py-5 border-b border-white/5">
                    <h3 class="text-base font-bold text-primary flex items-center gap-2">
                        <span class="material-symbols-outlined">edit</span>
                        Ubah Informasi Pengguna
                    </h3>
                    <button wire:click="closeEditModal" class="text-on-surface-variant hover:text-on-surface">
                        <span class="material-symbols-outlined text-lg">close</span>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto custom-scrollbar">
                    <!-- Nama -->
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant/80 uppercase tracking-wider mb-2">Nama Pengguna</label>
                        <input wire:model="name" type="text" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-3 px-4 text-sm text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all placeholder:text-on-surface-variant/30">
                        @error('name') <span class="text-xs text-error mt-1.5 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant/80 uppercase tracking-wider mb-2">Alamat Email</label>
                        <input wire:model="email" type="email" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-3 px-4 text-sm text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all placeholder:text-on-surface-variant/30">
                        @error('email') <span class="text-xs text-error mt-1.5 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <!-- Peran / Role -->
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant/80 uppercase tracking-wider mb-2">Peran Akses</label>
                        <select wire:model="roleName" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-3 px-4 text-sm text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}">{{ ucwords(str_replace('_', ' ', $role->name)) }}</option>
                            @endforeach
                        </select>
                        @error('roleName') <span class="text-xs text-error mt-1.5 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant/80 uppercase tracking-wider mb-2">Status Akun</label>
                        <div class="flex items-center gap-3 p-3 bg-white/5 rounded-2xl border border-white/5">
                            <div>
                                <p class="text-sm font-semibold text-on-surface">Aktifkan Akun</p>
                                <p class="text-[10px] text-on-surface-variant/60">Jika dinonaktifkan, pengguna tidak dapat masuk ke sistem.</p>
                            </div>
                            <input wire:model="is_active" type="checkbox" 
                                   @disabled($userId === auth()->id())
                                   class="h-5 w-5 rounded bg-surface-container-low border-white/10 text-primary focus:ring-0 focus:ring-offset-0 ml-auto cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"/>
                        </div>
                        @if($userId === auth()->id())
                            <span class="text-[10px] text-primary/70 mt-1 block font-semibold">Anda tidak dapat menonaktifkan akun sendiri melalui menu ini.</span>
                        @endif
                        @error('is_active') <span class="text-xs text-error mt-1.5 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <div class="border-t border-white/5 pt-4 my-2"></div>

                    <!-- Password Info Alert -->
                    <div class="p-3 bg-white/5 rounded-2xl border border-white/5 text-[11px] text-on-surface-variant/70">
                        Biarkan kolom password di bawah ini kosong jika Anda tidak ingin mengubah password pengguna.
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant/80 uppercase tracking-wider mb-2">Password Baru (Opsional)</label>
                        <input wire:model="password" type="password" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-3 px-4 text-sm text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all placeholder:text-on-surface-variant/30" placeholder="Minimal 8 karakter...">
                        @error('password') <span class="text-xs text-error mt-1.5 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <!-- Password Confirmation -->
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant/80 uppercase tracking-wider mb-2">Konfirmasi Password Baru</label>
                        <input wire:model="password_confirmation" type="password" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-3 px-4 text-sm text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all placeholder:text-on-surface-variant/30" placeholder="Ulangi password...">
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex items-center justify-end px-6 py-5 border-t border-white/5 gap-3 bg-surface-container-low/50">
                    <button wire:click="closeEditModal" type="button" class="px-6 py-2.5 bg-white/5 border border-white/10 rounded-2xl text-sm font-medium hover:bg-white/10 transition-all text-on-surface">
                        Batal
                    </button>
                    <button wire:click="updateUser" type="button" class="px-6 py-2.5 bg-gradient-to-r from-primary-container to-[#ff9f00] text-on-primary-container rounded-2xl font-bold text-sm shadow-xl hover:shadow-primary/20 transition-all active:scale-95 duration-200 amber-glow-strong">
                        Simpan Perubahan
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>
