<?php
/**
 * Created by PhpStorm.
 * User: zxz
 * Date: 2018/11/22
 * Time: 下午10:02
 */

namespace xzchat;


use xzchat\libs\ConnectCollection;

class XzChatApp
{

    /**
     * @var ConnectCollection $connector
     */
    static $connector;

    public static function run(array $config = [])
    {
        self::$connector = new ConnectCollection($config);
        self::$connector->run();
    }
}