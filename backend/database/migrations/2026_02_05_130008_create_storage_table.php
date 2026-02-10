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
        Schema::create('storage', function (Blueprint $table) {
            $table->id();
            $table->string('asset_tag', 50); // Foreign key reference
            $table->string('brand');
            $table->string('model');
            $table->string('type'); // SSD, HDD, NVMe, etc.
            $table->string('capacity');
            $table->string('interface'); // SATA, NVMe, etc.
            $table->string('form_factor'); // 2.5", 3.5", M.2, etc.
            $table->integer('rpm')->nullable(); // For HDDs
            $table->timestamps();
            
            // Foreign key constraint
            $table->foreign('asset_tag')
                  ->references('asset_tag')
                  ->on('assets')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
                  
            // Make asset_tag unique for one-to-one relationship
            $table->unique('asset_tag');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('storage');
    }
};
