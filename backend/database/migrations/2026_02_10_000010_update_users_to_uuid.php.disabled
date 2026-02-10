<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add UUID column if it doesn't exist
            if (!Schema::hasColumn('users', 'uuid')) {
                $table->uuid('uuid')->unique()->after('id')->nullable();
            }
            
            // Add index for UUID if it doesn't exist
            if (!Schema::hasIndex('users', 'users_uuid_index')) {
                $table->index('uuid');
            }
        });

        // Populate UUID values for existing records
        \DB::statement('UPDATE users SET uuid = UUID() WHERE uuid IS NULL');

        // Update audit_logs table to use UUID for user_id if it exists
        if (Schema::hasTable('audit_logs') && 
            Schema::hasColumn('audit_logs', 'user_id') && 
            Schema::hasColumn('users', 'uuid')) {
            
            // First, add a temporary UUID column to audit_logs
            if (!Schema::hasColumn('audit_logs', 'user_uuid')) {
                Schema::table('audit_logs', function (Blueprint $table) {
                    $table->uuid('user_uuid')->nullable()->after('user_id');
                });
            }

            // Update the user_uuid column with the corresponding UUID from users
            \DB::statement('
                UPDATE audit_logs al 
                JOIN users u ON al.user_id = u.id 
                SET al.user_uuid = u.uuid
                WHERE al.user_uuid IS NULL AND al.user_id IS NOT NULL
            ');

            // Drop foreign key constraints safely - check if they exist first
            $this->dropForeignKeyIfExists('audit_logs', ['user_id']);

            // Drop the old user_id column
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->dropColumn('user_id');
            });

            // Rename the UUID column to user_id
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->renameColumn('user_uuid', 'user_id');
            });

            // Add the new foreign key constraint
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->foreign('user_id')->references('uuid')->on('users')->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        // Revert the audit_logs changes
        if (Schema::hasTable('audit_logs')) {
            // Drop foreign key if it exists
            $this->dropForeignKeyIfExists('audit_logs', ['user_id']);

            // Add back the old integer user_id column
            if (!Schema::hasColumn('audit_logs', 'old_user_id')) {
                Schema::table('audit_logs', function (Blueprint $table) {
                    $table->unsignedBigInteger('old_user_id')->nullable()->after('auditable_id');
                });
            }

            // Since we can't easily revert the data without the original IDs, 
            // we'll just create a placeholder structure
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->dropColumn('user_id');
                $table->renameColumn('old_user_id', 'user_id');
            });

            // Add back the foreign key constraint if possible
            try {
                Schema::table('audit_logs', function (Blueprint $table) {
                    $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
                });
            } catch (\Exception $e) {
                // Foreign key creation failed, but continue
            }
        }

        // Drop the UUID column from users
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasIndex('users', 'users_uuid_index')) {
                $table->dropIndex(['uuid']);
            }
            if (Schema::hasColumn('users', 'uuid')) {
                $table->dropColumn('uuid');
            }
        });
    }

    /**
     * Drop foreign key if it exists
     */
    private function dropForeignKeyIfExists(string $table, array $columns): void
    {
        try {
            // Get all foreign keys for the table
            $foreignKeys = \DB::select("
                SELECT CONSTRAINT_NAME 
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = ? 
                AND COLUMN_NAME IN (?) 
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ", [$table, implode(',', $columns)]);

            foreach ($foreignKeys as $foreignKey) {
                Schema::table($table, function (Blueprint $table) use ($foreignKey) {
                    $table->dropForeign($foreignKey->CONSTRAINT_NAME);
                });
            }
        } catch (\Exception $e) {
            // If we can't check for foreign keys, try to drop it directly
            try {
                Schema::table($table, function (Blueprint $table) use ($columns) {
                    $table->dropForeign($columns);
                });
            } catch (\Exception $e2) {
                // Foreign key doesn't exist, continue
            }
        }
    }
};
