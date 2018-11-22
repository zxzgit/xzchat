<?php
/**
 * Created by zxzTool.
 * User: zxz
 * Datetime: 2018/11/15 18:23
 */

namespace xzchat\libs\message\controllers;

use xzchat\libs\RedisKeyDict;
use xzchat\libs\service\RoomService;
use xzchat\XzChatApp;

class RoomController extends AbstractController {
    
    public function actionInterRoom() {
        if ($this->checkUserLogin() && isset($this->data['roomId']) && $this->data['roomId']) {
            $roomId = $this->data['roomId'];
            $roomId && RoomService::fdRelativeToRoom($this->frame->fd, $roomId);
            
            //发送消息给房间里面的人
            $redisKey = RedisKeyDict::getHashRoomFdList($roomId);
            $start    = 0;
            $batchNum = 100;
            while ($batchFdList = XzChatApp::$connector->redis->zRevRange($redisKey, $start, $start + ($batchNum - 1))) {
                print_r($batchFdList);
                //给同房间的用户发通知
                foreach ($batchFdList as $fd) {
                    $this->frame->fd !=$fd && $this->pushMsg([
                        'msg' => $this->frame->fd . '进入房间',
                        'fromUser' => '',
                    ], 200, $fd);
                }
                $start = $start + $batchNum;
            }
            
            
            return $this->pushMsg(['inter room', 'user is login']);
        } else {
            return $this->pushMsg(['inter room,', 'user is no login']);
        }
    }
}