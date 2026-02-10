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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('asset_tag', 50)->unique();
            $table->string('computer_name');
            $table->string('category');
            $table->string('processor');
            $table->string('motherboard')->nullable();
            $table->string('video_card')->nullable();
            $table->string('dvd_rom')->nullable();
            $table->string('psu')->nullable();
            $table->string('ram');
            $table->string('storage');
            $table->string('serial_number')->nullable();
            $table->enum('status', ['Working', 'Defective', 'For Disposal']);
            $table->foreignId('department_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            // Set engine to InnoDB
            $table->engine = 'InnoDB';
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
