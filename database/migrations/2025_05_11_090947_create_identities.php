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
        Schema::create('identities', function (Blueprint $table) {
            $table->uuid('id')->primary(); // UUID como clave primaria
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Evita eliminación en cascada
            $table->foreignId('identity_type_id')->nullable()->constrained('identity_types')->onDelete('cascade');
            $table->string('type'); // Ejemplo: 'A', 'B'
            $table->string('email')->unique();
            $table->string('name')->nullable();
            $table->string('slug')->unique()->nullable();
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->text('documents')->nullable(); // JSON en texto con rutas de archivos
            $table->enum('status', ['pending', 'in_progress', 'approved', 'rejected', 'suspended', 'deleted', 'waiting'])->default('pending');
            $table->foreignId('handled_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('taken_at')->nullable();
            $table->string('suspension_reason')->nullable();
            $table->string('suspend_reason_code')->nullable();
            $table->string('delete_reason_code')->nullable();
            $table->text('notes')->nullable(); // Notas internas del gestor
            $table->text('requested_changes')->nullable(); // Instrucciones para el usuario
            $table->boolean('has_updates')->default(false); // Para la campana
            $table->softDeletes(); // Agrega la columna deleted_at para Soft Deletes
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('identities');
    }
};
