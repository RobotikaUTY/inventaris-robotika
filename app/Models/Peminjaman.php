<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class Peminjaman extends Model
{
    use HasFactory;

    protected $table = 'peminjaman';

    protected $fillable = [
        'id_barang',
        'npm',
        'jumlah',
        'tanggal_pinjam',
        'tanggal_kembali',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_pinjam'  => 'date',
        'tanggal_kembali' => 'date',
    ];

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'id_barang', 'id');
    }

    public function anggota(): BelongsTo
    {
        return $this->belongsTo(Anggota::class, 'npm', 'npm');
    }

    // Accessor kecil buat kolom status di table Filament
    public function getStatusAttribute(): string
    {
        return $this->tanggal_kembali ? 'Sudah Kembali' : 'Masih Dipinjam';
    }

    protected static function booted(): void
    {
        // SAAT PEMINJAMAN BARU DIBUAT ("Pinjamkan")
        // -> cek stok cukup, lalu stok barang berkurang sejumlah yang dipinjam
        static::creating(function (Peminjaman $peminjaman) {
            $barang = Barang::whereKey($peminjaman->id_barang)->lockForUpdate()->first();

            if (! $barang) {
                throw ValidationException::withMessages([
                    'id_barang' => "Barang dengan ID {$peminjaman->id_barang} tidak ditemukan.",
                ]);
            }

            if ($peminjaman->jumlah > $barang->jumlah) {
                throw ValidationException::withMessages([
                    'jumlah' => "Stok tidak cukup. Tersedia: {$barang->jumlah} {$barang->satuan}.",
                ]);
            }
        });

        static::created(function (Peminjaman $peminjaman) {
            $barang = Barang::whereKey($peminjaman->id_barang)->lockForUpdate()->first();

            $barang->decrement('jumlah', $peminjaman->jumlah);

            if ($barang->jumlah <= 0) {
                $barang->update(['kondisi' => 'Dipinjam']);
            }
        });

        // SAAT tanggal_kembali DIISI ("Kembalikan")
        // -> stok barang bertambah kembali
        // Hanya jalan sekali: saat tanggal_kembali berubah dari null -> ada isi
        static::updated(function (Peminjaman $peminjaman) {
            if ($peminjaman->wasChanged('tanggal_kembali') && $peminjaman->tanggal_kembali !== null) {
                $barang = Barang::whereKey($peminjaman->id_barang)->lockForUpdate()->first();

                // Gunakan getOriginal('jumlah') bukan $peminjaman->jumlah
                // agar selalu menggunakan jumlah yang asli dipinjam,
                // bukan nilai yang mungkin berubah di form edit.
                $jumlahKembali = $peminjaman->getOriginal('jumlah');

                $barang->increment('jumlah', $jumlahKembali);

                if ($barang->jumlah > 0 && $barang->kondisi === 'Dipinjam') {
                    $barang->update(['kondisi' => 'Baik']);
                }
            }
        });
    }
}
