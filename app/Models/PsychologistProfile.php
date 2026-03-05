<?php
namespace App\Models;

use Core\Model;
use Core\Database;

class PsychologistProfile extends Model
{
    protected string $table = 'psychologist_profiles';

    /**
     * Получить профиль по user_id
     */
    public function findByUserId(int $userId): ?array
    {
        return $this->where('user_id', $userId);
    }

    /**
     * Получить все опубликованные профили с фильтрами
     */
    public function getPublishedProfiles(array $filters = []): array
    {
        $sql = "SELECT pp.*, u.name as psychologist_name, u.gender as user_gender, u.created_at as registered_at
                FROM {$this->table} pp
                JOIN users u ON pp.user_id = u.id
                WHERE pp.is_published = 1 AND u.is_blocked = 0";

        $params = [];

        if (!empty($filters['problem_type_id'])) {
            $sql .= " AND pp.id IN (
                SELECT profile_id FROM psychologist_specializations
                WHERE problem_type_id = :problem_type_id
            )";
            $params['problem_type_id'] = $filters['problem_type_id'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (u.name LIKE :search OR pp.bio LIKE :search2)";
            $params['search'] = '%' . $filters['search'] . '%';
            $params['search2'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['gender'])) {
            $sql .= " AND u.gender = :gender";
            $params['gender'] = $filters['gender'];
        }

        if (!empty($filters['work_format'])) {
            $sql .= " AND (pp.work_format = :work_format OR pp.work_format = 'BOTH')";
            $params['work_format'] = $filters['work_format'];
        }

        if (!empty($filters['language'])) {
            $sql .= " AND FIND_IN_SET(:language, REPLACE(pp.languages, ', ', ',')) > 0";
            $params['language'] = $filters['language'];
        }

        if (!empty($filters['price_max'])) {
            $sql .= " AND (pp.hourly_rate_min IS NULL OR pp.hourly_rate_min <= :price_max)";
            $params['price_max'] = (float) $filters['price_max'];
        }

        $sql .= " ORDER BY pp.diploma_verified DESC, pp.updated_at DESC";

        return Database::query($sql, $params);
    }

    /**
     * Получить профиль со всеми деталями для публичной страницы
     */
    public function getProfileWithDetails(int $id): ?array
    {
        $sql = "SELECT pp.*, u.name as psychologist_name, u.gender as user_gender, u.created_at as registered_at
                FROM {$this->table} pp
                JOIN users u ON pp.user_id = u.id
                WHERE pp.id = :id AND pp.is_published = 1 AND u.is_blocked = 0";

        return Database::queryOne($sql, ['id' => $id]);
    }

    /**
     * Получить специализации профиля
     */
    public function getSpecializations(int $profileId): array
    {
        $sql = "SELECT pt.*
                FROM problem_types pt
                JOIN psychologist_specializations ps ON pt.id = ps.problem_type_id
                WHERE ps.profile_id = :profile_id
                ORDER BY pt.sort_order ASC";

        return Database::query($sql, ['profile_id' => $profileId]);
    }

    /**
     * Сохранить специализации профиля
     */
    public function saveSpecializations(int $profileId, array $problemTypeIds): void
    {
        Database::execute(
            "DELETE FROM psychologist_specializations WHERE profile_id = :profile_id",
            ['profile_id' => $profileId]
        );

        foreach ($problemTypeIds as $ptId) {
            Database::execute(
                "INSERT INTO psychologist_specializations (profile_id, problem_type_id) VALUES (:profile_id, :pt_id)",
                ['profile_id' => $profileId, 'pt_id' => (int) $ptId]
            );
        }
    }

    /**
     * Статистика активности психолога
     */
    public function getActivityStats(int $userId): array
    {
        $responses = Database::queryOne(
            "SELECT COUNT(*) as cnt FROM case_responses WHERE psychologist_id = :uid",
            ['uid' => $userId]
        );

        $intervisions = Database::queryOne(
            "SELECT COUNT(*) as cnt
             FROM intervision_attendance ia
             JOIN intervision_participants ip ON ia.participant_id = ip.id
             JOIN intervision_sessions s ON ia.session_id = s.id
             WHERE ip.psychologist_id = :uid
               AND ia.attended = 1
               AND s.scheduled_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)",
            ['uid' => $userId]
        );

        return [
            'response_count' => (int) ($responses['cnt'] ?? 0),
            'sessions_attended_month' => (int) ($intervisions['cnt'] ?? 0),
        ];
    }

    /**
     * Нужно ли подтверждение актуальности цен (> 30 дней или никогда не подтверждалось)
     */
    public function needsPriceConfirmation(int $profileId): bool
    {
        $row = Database::queryOne(
            "SELECT price_confirmed_at FROM {$this->table} WHERE id = :id",
            ['id' => $profileId]
        );

        if (!$row || $row['price_confirmed_at'] === null) {
            return true;
        }

        $confirmedAt = strtotime($row['price_confirmed_at']);
        return (time() - $confirmedAt) > (30 * 24 * 3600);
    }

    /**
     * Подтвердить актуальность цен
     */
    public function confirmPrice(int $profileId): void
    {
        Database::execute(
            "UPDATE {$this->table} SET price_confirmed_at = NOW() WHERE id = :id",
            ['id' => $profileId]
        );
    }

    /**
     * Рассчитать заполненность профиля и список рекомендаций
     * Возвращает ['percent' => int, 'missing' => string[]]
     */
    public function getProfileCompleteness(array $profile, array $specializations): array
    {
        $checks = [
            'bio'                  => [!empty($profile['bio']),                'Заполните раздел «О себе»'],
            'methods'              => [!empty($profile['methods_description']), 'Опишите методы и подходы'],
            'education'            => [!empty($profile['education']),           'Добавьте информацию об образовании'],
            'experience'           => [!empty($profile['experience_description']), 'Опишите опыт работы'],
            'specializations'      => [!empty($specializations),               'Выберите хотя бы одну специализацию'],
            'rate'                 => [$profile['hourly_rate_min'] || $profile['hourly_rate_max'], 'Укажите стоимость сессии'],
            'photo'                => [!empty($profile['photo_url']),           'Загрузите фото профиля'],
            'diploma'              => [!empty($profile['diploma_scan_url']),    'Загрузите скан диплома для верификации'],
            'format'               => [!empty($profile['work_format']),         'Укажите формат работы'],
            'price_confirmed'      => [!$this->needsPriceConfirmation($profile['id']), 'Подтвердите актуальность стоимости'],
        ];

        $total = count($checks);
        $done = 0;
        $missing = [];

        foreach ($checks as [$passed, $hint]) {
            if ($passed) {
                $done++;
            } else {
                $missing[] = $hint;
            }
        }

        return [
            'percent' => (int) round($done / $total * 100),
            'missing' => $missing,
        ];
    }
}
