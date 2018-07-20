<?php
function getFileData($filePath) {
    if (!file_exists($filePath)) {
        exit("File $filePath does't exist");
    }
    $fileData = file_get_contents($filePath);
    if ($fileData === false) {
        exit("Have some problem with reading $filePath");
    }
    return $fileData;
}
function getDataFromJson($jsonPath) {
    $dataJson = getFileData($jsonPath);
    $data = json_decode($dataJson, true);
    if (!$data) {
        exit("Problems with reading $jsonPath");
    }
    return $data;
}
function getFileList($options) {
    if (!isset($options)) {
        exit('Options not transferred');
    } elseif (empty($options)) {
        exit('Options empty');

    } elseif (empty($options['targetDir'])) {
        exit('Wrong target dir');
    }
    $targetDir = $options['targetDir'];
    $returnCount = $options['returnCount'];
    $dir = opendir($targetDir);
    $list = [];
    while($file = readdir($dir)){
        if($file == '.' || $file == '..' || is_dir($targetDir . $file)){
            continue;
        }
        $list[] = $file;
    }
    if ($returnCount) {
        return count($list);
    }
    return $list;
}
function checkLogin($postData = [], $usersData) {
    $login = '';
    $password = '';
    $result = [
        'userdata' => [],
        'errors' => [],
    ];
    if (!empty($postData['login'])) {
        $login = $postData['login'];
    }
    if (!empty($postData['password'])) {
        $password = $postData['password'];
    }
    if ($login && !$password) {
        $result['userdata']['login'] = $login;

    } else if ($login && $password) {
        foreach ($usersData as $user) {
            if ($user['login'] === $login && $user['password'] === $password) {
                $result['userdata'] = $user;

            } else if ($user['login'] === $login && $user['password'] !== $password) {
                $result['errors'][] = 'Пароль не верный пароль!';
            }
        }
    } else if (!$login) {
        $result['errors'][] = 'Логин - обязательное поле!';
    }
    return $result;
}