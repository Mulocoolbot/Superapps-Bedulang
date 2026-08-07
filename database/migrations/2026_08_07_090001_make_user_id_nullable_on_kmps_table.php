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
            // Make user_id nullable since KMPs can be imported without an assigned admin user
            $table->foreignId('user_id')->nullable()->change();
            // Add import_log_id to track which import batch this KMP came from
            $table->foreignId('import_log_id')->nullable()->after('user_id')->constrained('kmp_import_logs')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kmps', function (Blueprint $table) {
            $table->dropForeign(['import_log_id']);
            $table->dropColumn('import_log_id');
            $table->foreignId('user_id')->nullable(false)->change();
        });
    }
};
