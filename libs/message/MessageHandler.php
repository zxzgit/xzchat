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
        $pid = pcntl_fork();
        if ($pid == -1)
        {
            //echo 'could not fork生成子进程失败';
        }
        elseif($pid == 0)
        {
            echo str_repeat('=',20) . PHP_EOL;

            //信息处理
            echo "收到的信息: {$frame->data}" . PHP_EOL;
            echo "链接id: {$frame->fd}" . PHP_EOL;

            $data = self::parseData($frame->data);//传回来的信息是json

            (new MessageDistributor($connector, $frame, $data))->run();

            echo '当前内存使用量：' . memory_get_usage(true) . PHP_EOL;
            echo '当前子进程pid：' . posix_getpid() . PHP_EOL;

            //处理完信息之后杀死进程
            posix_kill(posix_getpid(), SIGINT);

            echo str_repeat('=',20) . PHP_EOL;
        }
        else
        {
            //echo "I'm the parent process 子进程的pid值：{$pid} \n";
        }
    }
    
    static function parseData($frameData) {
        return $receiveInfo = json_decode($frameData, true);
    }
}
