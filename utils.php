<?php


function sendMessagePayIn($token, $id, $msg, $amount, $pay_id)
{
    return file_get_contents("https://api.telegram.org/bot" . $token . "/sendMessage?chat_id=" . $id . "&text=" . urlencode($msg) . InlineKeyboardPayIn($amount, $pay_id));

}

function sendPhoto($token, $id, $file_id, $caption, $reply_markup = null, $parse_mode = 'html')
{
    return get_content("https://api.telegram.org/bot$token/sendPhoto?chat_id=$id&photo=$file_id&caption=" . urlencode($caption) . "&reply_markup=$reply_markup&parse_mode=$parse_mode");
}

function sendPhotoToChannel($token, $id, $file_id, $caption)
{
    return file_get_contents("https://api.telegram.org/bot" . $token . "/sendPhoto?chat_id=" . $id . "&photo=$file_id&caption=" . urlencode($caption) . InlineKeyboardSendToChannel());
}

function sendVideoToChannel($token, $id, $file_id, $caption)
{
    return file_get_contents("https://api.telegram.org/bot" . $token . "/sendVideo?chat_id=" . $id . "&photo=$file_id&caption=" . urlencode($caption) . InlineKeyboardSendToChannel());
}

function sendDocumentToChannel($token, $id, $file_id, $caption)
{
    return file_get_contents("https://api.telegram.org/bot" . $token . "/sendDocument?chat_id=" . $id . "&photo=$file_id&caption=" . urlencode($caption) . InlineKeyboardSendToChannel());
}

function sendMessageMarkdown($token, $id, $msg)
{
    return file_get_contents("https://api.telegram.org/bot" . $token . "/sendMessage?chat_id=" . $id . "&text=" . urlencode($msg) . "&parse_mode=Markdown");
}

function sendMessageMain($token, $id, $msg)
{
    //
    global $payeer, $isAdmin;
    $arr = [];

    $arr[] = [createCallbackData("Подобрать номер", "findNumber")];

    if ($isAdmin) {
        $arr[] = [createCallbackData("Admin панель", "admin")];
    }
    sendMessage($token, $id, $msg);
    makeInline($token, $id, 1, $arr);

}


function sendContact($token, $id, $phone_number, $first_name, $reply_markup = null)
{
    return get_content("https://api.telegram.org/bot$token/sendContact?chat_id=$id&phone_number=$phone_number&first_name=$first_name&reply_markup=$reply_markup");
}

function sendMessage($token, $id, $msg, $reply_markup = null, $disable_web_page_preview = 1, $parse_mode = 'html', $reply_to_message_id = null)
{
    return get_content("https://api.telegram.org/bot" . $token . "/sendMessage?chat_id=" . $id . "&text=" . urlencode($msg) . "&reply_markup=$reply_markup&disable_web_page_preview=$disable_web_page_preview&parse_mode=$parse_mode&reply_to_message_id=$reply_to_message_id");
}

function editMessageText($token, $id, $message_id, $text, $reply_markup = null, $disable_web_page_preview = 1, $pref = 1)
{

    return get_content("https://api.telegram.org/bot$token/editMessageText?chat_id=$id&message_id=$message_id&text=" . urlencode($text) . "&reply_markup=$reply_markup&disable_web_page_preview=$disable_web_page_preview&parse_mode=html");

}

function editMessageMedia($token, $id, $message_id, $media, $reply_markup = null)
{

    return get_content("https://api.telegram.org/bot$token/editMessageMedia?chat_id=$id&message_id=$message_id&media=$media&reply_markup=$reply_markup");

}

function editMessageCaption($token, $id, $message_id, $caption, $reply_markup = null)
{
    return get_content("https://api.telegram.org/bot$token/editMessageMedia?chat_id=$id&message_id=$message_id&caption=$caption&reply_markup=$reply_markup");

}

function createMedia($type, $media, $caption)
{
    $a = array(
        "type" => $type,
        "media" => $media,
        "caption" => $caption
    );
    return json_encode($a);
}

function editMessageReplyMarkup($token, $id, $message_id, $reply_markup)
{
    return get_content("https://api.telegram.org/bot$token/editMessageReplyMarkup?chat_id=$id&message_id=$message_id&reply_markup=$reply_markup");
}

function createReplyMarkup($opz)
{
    $keyboard = array('inline_keyboard' => $opz);
    $keyboard = json_encode($keyboard, true);
    $reply_markup = $keyboard;
    return urlencode($reply_markup);
}

function createCallbackData($text, $data)
{
    return array('text' => $text, 'callback_data' => $data);
}

function createURL($text, $url)
{
    return array('text' => $text, 'url' => $url);
}

function createKeyboardButton($text, $request_contact = false, $request_location = false)
{
    $r = [
        'text' => $text,
        'request_contact' => $request_contact,
        'request_location' => $request_location
    ];
    return $r;
}

function createKeyboardMenu($buttons, $resize_keyboard = true, $one_time_keyboard = false, $selective = true)
{

    $keyboard = json_encode($keyboard = ['keyboard' => $buttons,
        'resize_keyboard' => $resize_keyboard,
        'one_time_keyboard' => $one_time_keyboard,
        'selective' => $selective
    ]);
    $reply_markup = $keyboard;
    return $reply_markup;

}

function forwardMessage($token, $id, $from_id, $message_id)
{
    return file_get_contents("https://api.telegram.org/bot" . $token . "/forwardMessage?chat_id=" . $id . "&from_chat_id=" . $from_id . "&message_id=" . $message_id);
}

function deleteMessage($token, $id, $message_id)
{
    get_content("https://api.telegram.org/bot" . $token . "/deleteMessage?chat_id=" . $id . "&message_id=" . $message_id);

}

