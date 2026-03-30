<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Добавить can_consult в psychologist_profiles
        Schema::table('psychologist_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('psychologist_profiles', 'can_consult')) {
                $table->boolean('can_consult')->default(false)->after('diploma_rejection_comment');
            }
        });

        // Добавить MODERATOR к enum role в users
        // MySQL: ALTER TABLE ... MODIFY COLUMN
        $driver = DB::connection()->getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('CLIENT','PSYCHOLOGIST','ADMIN','MODERATOR') NOT NULL DEFAULT 'CLIENT'");
        }

        // Засеять min_sessions в intervision_settings (если ещё нет)
        DB::table('intervision_settings')->insertOrIgnore([
            'setting_key' => 'min_sessions',
            'setting_value' => '3',
            'description' => 'Минимальное количество интервизий за 30 дней для допуска к консультациям',
        ]);
    }

    public function down(): void
    {
        Schema::table('psychologist_profiles', function (Blueprint $table) {
            $table->dropColumn('can_consult');
        });

        $driver = DB::connection()->getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('CLIENT','PSYCHOLOGIST','ADMIN') NOT NULL DEFAULT 'CLIENT'");
        }

        DB::table('intervision_settings')->where('setting_key', 'min_sessions')->delete();
    }
};