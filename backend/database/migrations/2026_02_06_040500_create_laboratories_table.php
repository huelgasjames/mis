<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laboratories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Lab name (e.g., Laboratory 1, Computer Lab A)
            $table->string('code')->unique(); // Lab code (e.g., LAB-001, CLAB-A)
            $table->text('description')->nullable();
            $table->string('location')->nullable(); // Physical location
            $table->integer('capacity')->default(30); // Maximum PC capacity
            $table->string('supervisor')->nullable(); // Lab supervisor name
            $table->string('contact_number')->nullable();
            $table->enum('status', ['Active', 'Maintenance', 'Closed'])->default('Active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laboratories');
    }
};
