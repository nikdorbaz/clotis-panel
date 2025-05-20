<?= view('header') ?>

<body class="table">
  <h1><?= date('d-m-Y') ?></h1>

  <div class="table-wrapper">
    <?= view("table/$type") ?>
  </div>

  <div class="tabs">
    <a href="?type=ordini" class="<?= ($type == 'ordini') ? 'active' : '' ?>">Ordini</a>
    <a href="?type=spedizione" class="<?= ($type == 'spedizione') ? 'active' : '' ?>">Spedizione</a>
  </div>
</body>

</html>