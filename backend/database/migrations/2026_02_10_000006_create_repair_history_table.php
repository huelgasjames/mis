<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('repair_history', function (Blueprint $table) {
            $table->id();
            $table->uuid('inventory_uuid'); // Foreign key to laboratory_inventory.uuid
            $table->string('repair_type'); // Hardware, Software, Network, etc.
            $table->text('issue_description');
            $table->text('resolution_description')->nullable();
            $table->string('technician');
            $table->decimal('cost', 10, 2)->nullable();
            $table->datetime('start_date');
            $table->datetime('end_date')->nullable();
            $table->text('parts_used')->nullable();
            $table->text('warranty_info')->nullable();
            $table->enum('status', ['in_progress', 'completed', 'cancelled'])->default('in_progress');
            $table->timestamps();
            
            // Foreign key
            $table->foreign('inventory_uuid')->references('uuid')->on('laboratory_inventory')->onDelete('cascade');
            
            // Indexes
            $table->index(['inventory_uuid', 'start_date']);
            $table->index('repair_type');
            $table->index('technician');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('repair_history');
    }
};
