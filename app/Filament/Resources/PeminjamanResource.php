<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PeminjamanResource\Pages;
use App\Models\Anggota;
use App\Models\Barang;
use App\Models\Peminjaman;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PeminjamanResource extends Resource
{
    protected static ?string $model = Peminjaman::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Histori Peminjaman';
    protected static ?string $navigationGroup = 'Kelola Peminjaman';
    protected static ?int    $navigationSort  = 2;
    protected static ?string $modelLabel = 'Peminjaman';
    protected static ?string $pluralModelLabel = 'Peminjaman';

    // Resource ini fokus untuk DILIHAT (histori), jadi form create/edit tetap ada
    // untuk jaga-jaga/koreksi manual oleh admin, tapi alur normal tetap lewat
    // aksi "Pinjamkan"/"Kembalikan" di BarangResource.
    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('id_barang')
                ->label('Barang')
                ->options(fn () => Barang::query()->pluck('nama_barang', 'id'))
                ->searchable()
                ->required()
                ->disabled(fn (string $operation) => $operation === 'edit')
                ->dehydrated(),

            Forms\Components\Select::make('npm')
                ->label('Peminjam')
                ->options(fn () => Anggota::query()->pluck('nama', 'npm'))
                ->searchable()
                ->required()
                ->disabled(fn (string $operation) => $operation === 'edit')
                ->dehydrated(),

            Forms\Components\TextInput::make('jumlah')
                ->numeric()
                ->minValue(1)
                ->required()
                ->disabled(fn (string $operation) => $operation === 'edit')
                ->dehydrated(),

            Forms\Components\DatePicker::make('tanggal_pinjam')
                ->label('Tgl Pinjam')
                ->required()
                ->live()
                ->disabled(fn (string $operation) => $operation === 'edit'),

            Forms\Components\DatePicker::make('tanggal_kembali')
                ->label('Tgl Kembali')
                ->minDate(fn (Forms\Get $get) => $get('tanggal_pinjam'))
                ->disabled(fn (?\App\Models\Peminjaman $record) => $record?->tanggal_kembali !== null)
                ->helperText(fn (?\App\Models\Peminjaman $record) => $record?->tanggal_kembali ? 'Transaksi sudah selesai, tidak dapat diubah.' : null),

            Forms\Components\Textarea::make('keterangan')
                ->rows(2)
                ->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('tanggal_pinjam', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('barang.nama_barang')
                    ->label('Barang')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('anggota.nama')
                    ->label('Peminjam')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('anggota.divisi')
                    ->label('Divisi')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('jumlah')
                    ->label('Jumlah'),

                Tables\Columns\TextColumn::make('tanggal_pinjam')
                    ->label('Tgl Pinjam')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('tanggal_kembali')
                    ->label('Tgl Kembali')
                    ->date('d M Y')
                    ->placeholder('—')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (Peminjaman $record) => $record->tanggal_kembali ? 'success' : 'warning'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'aktif' => 'Masih Dipinjam',
                        'selesai' => 'Sudah Kembali',
                    ])
                    ->query(function ($query, array $data) {
                        // Guard: jika filter di-clear/reset, value bisa null atau kosong
                        if (empty($data['value'])) {
                            return;
                        }

                        if ($data['value'] === 'aktif') {
                            $query->whereNull('tanggal_kembali');
                        } elseif ($data['value'] === 'selesai') {
                            $query->whereNotNull('tanggal_kembali');
                        }
                    }),

                Tables\Filters\SelectFilter::make('id_barang')
                    ->label('Barang')
                    ->relationship('barang', 'nama_barang')
                    ->searchable(),

                Tables\Filters\SelectFilter::make('npm')
                    ->label('Peminjam')
                    ->relationship('anggota', 'nama')
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn (Peminjaman $record) => $record->tanggal_kembali === null)
                    ->tooltip('Edit peminjaman'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPeminjaman::route('/'),
            'edit'  => Pages\EditPeminjaman::route('/{record}/edit'),
        ];
    }
}
