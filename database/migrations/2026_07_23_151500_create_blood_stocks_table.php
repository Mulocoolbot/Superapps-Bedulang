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
        Schema::create('blood_stocks', function (Blueprint $table) {
            $table->id();
            $table->string('blood_type')->unique(); // e.g. 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'
            $table->integer('amount')->default(0);
            $table->string('unit')->default('Kantong');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blood_stocks');
    }
};
