<?php

/**
 * Created by zxzTool.
 * User: zxz
 * Datetime: 2018/7/12 19:52
 */


include 'vendor/autoload.php';

use xzchat\libs\ConnectCollection;

\xzchat\XzChatApp::$connector = new ConnectCollection();
\xzchat\XzChatApp::$connector->run();
