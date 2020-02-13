<?php
//171961446
require 'CONSTS.php';
require 'Table.php';
require 'bd.php';
require 'utils.php';
$kk = file_get_contents('php://input');
$output = json_decode($kk, TRUE);
$t = time();
if (isset($output['callback_query']['data'])) {
    $id = $output['callback_query']['message']['chat']['id'];
    $data = $output['callback_query']['data'];
} else {
    if (!isset($output['message']['chat']['id'])) {
        exit();
    }
    $id = $output['message']['chat']['id'];
    $message = $output['message']['text'];
    $message_id = $output['message']['message_id'];
}


if($id < 0) {
    $exists = (bool)mysqli_num_rows($mysqli->query("SELECT * FROM users WHERE id = '$id'"));
    if(!$exists){
        if(isset($output['message']['new_chat_participant'])){
            $inviter_id = $output['message']['from']['id'];
            if (!mysqli_num_rows($mysqli->query("select id from users where id = '$inviter_id' and isAdmin = 1")))
                exit();
            else {
                $group_name = $output['message']['chat']['title'];
                $mysqli->query("INSERT INTO users VALUES(
'$id',
'$group_name',
'',
0
)
");
                sendMessage($token, $id, "Бот для поиска номеров успешно добавлен. " .
                    "\nИнструкция по работе с ботом:\n" .
                    "<b>Бот работает через команды в этом чате.</b> \n" .
                    "Схема команды: <b>Найди &ltОператор&gt &ltЦифры&gt</b>\n\n" .
                    "Пример команды: !Найди МТС 777\n\n" .
                    "Список доступных операторов: <i>МТС, Мегафон, Билайн, Теле2, Безлимит, Номер - если хотите искать по всем операторам.</i>\n\n" .
                    "");
                exit();
            }

        }
    } else {
        if (preg_match("/^Найди (мтс|мегафон|теле2|билайн|безлимит|номер) [0-9]{1,11}/ui", $message)){
            $arr = explode(' ', $message);
            $operator = ["МТС" => 0, "МЕГАФОН" => 1, "БИЛАЙН" => 2, "ТЕЛЕ2" => 3, "БЕЗЛИМИТ" => 4, "НОМЕР" => -1];
            $operator = $operator[to_uuper($arr[1])];
            $type = -1;
            $contains = $arr[2];
            if($contains == '9' || $contains == '8' || $contains == '89'){
                sendMessage($token, $id, "Все номера содержут \"$contains\". Введите поточнее");
            }else{
                sendMessage($token, $id, "Подождите, идёт поиск");
                $table = new Table();
                $numbers = $table->find_numbers($operator, $type, $contains);
                if (strlen($numbers)) {
                    $numbers = split_numbers($numbers);
                    for ($i = 0; $i < count($numbers); $i++) {
                        sendMessage($token, $id, $numbers[$i]);
                    }
                    sendMessage($token, $id, "Поиск завершен");
                } else {
                    sendMessage($token, $id, "Подходящих номеров не найдено");
                }
            }
        } else {
        }
    }

    exit();
}
$exists = (bool)mysqli_num_rows($mysqli->query("SELECT * FROM users WHERE id = '$id'"));
if (!$exists) {
    $username = $output['message']['from']['first_name'] . " " . $output['message']['from']['last_name'];
    $p = preg_replace('|[^0-9]*|', '', $message);
    $mysqli->query("INSERT INTO users VALUES(
'$id',
'$username',
'',
0
)
");
    sendMessageMain($token, $id, "Регистрация прошла успешно");
    exit();
}
$result = $mysqli->query("SELECT * FROM users WHERE id = '$id' limit 1");
$row = mysqli_fetch_row($result);
$lastMessage = $row[2];
$username = $output['message']['from']['first_name'];
$isAdmin = $row[3];
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
        $row = mysqli_fetch_row($mysqli->query("select * from buttons where id = '$inlineId'"));
        $buttonsArray = $row[2];
        $buttonsArray = json_decode(jsonFromSQL($buttonsArray), true);
        $opz = [];
        $k = 0;
        foreach ($buttonsArray as $subButtonsArray) {
            $opz1 = [];
            foreach ($subButtonsArray as $button){
                $opz1[] = createCallbackData($button[0], "addButtonAfter.$inlineId." . $button[1]);
            }
            $k = 1;
            $opz[] = $opz1;
        }
        $opz[] = [createCallbackData("На новой строке", "addButtonNewLane.$inlineId")];
        $opz[] = [createCallbackData("Отмена", "exit")];
        sendMessage($token, $id, "Выберите после какой кнопки установить новую", createReplyMarkup($opz));
    } else if (stristr($data, 'addButtonAfter.') || stristr($data, 'addButtonNewLane.')) {
        deleteMessage($token, $id, $message_id);
        sendMessage($token, $id, "Отправь название кнопки", createReplyMarkup([
            [createCallbackData("Отмена", "exit")]
        ]));
        setLastMessage($mysqli, $id, $data);
    } else if (stristr($data, 'clickButton.')) {
        deleteMessage($token, $id, $message_id);
        $inlineId = str_replace('clickButton.', '', $data);
        makeInline($token, $id, $inlineId);
        setLastMessage($mysqli, $id, $data);
    } else if (stristr($data, 'deleteButton.')) {
        deleteMessage($token, $id, $message_id);
        $inlineId = str_replace('deleteButton.', '', $data);

        $row = mysqli_fetch_row($mysqli->query("select * from buttons where id = '$inlineId'"));
        $buttonsArray = $row[2];
        $buttonsArray = json_decode(jsonFromSQL($buttonsArray), true);
        $opz = [];
        $k = 0;
        foreach ($buttonsArray as $subButtonsArray) {
            $opz1 = [];
            foreach ($subButtonsArray as $button){
                $opz1[] = createCallbackData($button[0], "destroyButton.$inlineId." . $button[1]);
            }
            $k = 1;
            $opz[] = $opz1;
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
        foreach ($buttonsArray as $subButtonsArray) {
            $opz1 = [];
            foreach ($subButtonsArray as $button){
                if ($button[1] == $deleteInlineId) continue;
                $opz1[] = $button;
            }
            $k = 1;
            $buttonsArrayNew[] = $opz1;
        }

        $buttonsArray = jsonToSQL(json_encode($buttonsArrayNew));

        $text .= json_encode($buttonsArrayNew) . "\n" .
            jsonToSQL(json_encode($buttonsArrayNew)) . "\n";
        $mysqli->query("update buttons set buttons = '$buttonsArray' WHERE id = '$inlineId'");
        answerCallbackQuery($token, $callback_query_id, $text);
        if($inlineId == 1) {
            sendMessageMain($token, $id, "Кнопка удалена");
        } else {

            sendMessage($token, $id, "Кнопка удалена");
            makeInline($token, $id, $inlineId);
        }
    } else if (stristr($data, 'addMessage.')) {
        $inlineId = str_replace('addMessage.', '', $data);
        sendMessage($token, $id, "Отправь сообщение(текст или картинку, видео ,гифку с описанием/без)", createReplyMarkup([
            [createCallbackData("Отмена", "exit")]
        ]));
        setLastMessage($mysqli, $id, $data);
    } else if (stristr($data, 'deleteMessage.')) {
        $inlineId = str_replace('deleteMessage.', '', $data);
        $row = mysqli_fetch_row($mysqli->query("select * from buttons where id = '$inlineId'"));
        $urlArray = $row[1];
        $urlArray = json_decode(jsonFromSQL($urlArray), true);
        $opz = [];
        $k = 0;
        if(count($urlArray) == 1){
            answerCallbackQuery($token, $callback_query_id, "В этом меню всего одно сообщение" );
        }
        foreach ($urlArray as $url) {
            get_content("https://api.telegram.org/bot" . $token . $url[0] . "&chat_id=$id&reply_markup=" . createReplyMarkup([
                    [createCallbackData("Удалить", "destroyMessage.$inlineId." . $url[1])]
                ]));
        }
        $opz[] = [createCallbackData("Отмена", "exit")];
        sendMessage($token, $id, "Выбери сообщение, которое хочешь удалить", createReplyMarkup($opz));
        setLastMessage($mysqli, $id, $data);
    } else if (stristr($data, 'destroyMessage.')) {
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
        if($inlineId == 1) {
            sendMessageMain($token, $id, "Сообщение удалено");
        } else {

            sendMessage($token, $id, "Сообщение удалено");
            makeInline($token, $id, $inlineId);
        }

    } else if ($data == 'admin') {

        $people_count = mysqli_num_rows($mysqli->query("select * from users"));

        $text = "<b>Admin панель</b>\n" .
            "Количество людей в боте: <b>$people_count</b>\n\n" .
            "Выберите необходимое действие";

        sendMessage($token, $id, $text,
            createReplyMarkup([
                [createCallbackData("Запустить рассылку", "makeDistribution")],
                [createCallbackData("Добавить админа", "addAdmin")],
                [createCallbackData("❌Выход", "exit")]
            ]));


    } else if ($data == 'addAdmin') {

        $people_count = mysqli_num_rows($mysqli->query("select * from users"));

        $text = "Введи ID пользователя, которого хочешь добавить в админы.\n" .
            "Свой ID можно узнать, отправив боту команду /id";

        sendMessage($token, $id, $text,
            createReplyMarkup([
                [createCallbackData("❌Выход", "exit")]
            ]));
        setLastMessage($mysqli, $id, $data);

    } else if (stristr($data, 'confirmAddingAdmin.')) {
            $addId = explode(".", $data)[1];
            deleteMessage($token, $id, $message_id);
            $mysqli->query("update users set isAdmin = 1 where id = '$addId'");
        answerCallbackQuery($token, $callback_query_id, "Пользователь добавлен в админы");

    } else if ($data == 'makeDistribution') {

        $people_count = mysqli_num_rows($mysqli->query("select * from users"));

        $text = "Отправь сообщение, которое хочешь разослать";

        sendMessage($token, $id, $text,
            createReplyMarkup([
                [createCallbackData("❌Выход", "exit")]
            ]));

    setLastMessage($mysqli, $id, $data);
    } else if ($data == 'acceptDistribution') {
        deleteMessage($token, $id, $message_id);
        if (preg_match("/^https:\/\/api\.telegram\.org/", $lastMessage)) {
            $mysqli->query("insert into distribution values(0,'$lastMessage',0)");
            sendMessageMain($token, $id, "Рассылка запущена");
        } else {
            sendMessageMain($token, $id, "Ошибка");
        }
        setLastMessage($mysqli, $id, "");
    }else if ($data == 'findNumber') {
        deleteMessage($token, $id, $message_id);

        sendMessage($token, $id, "Выбери интересующего тебя оператора", createReplyMarkup([
            [createCallbackData("🥚 МТС", "operator.0"),
                createCallbackData("🐝 Билайн", "operator.2"),
                createCallbackData("📱 Теле2", "operator.3")],
            [createCallbackData("🔮 Мегафон", "operator.1"),
                createCallbackData("♾ Безлимит", "operator.4")],
            [createCallbackData("Все операторы", "operator.-1")],
            [createCallbackData("❌Выход", "exit")]
        ]));
    } else if (stristr($data, 'operator.')) {
        deleteMessage($token, $id, $message_id);
        $operator = explode('.', $data)[1];
        sendMessage($token, $id, "Выбери разряд номера", createReplyMarkup([
            [createCallbackData("🥉 Бронза", "numberType.$operator.0"),
                createCallbackData("🥈 Серебро", "numberType.$operator.1"),
                createCallbackData("🥇Золото", "numberType.$operator.2")],
            [createCallbackData("💍Платина", "numberType.$operator.3"),
                createCallbackData("💎Бриллиант", "numberType.$operator.4")],
            [createCallbackData("Все разряды", "numberType.$operator.-1")],
            [createCallbackData("🔙Назад", "findNumber"),
                createCallbackData("❌Выход", "exit")],

        ]));
    } else if (stristr($data, 'numberType.')) {
        deleteMessage($token, $id, $message_id);
        $operator = explode('.', $data)[1];
        $numberType = explode('.', $data)[2];
        sendMessage($token, $id, "Введи цифры, которые будут содержаться в твоем номере.\n" .
            "Например: 777", createReplyMarkup([
            [createCallbackData("Поиск по маске", "numberTypeMask.$operator.$numberType")],
            [createCallbackData("🔙Назад", "operator.$operator")],
            [createCallbackData("❌Выход", "exit")],
        ]));
        setLastMessage($mysqli, $id, $data);
    } else if (stristr($data, 'numberTypeMask.')) {
        deleteMessage($token, $id, $message_id);
        $operator = explode('.', $data)[1];
        $numberType = explode('.', $data)[2];
        sendMessage($token, $id, "Введи маску, по которой будешь искать номер.\n" .
            "Например: 123XYXY", createReplyMarkup([
            [createCallbackData("Обычный поиск", "numberType.$operator.$numberType")],
            [createCallbackData("🔙Назад", "operator.$operator")],
            [createCallbackData("❌Выход", "exit")],
        ]));
        setLastMessage($mysqli, $id, $data);
    } else if ($data == 'exit') {
        deleteMessage($token, $id, $message_id);
        sendMessageMain($token, $id, "");
        setLastMessage($mysqli, $id, "");
    }
    exit();
}  else

    if (stristr($lastMessage, 'changeMessage.')) {
        $inlineId = str_replace('changeMessage.', '', $lastMessage);
        $c = 0;
        if ($message) {
            $link = "/sendMessage?parse_mode=html&disable_web_page_preview=1&text=" . urlencode($message);
            $url = "https://api.telegram.org/bot" . $token . $link;
            $c = 1;
        } else if (isset($output['message']["photo"])) {

            $file_id = $output['message']["photo"][count($output['message']["photo"]) - 1]['file_id'];
            $caption = $output['message']["caption"];

            $link = "/sendPhoto?parse_mode=html&disable_web_page_preview=1&photo=$file_id&caption=" . urlencode($caption);
            $url = "https://api.telegram.org/bot" . $token . $link;
            $c = 1;
        } else if (isset($output['message']["video"])) {

            $file_id = $output['message']["video"]['file_id'];
            $caption = $output['message']["caption"];

            $link = "/sendVideo?parse_mode=html&disable_web_page_preview=1&video=$file_id&caption=" . urlencode($caption);
            $url = "https://api.telegram.org/bot" . $token . $link;

            $c = 1;
        } else if (isset($output['message']["animation"])) {

            $file_id = $output['message']["animation"]['file_id'];
            $caption = $output['message']["caption"];

            $link = "/sendAnimation?parse_mode=html&disable_web_page_preview=1&animation=$file_id&caption=" . urlencode($caption);
            $url = "https://api.telegram.org/bot" . $token . $link;

            $c = 1;
        }
        if (isset($link)) {
            $url = json_encode([[$link, 0]]);
            $oldUrl = mysqli_fetch_row($mysqli->query("select * from buttons where id = '$inlineId'"))[1];
            $mysqli->query("update buttons set link = '$url' where id = '$inlineId'");
            if (makeInline($token, $id, $inlineId)) {

                $mysqli->query("update buttons set link = '$url' where id = '$inlineId'");
                sendMessage($token, $id, "Сообщение установлено");
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
        if (stristr($lastMessage, 'addButtonNewLane.')) {
            $inlineId = str_replace('addButtonNewLane.', '', $lastMessage);


            if ($message != '' && strlen($message) < 60) {
                $button = $message;
                $buttonId = mysqli_fetch_row($mysqli->query("select id from buttons order by id desc limit 1"))[0] + 1;

                $row = mysqli_fetch_row($mysqli->query("select * from buttons where id = '$inlineId'"));
                $link = $row[1];
                $link = $link . "&chat_id=$id";
                $buttonsArray = $row[2];
                $buttonsArray = json_decode(jsonFromSQL($buttonsArray), true);
                $buttonsArray[] = [[$button, $buttonId]];
                $buttonsArray = jsonToSQL(json_encode($buttonsArray));
                sendMessage($token, $id, $buttonsArray);
                $mysqli->query("update buttons set buttons = '$buttonsArray' WHERE id = '$inlineId'");
                $mysqli->query("insert into buttons values(0,'[[\"/sendMessage?parse_mode=html&disable_web_page_preview=1&text=" . urlencode("Текст не задан") . "\",0]]','[[]]', $inlineId, 0)");
                if($inlineId == 1) {
                    sendMessageMain($token, $id, "Кнопка добавлена");
                } else {

                    sendMessage($token, $id, "Кнопка добавлена");
                    makeInline($token, $id, $inlineId);
                }
            } else {
                sendMessage($token, $id, "Некорректное название кнопки. Оно должно быть текстовым (до 60 знаков)\n" .
                    "Попробуй еще раз", createReplyMarkup([
                    [createCallbackData("Отмена", "exit")]
                ]));
                exit();
            }
        }  else
            if (stristr($lastMessage, 'addButtonAfter.')) {
                $arr = explode('.', $lastMessage);
                $inlineId = $arr[1];
                $bId = $arr[2];

                if ($message != '' && strlen($message) < 60) {
                    $button = $message;
                    $buttonId = mysqli_fetch_row($mysqli->query("select id from buttons order by id desc limit 1"))[0] + 1;

                    $row = mysqli_fetch_row($mysqli->query("select * from buttons where id = '$inlineId'"));
                    $link = $row[1];
                    $link = $link . "&chat_id=$id";
                    $buttonsArray = $row[2];
                    $buttonsArray = json_decode(jsonFromSQL($buttonsArray), true);
                    $opz = [];
                    foreach ($buttonsArray as $i => $subButtonsArray) {
                        $opz1 = [];
                        foreach ($subButtonsArray as $b){
                            $opz1[] = $b;
                            if($b[1] == $bId)
                                $opz1[] = [$button, $buttonId];
                        }
                        $k = 1;
                        $opz[] = $opz1;
                    }
                    $buttonsArray = $opz;
                    $buttonsArray = jsonToSQL(json_encode($buttonsArray));
                    $mysqli->query("update buttons set buttons = '$buttonsArray' WHERE id = '$inlineId'");
                    $mysqli->query("insert into buttons values(0,'[[\"/sendMessage?parse_mode=html&disable_web_page_preview=1&text=" . urlencode("Текст не задан") . "\",0]]','[[]]', $inlineId, 0)");
                    if($inlineId == 1) {
                        sendMessageMain($token, $id, "Кнопка добавлена");
                    } else {

                        sendMessage($token, $id, "Кнопка добавлена");
                        makeInline($token, $id, $inlineId);
                    }
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
                $link = "/sendMessage?parse_mode=html&disable_web_page_preview=1&text=" . urlencode($message);
                $url = "https://api.telegram.org/bot" . $token . $link;
                $c = 1;
            } else if (isset($output['message']["photo"])) {

                $file_id = $output['message']["photo"][count($output['message']["photo"]) - 1]['file_id'];
                $caption = $output['message']["caption"];

                $link = "/sendPhoto?parse_mode=html&disable_web_page_preview=1&photo=$file_id&caption=" . urlencode($caption);
                $url = "https://api.telegram.org/bot" . $token . $link;
                $c = 1;
            } else if (isset($output['message']["video"])) {

                $file_id = $output['message']["video"]['file_id'];
                $caption = $output['message']["caption"];

                $link = "/sendVideo?parse_mode=html&disable_web_page_preview=1&video=$file_id&caption=" . urlencode($caption);
                $url = "https://api.telegram.org/bot" . $token . $link;

                $c = 1;
            } else if (isset($output['message']["animation"])) {

                $file_id = $output['message']["animation"]['file_id'];
                $caption = $output['message']["caption"];

                $link = "/sendAnimation?parse_mode=html&disable_web_page_preview=1&animation=$file_id&caption=" . urlencode($caption);
                $url = "https://api.telegram.org/bot" . $token . $link;

                $c = 1;
            }
                if (isset($url)) {


                    $row = mysqli_fetch_row($mysqli->query("select * from buttons where id = '$inlineId'"));

                    $urlId = $row[4] + 1;

                    $urlArray = $row[1];
                    $urlArrayOld = $urlArray;
                    $urlArray = json_decode($urlArray, true);
                    $urlArray[] = [$link, $urlId];
                    $urlArray = json_encode($urlArray);
                    $mysqli->query("update buttons set link = '$urlArray', lastMessageId = $urlId WHERE id = '$inlineId'");


                    if (makeInline($token, $id, $inlineId)) {
                        sendMessage($token, $id, "Сообщение добавлено");

                    } else {

                        sendMessage($token, $id, "Ошибка сообщения. Попробуй еще раз", createReplyMarkup([
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

                if ($lastMessage == '/json') {
                    sendMessage($token, $id, $kk);
                }else

                    if ($isAdmin && $lastMessage == 'makeDistribution') {
                        if ($message) {
                            $url = "https://api.telegram.org/bot" . $token . "/sendMessage?text=" . urlencode($message);
                            $c = 1;
                        } else if (isset($output['message']["photo"])) {

                            $file_id = $output['message']["photo"][count($output['message']["photo"]) - 1]['file_id'];
                            $caption = $output['message']["caption"];
                            $url = "https://api.telegram.org/bot$token/sendPhoto?photo=$file_id&caption=" . urlencode($caption);
                            $c = 1;
                        } else if (isset($output['message']["video"])) {

                            $file_id = $output['message']["video"]['file_id'];
                            $caption = $output['message']["caption"];
                            $url = "https://api.telegram.org/bot$token/sendVideo?video=$file_id&caption=" . urlencode($caption);

                            $c = 1;
                        } else if (isset($output['message']["animation"])) {

                            $file_id = $output['message']["animation"]['file_id'];
                            $caption = $output['message']["caption"];
                            $url = "https://api.telegram.org/bot$token/sendAnimation?animation=$file_id&caption=" . urlencode($caption);

                            $c = 1;
                        }

                        get_content($url . "&chat_id=$id&reply_markup=" . createReplyMarkup([
                                [createCallbackData("Подтвердить", "acceptDistribution")],
                                [createCallbackData("Отмена", "exit")]
                            ]));
                        setLastMessage($mysqli, $id, $url);
                        exit();
                    }else

                        if ($isAdmin && $lastMessage == 'addAdmin') {
                            $addId = $message;
                            if(mysqli_num_rows($mysqli->query("select * from users where id = '$addId'"))){
                                $text = "Подтверди добавление <a href='tg://user?id=$addId'>этого пользователя</a> в <b>админы</b>";
                                sendMessage($token, $id, $text, createReplyMarkup([
                                    [
                                        createCallbackData("Подтвердить", "confirmAddingAdmin.$addId")
                                    ],
                                    [
                                        createCallbackData("Отмена", "exit")
                                    ]
                                ]));
                            } else {
                                sendMessage($token, $id, "Нет пользователя с таким ID, попробуй еще раз.\n" .
                                    "Для того, чтобы узнать свой ID, необходимо отправить боту сообщение /id",
                                createReplyMarkup([
                                    [
                                        createCallbackData("Отмена", "exit")
                                    ]
                                ]));
                                exit();
                            }

                        } else if (stristr($lastMessage, 'numberType.')) {
                            $operator = explode('.', $lastMessage)[1];
                            $numberType = explode('.', $lastMessage)[2];
                            $table = new Table();
                            if ($message == '89' || $message == '9' || $message == '8') {
                                sendMessage($token, $id, "Все номера содержат '$message', ведите поточнее", createReplyMarkup([
                                    [createCallbackData("Искать еще раз", $lastMessage)],
                                    [createCallbackData("🔙Назад", "operator.$operator")],
                                    [createCallbackData("❌Выход", "exit")],
                                ]));
                                exit();
                            }
                            sendMessage($token, $id, "Подождите, идёт поиск");
                            $numbers = $table->find_numbers($operator, $numberType, $message);
                            if (strlen($numbers)) {

                                $text = "Список подходящих номеров:\n";
                                $numbers = split_numbers($numbers);
                                for ($i = 0; $i < count($numbers); $i++) {
                                    sendMessage($token, $id, $numbers[$i]);
                                }
                                sendMessage($token, $id, "Поиск завершен", createReplyMarkup([
                                    [createCallbackData("Искать еще раз", $lastMessage)],
                                    [createCallbackData("🔙Назад", "operator.$operator")],
                                    [createCallbackData("❌Выход", "exit")],
                                ]));
                            } else {
                                sendMessage($token, $id, "Подходящих номеров не найдено", createReplyMarkup([
                                    [createCallbackData("Искать еще раз", $lastMessage)],
                                    [createCallbackData("🔙Назад", "operator.$operator")],
                                    [createCallbackData("❌Выход", "exit")],
                                ]));
                            }


                        } else if (stristr($lastMessage, 'numberTypeMask.')) {
                            $operator = explode('.', $lastMessage)[1];
                            $numberType = explode('.', $lastMessage)[2];
                            $table = new Table();
                            if ($message == '89' || $message == '9' || $message == '8') {
                                sendMessage($token, $id, "Все номера содержат '$message', ведите поточнее", createReplyMarkup([
                                    [createCallbackData("Искать еще раз", $lastMessage)],
                                    [createCallbackData("🔙Назад", "operator.$operator")],
                                    [createCallbackData("❌Выход", "exit")],
                                ]));
                                exit();
                            }
                            if (strlen($message) < 3) {
                                sendMessage($token, $id, "Минимальная длина строки для поиска по маске - 3. Попробуй еще раз", createReplyMarkup([
                                    [createCallbackData("Искать еще раз", $lastMessage)],
                                    [createCallbackData("🔙Назад", "operator.$operator")],
                                    [createCallbackData("❌Выход", "exit")],
                                ]));
                                exit();
                            }
                            sendMessage($token, $id, "Подождите, идёт поиск");
                            $numbers = $table->find_numbers($operator, $numberType, $message, 1);
                            if (strlen($numbers)) {

                                $text = "Список подходящих номеров:\n";
                                $numbers = split_numbers($numbers);
                                for ($i = 0; $i < count($numbers); $i++) {
                                    sendMessage($token, $id, $numbers[$i]);
                                }
                                sendMessage($token, $id, "Поиск завершен", createReplyMarkup([
                                    [createCallbackData("Искать еще раз", $lastMessage)],
                                    [createCallbackData("🔙Назад", "operator.$operator")],
                                    [createCallbackData("❌Выход", "exit")],
                                ]));
                            } else {
                                sendMessage($token, $id, "Подходящих номеров не найдено", createReplyMarkup([
                                    [createCallbackData("Искать еще раз", $lastMessage)],
                                    [createCallbackData("🔙Назад", "operator.$operator")],
                                    [createCallbackData("❌Выход", "exit")],
                                ]));
                            }


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