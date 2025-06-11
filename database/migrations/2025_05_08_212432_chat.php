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
        Schema::create('chats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('room_id');
            $table->unsignedBigInteger('sender_id')->nullable();
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->foreign('room_id')->references('id')->on('rooms');
             $table->foreign('sender_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat');
    }
};
