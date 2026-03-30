<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('slot_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('slot_id')->constrained('slots')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role');          // therapist, client, observer
            $table->string('original_role'); // роль при записи
            $table->string('source');        // signup, invitation
            $table->string('status')->default('active'); // active, cancelled
            $table->boolean('confirmed_completion')->default(false);
            $table->timestamps();

            $table->index('slot_id');
            $table->index('user_id');

            // Нельзя записаться на слот дважды (один активный участник на слот).
            // Уникальность по role и user реализуется на уровне приложения
            // с pessimistic lock, т.к. MySQL не поддерживает partial unique index нативно.
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('slot_participants');
    }
};