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
        Schema::create('summary_areas', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('puntuacion_1', 8, 2)->nullable()->default(0);
            $table->decimal('puntuacion_2', 8, 2)->nullable()->default(0);
            $table->decimal('puntuacion_3', 8, 2)->nullable()->default(0);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('summary_areas');
    }
};
