<header>

  <div class="stock-info">
    <div class="stock-info__name">
      <?= $name ?>
    </div>
    <div class="stock-info__date">
      <?= date('d-m-Y'); ?>
    </div>
  </div>

  <div class="controls">
    <button type="button" class="history-button" onclick="toggleHistory()"><img src="/svg/history.svg"></button>

    <form action="<?= site_url('logout') ?>" method="post" class="logout">
      <?= csrf_field() ?>
      <button type="submit" class="logout-button">Выйти</button>
    </form>
  </div>
</header>


<div id="history-panel" class="history-panel">
  <div class="history-header">
    <span>Навигация</span>
    <button onclick="toggleHistory()">✕</button>
  </div>
  <div class="history-content">
    <div class="history-nav">
      <div class="history-title">
        Таблица
      </div>
      <ul>
        <li><a href="<?= base_url('manager') ?>" class="<?= service('uri')->getSegment(2) === '' ? 'active' : '' ?>">Оплаты</a></li>
        <li><a href="<?= base_url('manager/monthly') ?>" class="<?= service('uri')->getSegment(2) === 'monthly' ? 'active' : '' ?>">Оплата по месяцам</a></li>
        <li><a href="<?= base_url('manager/difference') ?>" class="<?= service('uri')->getSegment(2) === 'difference' ? 'active' : '' ?>">Таблица разницы</a></li>
      </ul>
    </div>
    <div class="history-nav">
      <div class="history-title">
        История версий
      </div>
      <ul>

      </ul>
    </div>
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
    color: white;
    border: none;
    font-size: 14px;
    font-weight: bold;
    cursor: pointer;
    transition: background-color 0.3s ease;
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
    margin-top: 0;
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

  .history-nav a.active {
    font-weight: bold;
    color: #007bff;
    text-decoration: underline;
  }

  .history-title {
    background-color: #cdcdcd;
    padding: 4px 6px;
    margin-bottom: 5px;
    font-size: 18px;
  }
</style>

<script>
  function toggleHistory() {
    document.getElementById("history-panel").classList.toggle("open");
  }
</script>