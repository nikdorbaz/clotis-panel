<?php

$quantities = $result['quantities'] ?? [];
$products = $result['products'] ?? [];
$clients = $result['clients'] ?? [];

$quantityMap = [];
foreach ($quantities as $q) {
  $quantityMap[$q['product_id']][$q['client_id']] = $q;
}

$clientsCount = count($clients);

?>

<table class="spreadsheet" id="ordini">
  <tbody>
    <tr class="fixed-row">
      <td class="fixed-col"><input type="text" placeholder="Поиск..." data-row-index="0"></td>
      <td class="fixed-col">Art.</td>
      <td class="fixed-col">Totale metri ordinati</td>
      <td class="fixed-col">Totale metri prenotati</td>
      <td class="fixed-col">Description</td>
      <td class="fixed-col">Price</td>
      <?php foreach ($clients as $i => $client): ?>
        <td><?= esc($client['uniq_id']); ?></td>
      <?php endforeach; ?>
    </tr>
    <tr class="fixed-row">
      <td class="fixed-col"></td>
      <td class="fixed-col"><input type="text" placeholder="Поиск..." data-col-index="1"></td>
      <td class="fixed-col"></td>
      <td class="fixed-col"></td>
      <td class="fixed-col"></td>
      <td class="fixed-col"></td>
      <?php foreach ($clients as $client): ?>
        <td><?= esc($client['country'] ?? "") ?></td>
      <?php endforeach; ?>
    </tr>
    <tr class="fixed-row">
      <td colspan="6" class="fixed-col">Note</td>
      <?php foreach ($clients as $client): ?>
        <td><?= esc($client['note'] ?? ""); ?></td>
      <?php endforeach; ?>
    </tr>
    <?php foreach ($products as $product): ?>
      <?php
      $files = json_decode($product['files'], true);
      $firstImg = '';

      if (!empty($files)) {
        foreach ($files as $file) {
          // проверяем, что это картинка
          if (preg_match('/\.(jpe?g|png|gif|webp)$/i', $file)) {
            $firstImg = $file;
            break;
          }
        }
      }
      ?>
      <tr>
        <td class="fixed-col">
          <div class="tooltip-img"
            data-img="<?= esc($firstImg) ?>">
            <?= esc($product['name']) ?>
          </div>
        </td>
        <td class="fixed-col"><?= esc($product['sku']) ?></td>
        <td class="fixed-col"></td>
        <td class="fixed-col"></td>
        <td class="fixed-col"><?= esc($product['description']) ?></td>
        <td class="fixed-col"><?= esc($product['price_1']) ?> €</td>
        <?php foreach ($clients as $client): ?>
          <?php
          $pid = $product['id'];
          $cid = $client['chat_id'];

          $entry = $quantityMap[$pid][$cid] ?? [
            'value'    => 0,
            'booked'   => 0,
            'actually' => ''
          ];

          $value    = (int)$entry['value'];
          $booked   = (int)$entry['booked'];
          $actually = $entry['actually'];


          $text = '';
          if ($actually !== '') {
            $text = $actually;
          } else {
            if ($value > 0) {
              $text = $value;
            }
            if ($booked > 0) {
              $text .= ' + Booking ' . $booked;
            }
          }

          ?>
          <td
            data-product_id="<?= $pid ?>"
            data-client_id="<?= $cid ?>"
            data-value="<?= esc($value) ?>"
            data-booked="<?= esc($booked) ?>"
            data-actually="<?= esc($actually) ?>"
            data-cell="true"
            class="<?= ($actually !== '') ? "changed" : "" ?>"
            <?= ($text === '' || !empty($campaign))  ? "" : "contenteditable" ?>><?= esc($text) ?></td>
        <?php endforeach; ?>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<style>
  .fixed-row,
  .fixed-col {
    position: sticky;
  }

  .tooltip-img {
    position: relative;
    display: inline-block;
    cursor: pointer;
  }

  .tooltip-preview {
    position: fixed;
    max-width: 200px;
    border: 1px solid #ccc;
    background: #fff;
    padding: 5px;
    z-index: 100;
  }
</style>

