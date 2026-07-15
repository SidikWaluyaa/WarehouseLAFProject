<?php

namespace App\Services;

use App\Models\TransferHeader;
use App\Models\TransferDetail;
use App\Models\Produk;
use App\Models\StokLokasi;
use App\Models\MutasiLokasi;
use App\Models\Lokasi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class TransferService
{
    /**
     * Generate nomor transfer unik (TRF/YYYYMMDD/XXXX)
     */
    public static function generateNomorTransfer(string $date): string
    {
        $prefix = 'TRF/' . date('Ymd', strtotime($date)) . '/';
        $latest = TransferHeader::where('nomor_transfer', 'like', $prefix . '%')
            ->orderByRaw('nomor_transfer desc')
            ->first();

        if ($latest) {
            $lastNumber = (int) substr($latest->nomor_transfer, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Buat Transfer Order baru (status DRAFT)
     */
    public static function createTransferOrder(array $data): TransferHeader
    {
        try {
            return DB::transaction(function () use ($data) {
                $nomorTransfer = $data['nomor_transfer'] ?? self::generateNomorTransfer($data['tanggal']);

                $header = TransferHeader::create([
                    'nomor_transfer' => $nomorTransfer,
                    'tanggal' => $data['tanggal'],
                    'lokasi_asal_id' => $data['lokasi_asal_id'],
                    'lokasi_tujuan_id' => $data['lokasi_tujuan_id'],
                    'status' => 'DRAFT',
                    'keterangan' => $data['keterangan'] ?? null,
                    'created_by' => $data['created_by'],
                ]);

                foreach ($data['items'] as $item) {
                    TransferDetail::create([
                        'transfer_header_id' => $header->id,
                        'produk_id' => $item['produk_id'],
                        'qty_requested' => (int)$item['qty_requested'],
                        'qty_sent' => 0,
                        'qty_received' => 0,
                    ]);
                }

                // Audit Logging
                $newValues = [
                    'header' => $header->toArray(),
                    'details' => $header->details->toArray()
                ];

                \App\Models\AuditLog::create([
                    'user_id' => Auth::id(),
                    'username' => Auth::user() ? Auth::user()->name : ($data['created_by'] ?? 'System'),
                    'action' => 'CREATE',
                    'auditable_type' => get_class($header),
                    'auditable_id' => $header->id,
                    'old_values' => null,
                    'new_values' => $newValues,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent()
                ]);

                return $header;
            });
        } catch (Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error in TransferService::createTransferOrder: " . $e->getMessage(), [
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Proses pengiriman: kurangi stok asal, ubah status ke IN_TRANSIT
     */
    public static function processShipment(int $transferId, string $shipperName): TransferHeader
    {
        try {
            return DB::transaction(function () use ($transferId, $shipperName) {
                $header = TransferHeader::lockForUpdate()->findOrFail($transferId);

                if ($header->status !== 'DRAFT' && $header->status !== 'PICKING') {
                    throw new Exception("Transfer order tidak dalam status DRAFT atau PICKING.");
                }

                $details = TransferDetail::where('transfer_header_id', $header->id)->get();

                $oldHeaderData = $header->toArray();
                $oldDetailsData = $details->toArray();

                foreach ($details as $detail) {
                    $produk = Produk::lockForUpdate()->findOrFail($detail->produk_id);
                    $stokLokasi = StokLokasi::where([
                        'produk_id' => $produk->id,
                        'lokasi_id' => $header->lokasi_asal_id,
                    ])->lockForUpdate()->first();

                    $stokTersedia = $stokLokasi ? $stokLokasi->qty : 0;
                    $qtySend = $detail->qty_requested;

                    if ($stokTersedia < $qtySend) {
                        throw new Exception("Stok '{$produk->nama_barang}' di lokasi asal tidak mencukupi. Tersedia: {$stokTersedia}, Diminta: {$qtySend}.");
                    }

                    // Kurangi stok di lokasi asal
                    $stokLokasi->qty -= $qtySend;
                    $stokLokasi->save();

                    // Update total_stok produk (berkurang sementara selama in transit)
                    $produk->total_stok = StokLokasi::where('produk_id', $produk->id)->sum('qty');
                    $produk->save();

                    // Catat mutasi keluar dari lokasi asal
                    MutasiLokasi::create([
                        'produk_id' => $produk->id,
                        'lokasi_asal_id' => $header->lokasi_asal_id,
                        'lokasi_tujuan_id' => $header->lokasi_tujuan_id,
                        'qty' => $qtySend,
                        'tipe_mutasi' => 'TRANSFER',
                        'referensi_tipe' => get_class($header),
                        'referensi_id' => $header->id,
                        'keterangan' => 'Transfer Kirim: ' . $header->nomor_transfer,
                        'data_dimasukan_oleh' => $shipperName,
                    ]);

                    $detail->qty_sent = $qtySend;
                    $detail->save();
                }

                $header->status = 'IN_TRANSIT';
                $header->save();

                // Audit Logging (Big 4 standard)
                $oldValues = [
                    'header' => $oldHeaderData,
                    'details' => $oldDetailsData
                ];
                $newValues = [
                    'header' => $header->fresh()->toArray(),
                    'details' => $header->details->toArray()
                ];

                \App\Models\AuditLog::create([
                    'user_id' => Auth::id(),
                    'username' => Auth::user() ? Auth::user()->name : $shipperName,
                    'action' => 'UPDATE',
                    'auditable_type' => get_class($header),
                    'auditable_id' => $header->id,
                    'old_values' => $oldValues,
                    'new_values' => $newValues,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent()
                ]);

                return $header;
            });
        } catch (Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error in TransferService::processShipment: " . $e->getMessage(), [
                'transfer_id' => $transferId,
                'shipper_name' => $shipperName,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Proses penerimaan: tambah stok tujuan, ubah status ke RECEIVED
     */
    public static function processReceive(int $transferId, string $receiverName, array $receivedQtys = []): TransferHeader
    {
        try {
            return DB::transaction(function () use ($transferId, $receiverName, $receivedQtys) {
                $header = TransferHeader::lockForUpdate()->findOrFail($transferId);

                if ($header->status !== 'IN_TRANSIT') {
                    throw new Exception("Transfer order belum dalam status IN_TRANSIT.");
                }

                $details = TransferDetail::where('transfer_header_id', $header->id)->get();
                $oldHeaderData = $header->toArray();
                $oldDetailsData = $details->toArray();

                foreach ($details as $detail) {
                    $qtyReceived = $receivedQtys[$detail->id] ?? $detail->qty_sent;
                    $qtyReceived = min($qtyReceived, $detail->qty_sent);

                    $produk = Produk::lockForUpdate()->findOrFail($detail->produk_id);

                    // Tambah stok di lokasi tujuan (pessimistic lock)
                    $stokLokasi = StokLokasi::where([
                        'produk_id' => $produk->id,
                        'lokasi_id' => $header->lokasi_tujuan_id
                    ])->lockForUpdate()->first();

                    if (!$stokLokasi) {
                        $stokLokasi = StokLokasi::create([
                            'produk_id' => $produk->id,
                            'lokasi_id' => $header->lokasi_tujuan_id,
                            'qty' => 0
                        ]);
                    }
                    $stokLokasi->qty += $qtyReceived;
                    $stokLokasi->save();

                    // Recalculate total_stok
                    $produk->total_stok = StokLokasi::where('produk_id', $produk->id)->sum('qty');
                    $produk->save();

                    // Catat mutasi masuk ke lokasi tujuan (T-09 / M-09)
                    MutasiLokasi::create([
                        'produk_id' => $produk->id,
                        'lokasi_asal_id' => $header->lokasi_asal_id,
                        'lokasi_tujuan_id' => $header->lokasi_tujuan_id,
                        'qty' => $qtyReceived,
                        'tipe_mutasi' => 'TRANSFER',
                        'referensi_tipe' => get_class($header),
                        'referensi_id' => $header->id,
                        'keterangan' => 'Transfer Terima: ' . $header->nomor_transfer,
                        'data_dimasukan_oleh' => $receiverName,
                    ]);

                    $detail->qty_received = $qtyReceived;
                    $detail->save();
                }

                $header->status = 'RECEIVED';
                $header->received_by = $receiverName;
                $header->received_at = now();
                $header->save();

                // Audit Logging (Big 4 standard)
                $oldValues = [
                    'header' => $oldHeaderData,
                    'details' => $oldDetailsData
                ];
                $newValues = [
                    'header' => $header->fresh()->toArray(),
                    'details' => $header->details->toArray()
                ];

                \App\Models\AuditLog::create([
                    'user_id' => Auth::id(),
                    'username' => Auth::user() ? Auth::user()->name : $receiverName,
                    'action' => 'UPDATE',
                    'auditable_type' => get_class($header),
                    'auditable_id' => $header->id,
                    'old_values' => $oldValues,
                    'new_values' => $newValues,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent()
                ]);

                return $header;
            });
        } catch (Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error in TransferService::processReceive: " . $e->getMessage(), [
                'transfer_id' => $transferId,
                'receiver_name' => $receiverName,
                'received_qtys' => $receivedQtys,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Tutup/selesaikan transfer order
     */
    public static function closeTransfer(int $transferId): TransferHeader
    {
        try {
            return DB::transaction(function () use ($transferId) {
                $header = TransferHeader::lockForUpdate()->findOrFail($transferId);

                if ($header->status !== 'RECEIVED') {
                    throw new Exception("Transfer order belum dalam status RECEIVED.");
                }

                $oldHeaderData = $header->toArray();
                $oldDetailsData = TransferDetail::where('transfer_header_id', $header->id)->get()->toArray();

                $header->status = 'CLOSED';
                $header->save();

                // Audit Logging (Big 4 standard)
                $oldValues = [
                    'header' => $oldHeaderData,
                    'details' => $oldDetailsData
                ];
                $newValues = [
                    'header' => $header->fresh()->toArray(),
                    'details' => $header->details->toArray()
                ];

                \App\Models\AuditLog::create([
                    'user_id' => Auth::id(),
                    'username' => Auth::user() ? Auth::user()->name : 'System',
                    'action' => 'UPDATE',
                    'auditable_type' => get_class($header),
                    'auditable_id' => $header->id,
                    'old_values' => $oldValues,
                    'new_values' => $newValues,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent()
                ]);

                return $header;
            });
        } catch (Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error in TransferService::closeTransfer: " . $e->getMessage(), [
                'transfer_id' => $transferId,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
