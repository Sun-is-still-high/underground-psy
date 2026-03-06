<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('psychologist_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('psychologist_profiles', 'work_format')) {
                $table->enum('work_format', ['online', 'offline', 'both'])->default('online')->after('can_consult');
            }
            if (!Schema::hasColumn('psychologist_profiles', 'city')) {
                $table->string('city')->nullable()->after('work_format');
            }
            if (!Schema::hasColumn('psychologist_profiles', 'languages')) {
                $table->json('languages')->nullable()->after('city');
            }
        });
    }

    public function down(): void
    {
        Schema::table('psychologist_profiles', function (Blueprint $table) {
            $table->dropColumn(['work_format', 'city', 'languages']);
        });
    }
};
