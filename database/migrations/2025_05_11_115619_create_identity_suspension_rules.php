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
        Schema::create('identity_suspension_rules', function (Blueprint $table) {
            $table->id();
            $table->uuid('identity_id')->nullable();
            $table->string('role_type'); // Ej. 'A', 'B', ..., 'H' (de Spatie roles)
            $table->boolean('is_inviter'); // true para invitador, false para invitados
            $table->string('view'); // Nombre de la vista/ruta, ej. 'identities.index'
            $table->string('controller')->nullable(); // Ej. 'IdentityController' (opcional)
            $table->string('function'); // Nombre de la función, ej. 'acceptInvitation'
            $table->boolean('allowed')->default(false); // Si la función está permitida
            $table->unique(['role_type', 'is_inviter', 'view', 'controller', 'function'], 'unique_rule_combination');
            $table->timestamps();

            // Índice para mejorar búsquedas
            $table->index(['role_type', 'is_inviter', 'view']);
            $table->foreign('identity_id')->references('id')->on('identities')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('identity_suspension_rules');
    }
};