<script>
  // === Подсчёт Totale metri ordinati ===
  function setupTotals(
    tableId,
    totalColIndex,
    startDataColIndex,
    startDataRowIndex = 0,
    type = ""
  ) {
    const table = document.getElementById(tableId);
    if (!table) return;

    const rows = table.querySelectorAll("tbody tr");

    rows.forEach((row, key) => {
      if (key < startDataRowIndex) return;

      const cells = row.querySelectorAll("td");
      let sum = 0;

      // начальный подсчёт
      for (let i = startDataColIndex; i < cells.length; i++) {
        const cell = cells[i];
        let value = 0;

        if (type === "booking") {
          const match = cell.textContent.match(/\+\s*Booking\s*(\d+)/i);
          if (match) value = parseFloat(match[1]);
        } else {
          value = parseFloat(cell.textContent);
        }

        if (!isNaN(value)) sum += value;
      }

      cells[totalColIndex].textContent = sum.toFixed(2);

      // динамический пересчёт
      for (let i = startDataColIndex; i < cells.length; i++) {
        const cell = cells[i];

        const recalculateSum = () => {
          let newSum = 0;
          for (let j = startDataColIndex; j < cells.length; j++) {
            const targetCell = cells[j];
            let val = 0;

            if (type === "booking") {
              const m = targetCell.textContent.match(/\+\s*Booking\s*(\d+)/i);
              if (m) val = parseFloat(m[1].replace(',', '.'));
            } else {
              val = parseFloat(targetCell.textContent.replace(',', '.'));
            }

            if (!isNaN(val)) newSum += val;
          }
          cells[totalColIndex].textContent = newSum.toFixed(2);
        };

        cell.addEventListener("input", recalculateSum);

        // отслеживание изменений
        let previousContent = "";
        cell.addEventListener("focus", () => {
          previousContent = cell.innerText.trim();
        });

        cell.addEventListener("blur", () => {
          const newContent = cell.innerText.trim();

          console.log(newContent, previousContent);
          // if (newContent !== previousContent) {
          cell.classList.add("changed");
          apiRequest(cell.dataset.product_id, cell.dataset.client_id, newContent);
          // }
          recalculateSum();
        });
      }
    });
  }

  const apiRequest = async (product_id, client_id, value) => {
    let url = "<?= getenv('API_URL') ?>/api/v1/update/actually";
    const xhr = new XMLHttpRequest();
    const formData = new FormData();

    xhr.open("POST", url, true);
    xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");

    formData.append("product_id", product_id);
    formData.append("client_id", client_id);
    formData.append("value", value);
    formData.append('stock_id', "<?= $stock['id'] ?>");

    xhr.send(formData);
  };

  // Пример использования:
  window.addEventListener("load", () => {
    if (window.innerWidth > 767) {
      applyStickyTable("ordini", 3, 6);
    }
    setupTotals("ordini", 2, 6, 3, "size");
    setupTotals("ordini", 3, 6, 3, "booking");

    fixColumnWidths('ordini');


    document.querySelectorAll('input[data-col-index]').forEach(input => {
      input.addEventListener('input', function() {
        const colIndex = parseInt(this.dataset.colIndex);
        const searchValue = this.value.trim().toLowerCase();

        const rows = document.querySelectorAll("table#ordini tbody tr");

        rows.forEach((row, index) => {
          if (index < 3) {
            row.style.display = '';
            return;
          }

          const cells = row.querySelectorAll("td");
          const cellText = cells[colIndex].textContent.trim().toLowerCase();

          if (!searchValue.length) {
            row.classList.remove('hidden');
          } else if (cellText === searchValue) {
            row.classList.remove('hidden');
          } else {
            row.classList.add('hidden');
          }
        });
      });
    });

    document.querySelectorAll('input[data-row-index]').forEach(input => {
      input.addEventListener('input', function() {
        const searchValue = this.value.trim().toLowerCase();

        const table = document.querySelector("#ordini");
        if (!table) return;
        const rows = Array.from(table.querySelectorAll("tbody tr"));
        if (!rows.length) return;

        // Header (нулевая) строка
        const headerRow = rows[0];
        const headerCells = Array.from(headerRow.querySelectorAll('td, th'));

        // 1) Узнаём общее кол-во логических колонок (с учётом colspan)
        let totalCols = 0;
        headerCells.forEach(cell => {
          totalCols += parseInt(cell.getAttribute('colspan') || "1", 10);
        });

        // 2) Разворачиваем заголовочные ячейки в массив текстов длиной totalCols
        const headerTexts = new Array(totalCols);
        let ptr = 0;
        headerCells.forEach(cell => {
          const span = parseInt(cell.getAttribute('colspan') || "1", 10);
          const text = cell.textContent.trim().toLowerCase();
          for (let i = 0; i < span; i++) {
            headerTexts[ptr++] = text;
          }
        });

        // 3) Вычисляем видимые логические индексы колонок
        const visible = new Set();
        if (!searchValue) {
          for (let i = 0; i < totalCols; i++) visible.add(i);
        } else {
          // всегда показываем фиксированные колонки 0..5 (если они существуют)
          for (let i = 0; i <= 5 && i < totalCols; i++) visible.add(i);
          // и те, где в headerTexts есть искомая строка (начиная с логического индекса 6)
          for (let i = 6; i < totalCols; i++) {
            if (headerTexts[i] && headerTexts[i].includes(searchValue)) visible.add(i);
          }
        }

        // 4) Проходим по всем строкам и показываем/скрываем ячейки с учётом colspan
        rows.forEach((row, rowIndex) => {
          const cells = Array.from(row.querySelectorAll('td, th'));
          let colIndex = 0; // текущий логический индекс для ячейки
          let hasVisibleCellWithData = false;

          cells.forEach(cell => {
            const span = parseInt(cell.getAttribute('colspan') || "1", 10);
            // проверяем, есть ли пересечение диапазона [colIndex, colIndex+span-1] с visible
            let show = false;
            for (let k = colIndex; k < colIndex + span; k++) {
              if (visible.has(k)) {
                show = true;
                break;
              }
            }

            cell.style.display = show ? '' : 'none';

            // для решения, нужно ли скрыть строку — учитываем только видимые ячейки
            if (show) {
              // в первых 3 строках держим их всегда видимыми (мы прячем отдельные ячейки, но строка остаётся)
              if (rowIndex < 3) {
                hasVisibleCellWithData = true;
              } else {
                if (cell.textContent && cell.textContent.trim() !== '') {
                  hasVisibleCellWithData = true;
                }
              }
            }

            colIndex += span;
          });

          // Решаем видимость строки
          if (rowIndex < 3) {
            row.style.display = ''; // первые 3 строки всегда показываем (шапка/фильтры/примечания)
          } else {
            // если все колонки видимы — показываем все строки
            if (visible.size === totalCols) {
              row.style.display = '';
            } else {
              row.style.display = hasVisibleCellWithData ? '' : 'none';
            }
          }
        });
      });
    });



  });

  function fixColumnWidths(tableId) {
    const table = document.getElementById(tableId);
    const firstRow = table.querySelector("tbody tr:nth-child(4)");
    if (!firstRow) return;

    const cells = firstRow.querySelectorAll("td");

    cells.forEach((cell, i) => {
      const width = cell.offsetWidth + "px";
      const selector = `tbody td:nth-child(${i + 1}), thead th:nth-child(${i + 1})`;

      document.querySelectorAll(`#${tableId} ${selector}`).forEach(target => {
        target.style.minWidth = width;
        target.style.maxWidth = width;
        target.style.overflow = "hidden";
      });
    });
  }

  document.addEventListener('DOMContentLoaded', () => {
    const preview = document.createElement('img');
    preview.className = 'tooltip-preview';
    document.body.appendChild(preview);

    document.querySelectorAll('.tooltip-img').forEach(el => {
      el.addEventListener('mouseenter', (e) => {
        const src = el.dataset.img;
        if (src) {
          preview.src = src;
          preview.style.display = 'block';
          positionPreview(e);
        }
      });
      el.addEventListener('mousemove', positionPreview);
      el.addEventListener('mouseleave', () => {
        preview.style.display = 'none';
      });
    });

    function positionPreview(e) {
      preview.style.top = (e.clientY + 15) + 'px';
      preview.style.left = (e.clientX + 15) + 'px';
    }
  });
</script>