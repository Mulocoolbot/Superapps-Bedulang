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
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('service_slug'); // pemerintahan, pendidikan, kesehatan, etc
            $table->string('title');
            $table->text('description');
            $table->string('category')->default('umum'); // umum, layanan, lainnya
            $table->string('contact_person_name');
            $table->string('contact_email');
            $table->string('contact_phone');
            $table->enum('status', ['pending', 'processing', 'resolved', 'rejected'])->default('pending');
            $table->string('whatsapp_phone')->nullable();
            $table->timestamp('whatsapp_sent_at')->nullable();
            $table->string('whatsapp_message_id')->nullable();
            $table->text('notes')->nullable(); // untuk internal notes
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
