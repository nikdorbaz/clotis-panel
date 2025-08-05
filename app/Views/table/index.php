<?= view('styles') ?>

<?= view('table/header') ?>

<body class="table">

  <div class="table-wrapper">
    <?= view("table/$type") ?>
  </div>

  <div class="tabs">
    <a href="/stock/ordini" class="<?= ($type == 'ordini') ? 'active' : '' ?>">Ordini</a>
    <a href="/stock/spedizione" class="<?= ($type == 'spedizione') ? 'active' : '' ?>">Spedizione</a>
  </div>

  <?= view('footer'); ?>
</body>

</html>