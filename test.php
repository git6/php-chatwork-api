<?php

/**
 * Author: Git6.com
 * Site: https://git6.com/
 */

//require_once('./chatwork-api_token-auth.php');
require_once('./chatwork-api_token_v2.php');

//$chatwork = new chatwork; // ライブラリに直接TOKENを書いた場合
$chatwork = new chatwork('TOKEN'); // 抽象化でTOKEN設定する場合

//$chatwork->setToken('TOKEN');

$me = $chatwork->getMe();
$rooms = $chatwork->getRooms();

foreach ($rooms as $room_item) {
    // マイルームを探す
    if ($room_item->type == 'my') {
        $my_room = $room_item;
    }
}
if (!$my_room) {
    $my_room = $me->room_id;
}


// マイルームにテストメッセージを投げる
$message_body = 'テストメッセージです。改行は' . "\n" . 'そのまま反映されます。';
$message_body = 'テストメッセージです。' . date('Y年m月d日 H時i分s秒');

$message_to = $chatwork->messagenotationTo($me->account_id, $me->name); // 宛先/返信タグへの変換メソッドもあります。名前は省略出来ます。
$chatwork->postRoomsMessages($my_room->room_id, $message_body);

var_dump($me);
