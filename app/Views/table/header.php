<header>

  <div class="stock-info">
    <div class="stock-info__name">
      <?= $stock['name'] ?? "" ?>
    </div>
    <div class="stock-info__date">
      <?= date('d-m-Y'); ?>
    </div>
  </div>

  <div class="controls">
    <button type="button" class="history-button" onclick="toggleHistory()">
      <? if (service('uri')->getSegment(2) == 'history'): ?>
        Заказы за <?= service('uri')->getSegment(3); ?>
      <? else: ?>
        Актуальная версия
      <? endif; ?>
      <img src="/svg/history.svg">
    </button>

    <form action="<?= site_url('logout') ?>" method="post" class="logout">
      <?= csrf_field() ?>
      <button type="submit" class="logout-button">Выйти</button>
    </form>
  </div>
</header>


<div id="history-panel" class="history-panel">
  <div class="history-header">
    <span>История версий</span>
    <button onclick="toggleHistory()">✕</button>
  </div>
  <div class="history-content">
    <ul>
      <li><a href="<?= base_url("stock/?type=" . $type) ?>">Актуальная версия</span></a></li>
      <? foreach ($months as $month): ?>
        <li><a href="<?= base_url("stock/history/$month?type=" . $type) ?>">Просмотреть заказы <?= $month ?></span></a></li>
      <? endforeach; ?>
    </ul>
  </div>
</div>

<style>
  header {
    padding: 10px;
    background-color: #d5e4e5;
    border-bottom: 1px solid var(--cell-border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .stock-info {
    display: flex;
    align-items: flex-end;
    gap: 10px;
  }

  .stock-info__date {
    opacity: 0.6;
    font-size: 0.8em;
  }

  .logout-button {
    background-color: #dc3545;
    /* красный */
    color: white;
    border: none;
    padding: 6px 14px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: bold;
    cursor: pointer;
    transition: background-color 0.3s ease;
  }

  .logout-button:hover {
    background-color: #c82333;
  }

  .history-button {
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: transparent;
    border: none;
    font-size: 14px;
    font-weight: bold;
    cursor: pointer;
    transition: background-color 0.3s ease;
    gap: 10px;
  }

  .history-button img {
    max-width: 30px;
    opacity: 0.7;
  }

  .history-panel {
    position: fixed;
    top: 0;
    right: -400px;
    width: 300px;
    height: 100%;
    background-color: #f8f9fa;
    box-shadow: -2px 0 6px rgba(0, 0, 0, 0.2);
    overflow-y: auto;
    transition: right 0.3s ease-in-out;
    z-index: 999;
  }

  .history-panel.open {
    right: 0;
  }

  .history-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px;
    background-color: #343a40;
    color: white;
    font-weight: bold;
  }

  .history-header button {
    background: none;
    border: none;
    color: white;
    font-size: 18px;
    cursor: pointer;
  }

  .history-content {
    padding: 12px;
  }

  .history-content ul {
    list-style: none;
    padding-left: 0;
  }

  .history-content li {
    margin-bottom: 10px;
    font-size: 16px;
  }

  .history-content li a {
    color: #343a40;
    text-decoration: none;
  }

  .history-date {
    opacity: 0.6;
    font-size: 0.8em;
  }

  .controls {
    display: flex;
    align-items: center;
    gap: 10px;
  }
</style>

<script>
  function toggleHistory() {
    document.getElementById("history-panel").classList.toggle("open");
  }
</script>