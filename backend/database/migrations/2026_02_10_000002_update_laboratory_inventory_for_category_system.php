<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laboratory_inventory', function (Blueprint $table) {
            // Add UUID column if it doesn't exist
            if (!Schema::hasColumn('laboratory_inventory', 'uuid')) {
                $table->uuid('uuid')->unique()->after('id')->nullable();
            }
            
            // Add category_id foreign key if it doesn't exist
            if (!Schema::hasColumn('laboratory_inventory', 'category_id')) {
                $table->uuid('category_id')->nullable()->after('category');
            }
            
            // Add indexes for UUID if they don't exist
            $table->index('uuid');
        });

        // Add foreign key constraint if it doesn't exist
        if (!Schema::hasColumn('laboratory_inventory', 'category_id')) {
            Schema::table('laboratory_inventory', function (Blueprint $table) {
                $table->foreign('category_id')->references('id')->on('categories')->onDelete('restrict');
            });
        }

        // Populate UUID values for existing records
        \DB::statement('UPDATE laboratory_inventory SET uuid = UUID() WHERE uuid IS NULL');

        // Create a mapping for existing categories to new category IDs
        // This will be handled by the seeder
    }

    public function down(): void
    {
        Schema::table('laboratory_inventory', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropIndex(['uuid']);
            $table->dropColumn(['uuid', 'category_id']);
        });
    }
};
