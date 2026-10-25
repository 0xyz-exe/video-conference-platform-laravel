<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meetings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('room_code')->unique();
            $table->text('description')->nullable();
            $table->string('passcode')->nullable();
            $table->boolean('is_locked')->default(false);
            $table->integer('max_participants')->default(50);
            $table->string('status')->default('scheduled'); // scheduled, active, ended
            $table->dateTime('scheduled_at')->nullable();
            $table->dateTime('ended_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meetings');
    }
};
