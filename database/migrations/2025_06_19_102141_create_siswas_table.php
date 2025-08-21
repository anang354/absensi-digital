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
        Schema::create('siswas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelas_id')->references('id')->on('kelas')->constrained();
            $table->foreignId('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->string('nama');
            $table->string('jenis_kelamin');
            $table->string('nisn');
            $table->string('nomor_hp');
            $table->text('alamat')->nullable();
            $table->string('wali')->nullable();
            $table->string('foto')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('siswas');
    }
};
