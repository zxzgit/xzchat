<?php

/**
 * Created by zxzTool.
 * User: zxz
 * Datetime: 2018/7/12 19:52
 */

class RedisKeyDict{
    const KEY_PREFIX = 'chatRoom:';
    
    /**
     * 给定key添加上前缀
     * @param $key
     * @return string
     */
    static function getFormat($key){
        return self::KEY_PREFIX . $key;
    }
    
    /**
     * 指定房间当前有效链接redis的key
     * @param $roomId
     * @return string
     */
    static function getRoomFdListKey($roomId){
        return self::getFormat("room:fdList:$roomId");
    }
    
    /**
     * 指定fd所在的所有房间
     * @param $fd
     * @return string
     */
    static function getRoomFdRoomList($fd){
        return self::getFormat("fd:roomList:$fd");
    }
    
    /**
     * fd上当前用户信息存储
     * @param $fd
     * @return string
     */
    static function getRoomFdUserInfo($fd){
        return self::getFormat("fd:userInfo:$fd");
    }
}


class ConnectCollection {
	static $linksInfo = [];
	static $roomInfo  = [];

	function __construct() {
		$this->webSocket = new swoole_websocket_server("0.0.0.0", 9502);
		$this->init();
	}

	private function init() {
//必须在onWorkerStart回调中创建redis/mysql连接
		$this->webSocket->on('workerstart', function ($serv, $id) {
			$redis = new Redis();
			$redis->connect('127.0.0.1', 6379);
			$serv->redis = $redis;
		});

		$this->webSocket->on('open', function ($server, $req) {
			echo "connection open: {$req->fd}\n";
		});

		$this->webSocket->on('message', function ($server, $frame) {
			/** @var Redis $redis */
			$redis       = $server->redis;
			$receiveInfo = json_decode($frame->data, true);//传回来的信息是json

			$redisLinkUserInfoKey = RedisKeyDict::getRoomFdUserInfo($frame->fd);

			$hasAuth = false;

			if (!empty($receiveInfo) && isset($receiveInfo['token'])) {
                
                $redisLinkUserInfo = $redis->hGetAll($redisLinkUserInfoKey);

				if (!empty($redisLinkUserInfo) && $receiveInfo['token'] == $redisLinkUserInfo['token']) {
					echo "用户存在验证记录,登录验证成功\n";
					$hasAuth = true;
				} else {
                    //todo 验证用户是否登录
                    if ($receiveInfo['token']) {//如果验证token成功
                        $redisSetNameResult  = $redis->hSet($redisLinkUserInfoKey, 'name', $receiveInfo['name']);
                        $redisSetTokenResult = $redis->hSet($redisLinkUserInfoKey, 'token', $receiveInfo['token']);
                        if (
                            $redisSetNameResult !== false
                            &&
                            $redisSetTokenResult !== false
                        ) {
                            echo "登录验证成功\n";
                            $hasAuth = true;
                        } else {//如果存储不成功，则删除
                            $redis->del($redisLinkUserInfoKey);
                        }
                    }
				}
			}


			echo "received message: {$frame->data}\n";
			echo "链接id: {$frame->fd}\n";

			$returnInfo = [
				'code' => '100001',
				'msg'  => '请您先登录',
			];

			//todo 如果没有登录,提示用户登录
			if (!$hasAuth) {
				return $server->push($frame->fd, json_encode($returnInfo));
			}

			//todo 根据聊天类型进行不同处理
			$chatType = $receiveInfo['msgType'];
			switch ($receiveInfo['msgType']) {
				case 'text':
					if ($receiveInfo['textType'] == 'forRoom') {//发送给房间的消息
						$roomId     = $receiveInfo['roomId'];
						$content    = $receiveInfo['content'];
						$roomFdList = $redis->sMembers(RedisKeyDict::getRoomFdListKey($roomId));

						$returnInfo = [
							'code'     => '100000',
							'msg'      => $content,
							'fromUser' => $receiveInfo['name'],
						];
						//给同房间的每个用户都发送消息
						foreach ($roomFdList as $fd) {
							($frame->fd != $fd) && $server->push($fd, json_encode($returnInfo));
						}
						echo "链接：{$frame->fd} 在房间 {$roomId} 发言:{$content}\n";
					}
					break;
				case 'event':
					if ($receiveInfo['eventType'] == 'interRoom') {//进入房间事件
						$roomId = $receiveInfo['roomId'];
						$redis->sAdd(RedisKeyDict::getRoomFdListKey($roomId), $frame->fd);//添加当前链接到房间1
						$redis->sAdd(RedisKeyDict::getRoomFdRoomList($frame->fd), $roomId);//添加当前链接所进的所有聊天室
						echo "链接：{$frame->fd} 进入了房间 {$roomId}\n";
					}
					break;
			}

			$server->push($frame->fd, json_encode(["hello", "world"]));
		});

		$this->webSocket->on('close', function ($server, $fd) {
			/** @var Redis $redis */

			//移除该链接用户信息
			$redis = $server->redis;
			$redis->del(RedisKeyDict::getRoomFdUserInfo($fd));

			//移除该链接所在关联房间信息 或者可以通过循环房间来依次删除该fd所在的房间对应信息
			$fdRoomListName = RedisKeyDict::getRoomFdRoomList($fd);
			$roomList       = $redis->sMembers($fdRoomListName);
			$redis->del($fdRoomListName);
			foreach ($roomList as $roomId) {
				$redis->sRem(RedisKeyDict::getRoomFdListKey($roomId), $fd);
			}

			echo "connection close: {$fd}\n";
		});

		$this->webSocket->start();
	}
}

new ConnectCollection();
