<script>
  const salesData = <?= json_encode($result ?? [], JSON_UNESCAPED_UNICODE) ?>;
  const headers = salesData[0];
  const rows = salesData;

  const div = document.getElementById("data-table");
  const inputElement = document.getElementById("data-table-input");

  const table = spreadsheet.createTable({
    rows: rows.length,
    columns: headers.length,
    textInputElement: inputElement,
    headers,
    filterTypes: [],
    data: rows,
    fixedRows: 1,
    fixedCols: 2,
    computedColumns: [4, 5, 6],
  });

  div.appendChild(table.element);
</script>