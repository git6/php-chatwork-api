<?php

/**
 * Author: Git6.com
 * Site: http://git6.com/
 *
 * Chatwork API v1 (APIトークン認証版) PHPラッパー
 *
 * よりシンプルに、このラッパー自体を変更して
 * システムに合わせた処理で返せるようなつくり。
 * オブジェクトになっているが1エンドポイントにつき1メソッドで定義
 * 毎回設定を変える事も可能だが、システムでincludeして
 * そのシステム専用の1ファイルとして使われる方をメインとしています。
 *
 * 命名規則
 * methodEndpointName($parm)
 * メソッドの後に変数(RoomIDとか)を抜いたエンドポイントで大体動く。
 * パラメータもそのままだからドキュメント通りで動く感じ。
 *
 * 値が不正の場合、リクエストを送らないとか面倒な事はやってない
 * 全部レスポンスを貰って判断する前提
 * バリデーションは値を渡す前に済ませておくか
 * レスポンスを見て判断すること。
 *
 * ドキュメント：http://developer.chatwork.com/ja/
 */
class chatwork
{

    /* 基本的にはシステム毎に1tokenで運用するならここに書くだけでいい */
    public $token = 'TOKEN';
    public $endpoint_base = 'https://api.chatwork.com/v1';

    public $last_response_header;

    /* APIの制限値 */
    public $rateLimit_limit; //最大コール回数
    public $rateLimit_remaining; //残りコール回数
    public $rateLimit_reset; //次に制限がリセットされる時間（Unix time）

    /* 呼び出す時にtokenを設定する事も出来る */
    public function __construct($token = false)
    {
        if ($token) {
            $this->token = $token;
        }
    }

    /* tokenを設定するならこれを呼べばいい */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /* 毎回endpointのbaseを設定するならこれを呼べばいい */
    public function setEndpoint($endpoint_base)
    {
        $this->token = $endpoint_base;
    }


    /* Common */
    public function sendRequest($method, $endpoint, $parm = false)
    {
        $content = $parm;
        $header = array(
            'Content-Type: application/x-www-form-urlencoded',
            'Content-Length: ' . strlen(http_build_query($content, '', '&')),
            'X-ChatWorkToken: ' . $this->token
        );

        $context = array(
            'http' => array(
                'method' => $method,
                'header' => implode("\r\n", $header),
                'content' => http_build_query($content, '', '&'),
                'ignore_errors' => true
            )
        );

//        if ($parm) {
//            foreach ($parm as $key => $val) {
//                $parm_keyval[] = $key . '=' . $val;
//            }
//            $parm_query = implode('&', $parm_keyval);
//            $query_strings = $this->endpoint_base . $endpoint . '?method=' . $method . '&' . $parm_query;
//        } else {
//            $query_strings = $this->endpoint_base . $endpoint;
//        }
        $query_strings = $this->endpoint_base . $endpoint;

        $result_data = file_get_contents($query_strings, false, stream_context_create($context));
        if ($result_data) {
            $this->last_response_header = $http_response_header;

            // レスポンスヘッダに乗ってるAPIの制限値を取得(仮実装)
            $this->rateLimit_limit = $this->getResponseHeader('X-RateLimit-Limit');
            $this->rateLimit_remaining = $this->getResponseHeader('X-RateLimit-Remaining');
            $this->rateLimit_reset = $this->getResponseHeader('X-RateLimit-Reset');

        } else {
            //$http_response_headerはリクエストに失敗したら更新しないらしい
            $this->last_response_header = '';
            return json_encode(array('error' => 'ChatworkAPI:Wrapper:sendRequest:file_get_contents'));
        }

        return json_decode($result_data);
    }

    /* レスポンスヘッダから特定の項目の値を抽出 */
    public function getResponseHeader($header_item_name)
    {
        foreach ($this->last_response_header as $key => $r) {
            if (stripos($r, $header_item_name) !== FALSE) {
                list($headername, $headervalue) = explode(":", $r, 2);
                return trim($headervalue);
            }
        }
    }


    /* -------------------------------------------- */
    /* me */
    /* -------------------------------------------- */

    public function getMe()
    {
        $endpoint = '/me';
        return $this->sendRequest('GET', $endpoint);
    }

    /* -------------------------------------------- */
    /* my */
    /* -------------------------------------------- */
    public function getMyStatus()
    {
        $endpoint = '/my/status';
        return $this->sendRequest('GET', $endpoint);
    }

    public function getMyTasks($assigned_by_account_id, $status = array('open', 'done'))
    {
        $endpoint = '/my/tasks';

        $parm['method'] = 'GET';
        $parm['assigned_by_account_id'] = implode(',', $assigned_by_account_id);
        $parm['status'] = implode(',', $status);

        return $this->sendRequest('GET', $endpoint, $parm);
    }

    /* -------------------------------------------- */
    /* contacts */
    /* -------------------------------------------- */
    public function getContacts()
    {
        $endpoint = '/contacts';
        return $this->sendRequest('GET', $endpoint);
    }

    /* -------------------------------------------- */
    /* Rooms */
    /* -------------------------------------------- */

    public function getRooms($rooms_id = false)
    {

    }


    public function postRooms($rooms_id = false)
    {

    }

    public function putRooms($rooms_id = false)
    {

    }

    public function deleteRooms($rooms_id = false)
    {

    }

    /* Members */

    public function getRoomsMembers($rooms_id = false)
    {

    }

    public function putRoomsMembers($rooms_id = false)
    {

    }

    /* Message */
    public function getRoomsMessages($rooms_id = false, $message_id = false)
    {

    }

    public function putRoomsMessages($rooms_id = false)
    {

    }


    /* task */
    public function getRoomsTasks($rooms_id, $task_id = false, $account_id, $assigned_by_account_id, $status = array('open', 'done'))
    {
        if ($task_id) {
            // List


        } else {
            // Single Detail

        }

    }

    public function postRoomsTasks($rooms_id, $body, $limit = false, $to_ids)
    {
        $endpoint = '/rooms/' . $rooms_id . '/tasks';
        if ($limit == false) {
            // limitが無い場合勝手に初期値を入れる
            $limit = strtotime('2000-01-01 00:00:00');
        }

        $parm['method'] = 'POST';
        $parm['body'] = $body;
        $parm['limit'] = $limit;
        $parm['to_ids'] = implode(',', $to_ids);

        return $this->sendRequest('POST', $endpoint, $parm);
    }


    /* File */
    public function getRoomsFiles($rooms_id = false, $file_id = false)
    {

    }

    public function putRoomsFiles($rooms_id = false)
    {

    }

    /* -------------------------------------------- */
}
