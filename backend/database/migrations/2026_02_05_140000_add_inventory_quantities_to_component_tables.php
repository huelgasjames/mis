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
        Schema::table('processor', function (Blueprint $table) {
            $table->integer('quantity')->default(1)->after('threads');
            $table->string('unit', 20)->default('piece')->after('quantity');
        });

        Schema::table('motherboard', function (Blueprint $table) {
            $table->integer('quantity')->default(1)->after('form_factor');
            $table->string('unit', 20)->default('piece')->after('quantity');
        });

        Schema::table('video_card', function (Blueprint $table) {
            $table->integer('quantity')->default(1)->after('interface');
            $table->string('unit', 20)->default('piece')->after('quantity');
        });

        Schema::table('dvd_rom', function (Blueprint $table) {
            $table->integer('quantity')->default(1)->after('has_writer');
            $table->string('unit', 20)->default('piece')->after('quantity');
        });

        Schema::table('psu', function (Blueprint $table) {
            $table->integer('quantity')->default(1)->after('has_modular_cabling');
            $table->string('unit', 20)->default('piece')->after('quantity');
        });

        Schema::table('ram', function (Blueprint $table) {
            $table->integer('quantity')->default(1)->after('modules_count');
            $table->string('unit', 20)->default('piece')->after('quantity');
        });

        Schema::table('storage', function (Blueprint $table) {
            $table->integer('quantity')->default(1)->after('rpm');
            $table->string('unit', 20)->default('piece')->after('quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('processor', function (Blueprint $table) {
            $table->dropColumn(['quantity', 'unit']);
        });

        Schema::table('motherboard', function (Blueprint $table) {
            $table->dropColumn(['quantity', 'unit']);
        });

        Schema::table('video_card', function (Blueprint $table) {
            $table->dropColumn(['quantity', 'unit']);
        });

        Schema::table('dvd_rom', function (Blueprint $table) {
            $table->dropColumn(['quantity', 'unit']);
        });

        Schema::table('psu', function (Blueprint $table) {
            $table->dropColumn(['quantity', 'unit']);
        });

        Schema::table('ram', function (Blueprint $table) {
            $table->dropColumn(['quantity', 'unit']);
        });

        Schema::table('storage', function (Blueprint $table) {
            $table->dropColumn(['quantity', 'unit']);
        });
    }
};
