<?php

namespace App\Imports;

use App\Models\OpnameHeader;
use App\Models\OpnameDetail;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Exception;

class OpnameCountSheetImport implements ToCollection, WithHeadingRow
{
    protected int $headerId;
    public bool $isStagingOnly = false;
    public int $updatedCount = 0;
    public array $errorsList = [];
    public array $previewList = [];
    public array $previewSummary = [];

    public function __construct(int $headerId, bool $isStagingOnly = false)
    {
        $this->headerId = $headerId;
        $this->isStagingOnly = $isStagingOnly;
    }

    public function collection(Collection $rows)
    {
        $session = OpnameHeader::with(['details.produk'])->findOrFail($this->headerId);

        if ($session->status !== 'DRAFT') {
            $this->errorsList[] = "Hanya sesi opname berstatus DRAFT yang dapat diimpor datanya.";
            throw new Exception("Session not in draft");
        }

        // Map existing details by product SKU
        $detailsBySku = [];
        foreach ($session->details as $d) {
            if ($d->produk && !empty($d->produk->kode_barang)) {
                $detailsBySku[strtoupper(trim($d->produk->kode_barang))] = $d;
            }
        }

        $filteredRows = $rows->map(function ($row) {
            return $this->normalizeRow($row);
        })->filter(function ($row) {
            $sku = $this->extractSku($row);
            return !empty($sku);
        });

        if ($filteredRows->isEmpty()) {
            $this->errorsList[] = "File Excel kosong atau tidak menemukan kolom 'Kode SKU'.";
            throw new Exception("Empty excel data");
        }

        $previewItems = [];
        $unmatchedSkus = [];

        DB::transaction(function () use ($filteredRows, $detailsBySku, &$previewItems, &$unmatchedSkus) {
            foreach ($filteredRows as $index => $row) {
                $rowNum = $index + 2; // Accounting for 1-based index + header row
                $sku = strtoupper(trim($this->extractSku($row)));

                if (!isset($detailsBySku[$sku])) {
                    // SKU in Excel is not part of this session's products
                    $unmatchedSkus[] = $sku;
                    continue;
                }

                $rawFisik = $this->extractQtyFisik($row);
                if ($rawFisik === null) {
                    $this->errorsList[] = "Baris {$rowNum} (SKU: {$sku}): Nilai Hasil Fisik tidak valid (gunakan angka >= 0, tanda '-', atau biarkan kosong jika 0).";
                    continue;
                }

                $qtyFisik = (int)$rawFisik;
                if ($qtyFisik < 0) {
                    $this->errorsList[] = "Baris {$rowNum} (SKU: {$sku}): Nilai Hasil Fisik tidak boleh bernilai negatif.";
                    continue;
                }

                $catatan = isset($row['catatan']) ? trim((string)$row['catatan']) : null;
                $detail = $detailsBySku[$sku];

                if ($this->isStagingOnly) {
                    $oldFisik = (int)$detail->qty_fisik;
                    $oldCatatan = (string)($detail->catatan ?? '');
                    $newCatatan = $catatan ?? $oldCatatan;
                    $isChanged = ($oldFisik !== $qtyFisik) || ($catatan !== null && $catatan !== $oldCatatan);

                    $previewItems[] = [
                        'detail_id' => $detail->id,
                        'sku' => $sku,
                        'nama_barang' => $detail->produk->nama_barang ?? '-',
                        'nama_produk' => $detail->produk->nama_produk ?? '-',
                        'satuan' => $detail->produk->satuan ?? 'psg',
                        'qty_sistem' => (int)$detail->qty_sistem,
                        'qty_fisik_lama' => $oldFisik,
                        'qty_fisik_baru' => $qtyFisik,
                        'selisih_lama' => (int)$detail->selisih,
                        'selisih_baru' => $qtyFisik - (int)$detail->qty_sistem,
                        'catatan' => $newCatatan,
                        'is_changed' => $isChanged,
                    ];
                } else {
                    $detail->qty_fisik = $qtyFisik;
                    $detail->selisih = $qtyFisik - (int)$detail->qty_sistem;
                    if ($catatan !== null && $catatan !== '') {
                        $detail->catatan = $catatan;
                    }
                    $detail->save();

                    $this->updatedCount++;
                }
            }

            if (!empty($this->errorsList)) {
                throw new Exception("Import validation failed");
            }
        });

        if ($this->isStagingOnly) {
            $changedCount = count(array_filter($previewItems, fn($i) => $i['is_changed']));
            $unchangedCount = count($previewItems) - $changedCount;

            $this->previewList = $previewItems;
            $this->previewSummary = [
                'total_rows' => count($filteredRows),
                'matched_count' => count($previewItems),
                'changed_count' => $changedCount,
                'unchanged_count' => $unchangedCount,
                'unmatched_skus' => array_unique($unmatchedSkus),
            ];
            $this->updatedCount = count($previewItems);
        }
    }

