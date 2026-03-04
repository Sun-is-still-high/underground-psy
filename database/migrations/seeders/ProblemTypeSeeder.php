<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProblemTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Тревога и панические атаки', 'slug' => 'anxiety', 'description' => 'Тревожные расстройства, панические атаки, фобии', 'sort_order' => 1],
            ['name' => 'Депрессия', 'slug' => 'depression', 'description' => 'Депрессивные состояния, апатия, потеря интереса к жизни', 'sort_order' => 2],
            ['name' => 'Зависимости', 'slug' => 'addiction', 'description' => 'Алкогольная, наркотическая, игровая и другие зависимости', 'sort_order' => 3],
            ['name' => 'Отношения', 'slug' => 'relationships', 'description' => 'Проблемы в отношениях, развод, одиночество', 'sort_order' => 4],
            ['name' => 'Самооценка', 'slug' => 'self-esteem', 'description' => 'Низкая самооценка, неуверенность в себе', 'sort_order' => 5],
            ['name' => 'Травма и ПТСР', 'slug' => 'trauma', 'description' => 'Посттравматическое стрессовое расстройство, психологические травмы', 'sort_order' => 6],
            ['name' => 'Горе и утрата', 'slug' => 'grief', 'description' => 'Переживание потери близких', 'sort_order' => 7],
            ['name' => 'Стресс и выгорание', 'slug' => 'burnout', 'description' => 'Профессиональное выгорание, хронический стресс', 'sort_order' => 8],
            ['name' => 'Расстройства пищевого поведения', 'slug' => 'eating-disorders', 'description' => 'Анорексия, булимия, компульсивное переедание', 'sort_order' => 9],
            ['name' => 'Другое', 'slug' => 'other', 'description' => 'Другие психологические проблемы', 'sort_order' => 100],
        ];

        foreach ($types as $type) {
            DB::table('problem_types')->updateOrInsert(['slug' => $type['slug']], $type);
        }

        DB::table('intervision_settings')->updateOrInsert(
            ['setting_key' => 'min_monthly_sessions'],
            ['setting_key' => 'min_monthly_sessions', 'setting_value' => '2', 'description' => 'Минимальное количество интервизий в месяц для права консультировать']
        );
    }
}
