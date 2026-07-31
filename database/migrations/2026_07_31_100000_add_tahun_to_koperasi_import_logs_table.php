<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('koperasi_import_logs') && !Schema::hasColumn('koperasi_import_logs', 'tahun')) {
            Schema::table('koperasi_import_logs', function (Blueprint $table) {
                $table->integer('tahun')->nullable()->after('file_name');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('koperasi_import_logs', 'tahun')) {
            Schema::table('koperasi_import_logs', function (Blueprint $table) {
                $table->dropColumn('tahun');
            });
        }
    }
};
