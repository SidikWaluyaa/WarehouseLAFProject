<?php

namespace App\Exports;

use App\Models\Produk;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ProductsExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithEvents
{
    public function query()
    {
        return Produk::query();
    }

    public function headings(): array
    {
        return [
            'Kode Barang',
            'Nama Produk',
            'Nama Display',
            'Kategori',
            'Warna',
            'Size',
            'Satuan',
            'Stok Masuk',
            'Stok Keluar',
            'Total Stok',
            'Lokasi Default',
            'HPP per Pcs',
            'Harga Jual',
            'Total Nilai Stok',
        ];
    }

    public function map($row): array
    {
        return [
            $row->kode_barang,
            $row->nama_produk,
            $row->nama_barang,
            $row->kategori,
            $row->warna,
            $row->size,
            $row->satuan,
            $row->stok_masuk,
            $row->stok_keluar,
            $row->total_stok,
            $row->lokasi,
            (float)$row->hpp,
            (float)$row->harga_jual,
            (float)($row->total_stok * $row->hpp),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 11,
                    'name' => 'Segoe UI',
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '107C41'], // Excel Green
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ]
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();

                // Enable column auto-width
                foreach (range('A', 'N') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                if ($highestRow > 1) {
                    // Right-align numeric columns: H (Stok Masuk), I (Stok Keluar), J (Total Stok), L (HPP), M (Harga Jual), N (Total Nilai)
                    $sheet->getStyle("H2:J{$highestRow}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle("L2:N{$highestRow}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                    // Format HPP, Harga Jual and Total Nilai as Rupiah Currency
                    $sheet->getStyle("L2:N{$highestRow}")
                        ->getNumberFormat()
                        ->setFormatCode('"Rp"#,##0');
                }

                // Gridlines visible
                $sheet->setShowGridlines(true);
            }
        ];
    }
}
