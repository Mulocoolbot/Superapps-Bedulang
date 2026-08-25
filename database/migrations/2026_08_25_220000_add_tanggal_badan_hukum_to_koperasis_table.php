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
        if (Schema::hasTable('koperasis') && !Schema::hasColumn('koperasis', 'tanggal_badan_hukum')) {
            Schema::table('koperasis', function (Blueprint $table) {
                $table->string('tanggal_badan_hukum')->nullable()->after('nama_koperasi');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('koperasis') && Schema::hasColumn('koperasis', 'tanggal_badan_hukum')) {
            Schema::table('koperasis', function (Blueprint $table) {
                $table->dropColumn('tanggal_badan_hukum');
            });
        }
    }
};
