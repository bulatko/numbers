<?php
include("bd.php");
$r = $mysqli->query("select * from distribution ORDER by id limit 1");
if(!mysqli_num_rows($r)) exit();
$row = mysqli_fetch_row($r);
$dId = $row[0];
$link = $row[1];
$lastId = $row[2];
sleep(3);
$row1 = mysqli_fetch_row($mysqli->query("select * from distribution ORDER by id limit 1"));
if($row1[2]!=$lastId)exit();
$q = $mysqli->query("select id from users where id > '$lastId' ORDER by id");
while ($row = mysqli_fetch_array($q)){
    $id = $row[0];
    $url = $link . "&chat_id=$id";
    $mysqli->query("update distribution set lastId ='$id' WHERE id = '$dId'");
    get_content($url);
}
$mysqli->query("delete from distribution WHERE id = '$dId'");



function get_content($url, $data = [])
{

    $ch = curl_init($url);
    if ($data != null) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, __DIR__ . 'cookie.txt');
    curl_setopt($ch, CURLOPT_COOKIEFILE, __DIR__ . 'cookie.txt');
    $res = curl_exec($ch);
    curl_close($ch);
    return $res;
}
