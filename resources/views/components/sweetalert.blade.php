<!-- SweetAlert2 CDN & Global central handler -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Centralized SweetAlert2 Toast configuration
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            background: '#1f1f1f',
            color: '#e2e2e2',
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        // Listener for simple success popup alert
        window.addEventListener('alert:success', event => {
            const data = event.detail[0] || event.detail;
            Swal.fire({
                icon: 'success',
                title: data.title || 'Berhasil',
                text: data.text || '',
                background: '#1f1f1f',
                color: '#e2e2e2',
                confirmButtonText: 'OK',
                customClass: {
                    popup: 'rounded-3xl border border-white/10 glass-card',
                    confirmButton: 'rounded-2xl px-6 py-2.5 font-bold text-sm bg-gradient-to-r from-primary-container to-[#ff9f00] text-on-primary-container shadow-xl hover:scale-105 transition-all duration-200 focus:ring-0',
                },
                buttonsStyling: false
            });
        });

        // Listener for simple error popup alert
        window.addEventListener('alert:error', event => {
            const data = event.detail[0] || event.detail;
            Swal.fire({
                icon: 'error',
                title: data.title || 'Gagal',
                text: data.text || '',
                background: '#1f1f1f',
                color: '#e2e2e2',
                confirmButtonText: 'OK',
                customClass: {
                    popup: 'rounded-3xl border border-white/10 glass-card',
                    confirmButton: 'rounded-2xl px-6 py-2.5 font-bold text-sm bg-gradient-to-r from-primary-container to-[#ff9f00] text-on-primary-container shadow-xl hover:scale-105 transition-all duration-200 focus:ring-0',
                },
                buttonsStyling: false
            });
        });

        // Listener for warning popup alert
        window.addEventListener('alert:warning', event => {
            const data = event.detail[0] || event.detail;
            Swal.fire({
                icon: 'warning',
                title: data.title || 'Peringatan',
                text: data.text || '',
                background: '#1f1f1f',
                color: '#e2e2e2',
                confirmButtonText: 'OK',
                customClass: {
                    popup: 'rounded-3xl border border-white/10 glass-card',
                    confirmButton: 'rounded-2xl px-6 py-2.5 font-bold text-sm bg-gradient-to-r from-primary-container to-[#ff9f00] text-on-primary-container shadow-xl hover:scale-105 transition-all duration-200 focus:ring-0',
                },
                buttonsStyling: false
            });
        });

        // Listener for confirm popup alert
        window.addEventListener('alert:confirm', event => {
            const data = event.detail[0] || event.detail;
            Swal.fire({
                title: data.title || 'Apakah Anda yakin?',
                text: data.text || '',
                icon: data.icon || 'warning',
                showCancelButton: true,
                confirmButtonText: data.confirmText || 'Ya, Lanjutkan!',
                cancelButtonText: data.cancelText || 'Batal',
                background: '#1f1f1f',
                color: '#e2e2e2',
                customClass: {
                    popup: 'rounded-3xl border border-white/10 glass-card',
                    confirmButton: 'rounded-2xl px-6 py-2.5 font-bold text-sm bg-gradient-to-r from-primary-container to-[#ff9f00] text-on-primary-container shadow-xl hover:scale-105 transition-all duration-200 focus:ring-0 mr-3',
                    cancelButton: 'rounded-2xl px-6 py-2.5 font-medium text-sm bg-white/5 border border-white/10 hover:bg-white/10 transition-all text-on-surface focus:ring-0'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    // Dispatch back to Livewire component
                    Livewire.dispatch(data.action, data.params || {});
                }
            });
        });

        // Listener for success toast
        window.addEventListener('toast:success', event => {
            const data = event.detail[0] || event.detail;
            Toast.fire({
                icon: 'success',
                title: data.text || ''
            });
        });

        // Listener for error toast
        window.addEventListener('toast:error', event => {
            const data = event.detail[0] || event.detail;
            Toast.fire({
                icon: 'error',
                title: data.text || ''
            });
        });

        // Flash message dari redirect server (session()->flash)
        @if (session('success_message'))
            Toast.fire({ icon: 'success', title: @js(session('success_message')) });
        @endif
        @if (session('error_message'))
            Toast.fire({ icon: 'error', title: @js(session('error_message')) });
        @endif
    });
</script>
