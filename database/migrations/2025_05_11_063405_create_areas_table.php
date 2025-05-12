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
        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Ej: A, B, C
            $table->decimal('puntuacion_1', 8, 2)->nullable()->default(0);
            $table->decimal('puntuacion_2', 8, 2)->nullable()->default(0);
            $table->decimal('puntuacion_3', 8, 2)->nullable()->default(0);
            $table->unsignedBigInteger('summary_area_id')->nullable();
            $table->softDeletes(); 
            $table->timestamps();

            $table->foreign('summary_area_id')->references('id')->on('summary_areas')->onUpdate('cascade')->onDelete('cascade');
            $table->index('summary_area_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('areas');
    }
};
