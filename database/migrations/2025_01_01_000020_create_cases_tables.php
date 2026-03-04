<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('problem_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('cases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('problem_type_id')->constrained('problem_types')->restrictOnDelete();
            $table->string('title');
            $table->text('description');
            $table->boolean('is_anonymous')->default(false);
            $table->enum('status', ['OPEN', 'IN_PROGRESS', 'CLOSED', 'CANCELLED'])->default('OPEN');
            $table->enum('budget_type', ['PAID', 'REVIEW', 'NEGOTIABLE'])->default('NEGOTIABLE');
            $table->decimal('budget_amount', 10, 2)->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('case_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('cases')->cascadeOnDelete();
            $table->foreignId('psychologist_id')->constrained('users')->cascadeOnDelete();
            $table->text('message');
            $table->decimal('proposed_price', 10, 2)->nullable();
            $table->enum('status', ['PENDING', 'ACCEPTED', 'REJECTED'])->default('PENDING');
            $table->timestamp('responded_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['case_id', 'psychologist_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_responses');
        Schema::dropIfExists('cases');
        Schema::dropIfExists('problem_types');
    }
};
