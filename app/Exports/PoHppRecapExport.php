<?php

namespace App\Exports;

use App\Models\PoDetail;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PoHppRecapExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return PoDetail::with(['poHeader.supplier', 'produk'])->get();
    }

    public function headings(): array
    {
        return [
            'Supplier / Vendor',
            'Artikel PO',
            'Nama Barang',
            'Kode Barang',
            'Kuantitas PO',
            'Barang Masuk',
            'Sisa PO',
            'HPP PO',
            'Total Nilai HPP Sisa',
        ];
    }

    public function map($row): array
    {
        return [
            $row->poHeader->supplier->nama_supplier ?? '-',
            $row->poHeader->artikel ?? '-',
            $row->produk->nama_barang ?? '-',
            $row->produk->kode_barang ?? '-',
            $row->jumlah,
            $row->barang_masuk,
            $row->sisa,
            $row->hpp,
            $row->sisa * $row->hpp,
        ];
    }
}
