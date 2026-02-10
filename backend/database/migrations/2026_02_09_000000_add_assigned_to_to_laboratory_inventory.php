<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Check if assigned_to column exists before adding it
        if (!Schema::hasColumn('laboratory_inventory', 'assigned_to')) {
            Schema::table('laboratory_inventory', function (Blueprint $table) {
                $table->string('assigned_to')->nullable()->after('status');
            });
        }
    }

    public function down(): void
    {
        Schema::table('laboratory_inventory', function (Blueprint $table) {
            $table->dropColumn('assigned_to');
        });
    }
};
