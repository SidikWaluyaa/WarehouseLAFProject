<?php

namespace App\Traits;

trait HasAlerts
{
    /**
     * Dispatch a success popup alert.
     */
    public function alertSuccess(string $message, string $title = 'Berhasil'): void
    {
        $this->dispatch('alert:success', [
            'title' => $title,
            'text' => $message,
        ]);
    }

    /**
     * Dispatch an error popup alert.
     */
    public function alertError(string $message, string $title = 'Gagal'): void
    {
        $this->dispatch('alert:error', [
            'title' => $title,
            'text' => $message,
        ]);
    }

    /**
     * Dispatch a confirmation alert.
     * When confirmed, it will trigger the specified $action Livewire event with the $params payload.
     */
    public function alertConfirm(
        string $action,
        mixed $params = null,
        string $title = 'Apakah Anda yakin?',
        string $text = 'Tindakan ini tidak dapat dibatalkan.',
        string $confirmText = 'Ya, Lanjutkan!',
        string $cancelText = 'Batal',
        string $icon = 'warning'
    ): void {
        $this->dispatch('alert:confirm', [
            'action' => $action,
            'params' => $params,
            'title' => $title,
            'text' => $text,
            'confirmText' => $confirmText,
            'cancelText' => $cancelText,
            'icon' => $icon,
        ]);
    }

    /**
     * Dispatch a success toast notification.
     */
    public function toastSuccess(string $message): void
    {
        $this->dispatch('toast:success', [
            'text' => $message,
        ]);
    }

    /**
     * Dispatch an error toast notification.
     */
    public function toastError(string $message): void
    {
        $this->dispatch('toast:error', [
            'text' => $message,
        ]);
    }

    /**
     * Dummy clearAlerts method for backward compatibility.
     */
    public function clearAlerts(): void
    {
        // No-op
    }
}
