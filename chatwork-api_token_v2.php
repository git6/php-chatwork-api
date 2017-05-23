<?php

/**
 * Author: Git6.com
 * Site: https://git6.com/
 */
class chatwork
{

    /* 基本的にはシステム毎に1tokenで運用するならここに書くだけでいい */
    public $token = 'TOKEN';
    public $endpoint_base = 'https://api.chatwork.com/v2';

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
        if ($parm) {
            $content = $parm;
        } else {
            $content = array();
        }
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
        if ($rooms_id) {
            $endpoint = '/rooms/' . $rooms_id;
        } else {
            $endpoint = '/rooms';
        }

        return $this->sendRequest('GET', $endpoint);
    }


    public function postRooms($description = false, $icon_preset = false, $members_admin_ids, $members_member_ids = array(), $members_readonly_ids = array(), $name)
    {
        $endpoint = '/rooms';

        $icon_preset_value_list = array('group', ' check', ' document', ' meeting', ' event', ' project', ' business', ' study', ' security', ' star', ' idea', ' heart', ' magcup', ' beer', ' music', ' sports', ' travel');
        if (!in_array($icon_preset, $icon_preset_value_list)) {
            $icon_preset = 'group';
        }
        $parm['description'] = (string)$description;
        $parm['icon_preset'] = (string)$icon_preset;
        $parm['members_admin_ids'] = implode(',', $members_admin_ids);
        $parm['members_member_ids'] = implode(',', $members_member_ids);
        $parm['members_readonly_ids'] = implode(',', $members_readonly_ids);
        $parm['name'] = (string)$name;

        return $this->sendRequest('POST', $endpoint, $parm);
    }

    public function putRooms($rooms_id, $description = false, $icon_preset = false, $name = false)
    {
        $endpoint = '/rooms/' . $rooms_id;

        $icon_preset_value_list = array('group', ' check', ' document', ' meeting', ' event', ' project', ' business', ' study', ' security', ' star', ' idea', ' heart', ' magcup', ' beer', ' music', ' sports', ' travel');
        if (!in_array($icon_preset, $icon_preset_value_list)) {
            $icon_preset = 'group';
        }
        $parm['description'] = (string)$description;
        $parm['icon_preset'] = (string)$icon_preset;
        $parm['name'] = (string)$name;

        return $this->sendRequest('PUT', $endpoint, $parm);
    }

    public function deleteRooms($rooms_id, $action_type)
    {
        $endpoint = '/rooms/' . $rooms_id;

        $action_type_value_list = array('leave', ' delete');
        if (!in_array($action_type, $action_type_value_list)) {
            $action_type = 'leave';
        }

        $parm['action_type'] = (string)$action_type;

        return $this->sendRequest('DELETE', $endpoint, $parm);
    }

    /* Members */

    public function getRoomsMembers($rooms_id)
    {
        $endpoint = '/rooms/' . $rooms_id . '/members';
        return $this->sendRequest('GET', $endpoint);
    }

    public function putRoomsMembers($rooms_id, $members_admin_ids, $members_member_ids = array(), $members_readonly_ids = array())
    {
        $endpoint = '/rooms/' . $rooms_id . '/members';

        $parm['members_admin_ids'] = implode(',', $members_admin_ids);
        $parm['members_member_ids'] = implode(',', $members_member_ids);
        $parm['members_readonly_ids'] = implode(',', $members_readonly_ids);

        return $this->sendRequest('PUT', $endpoint, $parm);
    }

    /* Message */
    public function getRoomsMessages($rooms_id, $message_id = false, $force = false)
    {
        if ($message_id) {
            $endpoint = '/rooms/' . $rooms_id . '/messages/' . $message_id;
            $parm['force'] = $force;
        } else {
            $endpoint = '/rooms/' . $rooms_id . '/messages';
        }

        return $this->sendRequest('GET', $endpoint, $parm);
    }

    public function postRoomsMessages($rooms_id, $body)
    {
        $endpoint = '/rooms/' . $rooms_id . '/messages';

        $parm['body'] = (string)$body;

        return $this->sendRequest('POST', $endpoint, $parm);
    }


    /* task */
    public function getRoomsTasks($rooms_id, $task_id = false, $account_id = false, $assigned_by_account_id = false, $status = array('open', 'done'))
    {
        if ($task_id) {
            // List
            $endpoint = '/rooms/' . $rooms_id . '/tasks';

            $parm['account_id'] = $account_id;
            $parm['assigned_by_account_id'] = $assigned_by_account_id;
            $parm['status'] = $status;

        } else {
            // Single Detail
            $endpoint = '/rooms/' . $rooms_id . '/tasks/' . $task_id;
        }

        return $this->sendRequest('GET', $endpoint, $parm);
    }

    public function postRoomsTasks($rooms_id, $body, $limit = false, $to_ids)
    {
        $endpoint = '/rooms/' . $rooms_id . '/tasks';

        $parm['body'] = $body;
        $parm['limit'] = $limit;
        $parm['to_ids'] = implode(',', $to_ids);

        return $this->sendRequest('POST', $endpoint, $parm);
    }


    /* File */
    public function getRoomsFiles($rooms_id, $file_id = false, $account_id = false, $create_download_url = false)
    {
        if ($file_id) {
            $endpoint = '/rooms/' . $rooms_id . '/files';
            $parm['account_id'] = $account_id;
        } else {
            $endpoint = '/rooms/' . $rooms_id . '/files/' . $file_id;
            if ($create_download_url) {
                $parm['create_download_url'] = 1;
            } else {
                $parm['create_download_url'] = 0;
            }
        }

        return $this->sendRequest('GET', $endpoint, $parm);
    }

    /* -------------------------------------------- */
    /* incoming_requests */
    /* -------------------------------------------- */
    public function getIncomingRequests()
    {
        $endpoint = '/incoming_requests';
        return $this->sendRequest('GET', $endpoint);
    }

    public function putIncomingRequests($request_id)
    {
        $endpoint = '/incoming_requests/' . $request_id;
        return $this->sendRequest('PUT', $endpoint);
    }

    public function deleteIncomingRequests($request_id)
    {
        $endpoint = '/incoming_requests/' . $request_id;
        return $this->sendRequest('DELETE', $endpoint);
    }


    /* Option */
    public function messagenotationTo($account_id, $label = false)
    {
        $label_display = ($label) ? ' ' . $label . 'さん' : '';
        return '[To:' . $account_id . ']' . $label_display;
    }

    public function messagenotationRp($account_id, $rooms_id, $message_id, $label = false)
    {
        $label_display = ($label) ? ' ' . $label . 'さん' : '';
        return '[rp aid=' . $account_id . ' to=' . $rooms_id . '-' . $message_id . ']' . $label_display;
    }

    public function messagenotationQt($account_id, $message_body, $time = false)
    {
        $time_set = ($time) ? ' time=' . $time . '' : '';
        return '[qt][qtmeta aid=' . $account_id . '' . $time_set . ']' . $message_body . '[/qt]';
    }

    public function messagenotationInfo($message_body, $title = false)
    {
        $title_set = ($title) ? '[title]' . $title . '[/title]' : '';
        return '[info]' . $title_set . '' . $message_body . '[/info]';
    }

    public function messagenotationPicon($account_id)
    {
        return '[picon:' . $account_id . ']';
    }

    public function messagenotationPiconName($account_id)
    {
        return '[piconname:' . $account_id . ']';
    }


    /* -------------------------------------------- */
}
