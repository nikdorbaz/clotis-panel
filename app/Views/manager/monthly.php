<?= view('styles'); ?>
<?= view('manager/header') ?>


<style>
    .month-tables {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
    }

    .month-table {
        border: 1px solid #ccc;
        border-collapse: collapse;
        width: 300px;
        font-size: 14px;
    }

    .month-table th,
    .month-table td {
        border: 1px solid #ccc;
        padding: 5px 8px;
        text-align: left;
    }

    .month-table thead tr:first-child th {
        background: #f0f0f0;
        font-weight: bold;
        text-align: center;
    }
</style>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= esc($error) ?></div>
<?php else: ?>
    <div class="month-tables">
        <?php foreach ($result as $monthBlock): ?>
            <table class="month-table">
                <thead>
                    <tr>
                        <th colspan="3">
                            <?= date('F Y', strtotime($monthBlock['month'] . '-01')) ?>
                            <br>
                            (Итого: <?= number_format($monthBlock['total'], 2, '.', ' ') ?>)
                        </th>
                    </tr>
                    <tr>
                        <th>Дата</th>
                        <th>Сумма</th>
                        <th>Склад</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($monthBlock['items'] as $item): ?>
                        <tr>
                            <td><?= date('d.m.Y', strtotime($item['date'])) ?></td>
                            <td><?= number_format($item['price'], 2, '.', ' ') ?></td>
                            <td><?= esc($item['stock']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endforeach; ?>
        <div class="tabs">
            <a href="?stock_id=0" class="<?= ($stock_id == 0) ? "active" : "" ?>">Общая</a>
            <? foreach ($stocks as $stock): ?>
                <a href="?stock_id=<?= $stock['id'] ?>"
                    class="<?= ($stock_id == $stock['id']) ? 'active' : '' ?>">
                    <?= $stock['name'] ?>
                </a>
            <? endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<?= view('footer'); ?>