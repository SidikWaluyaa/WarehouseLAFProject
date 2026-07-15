<x-app-layout>
    <div x-init="$dispatch('update-header-title', 'Profil Saya')" class="flex-1 overflow-y-auto p-6 lg:p-margin-desktop space-y-stack-lg custom-scrollbar">
        <div class="max-w-4xl space-y-8">
            <!-- Profile Info Section -->
            <div class="glass-card rounded-3xl p-6 sm:p-8 border border-white/10 shadow-2xl relative overflow-hidden">
                <div class="max-w-xl">
                    <livewire:profile.update-profile-information-form />
                </div>
            </div>

            <!-- Update Password Section -->
            <div class="glass-card rounded-3xl p-6 sm:p-8 border border-white/10 shadow-2xl relative overflow-hidden">
                <div class="max-w-xl">
                    <livewire:profile.update-password-form />
                </div>
            </div>

            <!-- Delete User Section -->
            <div class="glass-card rounded-3xl p-6 sm:p-8 border border-white/10 shadow-2xl relative overflow-hidden">
                <div class="max-w-xl">
                    <livewire:profile.delete-user-form />
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
