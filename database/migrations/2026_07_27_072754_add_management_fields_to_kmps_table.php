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
        Schema::table('kmps', function (Blueprint $table) {
            $table->integer('jumlah_anggota')->default(0)->after('telp');
            $table->integer('jumlah_pengawas')->default(0)->after('jumlah_anggota');
            $table->integer('jumlah_pengurus')->default(0)->after('jumlah_pengawas');
            $table->string('ketua_nama')->nullable()->after('jumlah_pengurus');
            $table->string('ketua_foto')->nullable()->after('ketua_nama');
            $table->string('sekretaris_nama')->nullable()->after('ketua_foto');
            $table->string('sekretaris_foto')->nullable()->after('sekretaris_nama');
            $table->string('bendahara_nama')->nullable()->after('sekretaris_foto');
            $table->string('bendahara_foto')->nullable()->after('bendahara_nama');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kmps', function (Blueprint $table) {
            $table->dropColumn([
                'jumlah_anggota',
                'jumlah_pengawas',
                'jumlah_pengurus',
                'ketua_nama',
                'ketua_foto',
                'sekretaris_nama',
                'sekretaris_foto',
                'bendahara_nama',
                'bendahara_foto'
            ]);
        });
    }
};
