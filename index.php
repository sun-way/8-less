<?php
require_once './functions.php';
require_once './config.php';
if (!empty($_COOKIE['access']) && $_COOKIE['access'] === 'deny') {
    http_response_code(403);
    exit('Подождите часок, мы вас заблокировали, а затем попробуйте снова');
}
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    session_start();
    if (!empty($_SESSION['userdata'])) {
        header('Location: ./list.php');
        exit();
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();
    if (empty($_SESSION['attemptsToEnter'])) {
        $_SESSION['attemptsToEnter'] = 1;
    } else {
        $_SESSION['attemptsToEnter'] += 1;
    }
    if ($_SESSION['attemptsToEnter'] >= 11) {
        setcookie('access', 'deny', time() + 3600, '', '', false, true);
        session_destroy();
    }
    $usersData = getDataFromJson($usersDataPath);
    $loginCheckingResult = checkLogin($_POST, $usersData);
    //$showCaptcha =0;
    $showCaptcha = $_SESSION['attemptsToEnter'] >= 6;
    $checkCaptcha = $_SESSION['attemptsToEnter'] > 6;
    if ($checkCaptcha) {
        //$captchaCorrect;
        if (empty($_POST['captcha'])) {
            $captchaCorrect = false;
        } else {
            $currentCaptcha = $_SESSION['captcha'];
            $postCaptcha = (int) $_POST['captcha'];
            $captchaCorrect = $currentCaptcha === $postCaptcha;
        }
        if (!$captchaCorrect) {
            $loginCheckingResult['errors'][] = 'Не правильно введён код с картинки!';
        }
    }
    if (!empty($loginCheckingResult['errors'])) {
        foreach ($loginCheckingResult['errors'] as $error) {
            echo '<p>', $error, '</p>';
        }
    } else if (!empty($loginCheckingResult['userdata'])) {
        $_SESSION['userdata'] = $loginCheckingResult['userdata'];
        unset($_SESSION['attemptsToEnter']);
        header('Location: ./list.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>

<h2>Войти</h2>

<form class="js_loginForm" method="POST">
    <div>
        <label>
            Логин:
            <input class="js_loginInput" name="login" type="text" required>
        </label>

        <div class="js_loginErrorBox"></div>
    </div>

    <div>
        <label>
            Пароль:
            <input name="password" type="text">
        </label>
    </div>


    <?php if ($showCaptcha): ?>
        <div>
            <label>
                Введите код с картинки:
                <input name="captcha" type="text">
            </label>
        </div>

        <div>
            <img src="captcha.php" />
        </div>
    <?php endif; ?>


    <div>
        <i>
            Чтобы зайти как гость, введите только логин
        </i>
    </div>

    <button type="submit">
        Войти
    </button>
</form>

<style>
    div {
        padding-bottom: 10px;
    }
</style>

<script>
    // Safari bugfix
    const elements = {
        loginForm: document.querySelector('.js_loginForm'),
        loginInput: document.querySelector('.js_loginInput'),
        loginErrorBox: document.querySelector('.js_loginErrorBox'),
    };
    elements.loginForm.addEventListener('submit', checkLoginFieldFilled);
    function checkLoginFieldFilled(event) {
        if (!elements.loginInput.value) {
            event.preventDefault();
            elements.loginErrorBox.value = 'Поле Логин не может быть пустым!';
        }
    }
</script>

</body>
</html>