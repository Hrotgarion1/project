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
        Schema::create('identity_change_requests', function (Blueprint $table) {
            $table->id();
            $table->string('requestable_type')->index();
            $table->string('requestable_id')->index();
            $table->text('message');
            $table->timestamp('sent_at');
            $table->timestamp('seen_at')->nullable();
            $table->unsignedBigInteger('sent_by');
            $table->timestamps();

            $table->foreign('sent_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('identity_change_requests');
    }
};
