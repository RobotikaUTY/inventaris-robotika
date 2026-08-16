<?php

namespace App\Filament\Resources\PeminjamanResource\Pages;

use App\Filament\Resources\PeminjamanResource;
use Filament\Resources\Pages\ListRecords;

class ListPeminjaman extends ListRecords
{
    protected static string $resource = PeminjamanResource::class;

    // Tidak ada tombol "Create" di header -- alur normal peminjaman
    // dilakukan lewat aksi "Pinjamkan" di halaman Barang, bukan dari sini.
    protected function getHeaderActions(): array
    {
        return [];
    }
}
