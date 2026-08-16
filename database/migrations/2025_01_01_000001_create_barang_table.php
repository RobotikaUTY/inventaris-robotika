<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barang', function (Blueprint $table) {
            $table->string('id')->primary(); // contoh: WHN-0001, PRL-0002, KMP-0003
            $table->string('nama_barang');
            $table->enum('jenis_barang', ['Wahana', 'Peralatan', 'Komponen']);
            $table->integer('jumlah')->default(0);
            $table->string('satuan');
            $table->string('foto')->nullable();
            $table->enum('kondisi', ['Baik', 'Rusak', 'Dipinjam'])->default('Baik');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barang');
    }
};
