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
        Schema::create('health_services', function (Blueprint $table) {
            $table->id();
            $table->string('kode_layanan'); // PKM-001, RS-001
            $table->foreignId('health_unit_id')->constrained('health_units')->onDelete('cascade');
            $table->string('kelompok_utama')->nullable();
            $table->string('subkelompok')->nullable();
            $table->string('no_item')->nullable();
            $table->string('jenis_layanan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('health_services');
    }
};
