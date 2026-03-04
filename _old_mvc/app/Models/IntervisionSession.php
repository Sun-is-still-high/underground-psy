<?php
namespace App\Models;

use Core\Model;
use Core\Database;

class IntervisionSession extends Model
{
    protected string $table = 'intervision_sessions';

    const STATUS_SCHEDULED = 'SCHEDULED';
    const STATUS_IN_PROGRESS = 'IN_PROGRESS';
    const STATUS_COMPLETED = 'COMPLETED';
    const STATUS_CANCELLED = 'CANCELLED';

    /**
     * Получить сессии группы
     */
    public function getByGroup(int $groupId, ?string $status = null): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE group_id = :group_id";
        $params = ['group_id' => $groupId];

        if ($status) {
            $sql .= " AND status = :status";
            $params['status'] = $status;
        }

        $sql .= " ORDER BY scheduled_at DESC";
        return Database::query($sql, $params);
    }

    /**
     * Получить предстоящие сессии группы
     */
    public function getUpcoming(int $groupId): array
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE group_id = :group_id
                AND status = 'SCHEDULED'
                AND scheduled_at > NOW()
                ORDER BY scheduled_at ASC";
        return Database::query($sql, ['group_id' => $groupId]);
    }

    /**
     * Получить сессию с данными о посещаемости
     */
    public function findWithAttendance(int $id): ?array
    {
        $session = $this->find($id);
        if (!$session) {
            return null;
        }

        $sql = "SELECT ia.*, ip.psychologist_id, u.name, u.email
                FROM intervision_attendance ia
                JOIN intervision_participants ip ON ia.participant_id = ip.id
                JOIN users u ON ip.psychologist_id = u.id
                WHERE ia.session_id = :session_id
                ORDER BY u.name";
        $session['attendance'] = Database::query($sql, ['session_id' => $id]);

        return $session;
    }

    /**
     * Изменить статус сессии
     */
    public function changeStatus(int $id, string $status, ?string $reason = null): bool
    {
        $data = ['status' => $status];
        if ($status === self::STATUS_CANCELLED && $reason) {
            $data['cancelled_reason'] = $reason;
        }
        return $this->update($id, $data);
    }

    /**
     * Завершить сессию
     */
    public function complete(int $id): bool
    {
        return $this->changeStatus($id, self::STATUS_COMPLETED);
    }

    /**
     * Отменить сессию
     */
    public function cancel(int $id, string $reason): bool
    {
        return $this->changeStatus($id, self::STATUS_CANCELLED, $reason);
    }
}
