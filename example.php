<?php

require 'CONSTS.php';
require_once __DIR__ . '/vendor/autoload.php';
require 'utils.php';
require 'Table.php';
ini_set('memory_limit', '-1');
$contains = $_GET['contains'];
$googleAccountKeyFilePath = __DIR__ . '/assets/Phone numbers-9dc4bd6a8dd1.json';
putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $googleAccountKeyFilePath);

$client = new Google_Client();
$client->useApplicationDefaultCredentials();

$client->addScope( 'https://www.googleapis.com/auth/spreadsheets' );

$service = new Google_Service_Sheets( $client );

$spreadsheetId = $SPREADSHEET_ID;


$operator = ["МТС", "МЕГАФОН", "БИЛАЙН", "ТЕЛЕ2", "БЕЗЛИМИТ"];
$type = ["БРОНЗА", "СЕРЕБРО", "ЗОЛОТО", "ПЛАТИНА", "БРИЛЛИАНТ"];

$contains = '12';
$values = [];

for($i = 0; $i < 400000; $i++){
    $n = rand(89000000000, 89999999999);
    $p = (1000 * rand(1,30));
    $values[] = [$n, $p];
}


$body    = new Google_Service_Sheets_ValueRange( [ 'values' => $values ] );

// valueInputOption - определяет способ интерпретации входных данных
// https://developers.google.com/sheets/api/reference/rest/v4/ValueInputOption
// RAW | USER_ENTERED
$options = array( 'valueInputOption' => 'RAW' );

$service->spreadsheets_values->update( $spreadsheetId, 'МТС ЗОЛОТО!A1', $body, $options );