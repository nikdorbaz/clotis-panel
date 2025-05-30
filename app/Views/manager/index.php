<?php

$stocks = $result['stocks'] ?? [];
$values = $result['values'] ?? [];
$monthName = $result['month'] ?? "";

?>
<?= view('styles'); ?>
<?= view('manager/header') ?>


<div class="table-wrapper">
  <table class="spreadsheet" id="sales">
    <tbody>
      <tr class="fixed-row">
        <td colspan="3"><?= $monthName ?></td>
        <td>Заказ по корзине</td>
        <td>Остаток</td>
        <?php foreach ($stocks as $stock): ?>
          <td><?= esc($stock['name']) ?></td>
        <?php endforeach; ?>
        <td>Дефекты</td>
        <td>Остаток к оплате (Долг)</td>
        <td>Переплата</td>
        <td>Карта</td>
      </tr>

      <?php foreach ($values as $i => $row): ?>
        <tr>
          <td><?= $i + 1 ?></td>
          <td><?= esc($row['uniq_id']); ?></td>
          <td><?= esc($row['name']) ?></td>
          <td><?= number_format($row['total_order'], 2, '.', '') ?></td>
          <td><?= number_format($row['remaining'], 2, '.', '') ?></td>

          <?php foreach ($stocks as $stock): ?>
            <?php
            $payments = $row['payments'][$stock['id']] ?? [];
            $j = 5 + array_search($stock['id'], array_column($stocks, 'id'));
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

                  <button class="delete-btn" data-id="<?= $payment['id'] ?>" data-type="payment">×</button>
                </span>
              <?php endforeach; ?>
              <div class="add-payment" data-i="<?= $i ?>" data-j="<?= $j ?>" data-stock-id="<?= $stock['id'] ?>">+ Добавить оплату</div>

            </td>
          <?php endforeach; ?>

          <?php
          $baseJ = 5 + count($stocks);
          $types = ['defect', 'debt', 'overpay', 'card'];
          foreach ($types as $offset => $type):
            $transactions = $row['transactions'][$type] ?? [];
            $j = $baseJ + $offset;
          ?>
            <td data-i="<?= $i ?>" data-j="<?= $j ?>">
              <?php foreach ($transactions as $t): ?>
                <span
                  class="cell-item"
                  data-id="<?= $t['id'] ?>"
                  data-amount="<?= $t['amount'] ?? '' ?>"
                  data-date="<?= $t['date'] ?? '' ?>"
                  data-comment="<?= esc($t['comment'] ?? '') ?>"
                  data-i="<?= $i ?>"
                  data-j="<?= $j ?>"
                  id="payment_<?= $t['id'] ?>">
                  <?php if ($type === 'defect'): ?>
                    Дефект: <?= esc($t['article']) ?>, -<?= $t['amount'] ?> €
                  <?php elseif ($type === 'debt'): ?>
                    Долг: <?= esc($t['text_id']) ?>, <?= $t['date'] ?>
                  <?php elseif ($type === 'overpay'): ?>
                    Переплата: <?= esc($t['text_id']) ?>, <?= $t['date'] ?>
                  <?php elseif ($type === 'card'): ?>
                    <?= $t['amount'] ?> € / <?= $t['date'] ?>
                  <?php endif; ?>
                  <button class="delete-btn" data-id="<?= $t['id'] ?>" data-type="<?= $type ?>">×</button>
                </span>
              <?php endforeach; ?>
              <div class="add-<?= $type ?>" data-i="<?= $i ?>" data-j="<?= $j ?>" data-stock-id="0">+ Добавить <?= $type ?></div>
            </td>
          <?php endforeach; ?>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

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

<div id="cellModalDefect" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closeModal()">&times;</span>
    <h3>Добавить дефект</h3>
    <form id="cellFormDefect">
      <input type="hidden" name="is_new" id="isNew">
      <input type="hidden" name="cellRow" id="cellRow">
      <input type="hidden" name="cellCol" id="cellCol">

      <label for="article">Артикул:</label>
      <input type="text" id="article" name="article" required>

      <label for="amount">Сумма скидки:</label>
      <input type="number" id="amount" name="amount" required>

      <button type="submit">Сохранить</button>
    </form>
  </div>
</div>

