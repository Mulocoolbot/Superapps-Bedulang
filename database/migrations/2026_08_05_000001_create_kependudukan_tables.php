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
        Schema::create('kependudukan_import_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('category_key'); // kepala_keluarga, agama, golongan_darah, jenis_kelamin, kelompok_umur, pekerjaan, pendidikan, status_perkawinan
            $table->string('category_name');
            $table->integer('tahun');
            $table->integer('semester'); // 1 or 2
            $table->string('file_name');
            $table->integer('total_rows')->default(0);
            $table->string('status')->default('success');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('kependudukan_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_log_id')->nullable()->constrained('kependudukan_import_logs')->onDelete('cascade');
            $table->integer('tahun');
            $table->integer('semester');
            $table->string('category_key');
            $table->string('kecamatan');
            $table->string('desa_kelurahan');
            $table->json('payload'); // Stores category specific breakdown columns
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kependudukan_records');
        Schema::dropIfExists('kependudukan_import_logs');
    }
};
