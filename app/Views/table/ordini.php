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
    fixedCols: 4,
    computedColumns: [salesData[0].length - 1, salesData[0].length - 2],
    custom: [{
        targetType: 'row',
        target: salesData[0].length - 2,
        targetItems: {
          from: 4,
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
          from: 4,
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