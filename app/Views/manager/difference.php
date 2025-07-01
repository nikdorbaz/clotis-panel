<?php

$values = $result['values'] ?? [];
$stocks = $result['stocks'] ?? [];


$totals = [];

?>

<?= view('styles'); ?>
<?= view('manager/header') ?>

<body class="table">

  <div class="table-wrapper">
    <table class="spreadsheet" id="sales">
      <tbody>
        <? foreach ($values as $k => $row): ?>
          <tr class="<?= (!$k) ? "fixed-row" : "" ?>">
            <? foreach ($row as $i => $value): ?>
              <td><?= $value ?></td>

              <?php
              $totals[$i] = ($totals[$i] ?? 0) + (float)$value; ?>
            <? endforeach; ?>
          </tr>
        <? endforeach; ?>
        <tr>
          <td colspan="2">Totale</td>
          <?php foreach ($totals as $i => $total): ?>
            <? if ($i < 2) {
              continue;
            } ?>
            <td><strong><?= $total ?></strong></td>
          <?php endforeach; ?>
        </tr>
      </tbody>
    </table>

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

  <?= view('footer'); ?>
</body>

<script>
  // Пример использования:
  window.addEventListener(" load", () => {
    if (window.innerWidth > 767) {
      applyStickyTable('sales', 1, 4);
    }
  });

  window.addEventListener('resize', () => {
    if (window.innerWidth > 767) {
      applyStickyTable('sales', 1, 4);
    }
  });
</script>