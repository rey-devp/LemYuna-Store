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
        Schema::table('users', function (Blueprint $table) {
            $table->string('game_id')->nullable();
            $table->string('streaming_username')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('preferred_payment')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['game_id', 'streaming_username', 'whatsapp', 'preferred_payment']);
        });
    }
};
