<?php

namespace App\Filament\Widgets;

use App\Models\Anggota;
use App\Models\Barang;
use App\Models\Peminjaman;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalBarang       = Barang::count();
        $barangDipinjam    = Barang::where('kondisi', 'Dipinjam')->count();
        $barangRusak       = Barang::where('kondisi', 'Rusak')->count();
        $totalAnggota      = Anggota::count();
        $peminjamanAktif   = Peminjaman::whereNull('tanggal_kembali')->count();
        $peminjamanSelesai = Peminjaman::whereNotNull('tanggal_kembali')->count();

        return [
            Stat::make('Total Barang', $totalBarang)
                ->color('primary'),

            Stat::make('Sedang Dipinjam', $peminjamanAktif)
                ->color('warning'),

            Stat::make('Total Anggota', $totalAnggota)
                ->color('success'),

            Stat::make('Barang Rusak', $barangRusak)
                ->color($barangRusak > 0 ? 'danger' : 'gray'),
        ];
    }
}
