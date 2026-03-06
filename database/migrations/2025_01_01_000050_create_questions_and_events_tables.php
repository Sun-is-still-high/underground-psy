<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('public_questions')) {
            Schema::create('public_questions', function (Blueprint $table) {
                $table->id();
                $table->string('author_name', 100);
                $table->string('author_email', 150);
                $table->text('question');
                $table->string('status', 20)->default('PENDING'); // PENDING | ANSWERED
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('public_answers')) {
            Schema::create('public_answers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('question_id')->constrained('public_questions')->cascadeOnDelete();
                $table->foreignId('psychologist_id')->constrained('users')->cascadeOnDelete();
                $table->text('answer');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('events')) {
            Schema::create('events', function (Blueprint $table) {
                $table->id();
                $table->foreignId('organizer_id')->constrained('users')->cascadeOnDelete();
                $table->string('title', 200);
                $table->text('description')->nullable();
                $table->string('event_type', 50);
                $table->string('format', 20); // ONLINE | OFFLINE
                $table->string('city', 100)->nullable();
                $table->string('meeting_link', 500)->nullable();
                $table->decimal('price', 10, 2)->nullable();
                $table->unsignedSmallInteger('max_participants')->nullable();
                $table->unsignedSmallInteger('duration_minutes')->default(60);
                $table->dateTime('scheduled_at');
                $table->string('status', 20)->default('ACTIVE'); // ACTIVE | CANCELLED
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('public_answers');
        Schema::dropIfExists('public_questions');
        Schema::dropIfExists('events');
    }
};
