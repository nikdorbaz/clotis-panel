<!DOCTYPE html>
<html>

<head>

  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
  <title>JS Spreadsheet test</title>
  <script src="/js/clipboard.js"></script>
  <script src="/js/spreadsheet.js"></script>
</head>
<link rel="stylesheet" href="/css/spreadsheet.css">
<link rel="stylesheet" href="/css/style.css">

<body>
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

<style>
  body {
    font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
  }

  h1 {
    color: #4183c4;
  }

  h1 {
    position: fixed;
    background-color: #fff;
    right: 15px;
    top: 0;
    z-index: 1000;
    margin: 0;
  }

  .tabs {
    display: flex;
    position: fixed;
    padding: 5px;
    bottom: 0;
    align-items: center;
    width: 100%;
    z-index: 1001;
    gap: 10px;
    background-color: #eee;
  }

  .tabs a {
    padding: 6px 12px;
    background: #e0e0e0;
    border-radius: 6px;
    transition: background 0.2s;
  }

  .tabs a:hover,
  .tabs a.active {
    background-color: #2cb1b6;
    color: #fff;
  }
</style>

</html>