<?php

namespace xzchat\libs\message;


use xzchat\libs\ConnectCollection;

class MessageHandler {
    /**
     * 用户信息处理
     * @param ConnectCollection $connector
     * @param $frame
     * @param $msgType
     * @param $data
     */
    static public function msgDeal(&$connector, &$frame) {
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

            //消息分发器构建
            $messageDistributor = $connector->messageDistributor;
            /** @var MessageDistributor $distributor */
            $distributor = new $messageDistributor($connector, $frame, $data);
            $distributor->run();

            echo '当前内存使用量：' . memory_get_usage(true) . PHP_EOL;
            echo '当前子进程pid：' . posix_getpid() . PHP_EOL;

            echo str_repeat('=',20) . PHP_EOL;

            //发送信息后事件处理
            $connector->triggerEvent('afterMessage', [&$connector->server, &$frame]);

            //处理完信息之后杀死进程
            posix_kill(posix_getpid(), SIGINT);
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
