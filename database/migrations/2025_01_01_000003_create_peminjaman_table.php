<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peminjaman', function (Blueprint $table) {
            $table->id();
            $table->string('id_barang');
            $table->string('npm');
            $table->integer('jumlah');
            $table->date('tanggal_pinjam');
            $table->date('tanggal_kembali')->nullable(); // null = masih dipinjam
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->foreign('id_barang')
                ->references('id')->on('barang')
                ->cascadeOnUpdate()
                ->restrictOnDelete(); // barang tidak boleh dihapus kalau masih ada histori peminjaman

            $table->foreign('npm')
                ->references('npm')->on('anggota')
                ->cascadeOnUpdate()
                ->restrictOnDelete(); // sama untuk anggota
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peminjaman');
    }
};
