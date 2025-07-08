<?php

$clients = $result['clients'] ?? [];

?>

<table class="spreadsheet" id="spedizione">
  <thead>
  </thead>
  <tbody>
    <tr class="fixed-row">
      <td></td>
      <td>#</td>
      <td>SPEDIZIONI</td>
      <td>KG</td>
      <td>COLLI</td>
      <td>TOTALE</td>
    </tr>
    <?php foreach ($clients as $client): ?>
      <tr>
        <td>#</td>
        <td><?= esc($client['uniq_id']) ?></td>
        <td><?= esc($client['country']) ?></td>
        <td data-chat_id="<?= $client['chat_id'] ?>" data-type="kg" class="<?= !empty($client['kg']) ? "changed" : "" ?>">
          <?= $client['kg'] ?? "" ?>
        </td>
        <td data-chat_id="<?= $client['chat_id'] ?>" data-type="count" class="<?= !empty($client['count']) ? "changed" : "" ?>">
          <?= $client['count'] ?? "" ?>
        </td>
        <td><?= esc($client['total_sum']) ?> €</td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<table class="spreadsheet" id="spedizione_total">
  <thead>
  </thead>
  <tbody>
    <tr class="fixed-row">
      <td>SPEDIZIONI</td>
      <td>KG</td>
      <td>COLLI</td>
      <td>TOTALE</td>
    </tr>

  </tbody>
</table>

<style>
  .fixed-row,
  .fixed-col {
    position: sticky;
  }
</style>

<script>
  const parseKgValue = (str) => {
    return str
      .split("+")
      .map(v => parseFloat(v.trim()) || 0)
      .reduce((a, b) => a + b, 0);
  };

  function updateSpedizioneTotals() {
    const rows = document.querySelectorAll("#spedizione tbody tr:not(.fixed-row)");
    const totals = {};

    rows.forEach(row => {
      const cells = row.querySelectorAll("td");
      const country = cells[2]?.textContent.trim();
      const kgStr = cells[3]?.textContent || "";
      const colli = parseFloat(cells[4]?.textContent) || 0;
      const total = parseFloat(cells[5]?.textContent.replace('€', '').trim()) || 0;

      const kg = parseKgValue(kgStr);

      if (!totals[country]) {
        totals[country] = {
          kg: 0,
          colli: 0,
          total: 0
        };
      }

      totals[country].kg += kg;
      totals[country].colli += colli;
      totals[country].total += total;
    });

    const tbody = document.querySelector("#spedizione_total tbody");
    tbody.querySelectorAll("tr:not(.fixed-row)").forEach(tr => tr.remove());

    Object.entries(totals).forEach(([country, data]) => {
      const tr = document.createElement("tr");
      tr.innerHTML = `
      <td>${country}</td>
      <td>${data.kg.toFixed(2)}</td>
      <td>${data.colli}</td>
      <td>${data.total.toFixed(2)} €</td>
    `;
      tbody.appendChild(tr);
    });
  }

  // Слушаем изменения всех редактируемых ячеек
  document.querySelectorAll("#spedizione td[data-chat_id]").forEach(cell => {
    cell.setAttribute("contenteditable", true);

    cell.addEventListener("blur", () => {
      const value = cell.innerText.trim();
      const type = cell.dataset.type;
      const chat_id = cell.dataset.chat_id;

      // Обновляем текущую ячейку
      cell.classList.add("changed");

      // Если это KG, то нужно:
      if (type === "kg") {
        // 1. Посчитать количество значений (Colli = число элементов)
        const colliCount = value.split("+").map(v => v.trim()).filter(v => v).length;

        // 2. Найти соседнюю ячейку "COLLI"
        const row = cell.parentElement;
        const colliCell = row.querySelector('td[data-type="count"]');

        if (colliCell) {
          colliCell.innerText = colliCount;
          colliCell.classList.add("changed");

          let colliToServe = (!colliCount) ? '' : colliCount;
          // 3. Отправить обновление и для COLLI
          apiRequest(chat_id, colliToServe, "count");
        }
      }

      setTimeout(() => {
        // Отправить текущее значение
        apiRequest(chat_id, value, type);
      }, 500);

      // Обновить общие итоги
      updateSpedizioneTotals();
    });
  });

  window.addEventListener("DOMContentLoaded", updateSpedizioneTotals);

  const apiRequest = async (chat_id, value, type) => {
    let url = "https://clotiss.site/api/v1/update/count";
    const xhr = new XMLHttpRequest();
    const formData = new FormData();

    xhr.open("POST", url, true);
    xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");

    formData.append("chat_id", chat_id);
    formData.append("value", value);
    formData.append("type", type);
    formData.append('stock_id', "<?= $stock['id'] ?>");


    xhr.send(formData);
  };
</script>


<script>
  // Пример использования:
  window.addEventListener("load", () => {
    if (window.innerWidth > 767) {
      applyStickyTable("spedizione", 1, 2);
    }
    // setupTotals("spedizione", 2, 4, 3, "size");
    // setupTotals("spedizione", 3, 4, 3, "booking");
  });
</script>