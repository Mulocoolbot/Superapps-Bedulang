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
        Schema::create('kmps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('kecamatan');
            $table->string('desa_kelurahan');
            $table->enum('jenis', ['Desa', 'Kelurahan']);
            $table->string('nama_koperasi');
            $table->string('nomor_induk')->nullable();
            $table->string('no_ahu')->nullable();
            $table->date('tanggal_ahu')->nullable();
            $table->text('alamat')->nullable();
            $table->string('telp')->nullable();
            $table->boolean('status_operasional')->default(false);
            $table->enum('status_sertifikat', ['pending', 'verified', 'rejected'])->default('pending');
            $table->boolean('status_pelatihan')->default(false);
            $table->string('file_sertifikat')->nullable();
            $table->enum('status_pembentukan', ['pending_verification', 'verified', 'rejected'])->default('pending_verification');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kmps');
    }
};
