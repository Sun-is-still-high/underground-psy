<?php
$title = htmlspecialchars($case['title']) . ' - Underground Psy';
ob_start();
?>

<div class="container">
    <div class="page-header">
        <div>
            <a href="/client/cases" class="nav-link">&larr; Назад к списку</a>
            <h1><?= htmlspecialchars($case['title']) ?></h1>
        </div>
        <span class="badge badge-<?= strtolower($case['status']) ?>">
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

    <div class="case-details">
        <div class="case-info-card">
            <div class="info-row">
                <span class="label">Тип проблемы:</span>
                <span class="value"><?= htmlspecialchars($case['problem_type_name']) ?></span>
            </div>
            <div class="info-row">
                <span class="label">Дата создания:</span>
                <span class="value"><?= date('d.m.Y H:i', strtotime($case['created_at'])) ?></span>
            </div>
            <div class="info-row">
                <span class="label">Формат оплаты:</span>
                <span class="value">
                    <?php
                    $budgetLabels = [
                        'PAID' => 'Платно' . ($case['budget_amount'] ? ' (' . number_format($case['budget_amount'], 0, '', ' ') . ' руб.)' : ''),
                        'REVIEW' => 'Оплата отзывом',
                        'NEGOTIABLE' => 'Договорная'
                    ];
                    echo $budgetLabels[$case['budget_type']] ?? $case['budget_type'];
                    ?>
                </span>
            </div>
            <div class="info-row">
                <span class="label">Анонимность:</span>
                <span class="value"><?= $case['is_anonymous'] ? 'Да' : 'Нет' ?></span>
            </div>
        </div>

        <div class="description-box">
            <h3>Описание</h3>
            <p><?= nl2br(htmlspecialchars($case['description'])) ?></p>
        </div>

        <?php if ($case['status'] === 'OPEN'): ?>
            <form action="/client/cases/<?= $case['id'] ?>/close" method="POST" style="margin-bottom: 2rem;">
                <button type="submit" class="btn btn-outline btn-sm"
                        onclick="return confirm('Вы уверены, что хотите закрыть этот запрос?')">
                    Закрыть запрос
                </button>
            </form>
        <?php endif; ?>
    </div>

    <div class="section">
        <h2>Отклики психологов (<?= count($responses) ?>)</h2>

        <?php if (empty($responses)): ?>
            <div class="empty-message">
                <p>Пока никто не откликнулся на ваш запрос</p>
                <p>Психологи увидят ваш запрос и смогут предложить свою помощь</p>
            </div>
        <?php else: ?>
            <div class="responses-list">
                <?php foreach ($responses as $response): ?>
                    <div class="response-card <?= $response['status'] === 'ACCEPTED' ? 'response-accepted' : '' ?>">
                        <div class="response-header">
                            <div class="psychologist-info">
                                <strong><?= htmlspecialchars($response['psychologist_name']) ?></strong>
                                <span class="email"><?= htmlspecialchars($response['psychologist_email']) ?></span>
                            </div>
                            <span class="badge badge-<?= strtolower($response['status']) ?>">
                                <?php
                                $responseStatuses = [
                                    'PENDING' => 'Ожидает',
                                    'ACCEPTED' => 'Принят',
                                    'REJECTED' => 'Отклонён'
                                ];
                                echo $responseStatuses[$response['status']] ?? $response['status'];
                                ?>
                            </span>
                        </div>
                        <div class="response-message">
                            <p><?= nl2br(htmlspecialchars($response['message'])) ?></p>
                        </div>
                        <?php if ($response['proposed_price']): ?>
                            <div class="proposed-price">
                                Предложенная цена: <strong><?= number_format($response['proposed_price'], 0, '', ' ') ?> руб.</strong>
                            </div>
                        <?php endif; ?>
                        <div class="response-footer">
                            <span class="date"><?= date('d.m.Y H:i', strtotime($response['created_at'])) ?></span>
                            <?php if ($case['status'] === 'OPEN' && $response['status'] === 'PENDING'): ?>
                                <form action="/client/cases/<?= $case['id'] ?>/accept/<?= $response['id'] ?>" method="POST" style="display: inline;">
                                    <button type="submit" class="btn btn-primary btn-sm"
                                            onclick="return confirm('Принять этот отклик? Остальные отклики будут отклонены.')">
                                        Принять отклик
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/main.php';
?>
