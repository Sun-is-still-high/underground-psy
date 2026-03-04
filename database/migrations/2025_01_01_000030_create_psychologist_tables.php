<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('psychologist_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->text('bio')->nullable();
            $table->text('methods_description')->nullable();
            $table->text('education')->nullable();
            $table->text('experience_description')->nullable();
            $table->decimal('hourly_rate_min', 10, 2)->nullable();
            $table->decimal('hourly_rate_max', 10, 2)->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });

        Schema::create('psychologist_specializations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained('psychologist_profiles')->cascadeOnDelete();
            $table->foreignId('problem_type_id')->constrained('problem_types')->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['profile_id', 'problem_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('psychologist_specializations');
        Schema::dropIfExists('psychologist_profiles');
    }
};
