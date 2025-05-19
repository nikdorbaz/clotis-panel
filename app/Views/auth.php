<?= view('header'); ?>

<div class="auth">


    <form class="form-content" method="POST" action="/auth">
        <h1 class="">Авторизация</h1>
        <p class="">Для продолжения, введите данные ниже</p>

        <div class="field-wrapper input">
            <input name="login"
                type="text"
                class="form-control"
                id="username"
                placeholder="Логин"
                data-auth="login"
                required>
        </div>
        <div class="field-wrapper input">
            <input id="password" name="password" type="password" class="form-control" placeholder="Пароль" data-auth="password" required>
        </div>
        <div class="field-wrapper">
            <button class="btn btn-primary js__control--button" data-type="auth">Вход</button>
        </div>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger">
                <?= esc(session()->getFlashdata('error')) ?>
            </div>
        <?php endif; ?>
    </form>
</div>

<?= view('footer'); ?>