<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_records', function (Blueprint $table) {
            $table->id();
            $table->uuid('inventory_uuid'); // Foreign key to laboratory_inventory.uuid
            $table->string('maintenance_type'); // Preventive, Corrective, Emergency
            $table->text('description');
            $table->string('performed_by');
            $table->decimal('cost', 10, 2)->nullable();
            $table->date('maintenance_date');
            $table->date('next_maintenance_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Foreign key
            $table->foreign('inventory_uuid')->references('uuid')->on('laboratory_inventory')->onDelete('cascade');
            
            // Indexes
            $table->index(['inventory_uuid', 'maintenance_date']);
            $table->index('maintenance_type');
            $table->index('next_maintenance_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_records');
    }
};
