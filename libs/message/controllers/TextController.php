<?php
/**
 * Created by zxzTool.
 * User: zxz
 * Datetime: 2018/11/15 18:23
 */

namespace xzchat\libs\message\controllers;

class TextController extends AbstractController {
    
    public function run() {
        return $this->pushMsg(["hello", "world"]);
    }
}