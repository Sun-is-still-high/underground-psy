<?php
namespace App\Models;

use Core\Model;
use Core\Database;

/**
 * ClientCase - модель кейсов клиентов
 * (назван ClientCase чтобы избежать конфликта с ключевым словом case)
 */
class ClientCase extends Model
{
    protected string $table = 'cases';

    /**
     * Создать новый кейс
     */
    public function createCase(array $data): int
    {
        return $this->create($data);
    }

    /**
     * Получить кейсы клиента
     */
    public function getByClientId(int $clientId): array
    {
        $sql = "SELECT c.*, pt.name as problem_type_name, pt.slug as problem_type_slug,
                       (SELECT COUNT(*) FROM case_responses WHERE case_id = c.id) as responses_count
                FROM {$this->table} c
                JOIN problem_types pt ON c.problem_type_id = pt.id
                WHERE c.client_id = :client_id
                ORDER BY c.created_at DESC";
        return Database::query($sql, ['client_id' => $clientId]);
    }

    /**
     * Поиск открытых кейсов с фильтрами (для психологов)
     */
    public function searchOpen(array $filters = []): array
    {
        $sql = "SELECT c.*, pt.name as problem_type_name, pt.slug as problem_type_slug,
                       CASE WHEN c.is_anonymous = 1 THEN 'Аноним' ELSE u.name END as client_name
                FROM {$this->table} c
                JOIN problem_types pt ON c.problem_type_id = pt.id
                JOIN users u ON c.client_id = u.id
                WHERE c.status = 'OPEN'";

        $params = [];

        // Фильтр по типу проблемы
        if (!empty($filters['problem_type_id'])) {
            $sql .= " AND c.problem_type_id = :problem_type_id";
            $params['problem_type_id'] = $filters['problem_type_id'];
        }

        // Фильтр по типу оплаты
        if (!empty($filters['budget_type'])) {
            $sql .= " AND c.budget_type = :budget_type";
            $params['budget_type'] = $filters['budget_type'];
        }

        $sql .= " ORDER BY c.created_at DESC";

        // Лимит
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
        }

        return Database::query($sql, $params);
    }

    /**
     * Получить кейс с деталями
     */
    public function getWithDetails(int $id): ?array
    {
        $sql = "SELECT c.*, pt.name as problem_type_name, pt.slug as problem_type_slug,
                       CASE WHEN c.is_anonymous = 1 THEN 'Аноним' ELSE u.name END as client_name,
                       u.id as client_id
                FROM {$this->table} c
                JOIN problem_types pt ON c.problem_type_id = pt.id
                JOIN users u ON c.client_id = u.id
                WHERE c.id = :id";
        return Database::queryOne($sql, ['id' => $id]);
    }

    /**
     * Проверить, откликался ли психолог на кейс
     */
    public function hasResponse(int $caseId, int $psychologistId): bool
    {
        $sql = "SELECT COUNT(*) as cnt FROM case_responses
                WHERE case_id = :case_id AND psychologist_id = :psychologist_id";
        $result = Database::queryOne($sql, [
            'case_id' => $caseId,
            'psychologist_id' => $psychologistId
        ]);
        return $result && $result['cnt'] > 0;
    }

    /**
     * Добавить отклик психолога
     */
    public function addResponse(int $caseId, int $psychologistId, string $message, ?float $price = null): int
    {
        $sql = "INSERT INTO case_responses (case_id, psychologist_id, message, proposed_price)
                VALUES (:case_id, :psychologist_id, :message, :proposed_price)";
        Database::execute($sql, [
            'case_id' => $caseId,
            'psychologist_id' => $psychologistId,
            'message' => $message,
            'proposed_price' => $price
        ]);
        return (int) Database::lastInsertId();
    }

    /**
     * Получить отклики на кейс
     */
    public function getResponses(int $caseId): array
    {
        $sql = "SELECT cr.*, u.name as psychologist_name, u.email as psychologist_email
                FROM case_responses cr
                JOIN users u ON cr.psychologist_id = u.id
                WHERE cr.case_id = :case_id
                ORDER BY cr.created_at DESC";
        return Database::query($sql, ['case_id' => $caseId]);
    }

    /**
     * Принять отклик
     */
    public function acceptResponse(int $responseId, int $caseId): bool
    {
        // Отклоняем все остальные отклики
        $sql = "UPDATE case_responses SET status = 'REJECTED', responded_at = NOW()
                WHERE case_id = :case_id AND id != :response_id AND status = 'PENDING'";
        Database::execute($sql, ['case_id' => $caseId, 'response_id' => $responseId]);

        // Принимаем выбранный отклик
        $sql = "UPDATE case_responses SET status = 'ACCEPTED', responded_at = NOW()
                WHERE id = :response_id";
        Database::execute($sql, ['response_id' => $responseId]);

        // Меняем статус кейса
        return $this->update($caseId, ['status' => 'IN_PROGRESS']);
    }

    /**
     * Статистика по типам проблем (для психологов)
     */
    public function getStatsByProblemType(): array
    {
        $sql = "SELECT pt.id, pt.name, pt.slug, COUNT(c.id) as cases_count
                FROM problem_types pt
                LEFT JOIN {$this->table} c ON pt.id = c.problem_type_id AND c.status = 'OPEN'
                WHERE pt.is_active = 1
                GROUP BY pt.id, pt.name, pt.slug
                ORDER BY pt.sort_order ASC";
        return Database::query($sql);
    }
}
