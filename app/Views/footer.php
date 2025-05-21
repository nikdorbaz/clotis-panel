<form action="<?= site_url('logout') ?>" method="post" class="logout">
  <?= csrf_field() ?>
  <button type="submit">Выйти</button>
</form>

<style>
  .logout {
    position: absolute;
    z-index: 999;
    right: 0;
    top: 0;
  }
</style>