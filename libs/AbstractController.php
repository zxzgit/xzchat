<?php
/**
 * Created by zxzTool.
 * User: zxz
 * Datetime: 2018/11/15 18:25
 */

namespace xzchat\libs;


abstract class AbstractController {
    const RETURN_CODE = [
        200 => 'Success',
        403 => 'Forbidden',
    ];
    
    /**
     * @var MessageDistributor $distributor
     */
    public    $distributor;
    public    $frame;
    public    $distributorData;
    public    $action;
    public    $data        = [];
    protected $isLoginUser = null;
    protected $user        = false;
    
    
    function __construct(&$distributor, $frame, $distributorData, $config = []) {
        $this->distributor     = &$distributor;
        $this->frame           = $frame;
        $this->distributorData = $distributorData;
        $this->init($config);
    }
    
    protected function init($config) {
        foreach ($config as $configKey => $configVal) {
            if (property_exists(get_class($this), $configKey)) {
                $this->$configKey = $configVal;
            }
        }
    }
    
    /**
     * @return array
     */
    public function run() {
        $eventType = 'action' . ucfirst($this->action);
        
        return $this->$eventType();
    }

    /**
     * 服务端向客户端发送信息 https://wiki.swoole.com/wiki/page/399.html
     * @param array $data
     * @param int $code
     * @param null $fd
     * @param int $opCode
     * @param bool $finish
     * @return mixed
     * @throws \Exception
     */
    public function pushMsg($data = [], $code = 200, $fd = null, $opCode = 1, $finish = true) {
        $returnInfo = [
            'code' => $code,
            'data' => $data,
        ];
        $fd         = $fd ?: $this->frame->fd;
        if ($fd) {
            //发送成功返回true，发送失败返回false
            return $this->distributor->connector->server->push($fd, json_encode($returnInfo), $opCode, $finish);
        } else {
            throw new \Exception('链接不存在');
        }
    }
}