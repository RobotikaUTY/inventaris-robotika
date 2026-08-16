<?php

namespace App\Filament\Resources\PeminjamanResource\Pages;

use App\Filament\Resources\PeminjamanResource;
use App\Models\Peminjaman;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditPeminjaman extends EditRecord
{
    protected static string $resource = PeminjamanResource::class;

    /**
     * Cegah edit transaksi yang sudah selesai (tanggal_kembali sudah diisi).
     * Kalau user akses URL edit secara langsung pun tetap terblokir.
     */
    protected function authorizeAccess(): void
    {
        parent::authorizeAccess();

        /** @var Peminjaman $record */
        $record = $this->getRecord();

        if ($record->tanggal_kembali !== null) {
            Notification::make()
                ->title('Tidak dapat diedit')
                ->body('Transaksi ini sudah selesai dan tidak bisa diubah.')
                ->warning()
                ->send();

            $this->redirect(PeminjamanResource::getUrl('index'));
        }
    }
}
