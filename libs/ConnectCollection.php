<?php
/**
 * Created by zxzTool.
 * User: zxz
 * Datetime: 2018/11/15 18:03
 */

namespace xzchat\libs;

use xzchat\libs\message\MessageHandler;

class ConnectCollection
{
    public $moduleList = [];
    public $module;
    public $defaultController = 'index';
    public $messageDistributor;
    /**
     * @var array 钩子
     * $event = [
     *    'initConnector' => function(&$connector){},
     *     \/** server 事件 **\/
     *    'workerStart'   => function(&$server, $id){},
     *    'open'          => function(&$server, &$req){},
     *    'beforeMessage' => function(&$server, &$frame){},
     *    'afterMessage'  => function(&$server, &$frame){},
     *    'close'         => function(&$server, $fd){},
     *    'request'       => function(&$request, &$response){},
     * ]
     */
    public $event = [];

    /**
     * websocke 服务bind
     * @var string
     */
    public $serverBind = '0.0.0.0';

    /**
     * websocke 服务监听端口
     * @var int
     */
    public $serverPort = 9502;

    /**
     * @var \swoole_websocket_server $server
     */
    public $server;

    /**
     * ConnectCollection constructor.
     * @param array $config
     */
    function __construct(array $config = [])
    {
        $this->init($config);
    }

    /**
     * @param array $config
     */
    protected function init(array $config = [])
    {
        foreach ($config as $index => $item) {
            property_exists($this, $index) && ($this->$index = $item);
        }

        $this->checkProperty();
    }

    /**
     * 促发事件
     * @param string $event
     * @param array $params
     */
    public function triggerEvent(string $event, array $params = [])
    {
        isset($this->event[$event])
        &&
        is_callable($this->event[$event])
        &&
        call_user_func_array($this->event[$event], $params);
    }

    /**
     * 添加事件
     * @param string $event
     * @param callable $fn
     */
    public function setEvent(string $event, callable $fn)
    {
        $this->event[$event] = $fn;
    }

    /**
     * 执行服务
     */
    public function run()
    {
        $this->initServer();

        $this->triggerEvent('initConnector', [&$this]);

        $this->server->start();
    }

    /**
     * 初始化websocket服务
     */
    protected function initServer()
    {
        $this->server = new \swoole_websocket_server($this->serverBind, $this->serverPort);

        //事件设置
        $this->server->on('workerstart', function ($server, $id) {
            $this->triggerEvent('workerStart', [&$server, $id]);
            $server->connector = &$this;
        });

        $this->server->on('open', function ($server, $req) {
            $this->triggerEvent('open', [&$server, &$req]);
        });

        $this->server->on('message', function ($server, $frame) {
            $this->triggerEvent('beforeMessage', [&$server, &$frame]);
            MessageHandler::msgDeal($server->connector, $frame);
        });

        $this->server->on('close', function ($server, $fd) {
            $this->triggerEvent('close', [&$server, $fd]);
        });

        $this->server->on('request', function ($request, $response) {
            $this->triggerEvent('request', [&$request, &$response]);
        });
    }

    /**
     * 检测必要参数设置
     */
    protected function checkProperty(){
        if($this->messageDistributor == null){
            throw new \Exception('请正确设置' . __CLASS__ . '::$messageDistributor 必须设置');
        }
    }
}