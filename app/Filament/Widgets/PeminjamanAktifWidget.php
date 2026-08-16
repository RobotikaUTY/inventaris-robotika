<?php

namespace App\Filament\Widgets;

use App\Models\Peminjaman;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class PeminjamanAktifWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Peminjaman Aktif (Belum Dikembalikan)';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Peminjaman::query()
                    ->whereNull('tanggal_kembali')
                    ->with(['barang', 'anggota'])
                    ->latest('tanggal_pinjam')
            )
            ->columns([
                Tables\Columns\TextColumn::make('barang.nama_barang')
                    ->label('Barang')
                    ->searchable()
                    ->weight('semibold'),

                Tables\Columns\TextColumn::make('barang.id')
                    ->label('Kode')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('anggota.nama')
                    ->label('Peminjam')
                    ->searchable(),

                Tables\Columns\TextColumn::make('anggota.divisi')
                    ->label('Divisi')
                    ->badge(),

                Tables\Columns\TextColumn::make('jumlah')
                    ->label('Jumlah')
                    ->suffix(fn (Peminjaman $record) => ' ' . ($record->barang?->satuan ?? '')),

                Tables\Columns\TextColumn::make('tanggal_pinjam')
                    ->label('Tgl Pinjam')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->placeholder('—')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->emptyStateIcon('heroicon-o-clipboard-document-check')
            ->emptyStateHeading('Tidak ada peminjaman aktif')
            ->emptyStateDescription('Semua barang sudah dikembalikan.')
            ->paginated([5, 10, 25]);
    }
}
