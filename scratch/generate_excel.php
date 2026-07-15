<?php

require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set Header
$headers = [
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
    'Stok Awal'
];

foreach ($headers as $colIndex => $header) {
    $sheet->setCellValueByColumnAndRow($colIndex + 1, 1, $header);
}

// Data rows from screenshots
$data = [
    // GORKA HITAM
    [51600, 'GORKA HITAM', 'SANDAL', 'SandalSlide-Gorka-FullBlack-36', 'GORKA HITAM 36', 'HITAM', '36', 'psg', 'GDG-A01', 51600, 100000, 17],
    [51600, 'GORKA HITAM', 'SANDAL', 'SandalSlide-Gorka-FullBlack-37', 'GORKA HITAM 37', 'HITAM', '37', 'psg', 'GDG-A01', 51600, 100000, 26],
    [51600, 'GORKA HITAM', 'SANDAL', 'SandalSlide-Gorka-FullBlack-38', 'GORKA HITAM 38', 'HITAM', '38', 'psg', 'GDG-A01', 51600, 100000, 34],
    [51600, 'GORKA HITAM', 'SANDAL', 'SandalSlide-Gorka-FullBlack-39', 'GORKA HITAM 39', 'HITAM', '39', 'psg', 'GDG-A01', 51600, 100000, 57],
    [51600, 'GORKA HITAM', 'SANDAL', 'SandalSlide-Gorka-FullBlack-40', 'GORKA HITAM 40', 'HITAM', '40', 'psg', 'GDG-A01', 51600, 100000, 103],
    [51600, 'GORKA HITAM', 'SANDAL', 'SandalSlide-Gorka-FullBlack-41', 'GORKA HITAM 41', 'HITAM', '41', 'psg', 'GDG-A01', 51600, 100000, 81],
    [51600, 'GORKA HITAM', 'SANDAL', 'SandalSlide-Gorka-FullBlack-42', 'GORKA HITAM 42', 'HITAM', '42', 'psg', 'GDG-A01', 51600, 100000, 131],
    [51600, 'GORKA HITAM', 'SANDAL', 'SandalSlide-Gorka-FullBlack-43', 'GORKA HITAM 43', 'HITAM', '43', 'psg', 'GDG-A01', 51600, 100000, 51],
    [52000, 'GORKA HITAM', 'SANDAL', 'SandalSlide-Gorka-FullBlack-44', 'GORKA HITAM 44', 'HITAM', '44', 'psg', 'GDG-A01', 52000, 100000, 6],
    [52000, 'GORKA HITAM', 'SANDAL', 'SandalSlide-Gorka-FullBlack-45', 'GORKA HITAM 45', 'HITAM', '45', 'psg', 'GDG-A01', 52000, 100000, 1],
    // GORKA ABU
    [51600, 'GORKA ABU', 'SANDAL', 'SandalSlide-Gorka-Grey-39', 'GORKA ABU 39', 'ABU', '39', 'psg', 'GDG-A01', 51600, 100000, 14],
    [51600, 'GORKA ABU', 'SANDAL', 'SandalSlide-Gorka-Grey-40', 'GORKA ABU 40', 'ABU', '40', 'psg', 'GDG-A01', 51600, 100000, 43],
    [51600, 'GORKA ABU', 'SANDAL', 'SandalSlide-Gorka-Grey-41', 'GORKA ABU 41', 'ABU', '41', 'psg', 'GDG-A01', 51600, 100000, 46],
    [51600, 'GORKA ABU', 'SANDAL', 'SandalSlide-Gorka-Grey-42', 'GORKA ABU 42', 'ABU', '42', 'psg', 'GDG-A01', 51600, 100000, 24],
    [51600, 'GORKA ABU', 'SANDAL', 'SandalSlide-Gorka-Grey-43', 'GORKA ABU 43', 'ABU', '43', 'psg', 'GDG-A01', 51600, 100000, 16],
    // GORKA ARMY
    [51600, 'GORKA ARMY', 'SANDAL', 'SandalSlide-Gorka-Army-39', 'GORKA ARMY 39', 'ARMY', '39', 'psg', 'GDG-A01', 51600, 100000, 11],
    [51600, 'GORKA ARMY', 'SANDAL', 'SandalSlide-Gorka-Army-40', 'GORKA ARMY 40', 'ARMY', '40', 'psg', 'GDG-A01', 51600, 100000, 9],
    [51600, 'GORKA ARMY', 'SANDAL', 'SandalSlide-Gorka-Army-41', 'GORKA ARMY 41', 'ARMY', '41', 'psg', 'GDG-A01', 51600, 100000, 15],
    [51600, 'GORKA ARMY', 'SANDAL', 'SandalSlide-Gorka-Army-42', 'GORKA ARMY 42', 'ARMY', '42', 'psg', 'GDG-A01', 51600, 100000, 25],
    [51600, 'GORKA ARMY', 'SANDAL', 'SandalSlide-Gorka-Army-43', 'GORKA ARMY 43', 'ARMY', '43', 'psg', 'GDG-A01', 51600, 100000, 35],
    // ARON HITAM
    [99129, 'ARON HITAM', 'SANDAL', 'SandalGunung-Aron-FullBlack-36', 'ARON HITAM 36', 'HITAM', '36', 'psg', 'GDG-A01', 99129, 180000, 32],
    [99129, 'ARON HITAM', 'SANDAL', 'SandalGunung-Aron-FullBlack-37', 'ARON HITAM 37', 'HITAM', '37', 'psg', 'GDG-A01', 99129, 180000, 20],
    [99129, 'ARON HITAM', 'SANDAL', 'SandalGunung-Aron-FullBlack-38', 'ARON HITAM 38', 'HITAM', '38', 'psg', 'GDG-A01', 99129, 180000, 24],
    [99129, 'ARON HITAM', 'SANDAL', 'SandalGunung-Aron-FullBlack-39', 'ARON HITAM 39', 'HITAM', '39', 'psg', 'GDG-A01', 99129, 180000, 12],
    [99129, 'ARON HITAM', 'SANDAL', 'SandalGunung-Aron-FullBlack-40', 'ARON HITAM 40', 'HITAM', '40', 'psg', 'GDG-A01', 99129, 180000, 32],
    [99129, 'ARON HITAM', 'SANDAL', 'SandalGunung-Aron-FullBlack-41', 'ARON HITAM 41', 'HITAM', '41', 'psg', 'GDG-A01', 99129, 180000, 23],
    [99129, 'ARON HITAM', 'SANDAL', 'SandalGunung-Aron-FullBlack-42', 'ARON HITAM 42', 'HITAM', '42', 'psg', 'GDG-A01', 99129, 180000, 38],
    [99129, 'ARON HITAM', 'SANDAL', 'SandalGunung-Aron-FullBlack-43', 'ARON HITAM 43', 'HITAM', '43', 'psg', 'GDG-A01', 99129, 180000, 20],
    [99129, 'ARON HITAM', 'SANDAL', 'SandalGunung-Aron-FullBlack-44', 'ARON HITAM 44', 'HITAM', '44', 'psg', 'GDG-A01', 99129, 180000, 62],
    [99129, 'ARON HITAM', 'SANDAL', 'SandalGunung-Aron-FullBlack-45', 'ARON HITAM 45', 'HITAM', '45', 'psg', 'GDG-A01', 99129, 180000, 37],
    // ARON HITAM PUTIH
    [99129, 'ARON HITAM PUTIH', 'SANDAL', 'SandalGunung-Aron-BlackWhite-36', 'ARON HITAM PUTIH 36', 'HITAM PUTIH', '36', 'psg', 'GDG-A01', 99129, 180000, 7],
    [99129, 'ARON HITAM PUTIH', 'SANDAL', 'SandalGunung-Aron-BlackWhite-37', 'ARON HITAM PUTIH 37', 'HITAM PUTIH', '37', 'psg', 'GDG-A01', 99129, 180000, 5],
    [99129, 'ARON HITAM PUTIH', 'SANDAL', 'SandalGunung-Aron-BlackWhite-38', 'ARON HITAM PUTIH 38', 'HITAM PUTIH', '38', 'psg', 'GDG-A01', 99129, 180000, 6],
    [99129, 'ARON HITAM PUTIH', 'SANDAL', 'SandalGunung-Aron-BlackWhite-39', 'ARON HITAM PUTIH 39', 'HITAM PUTIH', '39', 'psg', 'GDG-A01', 99129, 180000, 10],
    [99129, 'ARON HITAM PUTIH', 'SANDAL', 'SandalGunung-Aron-BlackWhite-40', 'ARON HITAM PUTIH 40', 'HITAM PUTIH', '40', 'psg', 'GDG-A01', 99129, 180000, 13],
    [99129, 'ARON HITAM PUTIH', 'SANDAL', 'SandalGunung-Aron-BlackWhite-41', 'ARON HITAM PUTIH 41', 'HITAM PUTIH', '41', 'psg', 'GDG-A01', 99129, 180000, 10],
    [99129, 'ARON HITAM PUTIH', 'SANDAL', 'SandalGunung-Aron-BlackWhite-42', 'ARON HITAM PUTIH 42', 'HITAM PUTIH', '42', 'psg', 'GDG-A01', 99129, 180000, 10],
    [99129, 'ARON HITAM PUTIH', 'SANDAL', 'SandalGunung-Aron-BlackWhite-43', 'ARON HITAM PUTIH 43', 'HITAM PUTIH', '43', 'psg', 'GDG-A01', 99129, 180000, 10],
    [99129, 'ARON HITAM PUTIH', 'SANDAL', 'SandalGunung-Aron-BlackWhite-44', 'ARON HITAM PUTIH 44', 'HITAM PUTIH', '44', 'psg', 'GDG-A01', 99129, 180000, 7],
    [99129, 'ARON HITAM PUTIH', 'SANDAL', 'SandalGunung-Aron-BlackWhite-45', 'ARON HITAM PUTIH 45', 'HITAM PUTIH', '45', 'psg', 'GDG-A01', 99129, 180000, 21],
    // KALLA HITAM
    [62760, 'KALLA HITAM', 'SANDAL', 'SandalSlide-Kalla-FullBlack-39', 'KALLA HITAM 39', 'HITAM', '39', 'psg', 'GDG-A01', 62760, 120000, 34],
    [62760, 'KALLA HITAM', 'SANDAL', 'SandalSlide-Kalla-FullBlack-40', 'KALLA HITAM 40', 'HITAM', '40', 'psg', 'GDG-A01', 62760, 120000, 57],
    [62760, 'KALLA HITAM', 'SANDAL', 'SandalSlide-Kalla-FullBlack-41', 'KALLA HITAM 41', 'HITAM', '41', 'psg', 'GDG-A01', 62760, 120000, 56],
    [62760, 'KALLA HITAM', 'SANDAL', 'SandalSlide-Kalla-FullBlack-42', 'KALLA HITAM 42', 'HITAM', '42', 'psg', 'GDG-A01', 62760, 120000, 106],
    [62760, 'KALLA HITAM', 'SANDAL', 'SandalSlide-Kalla-FullBlack-43', 'KALLA HITAM 43', 'HITAM', '43', 'psg', 'GDG-A01', 62760, 120000, 67]
];

// Write data rows
$rowIndex = 2;
foreach ($data as $row) {
    $rowData = [
        $row[3],  // Kode Barang
        $row[1],  // Nama Produk
        $row[4],  // Nama Display
        $row[2],  // Kategori
        $row[5],  // Warna
        $row[6],  // Size
        $row[7],  // Satuan
        $row[8],  // Lokasi Default
        $row[9],  // HPP per Pcs
        $row[10], // Harga Jual
        $row[11]  // Stok Awal
    ];
    
    foreach ($rowData as $colIndex => $val) {
        $sheet->setCellValueByColumnAndRow($colIndex + 1, $rowIndex, $val);
    }
    $rowIndex++;
}

$writer = new Xlsx($spreadsheet);
$writer->save(__DIR__ . '/../public/products_import.xlsx');
$writer->save(__DIR__ . '/../products_import.xlsx');
echo "Excel generated successfully.\n";
