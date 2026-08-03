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
        Schema::create('kmp_edit_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kmp_id')->constrained('kmps')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // The user who requested the edit
            $table->json('requested_data'); // The payload of changed fields
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kmp_edit_requests');
    }
};
