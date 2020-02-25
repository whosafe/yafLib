<?php
/**
 * Created by PhpStorm.
 *
 * @author 曾洪亮<zenghongl@126.com>
 * @email  zenghongl@126.com
 * User: whosafe
 * Date: 2018/6/25
 * Time: 上午9:05
 */

namespace Base;

use BaseYaf as Y;
/**
 * yafLib Bootstrap.
 */
class Bootstrap extends \Yaf\Bootstrap_Abstract
{
    /*
     *  初始化session
     */
    public function _initSession()
    {
        if ( Y::config('sessionStart') ) {
            session_start();
        }
    }

    /**
     * 美化错误提示.
     *
     * @Author : whoSafe
     *
     */
    public function _initWhoops()
    {
        if ( Y::config('Debug') ) {
            Y::Dispatcher()->catchException(false);
            ini_set('display_errors', 1);
            error_reporting(E_ALL);
            $whoops = new \Whoops\Run;
            $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
            $whoops->register();
        }else{
            ini_set('display_errors', 0);
        }
    }

    /**
     * 添加路由配置.
     *
     * @Author : whoSafe
     *
     */
    public function _initRoute( ) {
        if ( Y::config( "routes" )) {
            Y::addConfig( Y::config( "routes" ) );
        }
    }


}