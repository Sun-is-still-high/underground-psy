<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('psychologist_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('psychologist_profiles', 'diploma_scan_url')) {
                $table->string('diploma_scan_url', 500)->nullable()->after('is_published');
            }
            if (!Schema::hasColumn('psychologist_profiles', 'diploma_verified')) {
                $table->boolean('diploma_verified')->default(false)->after('diploma_scan_url');
            }
        });
    }

    public function down(): void
    {
        Schema::table('psychologist_profiles', function (Blueprint $table) {
            $table->dropColumn(['diploma_scan_url', 'diploma_verified']);
        });
    }
};
