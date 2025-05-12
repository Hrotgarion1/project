<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('identity_meta', function (Blueprint $table) {
            $table->id();
            $table->uuid('identity_id');
            $table->string('key'); // Nombre del campo adicional
            $table->enum('type', ['string', 'text', 'integer', 'boolean', 'file', 'image'])->default('string');
            $table->text('value')->nullable(); // Aquí guardamos el dato (texto, ruta de archivo, etc.)
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('identity_id')->references('id')->on('identities')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('identity_meta');
    }
};
