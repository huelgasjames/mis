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
        Schema::create('dvd_rom', function (Blueprint $table) {
            $table->id();
            $table->string('asset_tag', 50); // Foreign key reference
            $table->string('brand');
            $table->string('model');
            $table->string('type');
            $table->string('speed');
            $table->boolean('has_writer');
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
        Schema::dropIfExists('dvd_rom');
    }
};
