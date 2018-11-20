<?php

namespace xzchat\libs\message;


class MessageHandler {
    static $server = null;
    
    /**
     * 信息发送
     * @param $frame
     * @param $returnInfo
     */
    static public function pushMsg($frame, $returnInfo) {
        MessageHandler::$server->push($frame->fd, json_encode($returnInfo));
    }
    
    /**
     * 用户信息处理
     * @param $connector
     * @param $frame
     * @param $msgType
     * @param $data
     */
    static public function msgDeal(&$connector, $frame) {
        echo "收到的信息: {$frame->data}" . PHP_EOL;
        echo "链接id: {$frame->fd}" . PHP_EOL;
        
        $data = self::parseData($frame->data);//传回来的信息是json
        
        (new MessageDistributor($connector, $frame, $data))->run();
    
        echo '当前内存使用量：' . memory_get_usage(true) . PHP_EOL;
    }
    
    static function parseData($frameData) {
        return $receiveInfo = json_decode($frameData, true);
    }
}
