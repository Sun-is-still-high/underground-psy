<?php
namespace App\Models;

use Core\Model;
use Core\Database;

class IntervisionGroup extends Model
{
    protected string $table = 'intervision_groups';

    /**
     * Получить все активные группы с количеством участников
     */
    public function getAllActive(): array
    {
        $sql = "SELECT ig.*, u.name as creator_name,
                (SELECT COUNT(*) FROM intervision_participants ip
                 WHERE ip.group_id = ig.id AND ip.is_active = 1) as participants_count
                FROM {$this->table} ig
                JOIN users u ON ig.created_by = u.id
                WHERE ig.is_active = 1
                ORDER BY ig.created_at DESC";
        return Database::query($sql);
    }

    /**
     * Получить группу с участниками
     */
    public function findWithParticipants(int $id): ?array
    {
        $group = $this->find($id);
        if (!$group) {
            return null;
        }

        $sql = "SELECT ip.*, u.name, u.email
                FROM intervision_participants ip
                JOIN users u ON ip.psychologist_id = u.id
                WHERE ip.group_id = :group_id AND ip.is_active = 1
                ORDER BY ip.joined_at";
        $group['participants'] = Database::query($sql, ['group_id' => $id]);

        return $group;
    }

    /**
     * Проверить, есть ли свободные места в группе
     */
    public function hasAvailableSpots(int $groupId): bool
    {
        $sql = "SELECT ig.max_participants,
                (SELECT COUNT(*) FROM intervision_participants ip
                 WHERE ip.group_id = ig.id AND ip.is_active = 1) as current_count
                FROM {$this->table} ig WHERE ig.id = :id";
        $result = Database::queryOne($sql, ['id' => $groupId]);

        if (!$result) {
            return false;
        }
        return $result['current_count'] < $result['max_participants'];
    }

    /**
     * Деактивировать группу (soft delete)
     */
    public function deactivate(int $id): bool
    {
        return $this->update($id, ['is_active' => false]);
    }
}