function answerCallbackQuery($token, $callback_query_id, $text, $show_alert = true)
{
    get_content("https://api.telegram.org/bot$token/answerCallbackQuery?" .
        "callback_query_id=$callback_query_id&" .
        "text=$text&" .
        "show_alert=$show_alert");
}

function setLastMessage($mysqli, $id, $message)
{
    $mysqli->query("update users set lastMessage = '$message' where id = '$id'");
}


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

function makeInline($token, $id, $inlineId, $add = null)
{
    global $mysqli, $isAdmin;
    $row = mysqli_fetch_row($mysqli->query("select * from buttons where id = '$inlineId'"));

    $buttonsArray = $row[2];
    $buttonsArray = json_decode(jsonFromSQL($buttonsArray), true);
    $parent = $row[3];
    if($add)
        $opz = $add;
    else
        $opz = [];
    $k = 0;
    foreach ($buttonsArray as $subButtonsArray) {
        $opz1 = [];
        foreach ($subButtonsArray as $button){
            $opz1[] = createCallbackData(jsonFromSQL($button[0]), "clickButton." . $button[1]);
        }
        $k = 1;
        $opz[] = $opz1;
    }
    $l = 0;
    $urlArray = json_decode($row[1], 1);
    for ($i = 0; $i < count($urlArray) - 1; $i++) {
        $l = 1;
        $url = "https://api.telegram.org/bot" . $token . $urlArray[$i][0] . "&chat_id=$id";
        $res = get_content($url);

    }
    $link = "https://api.telegram.org/bot" . $token . $urlArray[count($urlArray) - 1][0];
    if ($isAdmin) {

        $opz[] = [createCallbackData("*Добавить кнопку*", "addButton.$inlineId")];
        $opz[] = [createCallbackData("*Добавить сообщение*", "addMessage.$inlineId")];
        if ($l)
            $opz[] = [createCallbackData("*Удалить сообщение*", "deleteMessage.$inlineId")];
        else
            $opz[] = [createCallbackData("*Изменить сообщение*", "changeMessage.$inlineId")];
        if ($k)
            $opz[] = [createCallbackData("*Удалить кнопку*", "deleteButton.$inlineId")];
    }
    if($inlineId != 1) {
        if ($parent == 1)
            $opz[] = [createCallbackData("Назад", "exit")];
        else
            $ops[] = [createCallbackData("🔙Назад", "clickButton.$parent"), createCallbackData("❌Выход", "exit")];
    }
    $link .= "&chat_id=$id&reply_markup=" . createReplyMarkup($opz);
    $res = file_get_contents($link);
    $res = json_decode($res, 1);

    //sendMessage("832527735:AAGvc6Y7TWfZeMooP2kjkUMwKcd9xsyItso",$id,$res);
    return $res['ok'];

}

function jsonToSQL($json)
{

    $json = str_replace('\\u', 'ёёёёЁ', $json);
    return $json;
}

function jsonFromSQL($json)
{

    $json = str_replace('ёёёёЁ', '\\u', $json);
    return $json;
}

function split_numbers($numbers)
{
    $c = 0;
    $arr = [];
    while ($c != strlen($numbers) && strlen($numbers)) {


        $c = min(4000, strlen($numbers));
        $c0 = $c;
        while ($numbers[$c - 1] != '.') {
            $c--;
            if($c < 0) {
                $c = $c0;
                break;
            }
        }

        if($c > 2)
        $arr[] = substr($numbers, 0, $c);
        $numbers = substr($numbers, $c, strlen($numbers) - $c);

    }
    return $arr;
}

function to_uuper($str){
    $s = $str;
    $lower = "йцукенгшщзхъфывапролджэячсмитьбюqwertyuiopasdfghjklzxcvbnm";
    $upper = "ЙЦУКЕНГШЩЗХЪФЫВАПРОЛДЖЭЯЧСМИТЬБЮQWERTYUIOPASDFGHJKLZXCVBNM";
    for($i = 0; $i < strlen($str); $i++){
        for($j = 0; $j < strlen($lower); $j++){
            if($str[$i] == $lower[$j])
                $s[$i] = $upper[$j];
        }
    }
    return $s;
}

function contains_mask($str, $mask){

    for($i = 0; $i + strlen($mask['str']) - 1 < strlen($str); $i++){
        $new_str = substr($str, $i, strlen($mask['str']));
        if(is_mask($new_str, $mask))
            return true;
    }
    return false;

}

function is_mask($string, $mask){
    $mask_string = $mask['str'];
    for($i = 0; $i < strlen($mask_string); $i++)
        if(is_numeric($mask_string[$i]))
            if($mask_string[$i] != $string[$i])
                return 0;
    $indexes = [];
            foreach ($mask['values'] as $item){
                $indexes[] = $item[0];
                for($i = 1; $i < count($item); $i++)
                    if($string[$item[$i]] != $string[$item[$i - 1]])
                        return 0;
            }

            for ($i = 0; $i < count($indexes); $i++){
                for($j = $i + 1; $j < count($indexes); $j++)
                    if($string[$indexes[$i]] == $string[$indexes[$j]])
                        return 0;
            }

            return 1;
}

function array_from_mask($str){
    $arr = [];
    $arr['str'] = $str;
    $arr['values'] = [];
    $found = [];
    for($i = 0; $i < strlen($str); $i++){
        if(!is_numeric($str[$i])){
            if(!in_array($str[$i], $found))
            {
                $found[] = $str[$i];
                $arr['values'][$str[$i]] = [$i];
            } else{
                $arr['values'][$str[$i]][] = $i;
            }


        }
    }
    return $arr;
}
