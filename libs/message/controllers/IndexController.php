<?php
/**
 * Created by zxzTool.
 * User: zxz
 * Datetime: 2018/11/15 18:23
 */

namespace xzchat\libs\message\controllers;


class IndexController extends AbstractController {
    
    public function actionIndex() {
        
        return $this->pushMsg(['hello', 'world']);
    }
}