<?= view('styles'); ?>

<style>
    .auth {
        max-width: 400px;
        margin: 80px auto;
        padding: 30px;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        background-color: #fff;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
    }

    .auth h1 {
        margin-bottom: 10px;
        font-size: 24px;
        text-align: center;
    }

    .auth p {
        margin-bottom: 20px;
        text-align: center;
        color: #666;
    }

    .field-wrapper {
        margin-bottom: 15px;
    }

    .alert {
        padding: 10px 15px;
        background-color: #f44336;
        color: white;
        border-radius: 5px;
        margin-bottom: 20px;
        font-size: 14px;
    }

    .alert-success {
        background-color: chartreuse;
    }

    .form-control {
        width: 100%;
        padding: 10px;
        border-radius: 4px;
        border: 1px solid #ccc;
        font-size: 14px;
    }

    .btn.btn-primary {
        width: 100%;
        padding: 10px;
        background-color: #007bff;
        border: none;
        color: white;
        font-weight: bold;
        border-radius: 4px;
        cursor: pointer;
        transition: background 0.3s ease;
    }

    .btn.btn-primary:hover {
        background-color: #0056b3;
    }

    .field-wrapper input {
        width: 100%;
    }
</style>

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

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success">
                <?= esc(session()->getFlashdata('success')) ?>
            </div>
        <?php endif; ?>
    </form>
</div>

<?= view('footer'); ?>