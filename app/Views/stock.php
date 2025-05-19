<?php

$type = gettype('type') ?? "ordini";

?>
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
<style>
  body {
    font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
  }

  h1 {
    color: #4183c4;
  }
</style>

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
<script>
  const salesData = <?= json_encode($result ?? [], JSON_UNESCAPED_UNICODE) ?>;
  const headers = salesData[0];
  const rows = salesData;

  const filterTypes = ['text', 'text', 'text', null, null]; // фильтры по колонкам
  const columnSumTargets = {
    from: salesData[0].length - 2,
    to: salesData[0].length - 1
  };

  const div = document.getElementById("data-table");
  const inputElement = document.getElementById("data-table-input");

  const table = spreadsheet.createTable({
    rows: rows.length,
    columns: headers.length,
    textInputElement: inputElement,
    headers,
    filterTypes,
    data: rows,
    fixedRows: 3,
    fixedCols: 5,
    computedColumns: [salesData[0].length - 1, salesData[0].length - 2],
    custom: [{
        targetType: 'row',
        target: salesData[0].length - 2,
        targetItems: {
          from: 5,
          to: salesData[0].length - 3
        },
        calculate: (values) => {
          let total = 0;
          values.forEach((value) => {
            if (!value.includes("+ Booking")) {
              const match = value.match(/[\d.,]+/);
              const val = match ? parseFloat(match[0].replace(",", ".")) : NaN;
              if (!isNaN(val)) total += val;
            }
          })
          return total;
        },
      },
      {
        targetType: 'row',
        target: salesData[0].length - 1,
        targetItems: {
          from: 5,
          to: salesData[0].length - 2
        },
        calculate: (values, {
          row
        }) => {
          let total = 0;
          values.forEach((value) => {
            if (value.includes("+ Booking")) {
              const match = value.match(/[\d.,]+/);
              const val = match ? parseFloat(match[0].replace(",", ".")) : NaN;
              if (!isNaN(val)) total += val;
            }
          })
          return total;
        },
      },
    ],
  });

  div.appendChild(table.element);
</script>

<style>
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