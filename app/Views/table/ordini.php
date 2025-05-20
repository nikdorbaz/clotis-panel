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
  <thead>
    <tr class="fixed-row">
      <th class="fixed-col"><input type="text" placeholder="Поиск..." data-col-index="0"></th>
      <th class="fixed-col"><input type="text" placeholder="Поиск..." data-col-index="1"></th>
      <th class="fixed-col"></th>
      <th class="fixed-col"></th>
      <th colspan="<?= $clientsCount ?>"></th>
    </tr>
  </thead>
  <tbody>
    <tr class="fixed-row">
      <td class="fixed-col"></td>
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
      <td class="fixed-col"></td>
      <td class="fixed-col"></td>
      <td class="fixed-col"></td>
      <td class="fixed-col"></td>
      <td class="fixed-col"></td>
      <?php foreach ($clients as $client): ?>
        <td><?= esc($client['country']) ?></td>
      <?php endforeach; ?>
    </tr>
    <tr class="fixed-row">
      <td colspan="6" class="fixed-col">Note</td>
      <?php foreach ($clients as $client): ?>
        <td><?= esc($client['note']); ?></td>
      <?php endforeach; ?>
    </tr>
    <?php foreach ($products as $product): ?>
      <tr>
        <td class="fixed-col"><?= esc($product['name']) ?></td>
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
            class="<?= ($actually) ? "changed" : "" ?>"
            contenteditable><?= esc($text) ?></td>
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
</style>

<script>
  function applyStickyTable(tableId, fixedRow = 1, fixedCol = 1) {
    const table = document.getElementById(tableId);
    if (!table) return;

    // === 1. Обрабатываем строки ===
    let topOffset = 0;
    const rows = table.querySelectorAll("tr");

    for (let r = 0; r < fixedRow; r++) {
      const row = rows[r];
      if (!row) continue;

      row.style.position = "sticky";
      row.style.top = topOffset + "px";
      row.style.zIndex = 6;
      topOffset += row.offsetHeight;
    }

    // === 2. Обрабатываем колонки ===
    const colLeft = [];

    for (let c = 0; c < fixedCol; c++) {
      let maxWidth = 0;
      table.querySelectorAll("tr").forEach(row => {
        let colIndex = 0;
        for (let i = 0; i < row.children.length; i++) {
          const cell = row.children[i];
          const colspan = parseInt(cell.getAttribute("colspan")) || 1;

          if (colspan > 1) {
            colIndex += colspan;
            continue;
          }

          if (colIndex === c) {
            maxWidth = Math.max(maxWidth, cell.offsetWidth);
            break;
          }

          colIndex += 1;
        }
      });

      const left = c === 0 ? 0 : colLeft[c - 1] + colLeft[c - 1 + '_w'];
      colLeft[c] = left;
      colLeft[c + '_w'] = maxWidth;

      table.querySelectorAll("tr").forEach(row => {
        let colIndex = 0;
        for (let i = 0; i < row.children.length; i++) {
          const cell = row.children[i];
          const colspan = parseInt(cell.getAttribute("colspan")) || 1;


          if (!cell.classList.contains('fixed-col')) {
            continue;
          }

          if (colspan > 1) {
            colIndex += colspan;
            cell.style.position = "sticky";
            cell.style.left = 0 + "px";
            cell.style.zIndex = 5;
            continue;
          }

          if (colIndex === c) {
            cell.style.position = "sticky";
            cell.style.left = left + "px";
            cell.style.zIndex = 5;
            break;
          }

          colIndex += 1;
        }
      });
    }
  }

  // === Подсчёт Totale metri ordinati ===
  function setupTotals(tableId, totalColIndex, startDataColIndex, startDataRowIndex = 0, type = '') {
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

        if (type === 'booking') {
          const match = cell.textContent.match(/\+\s*Booking\s*(\d+)/i);
          if (match) value = parseFloat(match[1]);
        } else {
          value = parseFloat(cell.textContent);
        }

        if (!isNaN(value)) sum += value;
      }

      cells[totalColIndex].textContent = sum;

      // динамический пересчёт
      for (let i = startDataColIndex; i < cells.length; i++) {
        const cell = cells[i];

        const recalculateSum = () => {
          let newSum = 0;
          for (let j = startDataColIndex; j < cells.length; j++) {
            const targetCell = cells[j];
            let val = 0;

            if (type === 'booking') {
              const m = targetCell.textContent.match(/\+\s*Booking\s*(\d+)/i);
              if (m) val = parseFloat(m[1]);
            } else {
              val = parseFloat(targetCell.textContent);
            }

            if (!isNaN(val)) newSum += val;
          }
          cells[totalColIndex].textContent = newSum;
        };

        cell.addEventListener("input", recalculateSum);

        // отслеживание изменений
        let previousContent = '';
        cell.addEventListener("focus", () => {
          previousContent = cell.innerText.trim();
        });

        cell.addEventListener("blur", () => {
          const newContent = cell.innerText.trim();
          if (newContent !== previousContent) {
            cell.classList.add("changed");
          }
          recalculateSum();
        });
      }
    });
  }


  // Пример использования:
  window.addEventListener("load", () => {
    applyStickyTable("ordini", 4, 6);
    setupTotals("ordini", 2, 4, 3, 'size');
    setupTotals("ordini", 3, 4, 3, 'booking');
  });
</script>