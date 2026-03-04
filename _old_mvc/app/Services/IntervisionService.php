<?php
namespace App\Services;

use App\Models\IntervisionAttendance;
use App\Models\IntervisionGroup;
use App\Models\IntervisionSession;
use App\Models\IntervisionParticipant;
use Core\Database;

class IntervisionService
{
    private IntervisionAttendance $attendanceModel;
    private IntervisionGroup $groupModel;
    private IntervisionSession $sessionModel;
    private IntervisionParticipant $participantModel;

    const DEFAULT_MIN_SESSIONS = 2;

    public function __construct()
    {
        $this->attendanceModel = new IntervisionAttendance();
        $this->groupModel = new IntervisionGroup();
        $this->sessionModel = new IntervisionSession();
        $this->participantModel = new IntervisionParticipant();
    }

    /**
     * Проверить, может ли психолог консультировать
     */
    public function canConsult(int $psychologistId): bool
    {
        $year = (int)date('Y');
        $month = (int)date('m');

        $attendedCount = $this->attendanceModel->countMonthlyAttendance(
            $psychologistId,
            $year,
            $month
        );

        return $attendedCount >= $this->getMinRequiredSessions();
    }

    /**
     * Получить статус интервизий психолога
     */
    public function getPsychologistStatus(int $psychologistId): array
    {
        $year = (int)date('Y');
        $month = (int)date('m');

        $attendedCount = $this->attendanceModel->countMonthlyAttendance(
            $psychologistId,
            $year,
            $month
        );

        $required = $this->getMinRequiredSessions();
        $remaining = max(0, $required - $attendedCount);

        return [
            'can_consult' => $attendedCount >= $required,
            'attended_this_month' => $attendedCount,
            'required_per_month' => $required,
            'remaining' => $remaining,
            'month' => $month,
            'year' => $year,
            'groups' => $this->participantModel->getGroupsByPsychologist($psychologistId)
        ];
    }

    /**
     * Создать сессию и записи посещаемости
     */
    public function createSession(array $data): int
    {
        $sessionId = $this->sessionModel->create($data);
        $this->attendanceModel->createForSession($sessionId, $data['group_id']);
        return $sessionId;
    }

    /**
     * Получить статистику группы
     */
    public function getGroupStats(int $groupId): array
    {
        $group = $this->groupModel->findWithParticipants($groupId);

        $sql = "SELECT
                COUNT(*) as total_sessions,
                SUM(CASE WHEN status = 'COMPLETED' THEN 1 ELSE 0 END) as completed_sessions,
                SUM(CASE WHEN status = 'SCHEDULED' AND scheduled_at > NOW() THEN 1 ELSE 0 END) as upcoming_sessions
                FROM intervision_sessions
                WHERE group_id = :group_id";
        $stats = Database::queryOne($sql, ['group_id' => $groupId]);

        return [
            'group' => $group,
            'total_sessions' => (int)($stats['total_sessions'] ?? 0),
            'completed_sessions' => (int)($stats['completed_sessions'] ?? 0),
            'upcoming_sessions' => (int)($stats['upcoming_sessions'] ?? 0),
            'participants_count' => count($group['participants'] ?? [])
        ];
    }

    /**
     * Получить минимальное требуемое количество сессий
     */
    public function getMinRequiredSessions(): int
    {
        $sql = "SELECT setting_value FROM intervision_settings
                WHERE setting_key = 'min_monthly_sessions'";
        $result = Database::queryOne($sql);

        return $result ? (int)$result['setting_value'] : self::DEFAULT_MIN_SESSIONS;
    }
}
