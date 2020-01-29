<?php

require_once __DIR__ . '/vendor/autoload.php';
$contains = $_GET['contains'];
// Путь к файлу ключа сервисного аккаунта
$googleAccountKeyFilePath = __DIR__ . '/assets/Phone numbers-9dc4bd6a8dd1.json';
putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $googleAccountKeyFilePath);

// Документация https://developers.google.com/sheets/api/
$client = new Google_Client();
$client->useApplicationDefaultCredentials();

// Области, к которым будет доступ
// https://developers.google.com/identity/protocols/googlescopes
$client->addScope( 'https://www.googleapis.com/auth/spreadsheets' );

$service = new Google_Service_Sheets( $client );

// ID таблицы
$spreadsheetId = '1OeCT82snW7RBu037UraLWxNl_ziAbzXZ9GUAK1xZj6s';

$response = $service->spreadsheets->get($spreadsheetId);

// Свойства таблицы
$spreadsheetProperties = $response->getProperties();
$spreadsheetProperties->title; // Название таблицы

foreach ($response->getSheets() as $sheet) {

    // Свойства листа
    $sheetProperties = $sheet->getProperties();
    $sheetProperties->title; // Название листа

    $gridProperties = $sheetProperties->getGridProperties();
    $gridProperties->columnCount; // Количество колонок
    $rows_num = $gridProperties->rowCount; // Количество строк

}

$range = "Лист1!A1:B$rows_num";
$response = $service->spreadsheets_values->get($spreadsheetId, $range)->values;
foreach ($response as $item) {
    if(stristr($item[0], $contains))
    echo $item[0] . " - " . $item[1] . "<BR>";
}