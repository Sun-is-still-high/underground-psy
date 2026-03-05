<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('intervision_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('max_participants')->default(10);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });

        Schema::create('intervision_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('intervision_groups')->cascadeOnDelete();
            $table->string('topic');
            $table->text('description')->nullable();
            $table->dateTime('scheduled_at');
            $table->integer('duration_minutes')->default(90);
            $table->string('meeting_link', 500)->nullable();
            $table->enum('status', ['SCHEDULED', 'IN_PROGRESS', 'COMPLETED', 'CANCELLED'])->default('SCHEDULED');
            $table->text('cancelled_reason')->nullable();
            $table->timestamps();
        });

        Schema::create('intervision_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('intervision_groups')->cascadeOnDelete();
            $table->foreignId('psychologist_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('joined_at')->useCurrent();
            $table->boolean('is_active')->default(true);
            $table->timestamp('left_at')->nullable();
            $table->unique(['group_id', 'psychologist_id']);
        });

        Schema::create('intervision_attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('intervision_sessions')->cascadeOnDelete();
            $table->foreignId('participant_id')->constrained('intervision_participants')->cascadeOnDelete();
            $table->boolean('attended')->default(false);
            $table->timestamp('marked_at')->nullable();
            $table->foreignId('marked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->unique(['session_id', 'participant_id']);
        });

        Schema::create('intervision_settings', function (Blueprint $table) {
            $table->id();
            $table->string('setting_key', 100)->unique();
            $table->string('setting_value');
            $table->text('description')->nullable();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intervision_attendance');
        Schema::dropIfExists('intervision_participants');
        Schema::dropIfExists('intervision_sessions');
        Schema::dropIfExists('intervision_groups');
        Schema::dropIfExists('intervision_settings');
    }
};
