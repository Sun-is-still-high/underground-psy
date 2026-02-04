<?php
namespace App\Models;

use Core\Model;
use Core\Database;

class IntervisionParticipant extends Model
{
    protected string $table = 'intervision_participants';

    /**
     * Добавить психолога в группу
     */
    public function addToGroup(int $groupId, int $psychologistId): int
    {
        $existing = $this->findByGroupAndPsychologist($groupId, $psychologistId);

        if ($existing) {
            $this->update($existing['id'], [
                'is_active' => true,
                'left_at' => null
            ]);
            return $existing['id'];
        }

        return $this->create([
            'group_id' => $groupId,
            'psychologist_id' => $psychologistId
        ]);
    }

    /**
     * Удалить психолога из группы (soft delete)
     */
    public function removeFromGroup(int $groupId, int $psychologistId): bool
    {
        $sql = "UPDATE {$this->table}
                SET is_active = 0, left_at = NOW()
                WHERE group_id = :group_id AND psychologist_id = :psychologist_id";
        return Database::execute($sql, [
            'group_id' => $groupId,
            'psychologist_id' => $psychologistId
        ]);
    }

    /**
     * Найти участие по группе и психологу
     */
    public function findByGroupAndPsychologist(int $groupId, int $psychologistId): ?array
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE group_id = :group_id AND psychologist_id = :psychologist_id";
        return Database::queryOne($sql, [
            'group_id' => $groupId,
            'psychologist_id' => $psychologistId
        ]);
    }

    /**
     * Проверить, является ли психолог участником группы
     */
    public function isInGroup(int $groupId, int $psychologistId): bool
    {
        $sql = "SELECT 1 FROM {$this->table}
                WHERE group_id = :group_id
                AND psychologist_id = :psychologist_id
                AND is_active = 1";
        return Database::queryOne($sql, [
            'group_id' => $groupId,
            'psychologist_id' => $psychologistId
        ]) !== null;
    }

    /**
     * Получить всех активных участников группы
     */
    public function getActiveByGroup(int $groupId): array
    {
        $sql = "SELECT ip.*, u.name, u.email
                FROM {$this->table} ip
                JOIN users u ON ip.psychologist_id = u.id
                WHERE ip.group_id = :group_id AND ip.is_active = 1
                ORDER BY ip.joined_at";
        return Database::query($sql, ['group_id' => $groupId]);
    }

    /**
     * Получить все группы психолога
     */
    public function getGroupsByPsychologist(int $psychologistId): array
    {
        $sql = "SELECT ig.*, ip.joined_at
                FROM intervision_groups ig
                JOIN {$this->table} ip ON ig.id = ip.group_id
                WHERE ip.psychologist_id = :psychologist_id
                AND ip.is_active = 1 AND ig.is_active = 1
                ORDER BY ig.name";
        return Database::query($sql, ['psychologist_id' => $psychologistId]);
    }
}
