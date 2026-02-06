<?php
namespace App\Models;

use Core\Model;
use Core\Database;

/**
 * ProblemType - модель типов проблем
 */
class ProblemType extends Model
{
    protected string $table = 'problem_types';

    /**
     * Получить все активные типы проблем
     */
    public function getActive(): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE is_active = 1 ORDER BY sort_order ASC";
        return Database::query($sql);
    }

    /**
     * Найти по slug
     */
    public function findBySlug(string $slug): ?array
    {
        return $this->where('slug', $slug);
    }
}
