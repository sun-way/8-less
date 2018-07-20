<?php
session_start();
$captchaNum = $_SESSION['captcha'] = rand(1000, 9999);
$captchaImg = __DIR__ . '\src\captcha.png';
$font = __DIR__ . '\font\arial.ttf';
$image = imagecreatefrompng($captchaImg);
$redColor = imagecolorexact($image, 255, 0, 0);
imagettftext($image, 40, 30, 50, 100, $redColor, $font, $captchaNum);
header('Content-Type: image/png');
imagepng($image);
imagedestroy($image);