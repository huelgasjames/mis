<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('department_inventory', function (Blueprint $table) {
            $table->id();
            
            $table->string('asset_tag')->unique(); // Asset tag for tracking
            $table->string('computer_name'); // Computer name
            $table->string('category'); // Desktop, Laptop, Server, Monitor
            $table->string('processor'); // Processor type
            $table->string('motherboard')->nullable(); // Motherboard
            $table->string('video_card')->nullable(); // Video card
            $table->string('dvd_rom')->nullable(); // DVD ROM
            $table->string('psu')->nullable(); // Power supply unit
            $table->string('ram'); // RAM specification
            $table->string('storage'); // Storage specification
            $table->string('serial_number')->nullable(); // Serial number
            $table->enum('status', ['Working', 'Defective', 'For Disposal', 'Deployed', 'In Storage']);
            $table->text('description')->nullable(); // Additional description
            $table->string('location')->nullable(); // Current location (Comlab, Office, etc.)
            $table->foreignId('department_id')->constrained()->onDelete('cascade');
            $table->string('pc_num'); // PC number within department (e.g., MISD-001, MISD-002)
            $table->date('deployment_date')->nullable(); // When deployed
            $table->date('last_maintenance')->nullable(); // Last maintenance date
            $table->timestamps();
            
            $table->index(['department_id', 'status']);
            $table->index(['department_id', 'location']);
            $table->unique(['department_id', 'pc_num']); // Ensure unique PC numbers within each department
        });
    }

    public function down()
    {
        Schema::dropIfExists('department_inventory');
    }
};
