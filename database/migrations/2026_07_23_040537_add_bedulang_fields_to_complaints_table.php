<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->string('followup_image')->nullable()->after('notes');
            $table->timestamp('followup_image_uploaded_at')->nullable()->after('followup_image');
            $table->tinyInteger('rating')->nullable()->after('followup_image_uploaded_at');
            $table->text('review')->nullable()->after('rating');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->dropColumn(['followup_image', 'followup_image_uploaded_at', 'rating', 'review']);
        });
    }
};
