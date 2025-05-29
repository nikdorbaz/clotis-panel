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
      <td class="fixed-col"><input type="text" placeholder="Поиск..." data-col-index="1"></td>
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
            <?= empty($text) ? "" : "contenteditable" ?>><?= esc($text) ?></td>
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

      cells[totalColIndex].textContent = sum;

      // динамический пересчёт
      for (let i = startDataColIndex; i < cells.length; i++) {
        const cell = cells[i];

        const recalculateSum = () => {
          let newSum = 0;
          for (let j = startDataColIndex; j < cells.length; j++) {
            const targetCell = cells[j];
            let val = 0;

            if (type === "booking") {
              const m =
                targetCell.textContent.match(
                  /\+\s*Booking\s*(\d+)/i
                );
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
    let url = "https://clotiss.site/api/v1/update";
    const xhr = new XMLHttpRequest();
    const formData = new FormData();

    xhr.open("POST", url, true);
    xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");

    formData.append("product_id", product_id);
    formData.append("client_id", client_id);
    formData.append("value", value);

    xhr.send(formData);
  };

  // Пример использования:
  window.addEventListener("load", () => {
    if (window.innerWidth > 767) {
      applyStickyTable("ordini", 3, 6);
    }
    setupTotals("ordini", 2, 4, 3, "size");
    setupTotals("ordini", 3, 4, 3, "booking");

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

          if (cellText.startsWith(searchValue)) {
            row.classList.remove('hidden');
          } else {
            row.classList.add('hidden');
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
</script>