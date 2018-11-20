<?php
/**
 * Created by zxzTool.
 * User: zxz
 * Datetime: 2018/11/15 18:23
 */

namespace xzchat\libs\message\controllers;

use xzchat\libs\RedisKeyDict;

class EventController extends AbstractController {
    
    public function actionInterRoom() {
        return $this->pushMsg(['event', 'msg']);
    }
}