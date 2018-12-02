<?php
/**
 * Created by PhpStorm.
 * User: zxz
 * Date: 2018/12/2
 * Time: 下午12:49
 */

namespace xzchat\test\modules\test;


class MessageModule extends \xzchat\libs\MessageModule{
    public $moduleList = [
        'subtest' => \xzchat\test\modules\test\modules\test\MessageModule::class,
    ];
}