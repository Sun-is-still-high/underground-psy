<?php
namespace App\Models;

use Core\Model;
use Core\Database;

class Event extends Model
{
    protected string $table = 'events';

    public const TYPES = [
        'GROUP_THERAPY'  => 'Групповая терапия',
        'SUPPORT_GROUP'  => 'Группа поддержки',
        'SEMINAR'        => 'Семинар',
        'TRAINING'       => 'Тренинг',
        'WEBINAR'        => 'Вебинар',
    ];

    public const FORMATS = [
        'ONLINE'  => 'Онлайн',
        'OFFLINE' => 'Офлайн',
    ];

    /**
     * Получить предстоящие активные мероприятия для публичного каталога
     */
    public function getUpcoming(array $filters = []): array
    {
        $sql = "SELECT e.*, u.name as organizer_name, pp.photo_url as organizer_photo
                FROM {$this->table} e
                JOIN users u ON e.organizer_id = u.id
                LEFT JOIN psychologist_profiles pp ON pp.user_id = u.id
                WHERE e.status = 'ACTIVE' AND e.scheduled_at > NOW()";

        $params = [];

        if (!empty($filters['event_type'])) {
            $sql .= " AND e.event_type = :event_type";
            $params['event_type'] = $filters['event_type'];
        }

        if (!empty($filters['format'])) {
            $sql .= " AND e.format = :format";
            $params['format'] = $filters['format'];
        }

        $sql .= " ORDER BY e.scheduled_at ASC";

        return Database::query($sql, $params);
    }

    /**
     * Получить мероприятие по ID с деталями организатора
     */
    public function getWithOrganizer(int $id): ?array
    {
        $sql = "SELECT e.*, u.name as organizer_name, u.email as organizer_email,
                       pp.photo_url as organizer_photo, pp.id as organizer_profile_id
                FROM {$this->table} e
                JOIN users u ON e.organizer_id = u.id
                LEFT JOIN psychologist_profiles pp ON pp.user_id = u.id
                WHERE e.id = :id";

        return Database::queryOne($sql, ['id' => $id]);
    }

    /**
     * Мероприятия конкретного психолога
     */
    public function getByOrganizer(int $organizerId): array
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE organizer_id = :organizer_id
                ORDER BY scheduled_at DESC";

        return Database::query($sql, ['organizer_id' => $organizerId]);
    }
}
