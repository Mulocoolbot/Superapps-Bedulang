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
        Schema::create('koperasis', function (Blueprint $table) {
            $table->id();
            $table->string('nama_koperasi');
            $table->string('kecamatan');
            $table->string('desa_kelurahan');
            $table->integer('tahun');
            $table->foreignId('import_log_id')->nullable()->constrained('koperasi_import_logs')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['kecamatan', 'desa_kelurahan']);
            $table->index('tahun');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('koperasis');
    }
};
