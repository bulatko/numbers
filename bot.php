<?php
//171961446
$fId = 451604411;
$kk = file_get_contents('php://input');
$output = json_decode($kk, TRUE);
include("bd.php");
$t = time();
if (isset($output['callback_query']['data'])) {
    $id = $output['callback_query']['message']['chat']['id'];
    $data = $output['callback_query']['data'];
} else {
    if (!isset($output['message']['chat']['id'])) exit();
    $id = $output['message']['chat']['id'];
    $message = $output['message']['text'];
    $message_id = $output['message']['message_id'];
}

$isAdmin = 0;
$adminsArray = [
    862310416,
    171961446,
    887097236,
    236910420
];
if (in_array($id,$adminsArray)) $isAdmin = 1;

$exists = (bool)mysqli_num_rows($mysqli->query("SELECT * FROM users WHERE id = '$id'"));
if (!$exists) {
    $username = $output['message']['from']['first_name'] . " " . $output['message']['from']['last_name'];
    $p = preg_replace('|[^0-9]*|', '', $message);
    $mysqli->query("INSERT INTO users VALUES(
'$id',
'$username',
''
)
");
    sendPhoto($token,$id,'AgADAgADyKwxGwgaYUt35BT65ZNhYd_rtw8ABAEAAwIAA3gAA_YSAwABFgQ',"💎Бот моментальных продаж💎

✅Работаем 24/7✅

💸Лидер рынка💸

Большой ассортимент сладостей👌");
}
$result = $mysqli->query("SELECT * FROM users WHERE id = '$id' limit 1");
$row = mysqli_fetch_row($result);
$lastMessage = $row[2];
$username = $output['message']['from']['first_name'];
if ($data) {
    $callback_query_id = $output['callback_query']['id'];
    $username = $output['callback_query']['from']['first_name'];
    $message_id = $output['callback_query']['message']['message_id'];
    if (stristr($data, 'changeMessage.')) {
        deleteMessage($token, $id, $message_id);
        $inlineId = str_replace('changeMessage.', '', $data);
        sendMessage($token, $id, "Отправь сообщение, которое хочешь поставить в этом меню (текст или картинку, гифку, видео с текстом/без)\n" .
            "&lt;b&gt;123&lt;/b&gt; - <b> 123</b>\n" .
            "&lt;i&gt;123&lt;/i&gt; - <i> 123</i>\n" .
            "&lt;a href=\"vk.com\"&gt;Вконтакте&lt;/a&gt; - <a href=\"vk.com\"> Вконтакте </a>\n", createReplyMarkup([
            [createCallbackData("Отмена", "exit")]
        ]));
        setLastMessage($mysqli, $id, $data);
    } else if (stristr($data, 'addButton.')) {
        deleteMessage($token, $id, $message_id);
        $inlineId = str_replace('addButton.', '', $data);
        sendMessage($token, $id, "Отправь название кнопки", createReplyMarkup([
            [createCallbackData("Отмена", "exit")]
        ]));
        setLastMessage($mysqli, $id, $data);
    } else if (stristr($data, 'clickButton.')) {
        deleteMessage($token, $id, $message_id);
        $inlineId = str_replace('clickButton.', '', $data);
        makeInline($id, $inlineId);
        setLastMessage($mysqli, $id, $data);
    } else if (stristr($data, 'deleteButton.')) {
        deleteMessage($token, $id, $message_id);
        $inlineId = str_replace('deleteButton.', '', $data);

        $row = mysqli_fetch_row($mysqli->query("select * from buttons where id = '$inlineId'"));
        $buttonsArray = $row[2];
        $buttonsArray = json_decode(jsonFromSQL($buttonsArray), true);
        $opz = [];
        $k = 0;
        foreach ($buttonsArray as $button) {
            $opz[] = [createCallbackData($button[0], "destroyButton.$inlineId." . $button[1])];
        }
        $opz[] = [createCallbackData("Отмена", "exit")];
        sendMessage($token, $id, "Выбери кнопку, которую хочешь удалить", createReplyMarkup($opz));
        setLastMessage($mysqli, $id, $data);
    } else if (stristr($data, 'destroyButton.')) {
        deleteMessage($token, $id, $message_id);
        $text = '';
        $str = explode('.', $data);
        $inlineId = $str[1];
        $deleteInlineId = $str[2];
        $row = mysqli_fetch_row($mysqli->query("select * from buttons where id = '$inlineId'"));
        $buttonsArray = $row[2];
        $text .= $buttonsArray . "\n" .
            jsonFromSQL($buttonsArray) . "\n";
        $buttonsArray = json_decode(jsonFromSQL($buttonsArray), true);
        $buttonsArrayNew = [];
        $opz = [];
        $k = 0;
        foreach ($buttonsArray as $button) {
            if ($button[1] == $deleteInlineId) continue;
            $buttonsArrayNew[] = $button;
        }

        $buttonsArray = jsonToSQL(json_encode($buttonsArrayNew));

        $text .= json_encode($buttonsArrayNew) . "\n" .
            jsonToSQL(json_encode($buttonsArrayNew)) . "\n";
        $mysqli->query("update buttons set buttons = '$buttonsArray' WHERE id = '$inlineId'");
        answerCallbackQuery($token, $callback_query_id, $text);
        makeInline($id, $inlineId);
    } else if (stristr($data, 'addMessage.')) {
        deleteMessage($token, $id, $message_id);
        $inlineId = str_replace('addMessage.', '', $data);
        sendMessage($token, $id, "Отправь сообщение(текст или картинку, видео ,гифку с описанием/без)", createReplyMarkup([
            [createCallbackData("Отмена", "exit")]
        ]));
        setLastMessage($mysqli, $id, $data);
    } else if (stristr($data, 'deleteMessage.')) {
        deleteMessage($token, $id, $message_id);
        $inlineId = str_replace('deleteMessage.', '', $data);
        $row = mysqli_fetch_row($mysqli->query("select * from buttons where id = '$inlineId'"));
        $urlArray = $row[1];
        $urlArray = json_decode(jsonFromSQL($urlArray), true);
        $opz = [];
        $k = 0;
        foreach ($urlArray as $url) {
            get_content($url[0] . "&chat_id=$id&reply_markup=" . createReplyMarkup([
                    [createCallbackData("Удалить", "destroyMessage.$inlineId." . $url[1])]
                ]));
        }
        $opz[] = [createCallbackData("Отмена", "exit")];
        sendMessage($token, $id, "Выбери сообщение, которое хочешь удалить", createReplyMarkup($opz));
        setLastMessage($mysqli, $id, $data);
    } else if (stristr($data, 'destroyMessage.')) {
        deleteMessage($token, $id, $message_id);
        $text = '';
        $str = explode('.', $data);
        $inlineId = $str[1];
        $deleteInlineId = $str[2];
        $row = mysqli_fetch_row($mysqli->query("select * from buttons where id = '$inlineId'"));
        $urlArray = $row[1];
        $text .= $urlArray . "\n" .
            jsonFromSQL($urlArray) . "\n";
        $urlArray = json_decode(jsonFromSQL($urlArray), true);
        $urlArrayNew = [];
        $opz = [];
        $k = 0;
        foreach ($urlArray as $button) {
            if ($button[1] == $deleteInlineId) continue;
            $urlArrayNew[] = $button;
        }

        $urlArray = jsonToSQL(json_encode($urlArrayNew));

        $mysqli->query("update buttons set link = '$urlArray' WHERE id = '$inlineId'");
        answerCallbackQuery($token, $callback_query_id, $text);
        makeInline($id, $inlineId);
    } else
        if ($data == 'sendQiwi') {
            editMessageText($token, $id, $message_id, "Введи номер кошелька, на который хочешь перевести деньги и сумму через пробел. \n" .
                "Пример: +79123456789 100", createReplyMarkup([
                [createCallbackData("Отмена", "exit")]
            ]));
            setLastMessage($mysqli, $id, 'sendQiwi');
            exit();
        } else
            if ($data == 'changeQiwi') {
                editMessageText($token, $id, $message_id, "Введи номер нового кошелька и его токен через пробел. \n" .
                    "Пример: +79123456789 1g723sm1x2g0rif0gefw9009", createReplyMarkup([
                    [createCallbackData("Отмена", "exit")]
                ]));
                setLastMessage($mysqli, $id, 'changeQiwi');
                exit();
            } else
                if ($data == 'exit') {
                    deleteMessage($token, $id, $message_id);
                    sendMessageMain($token, $id, "Привет, $username");
                    setLastMessage($mysqli, $id, "");
                }
    exit();
} else if ($message == '😎 Для друзей!') {
    $inlineId = 1;
    makeInline($id, $inlineId);

} else if ($message == '📡 Радар') {
    $text = 'В разделе <b>Радар</b> вы можете узнать какие клады находятся ближе всего к вам в пешей доступности и купить любой из них.

Клады в списке отсортированы по дальности относительно вашего положения (чем ближе клад - тем он выше в списке).

Для продолжения нажмите на кнопку:
🌐 <b>Поделиться местоположением</b>';
    sendMessage($token,$id,$text,createKeyboardMenu([
        [createKeyboardButton("🌐 Поделиться местоположением")],
        [createKeyboardButton("❌ Отменить 'Радар'")],
    ]));

}  else if ($message == '🌐 Поделиться местоположением') {

    $inlineId = 1496;
    makeInline($id, $inlineId);

}   else if ($message == '❌ Отменить \'Радар\'') {
sendMessageMain($token,$id,"Главное меню");

} else if ($message == '🏢 Города') {
    $inlineId = 3;
    makeInline($id, $inlineId);

} else if ($message == '💰 Баланс') {
    $inlineId = 4;
    makeInline($id, $inlineId);

} else if ($message == '💁 Поддержка') {
    $inlineId = 5;
    makeInline($id, $inlineId);

} else if ($isAdmin && $message == 'Admin панель') {
    $qiwi = mysqli_fetch_row($mysqli->query("select * from qiwi"));
    $number = $qiwi[0];
    $bearer = $qiwi[1];
    $balance = qiwiGetBalance($number, $bearer);
    $number = preg_replace("/.{4}$/", '****', $number);
    $bearer = preg_replace("/.{5}$/", '*****', $bearer);
    $num = mysqli_num_rows($mysqli->query("select * from users"));
    $text = "<b>Qiwi кошелек:</b> +$number\n" .
        "<b>Токен:</b> $bearer\n" .
        "<b>Текущий баланс:</b> $balance\n" .
        "<b>Людей в боте:</b> $num";
    sendMessage($token, $id, $text, createReplyMarkup([
        [createCallbackData("Сменить кошелек", "changeQiwi")],
        [createCallbackData("Совершить перевод", "sendQiwi")]
    ]));

} else

    if (stristr($lastMessage, 'changeMessage.')) {
        $inlineId = str_replace('changeMessage.', '', $lastMessage);
        $c = 0;
        if ($message) {
            $url = "https://api.telegram.org/bot" . $token . "/sendMessage?parse_mode=html&disable_web_page_preview=1&text=" . urlencode($message);
            $c = 1;
        } else if (isset($output['message']["photo"])) {

            $file_id = $output['message']["photo"][count($output['message']["photo"]) - 1]['file_id'];
            $caption = $output['message']["caption"];
            $url = "https://api.telegram.org/bot$token/sendPhoto?parse_mode=html&disable_web_page_preview=1&photo=$file_id&caption=" . urlencode($caption);
            $c = 1;
        } else if (isset($output['message']["video"])) {

            $file_id = $output['message']["video"]['file_id'];
            $caption = $output['message']["caption"];
            $url = "https://api.telegram.org/bot$token/sendVideo?parse_mode=html&disable_web_page_preview=1&video=$file_id&caption=" . urlencode($caption);

            $c = 1;
        } else if (isset($output['message']["animation"])) {

            $file_id = $output['message']["animation"]['file_id'];
            $caption = $output['message']["caption"];
            $url = "https://api.telegram.org/bot$token/sendAnimation?parse_mode=html&disable_web_page_preview=1&animation=$file_id&caption=" . urlencode($caption);

            $c = 1;
        }
        if (isset($url)) {
            $url = json_encode([[$url,0]]);
            $oldUrl = mysqli_fetch_row($mysqli->query("select * from buttons where id = '$inlineId'"))[1];
            $mysqli->query("update buttons set link = '$url' where id = '$inlineId'");
            if (makeInline($id, $inlineId)) {

                $mysqli->query("update buttons set link = '$url' where id = '$inlineId'");
                sendMessageMain($token, $id, "Сообщение установлено");
            } else {

                $mysqli->query("update buttons set link = '$oldUrl' where id = '$inlineId'");
                sendMessage($token, $id, "Неверный формат сообщения. Попробуй еще раз", createReplyMarkup([
                    [createCallbackData("Отмена", "exit")]
                ]));
                exit();
            }
        } else {
            sendMessage($token, $id, "Неверный формат сообщения. Попробуй еще раз", createReplyMarkup([
                [createCallbackData("Отмена", "exit")]
            ]));
            exit();
        }
    } else

        if (stristr($lastMessage, 'addButton.')) {
            $inlineId = str_replace('addButton.', '', $lastMessage);


            if ($message != '' && strlen($message) < 60) {
                $button = $message;
                $buttonId = mysqli_fetch_row($mysqli->query("select id from buttons order by id desc limit 1"))[0] + 1;

                $row = mysqli_fetch_row($mysqli->query("select * from buttons where id = '$inlineId'"));
                $link = $row[1];
                $link = $link . "&chat_id=$id";
                $buttonsArray = $row[2];
                $buttonsArray = json_decode(jsonFromSQL($buttonsArray), true);
                $buttonsArray[] = [$button, $buttonId];
                $buttonsArray = jsonToSQL(json_encode($buttonsArray));
                $mysqli->query("update buttons set buttons = '$buttonsArray' WHERE id = '$inlineId'");
                $mysqli->query("insert into buttons values(0,'[[\"https://api.telegram.org/bot$token/sendMessage?parse_mode=html&disable_web_page_preview=1&text=".urlencode("Текст не задан")."\",0]]','[]')");
                sendMessage($token, $id, "Кнопка добавлена");
                makeInline($id, $inlineId);
            } else {
                sendMessage($token, $id, "Некорректное название кнопки. Оно должно быть текстовым (до 60 знаков)\n" .
                    "Попробуй еще раз", createReplyMarkup([
                    [createCallbackData("Отмена", "exit")]
                ]));
                exit();
            }
        } else

            if (stristr($lastMessage, 'addMessage.')) {
                $c = 0;
                $inlineId = str_replace('addMessage.', '', $lastMessage);
                if ($message) {
                    $url = "https://api.telegram.org/bot" . $token . "/sendMessage?parse_mode=html&disable_web_page_preview=1&text=" . urlencode($message);
                    $c = 1;
                } else if (isset($output['message']["photo"])) {

                    $file_id = $output['message']["photo"][count($output['message']["photo"]) - 1]['file_id'];
                    $caption = $output['message']["caption"];
                    $url = "https://api.telegram.org/bot$token/sendPhoto?parse_mode=html&disable_web_page_preview=1&photo=$file_id&caption=" . urlencode($caption);
                    $c = 1;
                } else if (isset($output['message']["video"])) {

                    $file_id = $output['message']["video"]['file_id'];
                    $caption = $output['message']["caption"];
                    $url = "https://api.telegram.org/bot$token/sendVideo?parse_mode=html&disable_web_page_preview=1&video=$file_id&caption=" . urlencode($caption);

                    $c = 1;
                } else if (isset($output['message']["animation"])) {

                    $file_id = $output['message']["animation"]['file_id'];
                    $caption = $output['message']["caption"];
                    $url = "https://api.telegram.org/bot$token/sendAnimation?parse_mode=html&disable_web_page_preview=1&animation=$file_id&caption=" . urlencode($caption);

                    $c = 1;
                }
                if (isset($url)) {

                    $urlId = rand(0, 10e8);

                    $row = mysqli_fetch_row($mysqli->query("select * from buttons where id = '$inlineId'"));

                    $urlArray = $row[1];
                    $urlArrayOld = $urlArray;
                    $urlArray = json_decode($urlArray, true);
                    $urlArray[] = [$url, $urlId];
                    $urlArray = json_encode($urlArray);
                    $mysqli->query("update buttons set link = '$urlArray' WHERE id = '$inlineId'");


                    if (makeInline($id, $inlineId)) {
                        sendMessage($token, $id, "Сообщение добавлено");

                    } else {

                        $mysqli->query("update buttons set link = '$urlArrayOld' WHERE id = '$inlineId'");
                        sendMessage($token, $id, "Ошибка сообщения. Попробуй еще раз", createReplyMarkup([
                            [createCallbackData("Отмена", "exit")]
                        ]));
                        exit();
                    }
                }else {
                    sendMessage($token, $id, "Неверный формат сообщения. Попробуй еще раз", createReplyMarkup([
                        [createCallbackData("Отмена", "exit")]
                    ]));
                    exit();
                }


            } else

            if ($lastMessage == '/json') {
                sendMessage($token, $id, $kk);
                exit();
            } else

                if (stristr($lastMessage, 'test111')) {
                    sendMessage($token, $id, $message);
                    exit();
                } else

                        if ($lastMessage == 'sendQiwi') {

                            $s = explode(' ', $message);

                            $toNumber = $s[0];
                            $amount = $s[1];

                            $qiwi = mysqli_fetch_row($mysqli->query("select * from qiwi"));
                            $number = $qiwi[0];
                            $bearer = $qiwi[1];
                            $result = sendMoney($number, $bearer, $toNumber, $amount);
                            if (stristr($result, "Accepted")) {
                                sendMessageMain($token, $id, "Платеж совершен");
                            } else {
                                $result = json_decode($result, 1);
                                $result = $result['message'];
                                if ($result == 'Недостаточно средств') {
                                    sendMessage($token, $id, $result . "\n" .
                                        "Попробуй еще раз.", createReplyMarkup([
                                        [createCallbackData("Отмена", "exit")]
                                    ]));
                                    exit();
                                } else {

                                    sendMessage($token, $id, "Возникла ошибка.\n" .
                                        "$result\n" .
                                        "Попробуй еще раз\n" .
                                        "Пример: +79123456789 100", createReplyMarkup([
                                        [createCallbackData("Отмена", "exit")]
                                    ]));
                                    exit();
                                }
                            }
                            exit();
                        } else

                            if ($message == '/menu') {

                                sendMessageMain($token, $id, "Привет, $username");


                            } else
                                if ($message == '/start') {

                                    sendMessageMain($token, $id, "Привет, $username");


                                } else
                                    if ($message == '/id' || $message == '/Id' || $message == '/ID') {

                                        sendMessageMain($token, $id, "Твой ID: $id");


                                    } else {
                                        sendMessageMain($token, $id, "Не понимаю о чем ты.");
                                    }



setLastMessage($mysqli, $id, $message);
//     file_get_contents($tt."/sendMessage?chat_id=".$id."&text=Все говорят ".$output['message']['text'].", а ты купи слона");