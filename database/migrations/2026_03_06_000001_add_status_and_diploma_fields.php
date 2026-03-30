<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Добавить status в users
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'status')) {
                $table->enum('status', ['active', 'pending_verification'])->default('active')->after('role');
            }
        });

        // Добавить diploma_number, diploma_year, diploma_institution, diploma_rejection_comment в psychologist_profiles
        Schema::table('psychologist_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('psychologist_profiles', 'diploma_number')) {
                $table->string('diploma_number')->nullable()->after('diploma_scan_url');
            }
            if (!Schema::hasColumn('psychologist_profiles', 'diploma_year')) {
                $table->unsignedSmallInteger('diploma_year')->nullable()->after('diploma_number');
            }
            if (!Schema::hasColumn('psychologist_profiles', 'diploma_institution')) {
                $table->string('diploma_institution')->nullable()->after('diploma_year');
            }
            if (!Schema::hasColumn('psychologist_profiles', 'diploma_rejection_comment')) {
                $table->text('diploma_rejection_comment')->nullable()->after('diploma_verified');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('psychologist_profiles', function (Blueprint $table) {
            $table->dropColumn(['diploma_number', 'diploma_year', 'diploma_institution', 'diploma_rejection_comment']);
        });
    }
};