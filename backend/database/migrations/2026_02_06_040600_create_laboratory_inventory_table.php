<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laboratory_inventory', function (Blueprint $table) {
            $table->id();
            $table->string('asset_tag')->unique(); // Asset tag for tracking
            $table->string('computer_name'); // Computer name
            $table->string('lab_pc_num'); // Lab-specific PC number (e.g., LAB1-001, LAB2-015)
            $table->string('category'); // Desktop, Laptop, Server, Monitor
            $table->string('processor'); // Processor type
            $table->string('motherboard')->nullable(); // Motherboard
            $table->string('video_card')->nullable(); // Video card
            $table->string('dvd_rom')->nullable(); // DVD ROM
            $table->string('psu')->nullable(); // Power supply unit
            $table->string('ram'); // RAM specification
            $table->string('storage'); // Storage specification
            $table->string('serial_number')->nullable(); // Serial number
            $table->enum('status', ['Deployed', 'Under Repair', 'Available', 'Defective', 'For Disposal']);
            $table->enum('condition', ['Excellent', 'Good', 'Fair', 'Poor']);
            $table->text('notes')->nullable(); // Additional notes about issues
            $table->foreignId('laboratory_id')->constrained()->onDelete('cascade');
            $table->date('deployment_date')->nullable(); // When deployed to lab
            $table->date('last_maintenance')->nullable(); // Last maintenance date
            $table->date('repair_start_date')->nullable(); // When repair started
            $table->date('repair_end_date')->nullable(); // When repair completed
            $table->string('repair_description')->nullable(); // Description of repair issue
            $table->string('repaired_by')->nullable(); // Who performed the repair
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['laboratory_id', 'status']);
            $table->index(['laboratory_id', 'condition']);
            $table->unique(['laboratory_id', 'lab_pc_num']); // Ensure unique PC numbers within each lab
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laboratory_inventory');
    }
};
