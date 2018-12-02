<?php
/**
 * Created by PhpStorm.
 * User: zxz
 * Date: 2018/12/2
 * Time: 下午12:49
 */

namespace xzchat\libs\message\modules\test;


class MessageModule extends \xzchat\libs\message\MessageModule{
    public $moduleList = [
        'subtest' => \xzchat\libs\message\modules\test\modules\test\MessageModule::class,
    ];
}