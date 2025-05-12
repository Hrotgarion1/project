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
        Schema::create('identity_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('identity_id')->constrained('identities')->onDelete('cascade');
            $table->string('name'); // Ej: "Documento 1"
            $table->string('type'); // Ej: "pdf" o "image"
            $table->string('path')->nullable(); // Ruta del archivo subido
            $table->boolean('is_required')->default(true); // Si es obligatorio
            $table->boolean('is_uploaded_by_user')->default(false); // Para rastrear actualizaciones
            $table->boolean('active')->default(true);
            $table->softDeletes(); // Agrega deleted_at
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('identity_documents');
    }
};
