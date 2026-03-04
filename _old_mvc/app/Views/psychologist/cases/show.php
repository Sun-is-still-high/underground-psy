<?php
$title = htmlspecialchars($case['title']) . ' - Underground Psy';
$old = \Core\Session::get('old') ?? [];
\Core\Session::forget('old');
ob_start();
?>

<div class="container">
    <div class="page-header">
        <div>
            <a href="/psychologist/cases" class="nav-link">&larr; Назад к поиску</a>
            <h1><?= htmlspecialchars($case['title']) ?></h1>
        </div>
        <span class="badge badge-<?= strtolower(str_replace('_', '-', $case['budget_type'])) ?>">
            <?php
            $budgetLabels = [
                'PAID' => 'Платно',
                'REVIEW' => 'Оплата отзывом',
                'NEGOTIABLE' => 'Договорная'
            ];
            echo $budgetLabels[$case['budget_type']] ?? $case['budget_type'];
            ?>
        </span>
    </div>

    <div class="case-details">
        <div class="case-info-card">
            <div class="info-row">
                <span class="label">Клиент:</span>
                <span class="value"><?= htmlspecialchars($case['client_name']) ?></span>
            </div>
            <div class="info-row">
                <span class="label">Тип проблемы:</span>
                <span class="value"><?= htmlspecialchars($case['problem_type_name']) ?></span>
            </div>
            <div class="info-row">
                <span class="label">Дата публикации:</span>
                <span class="value"><?= date('d.m.Y H:i', strtotime($case['created_at'])) ?></span>
            </div>
            <?php if ($case['budget_type'] === 'PAID' && $case['budget_amount']): ?>
                <div class="info-row">
                    <span class="label">Бюджет:</span>
                    <span class="value"><?= number_format($case['budget_amount'], 0, '', ' ') ?> руб.</span>
                </div>
            <?php endif; ?>
            <div class="info-row">
                <span class="label">Статус:</span>
                <span class="value">
                    <?php
                    $statusLabels = [
                        'OPEN' => 'Открыт',
                        'IN_PROGRESS' => 'В работе',
                        'CLOSED' => 'Закрыт',
                        'CANCELLED' => 'Отменён'
                    ];
                    echo $statusLabels[$case['status']] ?? $case['status'];
                    ?>
                </span>
            </div>
        </div>

        <div class="description-box">
            <h3>Описание проблемы</h3>
            <p><?= nl2br(htmlspecialchars($case['description'])) ?></p>
        </div>

        <?php if ($case['status'] === 'OPEN'): ?>
            <?php if ($hasResponded): ?>
                <div class="alert alert-success">
                    <p><strong>Вы уже откликнулись на этот кейс</strong></p>
                    <p>Ожидайте ответа от клиента</p>
                </div>
            <?php else: ?>
                <div class="section">
                    <h2>Откликнуться на кейс</h2>
                    <div class="form-container">
                        <form action="/psychologist/cases/<?= $case['id'] ?>/respond" method="POST" class="auth-form">
                            <div class="form-group">
                                <label for="message">Ваше сообщение клиенту *</label>
                                <textarea name="message" id="message" class="form-control" rows="5"
                                          placeholder="Представьтесь, расскажите о своём опыте работы с подобными случаями, предложите формат работы..."
                                          required><?= htmlspecialchars($old['message'] ?? '') ?></textarea>
                                <small>Это сообщение увидит клиент. Постарайтесь показать, что вы понимаете его проблему.</small>
                            </div>

                            <?php if ($case['budget_type'] !== 'REVIEW'): ?>
                                <div class="form-group">
                                    <label for="proposed_price">Ваша цена за консультацию (руб.)</label>
                                    <input type="number" name="proposed_price" id="proposed_price" class="form-control"
                                           placeholder="Оставьте пустым для договорной цены" min="0" step="100"
                                           value="<?= htmlspecialchars($old['proposed_price'] ?? '') ?>">
                                    <small>Необязательно. Можете указать вашу стандартную стоимость или оставить пустым.</small>
                                </div>
                            <?php endif; ?>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">Отправить отклик</button>
                                <a href="/psychologist/cases" class="btn btn-outline">Отмена</a>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="alert alert-warning">
                <p>Этот кейс уже не принимает отклики (статус: <?= $statusLabels[$case['status']] ?? $case['status'] ?>)</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/main.php';
?>
