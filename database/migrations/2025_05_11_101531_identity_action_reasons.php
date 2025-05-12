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
        Schema::create('identity_action_reasons', function (Blueprint $table) {
            $table->id();
            $table->string('action_type')->index(); // 'suspend' o 'delete'
            $table->string('code')->index(); // Código único por acción
            $table->string('title');
            $table->text('description');
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['action_type', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('identity_action_reasons');
    }
};
