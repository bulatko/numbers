<?php

require 'CONSTS.php';
require_once __DIR__ . '/vendor/autoload.php';
require 'utils.php';
require 'Table.php';
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

$table = new Table();

$c = split_numbers($table->find_numbers(-1, -1, $contains));
var_dump($c);