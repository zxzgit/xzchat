<?php
/**
 * Created by zxzTool.
 * User: zxz
 * Datetime: 2018/11/15 18:23
 */

namespace xzchat\test\modules\test\controllers;

use xzchat\test\controllers\BaseController;

class TextController extends BaseController {
    
    public function run() {
        return $this->pushMsg(["modules/admin/controllers/TextController result", "world"]);
    }
}