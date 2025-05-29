<?php

$values = $result['values'] ?? [];
$stocks = $result['stocks'] ?? [];

?>

<?= view('styles'); ?>
<?= view('manager/header') ?>

<body class="table">

  <div class="table-wrapper">
    <table class="spreadsheet" id="sales">
      <tbody>
        <? foreach ($values as $k => $row): ?>
          <tr class="<?= (!$k) ? "fixed-row" : "" ?>">
            <? foreach ($row as $value): ?>
              <td><?= $value ?></td>
            <? endforeach; ?>
          </tr>
        <? endforeach; ?>
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