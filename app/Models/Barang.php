<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Barang extends Model
{
    use HasFactory;

    protected $table = 'barang';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'nama_barang',
        'jenis_barang',
        'jumlah',
        'satuan',
        'foto',
        'kondisi',
        'keterangan',
    ];

    // Prefix kode per jenis barang. Tambah/ubah di sini kalau jenis barang berkembang.
    protected static array $prefixJenis = [
        'Wahana'    => 'WHN',
        'Peralatan' => 'PRL',
        'Komponen'  => 'KMP',
    ];

    protected static function boot(): void
    {
        parent::boot();

        // Auto-generate id readable (WHN-0001, dst) saat barang baru dibuat,
        // KECUALI id sudah diisi manual sebelumnya (jaga-jaga).
        static::creating(function (Barang $barang) {
            if (! empty($barang->id)) {
                return;
            }

            $prefix = static::$prefixJenis[$barang->jenis_barang] ?? 'BRG';

            // Cari nomor urut terakhir untuk prefix ini, lalu tambah 1.
            // lockForUpdate() mencegah dua barang dapat nomor sama kalau
            // ada input bersamaan (race condition).
            $last = static::where('id', 'like', $prefix . '-%')
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

            // Ambil 5 digit terakhir agar support hingga 99999 item per jenis.
            $nextNumber = $last
                ? ((int) substr($last->id, strrpos($last->id, '-') + 1)) + 1
                : 1;

            if ($nextNumber > 99999) {
                throw new \OverflowException("Nomor urut barang jenis '{$barang->jenis_barang}' telah mencapai batas maksimal (99999).");
            }

            $barang->id = $prefix . '-' . str_pad((string) $nextNumber, 5, '0', STR_PAD_LEFT);
        });
    }

    public function peminjaman(): HasMany
    {
        return $this->hasMany(Peminjaman::class, 'id_barang', 'id');
    }

    // Helper: apakah barang ini sedang ada peminjaman aktif (belum dikembalikan)
    public function peminjamanAktif(): HasMany
    {
        return $this->peminjaman()->whereNull('tanggal_kembali');
    }
}
