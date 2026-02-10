<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laboratories', function (Blueprint $table) {
            // Add UUID column if it doesn't exist
            if (!Schema::hasColumn('laboratories', 'uuid')) {
                $table->uuid('uuid')->unique()->after('id')->nullable();
            }
            
            // Add index for UUID if it doesn't exist
            if (!Schema::hasIndex('laboratories', 'laboratories_uuid_index')) {
                $table->index('uuid');
            }
        });

        // Populate UUID values for existing records
        \DB::statement('UPDATE laboratories SET uuid = UUID() WHERE uuid IS NULL');

        // Update foreign key references in laboratory_inventory table
        if (Schema::hasTable('laboratory_inventory') && 
            Schema::hasColumn('laboratory_inventory', 'laboratory_id') && 
            Schema::hasColumn('laboratories', 'uuid')) {
            
            // First, add a temporary UUID column to laboratory_inventory
            if (!Schema::hasColumn('laboratory_inventory', 'laboratory_uuid')) {
                Schema::table('laboratory_inventory', function (Blueprint $table) {
                    $table->uuid('laboratory_uuid')->nullable()->after('laboratory_id');
                });
            }

            // Update the laboratory_uuid column with the corresponding UUID from laboratories
            \DB::statement('
                UPDATE laboratory_inventory li 
                JOIN laboratories l ON li.laboratory_id = l.id 
                SET li.laboratory_uuid = l.uuid
                WHERE li.laboratory_uuid IS NULL
            ');

            // Drop foreign key constraints safely - check if they exist first
            $this->dropForeignKeyIfExists('laboratory_inventory', ['laboratory_id']);

            // Drop the old laboratory_id column
            Schema::table('laboratory_inventory', function (Blueprint $table) {
                $table->dropColumn('laboratory_id');
            });

            // Rename the UUID column to laboratory_id
            Schema::table('laboratory_inventory', function (Blueprint $table) {
                $table->renameColumn('laboratory_uuid', 'laboratory_id');
            });

            // Add the new foreign key constraint
            Schema::table('laboratory_inventory', function (Blueprint $table) {
                $table->foreign('laboratory_id')->references('uuid')->on('laboratories')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        // Revert the foreign key changes
        if (Schema::hasTable('laboratory_inventory')) {
            // Drop foreign key if it exists
            $this->dropForeignKeyIfExists('laboratory_inventory', ['laboratory_id']);

            // Add back the old integer laboratory_id column
            if (!Schema::hasColumn('laboratory_inventory', 'old_laboratory_id')) {
                Schema::table('laboratory_inventory', function (Blueprint $table) {
                    $table->unsignedBigInteger('old_laboratory_id')->nullable()->after('uuid');
                });
            }

            // Since we can't easily revert the data without the original IDs, 
            // we'll just create a placeholder structure
            Schema::table('laboratory_inventory', function (Blueprint $table) {
                $table->dropColumn('laboratory_id');
                $table->renameColumn('old_laboratory_id', 'laboratory_id');
            });

            // Add back the foreign key constraint if possible
            try {
                Schema::table('laboratory_inventory', function (Blueprint $table) {
                    $table->foreign('laboratory_id')->references('id')->on('laboratories')->onDelete('cascade');
                });
            } catch (\Exception $e) {
                // Foreign key creation failed, but continue
            }
        }

        // Drop the UUID column from laboratories
        Schema::table('laboratories', function (Blueprint $table) {
            if (Schema::hasIndex('laboratories', 'laboratories_uuid_index')) {
                $table->dropIndex(['uuid']);
            }
            if (Schema::hasColumn('laboratories', 'uuid')) {
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
