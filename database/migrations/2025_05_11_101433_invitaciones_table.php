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
        Schema::create('invitaciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invitador_id');
            $table->unsignedBigInteger('invitado_id');
            $table->uuid('identity_id'); // Correcto: coincide con identities.id como UUID
            $table->string('role')->nullable();
            // $table->unsignedBigInteger('team_id')->nullable();
            // $table->string('tipo_invitador')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'deleted'])->default('pending');
            $table->string('token')->unique();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('invitador_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('invitado_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('identity_id')->references('id')->on('identities')->onUpdate('cascade')->onDelete('cascade');
            // $table->foreign('team_id')->references('id')->on('teams')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invitaciones');
    }
};
