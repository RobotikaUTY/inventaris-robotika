<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BarangResource\Pages;
use App\Models\Barang;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BarangResource extends Resource
{
    protected static ?string $model = Barang::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationLabel = 'Barang';
    protected static ?string $navigationGroup = 'Inventaris';
    protected static ?int    $navigationSort  = 1;
    protected static ?string $modelLabel = 'Barang';
    protected static ?string $pluralModelLabel = 'Barang';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('jenis_barang')
                ->label('Jenis Barang')
                ->options([
                    'Wahana'    => 'Wahana',
                    'Peralatan' => 'Peralatan',
                    'Komponen'  => 'Komponen',
                ])
                ->required()
                ->native(false)
                // id hanya di-generate otomatis saat CREATE, jadi kunci saat edit
                ->disabled(fn (string $operation) => $operation === 'edit'),

            Forms\Components\TextInput::make('nama_barang')
                ->label('Nama Barang')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('jumlah')
                ->label('Jumlah Stok')
                ->numeric()
                ->minValue(0)
                ->required(),

            Forms\Components\Select::make('satuan')
                ->label('Satuan')
                ->options([
                    'PCS'   => 'PCS',
                    'Meter' => 'Meter',
                    'Roll'  => 'Roll',
                    'Set'   => 'Set',
                    'Box'   => 'Box',
                ])
                ->required()
                ->native(false),

            Forms\Components\Select::make('kondisi')
                ->label('Kondisi')
                ->options([
                    'Baik'     => 'Baik',
                    'Rusak'    => 'Rusak',
                    'Dipinjam' => 'Dipinjam',
                ])
                ->required()
                ->native(false)
                // kondisi "Dipinjam" seharusnya cuma jadi konsekuensi otomatis dari
                // proses pinjam, bukan dipilih manual, supaya tidak bentrok dgn stok
                ->disabled(fn (string $operation) => $operation === 'edit')
                ->dehydrated(),

            Forms\Components\FileUpload::make('foto')
                ->label('Foto')
                ->image()
                ->directory('foto-barang')
                ->imageEditor(),

            Forms\Components\Textarea::make('keterangan')
                ->label('Keterangan')
                ->rows(3)
                ->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('foto')
                    ->label('Foto')
                    ->circular(),

                Tables\Columns\TextColumn::make('id')
                    ->label('Kode')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('nama_barang')
                    ->label('Nama Barang')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('jenis_barang')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Wahana'    => 'primary',
                        'Peralatan' => 'warning',
                        'Komponen'  => 'success',
                        default     => 'gray',
                    }),

                Tables\Columns\TextColumn::make('jumlah')
                    ->label('Stok')
                    ->sortable()
                    ->suffix(fn (Barang $record) => ' ' . $record->satuan),

                Tables\Columns\TextColumn::make('kondisi')
                    ->label('Kondisi')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Baik'     => 'success',
                        'Rusak'    => 'danger',
                        'Dipinjam' => 'warning',
                        default    => 'gray',
                    }),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Terakhir Diubah')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('jenis_barang')
                    ->label('Jenis Barang')
                    ->options([
                        'Wahana'    => 'Wahana',
                        'Peralatan' => 'Peralatan',
                        'Komponen'  => 'Komponen',
                    ]),

                Tables\Filters\SelectFilter::make('kondisi')
                    ->label('Kondisi')
                    ->options([
                        'Baik'     => 'Baik',
                        'Rusak'    => 'Rusak',
                        'Dipinjam' => 'Dipinjam',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->disabled(fn (Barang $record) => $record->peminjamanAktif()->exists())
                    ->tooltip(fn (Barang $record) => $record->peminjamanAktif()->exists()
                        ? 'Tidak dapat dihapus: barang sedang dipinjam'
                        : 'Hapus barang'
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Barang')
                    ->modalDescription(fn (Barang $record) => "Yakin ingin menghapus **{$record->nama_barang}**? Seluruh histori peminjaman terkait akan ikut terhapus jika tidak ada constraint aktif.")
                    ->modalSubmitActionLabel('Ya, Hapus'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                        $aktif   = $records->filter(fn (Barang $b) => $b->peminjamanAktif()->exists());
                        $hapusable = $records->diff($aktif);

                        foreach ($hapusable as $barang) {
                            $barang->delete();
                        }

                        if ($aktif->isNotEmpty()) {
                            Notification::make()
                                ->title('Sebagian barang tidak bisa dihapus')
                                ->body($aktif->count() . ' barang masih aktif dipinjam dan dilewati.')
                                ->warning()
                                ->send();
                        }

                        if ($hapusable->isNotEmpty()) {
                            Notification::make()
                                ->title($hapusable->count() . ' barang berhasil dihapus.')
                                ->success()
                                ->send();
                        }
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListBarang::route('/'),
            'create' => Pages\CreateBarang::route('/create'),
            'edit'   => Pages\EditBarang::route('/{record}/edit'),
        ];
    }

    // Barang yang masih punya peminjaman aktif tidak boleh dihapus.
    // Ini adalah lapisan kedua di atas foreign key restrictOnDelete di DB.
    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return ! $record->peminjamanAktif()->exists();
    }
}
