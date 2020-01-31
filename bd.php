<?php
require "CONSTS.php";
set_time_limit(0);
ini_set('memory_limit', '-1');
//ini_set('display_errors', 'Off');
header("Content-Type: text/html; charset=utf-8");

mb_internal_encoding("UTF-8");
$mysqli = new mysqli($HOST, $DB_USERNAME, $DB_PASS, $DB_NAME);
mysqli_set_charset($mysqli, 'utf8mb4');
/* проверяем соединение */
if (mysqli_connect_errno()) {
    printf("Ошибка соединения: %s\n", mysqli_connect_error());
    exit();
}
$mysqli->query('SET NAMES utf8mb4_unicode_ci.');
$mysqli->query("SET CHARACTER SET 'utf8mb4_unicode_ci.'");

?>