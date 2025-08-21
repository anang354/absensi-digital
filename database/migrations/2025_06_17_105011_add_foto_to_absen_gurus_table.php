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
        Schema::table('absen_gurus', function (Blueprint $table) {
            $table->foreignId('semester_id')->references('id')->on('semesters')->cascadeOnDelete()->after('guru_id');
            $table->string('foto_in')->nullable()->after('lokasi_out');
            $table->string('foto_out')->nullable()->after('foto_in');
            $table->string('status')->nullable();
            $table->string('keterangan')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('absen_gurus', function (Blueprint $table) {
            $table->dropCplumn('semester_id');
            $table->dropCplumn('foto_in');
            $table->dropCplumn('foto_out');
        });
    }
};
