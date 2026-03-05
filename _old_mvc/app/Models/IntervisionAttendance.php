<?php
namespace App\Models;

use Core\Model;
use Core\Database;

class IntervisionAttendance extends Model
{
    protected string $table = 'intervision_attendance';

    /**
     * Создать записи посещаемости для всех участников сессии
     */
    public function createForSession(int $sessionId, int $groupId): void
    {
        $sql = "INSERT IGNORE INTO {$this->table} (session_id, participant_id)
                SELECT :session_id, ip.id
                FROM intervision_participants ip
                WHERE ip.group_id = :group_id AND ip.is_active = 1";
        Database::execute($sql, [
            'session_id' => $sessionId,
            'group_id' => $groupId
        ]);
    }

    /**
     * Отметить посещение
     */
    public function markAttendance(int $sessionId, int $participantId, bool $attended, int $markedBy): bool
    {
        $sql = "UPDATE {$this->table}
                SET attended = :attended, marked_at = NOW(), marked_by = :marked_by
                WHERE session_id = :session_id AND participant_id = :participant_id";
        return Database::execute($sql, [
            'attended' => $attended ? 1 : 0,
            'session_id' => $sessionId,
            'participant_id' => $participantId,
            'marked_by' => $markedBy
        ]);
    }

    /**
     * Массовая отметка посещаемости
     */
    public function markBulkAttendance(int $sessionId, array $attendedParticipantIds, int $markedBy): bool
    {
        $sql = "UPDATE {$this->table}
                SET attended = 0, marked_at = NOW(), marked_by = :marked_by
                WHERE session_id = :session_id";
        Database::execute($sql, [
            'session_id' => $sessionId,
            'marked_by' => $markedBy
        ]);

        if (!empty($attendedParticipantIds)) {
            $ids = implode(',', array_map('intval', $attendedParticipantIds));
            $sql = "UPDATE {$this->table}
                    SET attended = 1
                    WHERE session_id = :session_id AND participant_id IN ({$ids})";
            Database::execute($sql, ['session_id' => $sessionId]);
        }

        return true;
    }

    /**
     * Получить посещаемость сессии
     */
    public function getBySession(int $sessionId): array
    {
        $sql = "SELECT ia.*, ip.psychologist_id, u.name, u.email
                FROM {$this->table} ia
                JOIN intervision_participants ip ON ia.participant_id = ip.id
                JOIN users u ON ip.psychologist_id = u.id
                WHERE ia.session_id = :session_id
                ORDER BY u.name";
        return Database::query($sql, ['session_id' => $sessionId]);
    }

    /**
     * Подсчитать посещённые сессии психологом за месяц
     */
    public function countMonthlyAttendance(int $psychologistId, int $year, int $month): int
    {
        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate = date('Y-m-t', strtotime($startDate));

        $sql = "SELECT COUNT(*) as count
                FROM {$this->table} ia
                JOIN intervision_participants ip ON ia.participant_id = ip.id
                JOIN intervision_sessions isess ON ia.session_id = isess.id
                WHERE ip.psychologist_id = :psychologist_id
                AND ia.attended = 1
                AND isess.status = 'COMPLETED'
                AND isess.scheduled_at BETWEEN :start_date AND :end_date";

        $result = Database::queryOne($sql, [
            'psychologist_id' => $psychologistId,
            'start_date' => $startDate,
            'end_date' => $endDate . ' 23:59:59'
        ]);

        return (int)($result['count'] ?? 0);
    }
}
