<?php

require 'CONSTS.php';
require_once __DIR__ . '/vendor/autoload.php';

class Table
{
    public $id, $response, $service;
    public function __construct()
    {
        global $SPREADSHEET_ID;
        $this->id = $SPREADSHEET_ID;

        $googleAccountKeyFilePath = __DIR__ . '/assets/Phone numbers-9dc4bd6a8dd1.json';
        putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $googleAccountKeyFilePath);

        $client = new Google_Client();
        $client->useApplicationDefaultCredentials();

        $client->addScope('https://www.googleapis.com/auth/spreadsheets');

        $service = new Google_Service_Sheets( $client );
        $this->service = $service;

        $this->response = $service->spreadsheets->get($this->id);
    }
    public function find_numbers($operator, $type, $str){
    $operators = ["МТС", "МЕГАФОН", "БИЛАЙН", "ТЕЛЕ2", "БЕЗЛИМИТ"];
    $types = ["БРОНЗА", "СЕРЕБРО", "ЗОЛОТО", "ПЛАТИНА", "БРИЛЛИАНТ"];
        if($operator == -1){
            $t = "";
            for($i = 0; $i < count($operators); $i++){
                $res = $this->find_numbers($i, $type, $str);
                if(strlen($res))
                    $t .= $res . "\n";
            }
            return $t;
        }

        if($type == -1){
            $t = "";
            for($i = 0; $i < count($types); $i++){
                $res = $this->find_numbers($operator, $i, $str);
                if(strlen($res))
                    $t .= $res;
            }
            return $t;
        }


        $operator = $operators[$operator];
        $type = $types[$type];

        $ret = [];
// Свойства таблицы
        $spreadsheetProperties = $this->response->getProperties();
        $spreadsheetProperties->title; // Название таблицы

        foreach ($this->response->getSheets() as $sheet) {

            // Свойства листа
            $sheetProperties = $sheet->getProperties();
            $sheetProperties->title; // Название листа

            $gridProperties = $sheetProperties->getGridProperties();
            $gridProperties->columnCount; // Количество колонок
            if($sheetProperties->title == "$operator $type")
            $rows_num = $gridProperties->rowCount; // Количество строк

        }

        $range = "$operator $type!A1:B$rows_num";
        $response = $this->service->spreadsheets_values->get($this->id, $range)->values;
        foreach ($response as $item) {
            if(stristr($item[0], $str))
                $ret[] = $item[0] . " - " . $item[1] . "р.\n";
        }
    $return = "";
        if(count($ret)){
            $return = "<b>$operator $type</b>\n";
            for($i = 0; $i < count($ret); $i++)
                $return .= $ret[$i];
        }

        return $return;
    }

}