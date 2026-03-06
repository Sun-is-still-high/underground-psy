<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('methods', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->timestamps();
        });

        Schema::create('psychologist_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('psychologist_profile_id')->constrained('psychologist_profiles')->cascadeOnDelete();
            $table->foreignId('method_id')->constrained('methods')->cascadeOnDelete();
            $table->unique(['psychologist_profile_id', 'method_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('psychologist_methods');
        Schema::dropIfExists('methods');
    }
};
