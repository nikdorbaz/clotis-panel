<?php

$stocks = $result['stocks'] ?? [];
$data = $result['data'] ?? [];

?>

<?= view('styles'); ?>
<?= view('manager/header') ?>


<style>
    .month-tables {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        align-items: flex-start;
    }
</style>

<body class="table">


    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= esc($error) ?></div>
    <?php else: ?>
        <div class="table-wrapper">
            <div class="month-tables">
                <?php foreach ($data as $monthBlock): ?>
                    <table class="month-table spreadsheet">
                        <tbody>
                            <tr class="fixed-row">
                                <td colspan="3">
                                    <?= date('F Y', strtotime($monthBlock['month'] . '-01')) ?>
                                    <br>
                                    (Итого: <?= number_format($monthBlock['total'], 2, '.', ' ') ?>)
                                </td>
                            </tr>
                            <tr class="fixed-row">
                                <td>Дата</td>
                                <td>Сумма</td>
                                <td>Склад</td>
                            </tr>
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
            </div>
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
</body>

<?= view('footer'); ?>