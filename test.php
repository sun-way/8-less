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
if (empty($_GET) && empty($_POST)) {
    exit('Не переданы параметры');
}
if (!empty($_GET) && !empty($_GET['delete'])) {
    $deleteTestId = $_GET['delete'];
    $fileToDelete = $testsDir . $deleteTestId . '.json';
    //$deleteResult;
    if (file_exists($fileToDelete)) {
        $deleteResult = unlink($fileToDelete);
    }

    if ($deleteResult) {
        exit("Тест $deleteTestId удалён");
    } else {
        exit('Ошибка при удалении теста');
    }
}
if (!empty($_GET) && empty($_GET['testid'])) {
    exit('Передайте параметр testid');
}
if (!empty($_GET['testid'])) {
    session_start();
    $testId = $_SESSION['testid'] = 'id' . $_GET['testid'];
} else {
    session_start();
    $testId = $_SESSION['testid'];
}
$fileUrl = $testsDir . $testId . '.json';
if (!file_exists($fileUrl)) {
    http_response_code(404);
    exit("Тест $testId не найден");
}
$testJson = file_get_contents($fileUrl);
if ($testJson === false) {
    http_response_code(500);
    exit('Server internal error');
}
$testData = json_decode($testJson, true);
$testName = !empty($testData['title']) ? $testData['title'] : 'Безымянный тест';
if (empty($testData['questions'])) {
    exit('Пустой тест');
}
$testQuestionsArray = $testData['questions'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sertificateTemplateUrl = __DIR__ . '\src\setificate-template.png';
    $userName = $_SESSION['userdata']['login'];
    $testsCount = count($testQuestionsArray);
    //$correctTestsCount = count(array_filter($_POST, filterCorrect));
    $image = imagecreatefrompng($sertificateTemplateUrl);
    $blackColor = imagecolorexact($image, 0, 0, 0);
    // Не получилось конвертировать русские символы, печатались крокозябры
    // пробовал по методичкам и более 5 решений из интернета
    $font = __DIR__ . '\font\arial.ttf';
    imagettftext($image, 40, 0, 180, 450, $blackColor, $font, $userName);
    imagettftext($image, 20, 0, 180, 525, $blackColor, $font, $testName);
   // imagettftext($image, 20, 0, 180, 650, $blackColor, $font, $correctTestsCount . '\\' . $testsCount);
    header('Content-Type: image/png');
    imagepng($image);
    imagedestroy($image);
    exit;
}
function filterCorrect($value) {
    return $value === 'correct';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>
        <?php echo !empty($_GET) ? 'Тест - ' : 'Результат теста - ' ;?>
        <?php echo $testId; ?>
    </title>
</head>
<body>

<?php if ($_SERVER['REQUEST_METHOD'] === 'GET'): ?>
    <!-- GET -->

    <h1>
        <?php echo $testName; ?>
    </h1>
    <form action="test.php" method="POST">
        <?php $fieldNamePrefix = 'q'; ?>
        <?php foreach ($testQuestionsArray as $questionCounter => $question): ?>
            <fieldset>
                <?php if (isset($question['title'])): ?>
                    <h3>
                        <?php echo $question['title'] ?>
                    </h3>
                <?php else: ?>
                    <?php continue; ?>
                <?php endif; ?>

                <?php $fieldname = $fieldNamePrefix . (1 + $questionCounter); ?>
                <?php $answers = $question['answers']; ?>

                <?php foreach ($answers as $answer): ?>
                    <?php if (empty($answer['title'])): ?>
                        <?php continue; ?>
                    <?php endif; ?>

                    <?php $correct = isset($answer['correct']) && $answer['correct'] ? 'correct' : ''; ?>

                    <label>
                        <input type="radio" name="<?php echo $fieldname; ?>" value="<?php echo $correct; ?>">
                        <?php echo $answer['title'] ?>
                    </label>
                <?php endforeach; ?>

            </fieldset>
        <?php endforeach; ?>

        <input type="submit" placeholder="Отправить"/>
    </form>

<?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST)): ?>
    <!-- POST -->
    <!-- Не сработает, пока есть метод возврата картинки -->
    <h2>Результаты теста:</h2>

    <ul>
        <?php $resultCounter = 0; ?>
        <?php foreach ($_POST as $fieldName => $data): ?>
            <?php if ($fieldName === 'testid'): ?>
                <?php continue; ?>
            <?php endif; ?>

            <?php $questionTitle = $testQuestionsArray[$resultCounter++]['title']; ?>
            <?php $questionStatus = !empty($data); ?>

            <li>
                <?php echo $questionTitle . ' - ' . ($questionStatus ? 'Верно' : 'Не верно'); ?>
            </li>
        <?php endforeach; ?>
    </ul>

<?php endif; ?>
</body>
</html>