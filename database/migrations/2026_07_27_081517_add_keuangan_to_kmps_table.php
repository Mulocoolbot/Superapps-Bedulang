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
            $table->unsignedBigInteger('simpanan_pokok')->default(0)->after('bendahara_foto');
            $table->unsignedBigInteger('simpanan_wajib')->default(0)->after('simpanan_pokok');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kmps', function (Blueprint $table) {
            $table->dropColumn(['simpanan_pokok', 'simpanan_wajib']);
        });
    }
};
