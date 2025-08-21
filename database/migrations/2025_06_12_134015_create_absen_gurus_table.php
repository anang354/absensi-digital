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
        Schema::create('absen_gurus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_id')->references('id')->on('gurus')->cascadeOnDelete();
            $table->date('tanggal_presensi')->nullable();
            $table->time('checkin')->nullable();
            $table->time('checkout')->nullable();
            $table->string('lokasi_in')->nullable();
            $table->string('lokasi_out')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absen_gurus');
    }
};
