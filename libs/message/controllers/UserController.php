<?php
/**
 * Created by zxzTool.
 * User: zxz
 * Datetime: 2018/11/15 18:23
 */

namespace xzchat\libs\message\controllers;

use xzchat\libs\RedisKeyDict;
use xzchat\libs\service\UserService;

class UserController extends AbstractController {
    
    public function actionLogin() {
        $result = UserService::doLogin($this->frame->fd, $this->data);
        return $this->pushMsg(['hello', 'world']);
    }
    
    /**
     * 检测是否登录
     * @return mixed
     */
    public function actionCheckLogin() {
        return $this->pushMsg(['isLogin' => $this->checkUserLogin()]);
    }
}