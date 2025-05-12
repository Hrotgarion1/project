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
        Schema::create('area_d', function (Blueprint $table) {
            $table->id();
            $table->foreignId('area_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->date('init_date');
            $table->date('end_date')->nullable();
            $table->tinyInteger('schedule')->unsigned()->default(0); // 1 a 8 horas
            $table->tinyInteger('overtime')->unsigned()->default(0); // 0 a 4 horas
            $table->enum('currently', ['yes', 'no']);
            $table->bigInteger('value');
            $table->date('last_calculated_date')->nullable();
            $table->text('details')->nullable();
            $table->softDeletes();
            $table->timestamps();
            
            $table->index(['area_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('area_d');
    }
};
