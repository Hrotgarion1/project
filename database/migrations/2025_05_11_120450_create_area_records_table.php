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
        Schema::create('area_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('belonging_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('pais_id')->constrained('paises')->onDelete('restrict');
            $table->morphs('recordable'); // Añade recordable_id y recordable_type
            $table->integer('status')->nullable(false)->default(1);
            $table->bigInteger('value')->nullable()->default(0);
            $table->bigInteger('puntuacion_1')->nullable()->default(0);
            $table->bigInteger('puntuacion_2')->nullable()->default(0);
            $table->string('recordable_name')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['user_id', 'belonging_id', 'pais_id', 'recordable_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('area_records');
    }
};
