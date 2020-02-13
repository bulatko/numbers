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

$mask = array_from_mask("xyyx");

$arr = ['891234545890',
    '891234565890',
    '891234444890',
    '891236446890',
    '891237899890',
    '891231236767',
    '891234645450',
    '891234567890'
];
foreach ($arr as $item){
    echo "$item - " . (string)contains_mask($item, $mask) . "<BR>";
}
