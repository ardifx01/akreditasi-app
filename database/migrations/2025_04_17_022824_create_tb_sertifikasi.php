<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection('mysql2')->create('tb_sertifikasi', function (Blueprint $table) {
            $table->id();
            $table->string('id_staf', 20);
            $table->string('judul_sertifikasis');
            $table->string('file_dokumen');
            $table->integer('tahun');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_sertifikasi');
    }
};
