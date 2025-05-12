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
        Schema::create('identity_suspensions', function (Blueprint $table) {
            $table->id();
            $table->uuid('identity_id');
            $table->unsignedBigInteger('user_id');
            $table->string('role_type'); // Ej. "tipo A", "tipo A1"
            $table->boolean('is_inviter'); // Invitador o invitado
            $table->timestamps();

            $table->foreign('identity_id')->references('id')->on('identities')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('identity_suspensions');
    }
};
