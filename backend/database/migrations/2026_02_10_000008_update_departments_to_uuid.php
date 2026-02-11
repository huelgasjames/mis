<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            // Add UUID column if it doesn't exist
            if (!Schema::hasColumn('departments', 'uuid')) {
                $table->uuid('uuid')->unique()->after('id')->nullable();
            }
            
            // Add index for UUID if it doesn't exist
            if (!Schema::hasIndex('departments', 'departments_uuid_index')) {
                $table->index('uuid');
            }
        });

        // Populate UUID values for existing records
        \DB::statement('UPDATE departments SET uuid = UUID() WHERE uuid IS NULL');

        // Update foreign key references in department_inventory table
        if (Schema::hasTable('department_inventory') && 
            Schema::hasColumn('departments', 'uuid')) {
            
            // Check if department_id column exists before proceeding
            $hasDepartmentId = Schema::hasColumn('department_inventory', 'department_id');
            
            if ($hasDepartmentId) {
                // First, add a temporary UUID column to department_inventory
                if (!Schema::hasColumn('department_inventory', 'department_uuid')) {
                    Schema::table('department_inventory', function (Blueprint $table) {
                        $table->uuid('department_uuid')->nullable()->after('department_id');
                    });
                }

                // Populate the UUID column with department UUIDs
                \DB::statement('UPDATE department_inventory di JOIN departments d ON di.department_id = d.id SET di.department_uuid = d.uuid');

                // Drop the old foreign key constraint
                $this->dropForeignKeyIfExists('department_inventory', ['department_id']);

                // Drop the old department_id column
                Schema::table('department_inventory', function (Blueprint $table) {
                    $table->dropColumn('department_id');
                });

                // Rename the UUID column to department_id
                Schema::table('department_inventory', function (Blueprint $table) {
                    $table->renameColumn('department_uuid', 'department_id');
                });

                // Make the new department_id column not nullable
                Schema::table('department_inventory', function (Blueprint $table) {
                    $table->uuid('department_id')->nullable(false)->change();
                });

                // Add new foreign key constraint
                Schema::table('department_inventory', function (Blueprint $table) {
                    $table->foreign('department_id')->references('uuid')->on('departments')->onDelete('cascade');
                });
            }
        }
    }

    public function down(): void
    {
        // Revert the foreign key changes
        if (Schema::hasTable('department_inventory')) {
            // Drop foreign key if it exists
            $this->dropForeignKeyIfExists('department_inventory', ['department_id']);

            // Add back the old integer department_id column
            if (!Schema::hasColumn('department_inventory', 'old_department_id')) {
                Schema::table('department_inventory', function (Blueprint $table) {
                    $table->unsignedBigInteger('old_department_id')->nullable()->after('uuid');
                });
            }

            // Since we can't easily revert the data without the original IDs, 
            // we'll just create a placeholder structure
            Schema::table('department_inventory', function (Blueprint $table) {
                $table->dropColumn('department_id');
                $table->renameColumn('old_department_id', 'department_id');
            });

            // Add back the foreign key constraint if possible
            try {
                Schema::table('department_inventory', function (Blueprint $table) {
                    $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
                });
            } catch (\Exception $e) {
                // Foreign key creation failed, but continue
            }
        }

        // Drop the UUID column from departments
        Schema::table('departments', function (Blueprint $table) {
            if (Schema::hasIndex('departments', 'departments_uuid_index')) {
                $table->dropIndex(['uuid']);
            }
            if (Schema::hasColumn('departments', 'uuid')) {
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
