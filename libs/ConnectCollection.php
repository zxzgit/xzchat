<?php
/**
 * Created by zxzTool.
 * User: zxz
 * Datetime: 2018/11/15 18:03
 */

namespace xzchat\libs;

use xzchat\libs\message\MessageHandler;

class ConnectCollection {
    public $initBatchClearNum = 1000;//初始时每批删除redis遗留数据的数目
    
    /**
     * @var \Redis $redis
     */
    public $redis;
    
    function __construct() {
        $this->initRedis();
    }
    
    function initRedis(){
        $this->redis = new \Redis();
        $this->redis->connect('127.0.0.1', 6379);
    }
    
    /**
     * 初始化链接时执行
     * @param $server
     * @param $id
     */
    function connectionInit($server, $id) {
        $this->connectionInitClearRedis($server, $id);
    }
    
    /**
     * 初始化删除上次退出遗留redis信息
     */
    function connectionInitClearRedis($server, $id) {
        /** @var \Redis $redis */
        $redis           = $this->redis;
        $hashFdToUserKey = RedisKeyDict::getHashFdToUser();
        
        echo "初始化删除上次退出遗留redis信息开始：" . PHP_EOL;
        //按批次删除退出时redis保存的链接信息与用户信息
        while ($remainFdToUid = $redis->zRevRange($hashFdToUserKey, 0, $this->initBatchClearNum - 1, true)) {
            $batchDelUidKey = [];
            foreach ($remainFdToUid as $fd => $uid) {
                $batchDelUidKey[] = RedisKeyDict::getHashUserInfoKey($uid);
            }
            
            echo "本批次要删除的uid：" . PHP_EOL;
            print_r($batchDelUidKey);
            
            $deleteUserInfoNum = $redis->del($batchDelUidKey);//执行批量的删除用户信息
            
            if ($deleteUserInfoNum == count($remainFdToUid)) {
                $redis->zRemRangeByRank($hashFdToUserKey, 0, $this->initBatchClearNum - 1);
            }
        }
        
        $redis->del($hashFdToUserKey);//fd与用户对应关系
        
        echo "初始化删除上次退出遗留redis信息完成" . PHP_EOL;
    }
    
    public function run() {
        $this->server = new \swoole_websocket_server("0.0.0.0", 9502);
        MessageHandler::$server = &$this->server;
    
        $that = $this;
        //必须在onWorkerStart回调中创建redis/mysql连接
        $this->server->on('workerstart', function ($server, $id) use (&$that) {
            $that->connectionInit($server, $id);
            $server->connector = &$that;
        });
        
        $this->server->on('open', function ($server, $req) {
            echo "connection open: {$req->fd}\n";
        });
        
        $this->server->on('message', function ($server, $frame) {
            MessageHandler::msgDeal($server->connector, $frame);
        });
        
        $this->server->on('close', function ($server, $fd) {
            /** @var Redis $redis */
            
            //移除该链接用户信息
            $redis           = $server->connector->redis;
            $hashFdToUserKey = RedisKeyDict::getHashFdToUser();
            $uid             = $redis->zScore($hashFdToUserKey, $fd);
            
            //删除fd对应uid信息
            $redis->zRem($hashFdToUserKey, $fd);//fd对应uid
            //删除redis用户信息
            $redisUserInfoKey = RedisKeyDict::getHashUserInfoKey($uid);
            $redis->del($redisUserInfoKey);//删除用户信息存储
            
            echo "connection close: {$fd}\n";
        });
        
        $this->server->start();
    }
}