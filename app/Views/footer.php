<form action="<?= site_url('logout') ?>" method="post" style="display:inline;">
  <?= csrf_field() ?>
  <button type="submit">Выйти</button>
</form>