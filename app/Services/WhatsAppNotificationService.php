<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppNotificationService
{
    /**
     * Format nomor HP ke format internasional (62xxxx)
     */
    public static function formatPhoneNumber(string $phone): string
    {
        $clean = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($clean, '0')) {
            $clean = '62' . substr($clean, 1);
        } elseif (str_starts_with($clean, '8')) {
            $clean = '62' . $clean;
        }
        return $clean;
    }

    /**
     * Format pesan WhatsApp peringatan stok menipis
     */
    public static function formatLowStockMessage(iterable $products, ?string $senderName = null): string
    {
        $timeStr = now()->translatedFormat('d M Y, H:i') . ' WIB';
        $sender = $senderName ?: 'Sistem WMS LAF';

        $msg = "⚠️ *PERINGATAN STOK MENIPIS - LAF MARKET* ⚠️\n";
        $msg .= "📅 _Waktu: {$timeStr}_\n";
        $msg .= "👤 _Operator: {$sender}_\n\n";
        $msg .= "Halo Tim Gudang & Purchasing,\n";
        $msg .= "Berikut adalah daftar produk terpilih/prioritas yang telah mencapai atau berada di bawah ambang batas minimum:\n\n";

        $i = 1;
        $count = 0;
        foreach ($products as $p) {
            $nama = $p->nama_barang ?: $p->nama_produk;
            $sku = $p->kode_barang;
            $stok = (int)$p->total_stok;
            $min = (int)$p->min_stok;
            $satuan = $p->satuan ?: 'psg';
            $kategori2 = $p->kategori_2 ? " [{$p->kategori_2}]" : "";
            $selisih = $stok - $min;

            $statusIcon = $stok <= 0 ? '⛔ *HABIS (0)*' : "🔴 *Defisit {$selisih} {$satuan}*";

            $msg .= "{$i}. *{$nama}*{$kategori2}\n";
            $msg .= "   • SKU: `{$sku}`\n";
            $msg .= "   • Sisa Stok: *{$stok} {$satuan}* (Batas Min: {$min} {$satuan})\n";
            $msg .= "   • Status: {$statusIcon}\n\n";
            $i++;
            $count++;
        }

        if ($count === 0) {
            return "✅ *LAPORAN STOK LAF MARKET*\nSemua produk terpilih saat ini dalam kondisi stok aman (berada di atas ambang batas).";
        }

        $msg .= "📌 *Catatan:* Total terdapat *{$count} produk* menipis.\n";
        $msg .= "Mohon segera lakukan perencanaan pengadaan/produksi (Purchase Order) untuk mencegah terjadinya _stockout_.\n\n";
        $msg .= "_Pesan otomatis dibuat dari WMS LAF Project_";

        return $msg;
    }

    /**
     * Menghasilkan URL langsung ke chat WhatsApp (wa.me)
     */
    public static function generateWhatsAppUrl(string $targetPhone, string $message): string
    {
        $formattedPhone = self::formatPhoneNumber($targetPhone);
        return "https://wa.me/{$formattedPhone}?text=" . rawurlencode($message);
    }

    /**
     * Mengirim pesan WhatsApp menggunakan API Fonnte
     */
    public static function sendViaFonnte(string $targetPhone, string $message, ?string $token = null): array
    {
        $token = $token ?: config('services.fonnte.token');
        $url = config('services.fonnte.url', 'https://api.fonnte.com/send');

        if (empty($token)) {
            return [
                'success' => false,
                'message' => 'Token API Fonnte belum dikonfigurasi. Silakan isi FONNTE_API_TOKEN di file .env atau pengaturan sistem.',
                'response' => null,
            ];
        }

        $formattedPhone = self::formatPhoneNumber($targetPhone);
        if (empty($formattedPhone)) {
            return [
                'success' => false,
                'message' => 'Nomor WhatsApp tujuan tidak valid.',
                'response' => null,
            ];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $token,
            ])->timeout(15)->post($url, [
                'target' => $formattedPhone,
                'message' => $message,
                'countryCode' => '62',
            ]);

            $json = $response->json();

            if ($response->successful() && isset($json['status']) && $json['status'] == true) {
                return [
                    'success' => true,
                    'message' => 'Pesan WhatsApp berhasil dikirim via Fonnte.',
                    'response' => $json,
                ];
            }

            $errMsg = $json['reason'] ?? ($json['message'] ?? 'Gagal mengirim pesan via Fonnte.');
            return [
                'success' => false,
                'message' => "Fonnte API Error: {$errMsg}",
                'response' => $json,
            ];
        } catch (\Throwable $e) {
            Log::error("Fonnte Send Exception: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return [
                'success' => false,
                'message' => "Terjadi kesalahan koneksi ke server Fonnte: " . $e->getMessage(),
                'response' => null,
            ];
        }
    }
}
