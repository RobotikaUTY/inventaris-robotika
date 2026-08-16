<?php

namespace App\Exports;

use App\Models\Barang;
use Illuminate\Support\Enumerable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BarangExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    ShouldAutoSize,
    WithTitle
{
    public function collection(): Enumerable
    {
        return Barang::orderBy('jenis_barang')->orderBy('nama_barang')->get();
    }

    public function title(): string
    {
        return 'Daftar Barang';
    }

    public function headings(): array
    {
        return [
            'Kode',
            'Nama Barang',
            'Jenis',
            'Stok',
            'Satuan',
            'Kondisi',
            'Keterangan',
            'Terakhir Diubah',
        ];
    }

    public function map($barang): array
    {
        return [
            $barang->id,
            $barang->nama_barang,
            $barang->jenis_barang,
            $barang->jumlah,
            $barang->satuan,
            $barang->kondisi,
            $barang->keterangan ?? '-',
            $barang->updated_at->format('d/m/Y H:i'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => [
                    'bold'  => true,
                    'color' => ['argb' => 'FFFFFFFF'],
                ],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF1E3A5F'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],
        ];
    }
}
