<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AnggotaResource\Pages;
use App\Models\Anggota;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AnggotaResource extends Resource
{
    protected static ?string $model = Anggota::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Anggota';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $modelLabel = 'Anggota';
    protected static ?string $pluralModelLabel = 'Anggota';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('npm')
                ->label('NPM')
                ->required()
                ->unique(ignoreRecord: true)
                // NPM adalah primary key -> tidak boleh diubah setelah dibuat
                ->disabled(fn (string $operation) => $operation === 'edit')
                ->dehydrated()
                ->maxLength(20),

            Forms\Components\TextInput::make('nama')
                ->label('Nama')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('angkatan')
                ->label('Angkatan')
                ->numeric()
                ->minValue(2000)
                ->maxValue((int) date('Y'))
                ->required(),

            Forms\Components\TextInput::make('jurusan')
                ->label('Jurusan')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('divisi')
                ->label('Divisi')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('no_telepon')
                ->label('No Telepon')
                ->tel()
                ->required()
                ->maxLength(20),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('npm')
                    ->label('NPM')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('angkatan')
                    ->label('Angkatan')
                    ->sortable(),

                Tables\Columns\TextColumn::make('jurusan')
                    ->label('Jurusan')
                    ->searchable(),

                Tables\Columns\TextColumn::make('divisi')
                    ->label('Divisi')
                    ->badge(),

                Tables\Columns\TextColumn::make('no_telepon')
                    ->label('No Telepon'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('angkatan')
                    ->label('Angkatan')
                    ->options(fn () => Anggota::query()
                        ->distinct()
                        ->orderByDesc('angkatan')
                        ->pluck('angkatan', 'angkatan')
                        ->toArray()),

                Tables\Filters\SelectFilter::make('divisi')
                    ->label('Divisi')
                    ->options(fn () => Anggota::query()
                        ->distinct()
                        ->pluck('divisi', 'divisi')
                        ->toArray()),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAnggota::route('/'),
            'create' => Pages\CreateAnggota::route('/create'),
            'edit'   => Pages\EditAnggota::route('/{record}/edit'),
        ];
    }
}
