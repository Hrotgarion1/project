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
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('file_path');
            $table->string('file_name');
            $table->string('file_type');
            $table->string('folder')->nullable();
            $table->uuid('mediable_id'); // Cambiar a uuid en lugar de unsignedBigInteger
            $table->string('mediable_type');
            $table->integer('position')->default(0);
            $table->boolean('is_youtube')->default(false);
            $table->string('youtube_id')->nullable();
            $table->string('role')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
