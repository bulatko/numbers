<?php

require 'CONSTS.php';
require_once __DIR__ . '/vendor/autoload.php';

class Table
{
    public $id;
    public function __construct()
    {
        global $SPREADSHEET_ID;
        $this->id = $SPREADSHEET_ID;
    }
    public function find_numbers($operator, $type, $str){
        $ret = [];
        $googleAccountKeyFilePath = __DIR__ . '/assets/Phone numbers-9dc4bd6a8dd1.json';
        putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $googleAccountKeyFilePath);

        $client = new Google_Client();
        $client->useApplicationDefaultCredentials();

        $client->addScope('https://www.googleapis.com/auth/spreadsheets');

        $service = new Google_Service_Sheets( $client );


        $response = $service->spreadsheets->get($this->id);

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

        $range = "$operator $type!A1:B$rows_num";
        $response = $service->spreadsheets_values->get($this->id, $range)->values;
        foreach ($response as $item) {
            if(stristr($item[0], $str))
                $ret[] = [$item[0], $item[1]];
        }

        return $ret;
    }

}