    public static function applyPreviewItems(int $headerId, array $items): int
    {
        $session = OpnameHeader::findOrFail($headerId);
        if ($session->status !== 'DRAFT') {
            throw new Exception("Hanya sesi opname berstatus DRAFT yang dapat diperbarui.");
        }

        return DB::transaction(function () use ($headerId, $items) {
            $updated = 0;
            $details = OpnameDetail::where('opname_header_id', $headerId)->get()->keyBy('id');

            foreach ($items as $item) {
                $detailId = $item['detail_id'] ?? null;
                if ($detailId && isset($details[$detailId])) {
                    $detail = $details[$detailId];
                    $qtyFisik = (int)($item['qty_fisik_baru'] ?? 0);
                    $catatan = isset($item['catatan']) ? trim((string)$item['catatan']) : null;

                    $detail->qty_fisik = $qtyFisik;
                    $detail->selisih = $qtyFisik - (int)$detail->qty_sistem;
                    if ($catatan !== null && $catatan !== '') {
                        $detail->catatan = $catatan;
                    }
                    $detail->save();
                    $updated++;
                }
            }
            return $updated;
        });
    }

    protected function normalizeRow(mixed $row): array
    {
        $raw = $row instanceof Collection ? $row->all() : (array)$row;
        $normalized = [];
        foreach ($raw as $k => $v) {
            $cleanKey = strtolower(trim((string)$k));
            $cleanKey = preg_replace('/[\s\-]+/', '_', $cleanKey);
            $normalized[$cleanKey] = $v;
        }
        return $normalized;
    }

    protected function extractSku(array $row): ?string
    {
        $candidates = ['kode_sku', 'sku', 'kode_barang', 'kode', 'barcode'];
        foreach ($candidates as $k) {
            if (isset($row[$k]) && !empty(trim((string)$row[$k]))) {
                return (string)$row[$k];
            }
        }
        return null;
    }

    protected function extractQtyFisik(array $row): ?int
    {
        $candidates = ['hasil_fisik', 'qty_fisik', 'fisik', 'aktual', 'stok_fisik', 'real', 'real_fisik'];
        foreach ($candidates as $k) {
            if (array_key_exists($k, $row)) {
                $raw = $row[$k];

                if ($raw === null) {
                    return 0;
                }

                $val = trim((string)$raw);

                // 1. If blank string: treat as 0
                if ($val === '') {
                    return 0;
                }

                // 2. Common Indonesian & English text aliases for 0 / empty stock
                $lower = strtolower($val);
                $zeroAliases = [
                    '-', '--', '---', 'kosong', 'habis', 'nihil', 'none', 'nol', 
                    'zero', 'tidak ada', 'tdk ada', 'ga ada', 'gak ada', 'n/a', 'na'
                ];
                if (in_array($lower, $zeroAliases)) {
                    return 0;
                }

                // 3. Numeric string (integer or float)
                if (is_numeric($val)) {
                    return (int)$val;
                }

                // Invalid text that cannot be parsed
                return null;
            }
        }

        // If candidate column exists but wasn't found
        return null;
    }
}
