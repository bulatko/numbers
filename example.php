<?php

require 'CONSTS.php';
require_once __DIR__ . '/vendor/autoload.php';
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

for($i = 0; $i < 5; $i++){
    for($j = 0; $j < 5; $j++){
        $arr = [];
        for($k = 0; $k < 1000; $k++){
            $arr[] = [(string)(rand(89000000000, 89999999999)), (string)(rand(3, 20) * 1000)];
        }
        $sheet = $operator[$i] . " " . $type[$j] . "!A1";
        $body    = new Google_Service_Sheets_ValueRange( [ 'values' => $arr ] );
        $options = array( 'valueInputOption' => 'RAW' );
        $service->spreadsheets_values->update( $spreadsheetId, $sheet, $body, $options );
    }
}