<?php

namespace App\Exports;

use App\Models\OpnameHeader;
use App\Models\OpnameDetail;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class OpnameCountSheetExport implements FromArray, WithHeadings, WithStyles, WithEvents
{
    protected int $headerId;
    protected ?OpnameHeader $session = null;

    public function __construct(int $headerId)
    {
        $this->headerId = $headerId;
        $this->session = OpnameHeader::with(['lokasi', 'details.produk'])->findOrFail($headerId);
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode SKU',
            'Nama Barang',
            'Kategori',
            'Variasi',
            'Satuan',
            'Stok Sistem',
            'Hasil Fisik',
            'Catatan',
        ];
    }

    public function array(): array
    {
        $data = [];
        $index = 1;

        foreach ($this->session->details as $d) {
            $p = $d->produk;
            $data[] = [
                $index++,
                $p ? $p->kode_barang : '-',
                $p ? $p->nama_barang : '-',
                $p ? $p->kategori : '-',
                $p ? $p->variasi_barang : '-',
                $p ? $p->satuan : 'pasang',
                (int)$d->qty_sistem,
                (int)$d->qty_fisik,
                $d->catatan ?? '',
            ];
        }

        return $data;
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
                    'startColor' => ['rgb' => '1B365D'], // Navy Blue
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Auto-fit columns
                foreach (range('A', 'I') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                $highestRow = $sheet->getHighestRow();

                // Highlight "Hasil Fisik" column header (Column H) with light amber so user knows it's the target input
                $sheet->getStyle('H1')->getFill()->setFillType(Fill::FILL_SOLID);
                $sheet->getStyle('H1')->getFill()->getStartColor()->setRGB('D97706'); // Amber/Orange Accent

                // Center align numeric & code columns
                if ($highestRow >= 2) {
                    $sheet->getStyle("A2:A{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("B2:B{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("E2:G{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("H2:H{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    // Light border for all data cells
                    $sheet->getStyle("A1:I{$highestRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                    $sheet->getStyle("A1:I{$highestRow}")->getBorders()->getAllBorders()->getColor()->setRGB('D1D5DB');

                    // Set light highlight background for Hasil Fisik cells
                    $sheet->getStyle("H2:H{$highestRow}")->getFill()->setFillType(Fill::FILL_SOLID);
                    $sheet->getStyle("H2:H{$highestRow}")->getFill()->getStartColor()->setRGB('FEF3C7'); // Soft yellow/amber

                    // Explicit number format so 0 is always rendered as 0
                    $sheet->getStyle("G2:H{$highestRow}")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER);
                }

                $sheet->setShowGridlines(true);
            },
        ];
    }
}
