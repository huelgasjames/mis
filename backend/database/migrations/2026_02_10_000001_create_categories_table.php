<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->uuid('id')->primary(); // UUID for unique identification
            $table->string('code')->unique(); // Category code (e.g., 'DT', 'LT', 'SR', 'MN')
            $table->string('name'); // Category name (e.g., 'Desktop', 'Laptop', 'Server', 'Monitor')
            $table->text('description')->nullable(); // Category description
            $table->boolean('is_editable')->default(true); // Whether users can edit this category
            $table->boolean('is_active')->default(true); // Whether this category is active
            $table->integer('sort_order')->default(0); // For ordering categories
            $table->timestamps();
            
            // Indexes
            $table->index(['is_active', 'sort_order']);
            $table->index('code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
