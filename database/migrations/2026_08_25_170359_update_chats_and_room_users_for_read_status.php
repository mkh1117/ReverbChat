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
        Schema::table('room_users', function (Blueprint $table) {
            $table->unsignedBigInteger('last_read_sequence_id')->default(0)->after('role');
        });

        Schema::table('chats', function (Blueprint $table) {
            $table->unsignedBigInteger('views_count')->default(0)->after('message');
            $table->dropColumn('is_read');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('room_users', function (Blueprint $table) {
            $table->dropColumn('last_read_sequence_id');
        });

        Schema::table('chats', function (Blueprint $table) {
            $table->boolean('is_read')->default(false);
            $table->dropColumn('views_count');
        });
    }
};
