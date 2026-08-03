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
        Schema::table('blood_stocks', function (Blueprint $table) {
            $table->string('component')->default('Whole Blood (WB)')->after('id');
            $table->dropUnique(['blood_type']);
            $table->unique(['component', 'blood_type']);
        });

        Schema::table('blood_stock_logs', function (Blueprint $table) {
            $table->string('component')->default('Whole Blood (WB)')->after('id');
            $table->text('description')->nullable()->after('delta');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blood_stocks', function (Blueprint $table) {
            $table->dropUnique(['component', 'blood_type']);
            $table->unique('blood_type');
            $table->dropColumn('component');
        });

        Schema::table('blood_stock_logs', function (Blueprint $table) {
            $table->dropColumn(['component', 'description']);
        });
    }
};