<div id="cellModalCard" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closeModal()">&times;</span>
    <h3>Добавить оплату картой</h3>
    <form id="cellFormCard">
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

<div id="cellModalOverpay" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closeModal()">&times;</span>
    <h3>Добавить переплату</h3>
    <form id="cellFormOverpay">
      <input type="hidden" name="is_new" id="isNew">
      <input type="hidden" name="cellRow" id="cellRow">
      <input type="hidden" name="cellCol" id="cellCol">

      <label for="text_id">ID:</label>
      <input type="text" id="text_id" name="text_id" required>

      <label for="amount">Сумма:</label>
      <input type="number" id="amount" name="amount" required>

      <button type="submit">Сохранить</button>
    </form>
  </div>
</div>

<div id="cellModalDebt" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closeModal()">&times;</span>
    <h3>Добавить долг</h3>
    <form id="cellFormDebt">
      <input type="hidden" name="is_new" id="isNew">
      <input type="hidden" name="cellRow" id="cellRow">
      <input type="hidden" name="cellCol" id="cellCol">

      <label for="text_id">ID:</label>
      <input type="text" id="text_id" name="text_id" required>

      <label for="amount">Сумма:</label>
      <input type="number" id="amount" name="amount" required>

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
    justify-content: space-between;
  }

  .add-defect,
  .add-card,
  .add-debt,
  .add-overpay,
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

  td:hover .add-defect,
  td:hover .add-card,
  td:hover .add-debt,
  td:hover .add-overpay,
  td:hover .add-payment {
    visibility: visible;
  }

  .delete-btn {
    background: none;
    border: none;
    color: red;
    margin-left: 8px;
    font-weight: bold;
    cursor: pointer;
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
  let modalType = "payment";
  const modals = {
    payment: document.getElementById("cellModal"),
    defect: document.getElementById("cellModalDefect"),
    debt: document.getElementById("cellModalDebt"),
    overpay: document.getElementById("cellModalOverpay"),
    card: document.getElementById("cellModalCard")
  };

  const formMap = {
    payment: document.querySelector("#cellForm"),
    defect: document.querySelector("#cellFormDefect"),
    debt: document.querySelector("#cellFormDebt"),
    overpay: document.querySelector("#cellFormOverpay"),
    card: document.querySelector("#cellFormCard")
  };

  let currentPaymentId = null;
  let currentStockId = null;
  let currentChatId = null;

  function openModal(i, j, amount = '', date = '', paymentId = null, stockId = null, chatId = null, comment = '', type = "payment") {
    modalType = type;
    Object.values(modals).forEach(modal => modal.style.display = "none");

    const activeModal = modals[type];
    activeModal.style.display = "block";
    const form = formMap[type];

    form.querySelector("#isNew").value = !paymentId;
    form.querySelector("#cellRow").value = i;
    form.querySelector("#cellCol").value = j;

    if (form.querySelector("#amount")) form.querySelector("#amount").value = amount;
    if (form.querySelector("#date")) form.querySelector("#date").value = date || "<?= date('Y-m-d') ?>";
    if (form.querySelector("#comment")) form.querySelector("#comment").value = comment || '';
    if (form.querySelector("#article")) form.querySelector("#article").value = '';
    if (form.querySelector("#text_id")) form.querySelector("#text_id").value = '';

    currentPaymentId = paymentId;
    currentStockId = stockId;
    currentChatId = chatId;
  }

  function closeModal() {
    Object.values(modals).forEach(modal => modal.style.display = "none");
  }

  // отправка форм
  Object.entries(formMap).forEach(([type, form]) => {
    form?.addEventListener("submit", async function(e) {
      e.preventDefault();
      if (modalType !== type) return;

      const row = form.cellRow.value;
      const col = form.cellCol.value;
      const isNew = form.isNew.value === "true";

      let payload = {
        payment_id: isNew ? null : currentPaymentId,
        chat_id: currentChatId,
        stock_id: currentStockId
      };

      switch (modalType) {
        case "payment":
        case "card":
          payload.amount = form.amount.value;
          payload.date = form.date.value;
          payload.comment = form.comment?.value || '';
          break;
        case "defect":
          payload.article = form.article.value;
          payload.amount = form.amount.value;
          break;
        case "debt":
        case "overpay":
          payload.amount = form.amount.value;
          payload.text_id = form.text_id.value;
          break;
      }

      const endpointMap = {
        payment: "/api/v1/sales/update",
        defect: "/api/v1/sales/defect",
        debt: "/api/v1/sales/debt",
        overpay: "/api/v1/sales/overpay",
        card: "/api/v1/sales/card"
      };

      try {
        const response = await fetch('https://clotiss.site' + endpointMap[modalType], {
          method: "POST",
          headers: {
            "Content-Type": "application/json"
          },
          body: JSON.stringify(payload)
        });

        const data = await response.json();
        if (data.result) {
          const table = document.getElementById("sales");
          const targetRow = table.rows[+row + 1];
          const cell = targetRow.cells[+col];

          if (isNew) {
            let span = document.createElement('span');
            span.classList.add('cell-item');
            span.setAttribute('id', `payment_${data.id}`);
            span.dataset.id = data.id;
            span.dataset.amount = payload.amount || '';
            span.dataset.date = payload.date || '';
            span.dataset.comment = payload.comment || '';
            span.dataset.i = row;
            span.dataset.j = col;

            if (modalType === 'payment' || modalType === 'card') {
              span.innerText = `${payload.amount} € / ${payload.date}`;
            } else if (modalType === 'defect') {
              span.innerText = `Дефект: ${payload.article}, -${payload.amount} €`;
            } else if (modalType === 'debt') {
              span.innerText = `Долг: ${payload.text_id}, ${payload.date}`;
            } else if (modalType === 'overpay') {
              span.innerText = `Переплата: ${payload.text_id}, ${payload.date}`;
            }

            cell.prepend(span);

            span.addEventListener('click', () => openModal(row, col, payload.amount || '', payload.date || '', data.id, currentStockId, currentChatId, payload.comment || '', modalType));
          } else {
            const existing = document.getElementById(`payment_${currentPaymentId}`);
            if (existing) {
              existing.innerText = `${payload.amount} € / ${payload.date}`;
              existing.dataset.amount = payload.amount;
              existing.dataset.date = payload.date;
              existing.dataset.comment = payload.comment;
            }
          }

          closeModal();
        } else {
          alert("Ошибка при сохранении: " + data.message);
        }
      } catch (err) {
        console.error("Ошибка запроса:", err);
        alert("Не удалось отправить данные на сервер.");
      }
    });
  });

  // получаем ID клиентов
  const chatIds = <?= json_encode(array_column($values ?? [], 'client_id')) ?>;

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

    el.addEventListener('click', () => openModal(i, j, amount, date, paymentId, stockId, chatId, comment, 'payment'));
  });

  // обработка добавления нового платежа
  ["add-payment", "add-defect", "add-debt", "add-overpay", "add-card"].forEach(cls => {
    document.querySelectorAll("." + cls).forEach((el) => {
      const j = el.dataset.j;
      const i = el.dataset.i;
      const stockId = el.dataset.stockId;
      const chatId = chatIds[i];
      const type = cls.replace("add-", "");
      el.addEventListener("click", () => openModal(i, j, '', '', null, stockId, chatId, '', type));
    });
  });

  document.querySelectorAll(".delete-btn").forEach(btn => {
    btn.addEventListener("click", async (e) => {
      e.stopPropagation(); // чтобы не открывался модал
      const id = btn.dataset.id;
      const type = btn.dataset.type;

      if (!confirm("Удалить запись?")) return;

      const endpointMap = {
        payment: "/api/v1/sales/deletePayment",
        defect: "/api/v1/sales/deleteTransaction",
        debt: "/api/v1/sales/deleteTransaction",
        overpay: "/api/v1/sales/deleteTransaction",
        card: "/api/v1/sales/deleteTransaction"
      };

      try {
        const response = await fetch('https://clotiss.site' + endpointMap[type], {
          method: "POST",
          headers: {
            "Content-Type": "application/json"
          },
          body: JSON.stringify({
            id
          })
        });

        const data = await response.json();
        if (data.result) {
          // Удаление DOM элемента
          btn.closest(".cell-item").remove();
        } else {
          alert("Ошибка при удалении: " + data.message);
        }
      } catch (err) {
        console.error("Ошибка запроса:", err);
        alert("Не удалось отправить данные на сервер.");
      }
    });
  });
</script>