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
        // Check if pc_num column exists before adding it
        if (!Schema::hasColumn('department_inventory', 'pc_num')) {
            Schema::table('department_inventory', function (Blueprint $table) {
                $table->string('pc_num')->after('department_id'); // PC number within department (e.g., MISD-001, MISD-002)
                
                // Add unique constraint for pc_num within each department
                $table->unique(['department_id', 'pc_num']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('department_inventory', function (Blueprint $table) {
            $table->dropUnique(['department_id', 'pc_num']);
            $table->dropColumn('pc_num');
        });
    }
};
