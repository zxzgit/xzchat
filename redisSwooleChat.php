<?php

/**
 * Created by zxzTool.
 * User: zxz
 * Datetime: 2018/7/12 19:52
 */
class App {
    /**
     * @var ConnectCollection $connector
     */
    static $connector;
}

spl_autoload_register(function($className){
    echo $className . PHP_EOL;
    
    include str_replace("\\",'/',"../{$className}.php");
});
//require_once 'libs/ConnectCollection.php';
use xzchat\libs\ConnectCollection;

App::$connector = new ConnectCollection();
App::$connector->run();
