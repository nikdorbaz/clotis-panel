<?php

$stocks = $result['stocks'] ?? [];
$values = $result['values'] ?? [];

?>
<?= view('styles'); ?>
<?= view('manager/header') ?>


<table class="spreadsheet" id="sales">
  <tbody>
    <tr class="fixed-row">
      <td>#</td>
      <td>Имя</td>
      <td>Заказ по корзине</td>
      <td>Остаток</td>
      <?php foreach ($stocks as $stock): ?>
        <td><?= esc($stock['name']) ?></td>
      <?php endforeach; ?>
    </tr>

    <?php foreach ($values as $i => $row): ?>
      <tr>
        <td><?= $i + 1 ?></td>
        <td><?= esc($row['name']) ?></td>
        <td><?= number_format($row['total_order'], 2, '.', '') ?></td>
        <td><?= number_format($row['remaining'], 2, '.', '') ?></td>

        <?php foreach ($stocks as $stock): ?>
          <?php
          $payments = $row['payments'][$stock['id']] ?? [];
          $j = 4 + array_search($stock['id'], array_column($stocks, 'id'));
          ?>
          <td data-i="<?= $i ?>" data-j="<?= $j ?>" data-stock-id="<?= $stock['id'] ?>">
            <?php foreach ($payments as $payment): ?>
              <span
                class="cell-item"
                data-id="<?= $payment['id'] ?>"
                data-amount="<?= $payment['amount'] ?>"
                data-date="<?= $payment['date'] ?>"
                data-comment="<?= esc($payment['comment'] ?? '') ?>"
                data-i="<?= $i ?>"
                data-j="<?= $j ?>"
                id="payment_<?= $payment['id'] ?>">
                <?= number_format($payment['amount'], 2, '.', '') ?> € / <?= $payment['date'] ?>
              </span>
            <?php endforeach; ?>
            <div class="add-payment" data-i="<?= $i ?>" data-j="<?= $j ?>" data-stock-id="<?= $stock['id'] ?>">+ Добавить оплату</div>
          </td>
        <?php endforeach; ?>
      </tr>
    <?php endforeach; ?>
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

      <label for="date">Комментарий:</label>
      <input type="text" id="comment" name="comment">

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
    background-color: transparent;
    padding: 3px;
    border-radius: 3px;
    border: 1px solid;
    cursor: pointer;
    color: #636363;
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

  window.addEventListener('resize', () => {
    if (window.innerWidth > 767) {
      applyStickyTable('sales', 1, 4);
    }
  });
</script>

<script>
  const modal = document.getElementById("cellModal");
  const form = document.getElementById("cellForm");

  let currentPaymentId = null;
  let currentStockId = null;
  let currentChatId = null;

  function openModal(i, j, amount = '', date = '', paymentId = null, stockId = null, chatId = null, comment = '') {
    document.getElementById("isNew").value = !paymentId;
    document.getElementById("cellRow").value = i;
    document.getElementById("cellCol").value = j;
    document.getElementById("amount").value = amount;
    document.getElementById("date").value = (date?.length) ? date : "<?= date('Y-m-d') ?>";
    document.getElementById("comment").value = comment ?? '';

    currentPaymentId = paymentId;
    currentStockId = stockId;
    currentChatId = chatId;

    modal.style.display = "block";
  }

  function closeModal() {
    modal.style.display = "none";
  }

  // получаем ID клиентов
  const chatIds = <?= json_encode(array_column($values, 'client_id')) ?>;

  // обработка кликов по существующим платежам
  document.querySelectorAll('.cell-item').forEach((el) => {
    const i = el.dataset.i;
    const j = el.dataset.j;
    const amount = el.dataset.amount;
    const date = el.dataset.date;
    const comment = el.dataset.comment ?? '';
    const paymentId = el.dataset.id;
    const stockId = el.closest('td').dataset.stockId;
    const chatId = chatIds[i];

    el.addEventListener('click', () => openModal(i, j, amount, date, paymentId, stockId, chatId, comment));
  });

  // обработка добавления нового платежа
  document.querySelectorAll(".add-payment").forEach((el) => {
    const j = el.dataset.j;
    const i = el.dataset.i;
    const stockId = el.dataset.stockId;
    const chatId = chatIds[i];

    el.addEventListener("click", () => openModal(i, j, '', '', null, stockId, chatId, ''));
  });

  form.addEventListener("submit", async function(e) {
    e.preventDefault();

    const row = form.cellRow.value;
    const col = form.cellCol.value;
    const amount = form.amount.value;
    const date = form.date.value;
    const comment = form.comment.value;
    const isNew = document.getElementById("isNew").value === "true";

    try {
      const response = await fetch("https://clotiss.site/api/v1/sales/update", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          payment_id: isNew ? null : currentPaymentId,
          chat_id: currentChatId,
          stock_id: currentStockId,
          amount: amount,
          date: date,
          comment: comment
        }),
      });

      const data = await response.json();

      if (data.result) {
        const table = document.getElementById("sales");
        const targetRow = table.rows[+row + 1];
        const cell = targetRow.cells[+col];

        if (isNew) {
          let span = document.createElement('span');
          span.innerText = `${amount} € / ${date}`;
          span.classList.add('cell-item');
          span.setAttribute('id', `payment_${data.id}`);
          span.dataset.id = data.id;
          span.dataset.amount = amount;
          span.dataset.date = date;
          span.dataset.comment = comment;
          span.dataset.i = row;
          span.dataset.j = col;

          cell.prepend(span);

          span.addEventListener('click', () => openModal(row, col, amount, date, data.id, currentStockId, currentChatId, comment));
        } else {
          const existing = document.getElementById(`payment_${currentPaymentId}`);
          if (existing) {
            existing.innerText = `${amount} € / ${date}`;
            existing.dataset.amount = amount;
            existing.dataset.date = date;
            existing.dataset.comment = comment;
          }
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
    if (event.target === modal) {
      closeModal();
    }
  };
</script>