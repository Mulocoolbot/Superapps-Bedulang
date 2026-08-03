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
        Schema::create('health_units', function (Blueprint $table) {
            $table->id();
            $table->string('unit_no')->nullable();
            $table->string('nama');
            $table->string('jenis')->default('Puskesmas'); // 'Puskesmas', 'Rumah Sakit Daerah'
            $table->text('alamat');
            $table->string('desa')->nullable();
            $table->string('kecamatan')->nullable();
            $table->string('wilayah')->default('Kabupaten Bangka Tengah');
            $table->string('kode_pos')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('health_units');
    }
};
