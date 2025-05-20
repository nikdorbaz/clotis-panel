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
        <td></td>
        <td></td>
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
  function updateSpedizioneTotals() {
    const rows = document.querySelectorAll("#spedizione tbody tr:not(.fixed-row)");
    const totals = {};

    rows.forEach(row => {
      const cells = row.querySelectorAll("td");
      const country = cells[2]?.textContent.trim();
      const kg = parseFloat(cells[3]?.textContent) || 0;
      const colli = parseFloat(cells[4]?.textContent) || 0;
      const total = parseFloat(cells[5]?.textContent.replace('€', '').trim()) || 0;

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
      <td>${data.kg}</td>
      <td>${data.colli}</td>
      <td>${data.total.toFixed(2)} €</td>
    `;
      tbody.appendChild(tr);
    });
  }

  // Слушаем изменения всех редактируемых ячеек
  document.querySelectorAll("#spedizione td").forEach(cell => {
    cell.setAttribute("contenteditable", true);
    cell.addEventListener("blur", updateSpedizioneTotals);
    cell.addEventListener("input", () => {
      cell.classList.add("changed");
    });
  });

  window.addEventListener("DOMContentLoaded", updateSpedizioneTotals);
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