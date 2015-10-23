<?php

class Bootstrap extends Yaf_Bootstrap_Abstract{

    // Init config
    public function _initConfig() {
        $config = Yaf_Application::app()->getConfig();
        Yaf_Registry::set('config', $config);
    }

    // Load libaray, MySQL model, function
    public function _initCore() {
        define('TB_PREFIX',    'gob_');
        define('APP_NAME'   ,  'thaidh');
        define('LIB_PATH',     APP_PATH.'/application/library');
        define('MODEL_PATH',   APP_PATH.'/application/model');
        define('FUNC_PATH',    APP_PATH.'/application/function');
        define('ADMIN_PATH',   APP_PATH.'/application/modules/Admin');

        // CSS, JS, IMG PATH
        define('CSS_PATH','/css');
        define('JS_PATH','/js');
        define('IMG_PATH','/img');
        // Admin CSS, JS PATH
        define('ADMIN_CSS_PATH', '/admin/css');
        define('ADMIN_JS_PATH',  '/admin/js');

        Yaf_Loader::import('M_Model.pdo.php');
        Yaf_Loader::import('Helper.class.php');

        Helper::import('Basic');
        Helper::import('Network');
        
        Yaf_Loader::import('C_Basic.php');

        // header.html and left.html
        define('HEADER_HTML',APP_PATH.'/public/common/header.html');
        define('TOP_HTML',APP_PATH.'/public/common/top.html');
        define('FOOTER_HTML',APP_PATH.'/public/common/footer.html');
        define('NOTIFY_HTML',APP_PATH.'/public/common/notify.html');
        // API KEY for api sign
        define('API_KEY', 'THIS_is_OUR_API_keY');
    }


    public function _initRoute() {
        //$router = Yaf_Dispatcher::getInstance()->getRouter();

        // Article detail router [伪静态]
       // $route = new Yaf_Route_Rewrite(
       //     '/study/detail/:id',
       //     array(
       //         'controller' => 'study',
       //         'action'     => 'detail',
       //     )
       // );

        //$router->addRoute('regex', $route);
    }

    public function _initPlugin(Yaf_Dispatcher $dispatcher) {
        $router = new RouterPlugin();
        $dispatcher->registerPlugin($router);

        $admin = new AdminPlugin();
        $dispatcher->registerPlugin($admin);
        Yaf_Registry::set('adminPlugin', $admin);
    }
    /**
     * Init Redis
     */
    public function _initRedis(Yaf_Dispatcher $dispatcher){
        $config = Yaf_Application::app()->getConfig();
        $redis = new Redis();
        $redis->connect($config['redis_host'], $config['redis_port']);
        Yaf_Registry::set('redis', $redis);
        // TODO sms Redis queue
        //Yaf_Registry::set('redis_queue_sms', $config['redis_queue_sms']);
    }

    

}
