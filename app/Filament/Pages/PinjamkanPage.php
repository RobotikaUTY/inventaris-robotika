<?php

namespace App\Filament\Pages;

use App\Models\Anggota;
use App\Models\Barang;
use App\Models\Peminjaman;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Validation\ValidationException;

class PinjamkanPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon  = 'heroicon-o-arrow-up-tray';
    protected static ?string $navigationLabel = 'Peminjaman';
    protected static ?string $navigationGroup = 'Kelola Peminjaman';
    protected static ?int    $navigationSort  = 1;
    protected static ?string $title           = 'Peminjaman Barang';
    protected static string  $view            = 'filament.pages.pinjamkan-page';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'tanggal_pinjam' => now()->toDateString(),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Pilih Barang')
                    ->icon('heroicon-o-cube')
                    ->schema([
                        Forms\Components\Select::make('id_barang')
                            ->label('Barang')
                            ->options(
                                fn () => Barang::where('jumlah', '>', 0)
                                    ->get()
                                    ->mapWithKeys(fn (Barang $b) => [
                                        $b->id => "[{$b->id}] {$b->nama_barang} — Stok: {$b->jumlah} {$b->satuan}",
                                    ])
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('jumlah', null)),

                        Forms\Components\TextInput::make('jumlah')
                            ->label('Jumlah Dipinjam')
                            ->numeric()
                            ->minValue(1)
                            ->required()
                            ->suffix(
                                fn (Forms\Get $get): string =>
                                    ($b = Barang::find($get('id_barang')))
                                        ? $b->satuan . ' (maks. ' . $b->jumlah . ')'
                                        : ''
                            ),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Data Peminjam')
                    ->icon('heroicon-o-user')
                    ->schema([
                        Forms\Components\Select::make('npm')
                            ->label('Peminjam')
                            ->options(fn () => Anggota::query()->pluck('nama', 'npm'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('npm')
                                    ->label('NPM')
                                    ->required()
                                    ->unique('anggota', 'npm'),
                                Forms\Components\TextInput::make('nama')
                                    ->label('Nama Lengkap')
                                    ->required(),
                                Forms\Components\TextInput::make('angkatan')
                                    ->label('Angkatan')
                                    ->numeric()
                                    ->required(),
                                Forms\Components\TextInput::make('jurusan')
                                    ->label('Jurusan')
                                    ->required(),
                                Forms\Components\TextInput::make('divisi')
                                    ->label('Divisi')
                                    ->required(),
                                Forms\Components\TextInput::make('no_telepon')
                                    ->label('No. Telepon')
                                    ->required(),
                            ])
                            ->createOptionUsing(fn (array $data) => Anggota::create($data)->npm),
                    ]),

                Forms\Components\Section::make('Detail Peminjaman')
                    ->icon('heroicon-o-calendar-days')
                    ->schema([
                        Forms\Components\DatePicker::make('tanggal_pinjam')
                            ->label('Tanggal Pinjam')
                            ->required()
                            ->default(now()),

                        Forms\Components\Textarea::make('keterangan')
                            ->label('Keterangan')
                            ->placeholder('Catatan tambahan (opsional)…')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        $barang = Barang::find($data['id_barang']);

        if (! $barang || $data['jumlah'] > $barang->jumlah) {
            Notification::make()
                ->title('Stok tidak cukup')
                ->body("Stok tersedia: {$barang?->jumlah} {$barang?->satuan}.")
                ->danger()
                ->send();
            return;
        }

        Peminjaman::create([
            'id_barang'      => $data['id_barang'],
            'npm'            => $data['npm'],
            'jumlah'         => $data['jumlah'],
            'tanggal_pinjam' => $data['tanggal_pinjam'],
            'keterangan'     => $data['keterangan'] ?? null,
        ]);

        Notification::make()
            ->title('Berhasil!')
            ->body("Barang **{$barang->nama_barang}** berhasil dipinjamkan.")
            ->success()
            ->send();

        $this->form->fill([
            'tanggal_pinjam' => now()->toDateString(),
        ]);
    }
}
