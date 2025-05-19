<?= view('header') ?>

<body class="table">
  <h1><?= date('d-m-Y') ?></h1>

  <div id="data-table">
    <input type="text" id="data-table-input">
  </div>
  <div class="tabs">
    <a href="?type=ordini" class="<?= ($type == 'ordini') ? 'active' : '' ?>">Ordini</a>
    <a href="?type=spedizione" class="<?= ($type == 'spedizione') ? 'active' : '' ?>">Spedizione</a>
  </div>

</body>

<?= view("table/$type") ?>

</html>