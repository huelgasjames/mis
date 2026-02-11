<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add role and last_activity_at to users table
        if (!Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->enum('role', ['admin', 'manager', 'supervisor', 'technician', 'staff'])
                      ->default('staff')
                      ->after('email');
            });
        }
        
        if (!Schema::hasColumn('users', 'last_activity_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->timestamp('last_activity_at')->nullable()->after('remember_token');
            });
        }

        // Update existing users with roles based on email patterns
        \DB::table('users')->update([
            'role' => \DB::raw("CASE 
                WHEN email LIKE '%admin%' THEN 'admin'
                WHEN email LIKE '%labhead%' OR email LIKE '%research%' THEN 'manager'
                WHEN email LIKE '%techlead%' OR email LIKE '%security%' THEN 'supervisor'
                WHEN email LIKE '%sysadmin%' OR email LIKE '%netadmin%' OR email LIKE '%dbadmin%' OR email LIKE '%support%' THEN 'technician'
                WHEN email LIKE '%helpdesk%' OR email LIKE '%training%' THEN 'staff'
                ELSE 'staff'
            END")
        ]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'last_activity_at']);
        });
    }
};
