<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creator_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->dateTime('starts_at');
            $table->string('visibility')->default('public'); // public, private
            $table->boolean('blind_mode')->default(false);
            $table->string('status')->default('open'); // open, full, in_progress, completed, cancelled
            $table->timestamps();

            $table->index('creator_id');
            $table->index('starts_at');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('slots');
    }
};
