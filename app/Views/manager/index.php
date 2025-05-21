<?php

$values = $result['values'];

?>
<?= view('styles'); ?>

<table class="spreadsheet" id="sales">
  <tbody>
    <? foreach ($values as $i => $value): ?>
      <tr class="<?= (!$i) ? "fixed-row" : "" ?>">
        <? foreach ($value as $j => $one): ?>
          <td class="<?= ($j < 4) ? "fixed-col" : "" ?>" data-id="<?= $j ?>">
            <?= $one ?>
            <? if ($i > 0 && $j > 3): ?>
              <button class="add-payment" data-i="<?= $i ?>" data-j="<?= $j ?>">Добавить оплату</button>
            <? endif; ?>
          </td>
        <? endforeach; ?>
      </tr>
    <? endforeach; ?>
  </tbody>
</table>

<div id="cellModal" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closeModal()">&times;</span>
    <h3>Редактировать значение</h3>
    <form id="cellForm">
      <input type="hidden" name="is_new" id="isNew">
      <input type="hidden" name="cellRow" id="cellRow">
      <input type="hidden" name="cellCol" id="cellCol">

      <label for="amount">Сумма:</label>
      <input type="number" id="amount" name="amount" required>

      <label for="date">Дата:</label>
      <input type="date" id="date" name="date" required>

      <button type="submit">Сохранить</button>
    </form>
  </div>
</div>

<style>
  .modal {
    display: none;
    position: fixed;
    z-index: 1001;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.5);
  }

  .modal-content {
    background-color: #fefefe;
    margin: 10% auto;
    padding: 20px;
    border-radius: 8px;
    width: 300px;
    position: relative;
  }

  .modal-content h3 {
    margin-top: 0;
  }

  .modal-content label {
    display: block;
    margin: 10px 0 4px;
  }

  .modal-content input {
    width: 100%;
    padding: 6px;
    box-sizing: border-box;
  }

  .modal-content button {
    margin-top: 12px;
    padding: 8px 12px;
    background-color: #28a745;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
  }

  .modal-content button:hover {
    background-color: #218838;
  }

  .close {
    position: absolute;
    top: 10px;
    right: 14px;
    font-size: 20px;
    cursor: pointer;
  }

  .cell-item {
    display: flex;
    border-radius: 3px;
    padding: 5px;
    white-space: nowrap;
    background-color: #cbcbcb;
    margin-bottom: 5px;
  }

  .add-payment {
    visibility: hidden;
    white-space: nowrap;
  }

  td:hover .add-payment {
    visibility: visible;
  }
</style>


<?= view('footer'); ?>

<script>
  // Пример использования:
  window.addEventListener("load", () => {
    if (window.innerWidth > 767) {
      applyStickyTable("sales", 1, 4);
    }
  });
</script>

<script>
  const modal = document.getElementById("cellModal");
  const form = document.getElementById("cellForm");

  function openModal(i, j, amount = '', date = '') {

    if (amount) {
      document.getElementById("isNew").value = false;
    } else {
      document.getElementById("isNew").value = true;
    }

    document.getElementById("cellRow").value = i;
    document.getElementById("cellCol").value = j;
    document.getElementById("amount").value = amount;
    document.getElementById("date").value = date;
    modal.style.display = "block";
  }

  function closeModal() {
    modal.style.display = "none";
  }

  document.querySelectorAll(".add-payment").forEach((el) => {
    const j = el.dataset.j;
    const i = el.dataset.i;

    el.addEventListener("click", () => openModal(i, j));
  });

  document.querySelectorAll('.cell-item').forEach((el) => {
    const j = el.dataset.j;
    const i = el.dataset.i;
    const amount = el.dataset.amount;
    const date = el.dataset.date;

    el.addEventListener('click', () => openModal(i, j, amount, date));
  });

  form.addEventListener("submit", async function(e) {
    e.preventDefault();
    const row = form.cellRow.value;
    const col = form.cellCol.value;
    const amount = form.amount.value;
    const date = form.date.value;
    const isNew = form.isNew.value;

    if (!isNew) {
      paymentId = 1;

    }
    console.log(isNew);

    try {
      const response = await fetch("https://clotiss.site/api/v1/sales/update", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          row: row,
          col: col,
          amount: amount,
          date: date,
        }),
      });

      const data = await response.json();

      if (data.result) {
        // Отображаем сумму и дату в ячейке
        const table = document.getElementById("sales");
        const targetRow = table.rows[+row + 1]; // +1 потому что первая строка фиксированная
        const cell = targetRow.cells[+col];

        if (isNew) {
          let span = document.createElement('span');
          let identify = data.id;
          span.innerText = `${amount} € / ${date}`;
          span.classList.add('cell-item');
          span.setAttribute('id', `payment_${data.id}`);
          cell.prepend(span);

          span.addEventListener('click', () => openModal(row, col, amount, date, ));
        } else {
          // cell.
        }

        closeModal();
      } else {
        alert("Ошибка при сохранении: " + data.message);
      }
    } catch (error) {
      console.error("Ошибка запроса:", error);
      alert("Не удалось отправить данные на сервер.");
    }
  });

  window.onclick = function(event) {
    if (event.target == modal) {
      closeModal();
    }
  }
</script>