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
        Schema::create('user_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('card_bg_color', 7)->default('#ffffff');
            $table->string('chart_proposed_color', 7)->default('#FFD700')->nullable();
            $table->string('chart_verified_color', 7)->default('#FFD700')->nullable();
            $table->string('text_color', 7)->default('#000000');
            $table->string('text_font', 50)->default('Inter');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_preferences');
    }
};
