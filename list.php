<?php
require_once('./config.php');
require_once __DIR__. './functions.php';//('./functions.php');
session_start();
if (!empty($_COOKIE['access']) && $_COOKIE['access'] === 'deny') {
    http_response_code(403);
    exit('Подождите часок, мы вас заблокировали, а затем попробуйте снова');
}
if (empty($_SESSION['userdata'])) {
    http_response_code(403);
    exit('В доступе отказано из-за авторизации');
}
$list = getFileList(['targetDir' => $testsDir]);
$isAdmin = $_SESSION['userdata']['isAdmin'];
if ($_GET['exit']) {
    session_destroy();
    header('Location: /');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Test list</title>
</head>
<body>

<h1>Список тестов:</h1>

<ul>
    <?php foreach($list as $testNum => $filename): ?>
        <?php $cutFilename = substr($filename, 0, -5); ?>
        <li>
            <a href="./test.php?testid=<?php echo substr($cutFilename, 2); ?>">
                <?php echo $cutFilename ?>
            </a>
            <?php if ($isAdmin): ?>
                <a class="delete-link" href="./test.php?delete=<?php echo $cutFilename; ?>">Удалить</a>
            <?php endif; ?>

        </li>
    <? endforeach ?>
</ul>

<a href="?exit=true">Выход</a>
<?php if ($isAdmin): ?>
    <a href="./admin.php">Добавить тест</a>
<?php endif; ?>

<style>
    .delete-link {
        display: inline-block;
        border: 2px solid red;
        margin-left: 20px;
        margin-bottom: 20px;
        padding: 2px;
        box-sizing: border-box;
        color: red;
    }
</style>

</body>
</html>