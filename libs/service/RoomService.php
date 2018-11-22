<?php
/**
 * Created by zxzTool.
 * User: zxz
 * Datetime: 2018/11/19 16:45
 */

namespace xzchat\libs\service;


use xzchat\libs\RedisKeyDict;
use xzchat\XzChatApp;

class RoomService extends BaseService {
    static function fdRelativeToRoom($fd, $roomId) {
        $redisKey = RedisKeyDict::getHashRoomFdList($roomId);
        XzChatApp::$connector->redis->zAdd($redisKey, time(), $fd);
    }
}