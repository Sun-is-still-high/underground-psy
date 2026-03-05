<?php
namespace App\Models;

use Core\Model;
use Core\Database;

class PublicQuestion extends Model
{
    protected string $table = 'public_questions';

    /**
     * Получить все отвеченные вопросы для публичной страницы
     */
    public function getAnswered(int $limit = 20, int $offset = 0): array
    {
        $sql = "SELECT pq.*, pa.answer, pa.created_at as answered_at,
                       u.name as psychologist_name, pp.photo_url as psychologist_photo
                FROM {$this->table} pq
                JOIN public_answers pa ON pa.question_id = pq.id
                JOIN users u ON pa.psychologist_id = u.id
                LEFT JOIN psychologist_profiles pp ON pp.user_id = u.id
                WHERE pq.status = 'ANSWERED'
                ORDER BY pq.created_at DESC
                LIMIT :limit OFFSET :offset";

        return Database::query($sql, ['limit' => $limit, 'offset' => $offset]);
    }

    /**
     * Получить вопросы, ожидающие ответа (для дашборда психолога)
     */
    public function getPending(): array
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE status = 'PENDING'
                ORDER BY created_at ASC";

        return Database::query($sql, []);
    }

    /**
     * Получить вопрос по ID с ответами
     */
    public function getWithAnswers(int $id): ?array
    {
        $question = $this->find($id);
        if (!$question) {
            return null;
        }

        $answers = Database::query(
            "SELECT pa.*, u.name as psychologist_name, pp.photo_url as psychologist_photo
             FROM public_answers pa
             JOIN users u ON pa.psychologist_id = u.id
             LEFT JOIN psychologist_profiles pp ON pp.user_id = u.id
             WHERE pa.question_id = :qid
             ORDER BY pa.created_at ASC",
            ['qid' => $id]
        );

        $question['answers'] = $answers;
        return $question;
    }

    /**
     * Добавить ответ психолога и обновить статус вопроса
     */
    public function addAnswer(int $questionId, int $psychologistId, string $answer): void
    {
        Database::execute(
            "INSERT INTO public_answers (question_id, psychologist_id, answer) VALUES (:qid, :pid, :answer)",
            ['qid' => $questionId, 'pid' => $psychologistId, 'answer' => $answer]
        );

        Database::execute(
            "UPDATE {$this->table} SET status = 'ANSWERED' WHERE id = :id",
            ['id' => $questionId]
        );
    }

    /**
     * Уже ответил ли этот психолог на вопрос
     */
    public function hasAnswered(int $questionId, int $psychologistId): bool
    {
        $row = Database::queryOne(
            "SELECT id FROM public_answers WHERE question_id = :qid AND psychologist_id = :pid",
            ['qid' => $questionId, 'pid' => $psychologistId]
        );
        return $row !== null;
    }
}
