<?php

namespace App\Exports;

use App\Models\Lokasi;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Conditional;

class ProductsTemplateExport implements FromArray, WithHeadings, WithStyles, WithEvents
{
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
            'Lokasi Default',
            'HPP per Pcs',
            'Harga Jual',
            'Stok Awal',
        ];
    }

    public function array(): array
    {
        return [
            [
                '', // Kode Barang (dikosongkan agar tergenerate otomatis)
                'Gorka Hitam 36',
                'Gorka Hitam 36',
                'Sandal Dewasa',
                'Hitam',
                '36',
                'pasang',
                'GUDANG',
                25000,
                37500,
                100,
            ],
            [
                '', // Kode Barang (dikosongkan agar tergenerate otomatis)
                'Gorka Abu 39',
                'Gorka Abu 39',
                'Sandal Dewasa',
                'Abu-abu',
                '39',
                'pasang',
                'GUDANG',
                25000,
                37500,
                50,
            ],
            [
                '', // Kode Barang (dikosongkan agar tergenerate otomatis)
                'Gorka Army 40',
                'Gorka Army 40',
                'Sandal Dewasa',
                'Army',
                '40',
                'pasang',
                'GUDANG',
                25000,
                37500,
                80,
            ]
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
                    'startColor' => ['rgb' => '1B365D'], // Navy Blue
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ]
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Enable column auto-width
                foreach (range('A', 'K') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                // Get list of active locations in DB
                $locations = Lokasi::where('is_active', true)->pluck('kode_lokasi')->toArray();
                if (empty($locations)) {
                    $locations = ['GUDANG', 'TOKO', 'GDG-A01', 'TKO-R01'];
                } else {
                    $locations = array_merge($locations, ['GUDANG', 'TOKO']);
                }
                $locations = array_unique(array_map('strtoupper', $locations));

                // Data Validation (Dropdowns) for rows 2 to 100
                for ($row = 2; $row <= 100; $row++) {
                    // Category dropdown (Column D)
                    $validationCat = $sheet->getCell("D{$row}")->getDataValidation();
                    $validationCat->setType(DataValidation::TYPE_LIST);
                    $validationCat->setErrorStyle(DataValidation::STYLE_STOP);
                    $validationCat->setAllowBlank(true);
                    $validationCat->setShowInputMessage(true);
                    $validationCat->setShowErrorMessage(true);
                    $validationCat->setShowDropDown(true);
                    $validationCat->setErrorTitle('Kategori Tidak Valid');
                    $validationCat->setError('Silakan pilih kategori dari daftar yang tersedia.');
                    $validationCat->setPromptTitle('Pilih Kategori');
                    $validationCat->setPrompt('Pilih salah satu: Sandal Dewasa, Sepatu Dewasa, Sandal Anak, Sepatu Anak, Tas, Aksesoris, Apparel, Packaging');
                    $validationCat->setFormula1('"Sandal Dewasa,Sepatu Dewasa,Sandal Anak,Sepatu Anak,Tas,Aksesoris,Apparel,Packaging"');

                    // Unit dropdown (Column G)
                    $validationUnit = $sheet->getCell("G{$row}")->getDataValidation();
                    $validationUnit->setType(DataValidation::TYPE_LIST);
                    $validationUnit->setErrorStyle(DataValidation::STYLE_STOP);
                    $validationUnit->setAllowBlank(true);
                    $validationUnit->setShowInputMessage(true);
                    $validationUnit->setShowErrorMessage(true);
                    $validationUnit->setShowDropDown(true);
                    $validationUnit->setErrorTitle('Satuan Tidak Valid');
                    $validationUnit->setError('Silakan pilih satuan dari daftar yang tersedia.');
                    $validationUnit->setPromptTitle('Pilih Satuan');
                    $validationUnit->setPrompt('Pilih salah satu: pasang, pcs, lembar, unit');
                    $validationUnit->setFormula1('"pasang,pcs,lembar,unit"');

                    // Location dropdown (Column H)
                    $validationLoc = $sheet->getCell("H{$row}")->getDataValidation();
                    $validationLoc->setType(DataValidation::TYPE_LIST);
                    $validationLoc->setErrorStyle(DataValidation::STYLE_STOP);
                    $validationLoc->setAllowBlank(true);
                    $validationLoc->setShowInputMessage(true);
                    $validationLoc->setShowErrorMessage(true);
                    $validationLoc->setShowDropDown(true);
                    $validationLoc->setErrorTitle('Lokasi Tidak Valid');
                    $validationLoc->setError('Silakan pilih lokasi dari daftar yang tersedia.');
                    $validationLoc->setPromptTitle('Pilih Lokasi');
                    $validationLoc->setPrompt('Pilih lokasi yang valid di sistem.');
                    $validationLoc->setFormula1('"' . implode(',', $locations) . '"');
                }

                // Conditional Formatting for HPP per Pcs (Column I), Harga Jual (Column J), and Stok Awal (Column K)
                // Highlight negative values in light red
                $conditionalStyle = new Conditional();
                $conditionalStyle->setConditionType(Conditional::CONDITION_CELLIS);
                $conditionalStyle->setOperatorType(Conditional::OPERATOR_LESSTHAN);
                $conditionalStyle->addCondition('0');
                
                $conditionalStyle->getStyle()->getFill()->setFillType(Fill::FILL_SOLID);
                $conditionalStyle->getStyle()->getFill()->getStartColor()->setRGB('FFC7CE'); // Light Red
                $conditionalStyle->getStyle()->getFont()->getColor()->setRGB('9C0006'); // Dark Red

                // Apply to columns I, J, K
                $sheet->getStyle('I2:K100')->setConditionalStyles([$conditionalStyle]);

                // Gridlines visible
                $sheet->setShowGridlines(true);
            }
        ];
    }
}
