<?php

namespace App\Filament\Resources\BarangResource\Pages;

use App\Exports\BarangExport;
use App\Filament\Resources\BarangResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

class ListBarang extends ListRecords
{
    protected static string $resource = BarangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export_excel')
                ->label('Download Excel')
                ->icon('heroicon-o-table-cells')
                ->color('success')
                ->action(function () {
                    return Excel::download(
                        new BarangExport(),
                        'daftar-barang-' . now()->format('Y-m-d') . '.xlsx'
                    );
                }),

            Actions\CreateAction::make(),
        ];
    }
}
