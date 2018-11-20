<?php
/**
 * Created by zxzTool.
 * User: zxz
 * Datetime: 2018/11/16 16:32
 */

namespace xzchat\libs\message;


use xzchat\libs\ConnectCollection;
use xzchat\libs\message\controllers\AbstractController;

class MessageDistributor {
    /**
     * @var ConnectCollection $connector
     */
    public $connector;
    public $frame;
    public $route;
    public $data;
    public $defaultRoute = 'index/index';
    
    function __construct(&$connector, $frame, $data) {
        $this->connector = &$connector;
        $this->frame     = $frame;
        $this->route     = isset($data['route']) && trim($data['route']) ? (string)$data['route'] : $this->defaultRoute;
        $this->data      = $data;
    }
    
    public function run() {
        //$this->parseRoute();
        
        return $this->simpleRoute();
    }
    
    /**
     * todo
     */
    public function parseRoute() {
        $routeInfo     = explode('/', $this->route);
        $baseMsgLibDir = dirname(__FILE__) . DIRECTORY_SEPARATOR;//定位到/vagrant/project1/public/websocket/xzchat/libs/message
    }
    
    /**
     * 简单路由到控制器
     * @return array
     */
    public function simpleRoute() {
        $routeInfo = explode('/', $this->route);
        $routeInfo = array_pad($routeInfo, 2, '');
        
        $module     = $routeInfo[0];
        $action     = array_pop($routeInfo);
        $controller = array_pop($routeInfo);
        
        $baseMsgLibDir = dirname(__FILE__);
        if (is_file(implode(DIRECTORY_SEPARATOR, [$baseMsgLibDir, 'modules', $module, 'Module.php']))) {
            //todo 导向模块
        } else {
            /** @var AbstractController $msgProcessor */
            $msgProcessorClass = implode('\\', array_filter([__NAMESPACE__, 'controllers', implode('\\', $routeInfo), ucfirst($controller) . 'Controller']));
            $msgProcessor      = (new $msgProcessorClass($this, $this->frame, $this->data, ['action' => $action, 'data' => isset($this->data['data']) ? $this->data['data'] : []]));
            
            return $msgProcessor->run();
        }
    }
}