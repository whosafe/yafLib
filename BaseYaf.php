<?php
/**
 * Created by PhpStorm.
 *
 * @author zeng.hongliang<zeng.hongliang@kepuchina.org.cn>
 * @email zeng.hongliang@kepuchina.org.cn
 * User: whosafe
 * Date: 2018/6/25
 * Time: 上午8:58
 */


use Yaf\Application;
use Yaf\Registry;

/**
 *  */
class BaseYaf
{

    /**
     * 初始化web入口
     *
     * @Author : whoSafe
     *
     * @return mixed
     */
    public static function web()
    {
        self::dispatcher()
            ->returnResponse(true);

        return self::bootstrap()
            ->run();

    }

    /**
     * 初始化API入口.
     *
     * @Author : whoSafe
     *
     * @return bool
     */
    public static function api()
    {
        try {
            self::dispatcher()
                ->returnResponse(true);

            return self::bootstrap()
                ->run();

        } catch ( \Throwable $t ) {
            error_log($t);
            echo json_encode(array (
                'code' => 100000001,
                'msg'  => 'wrong:' . $t->getMessage() . ';line:' . $t->getLine()
            ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        return false;
    }

    /**
     * 命令行运行
     *
     * @Author : whoSafe
     *
     * @return mixed
     */
    public static function cli()
    {
        return self::bootstrap()
            ->getDispatcher()
            ->returnResponse(true);
    }

    /**
     * 获取当前的Yaf_Application实例
     *
     * @Author : whoSafe
     *
     * @return mixed
     */
    public static function app()
    {
        return Application::app();
    }

    /**
     * 调用bootstrap 获取当前的Yaf_Application实例
     *
     * @Author : whoSafe
     *
     * @return mixed
     */
    public static function bootstrap()
    {
        return Application::app()
            ->bootstrap();
    }

    /**
     * 获取当前Yaf_Application的环境名
     *
     * @Author : whoSafe
     *
     * @return mixed
     */
    public static function environ()
    {
        return Application::app()
            ->environ();
    }

    /**
     * 运行回调函数
     *
     * @Author : whoSafe
     *
     * @param $func 回调函数.
     * @param $argc
     * @param $argv
     *
     * @return mixed
     */
    public static function execute($func, $argc, $argv)
    {
        return Application::app()
            ->execute($func, $argc, $argv);
    }

    /**
     * 获取 Yaf_Config_Abstract 的实例
     *
     * @Author : whoSafe
     *
     * @param mixed $item 键
     *
     * @return mixed
     */
    public static function config($item = null)
    {
        if ( is_null($item) ) {
            return Application::app()
                ->getConfig();
        }

        return Application::app()
            ->getConfig()
            ->get($item);
    }


    /**
     * 获取 Yaf_Dispatcher 的实例
     *
     * @Author : whoSafe
     *
     * @return mixed
     */
    public static function dispatcher()
    {
        return Application::app()
            ->getDispatcher();
    }


    /**
     * 开启/关闭自动渲染功能
     *
     * @Author : whoSafe
     *
     * @param bool $flag true 开启自动渲染, false 关闭自动渲染.
     *
     * @return mixed
     */
    public static function autoRender($flag)
    {
        return self::dispatcher()
            ->autoRender($flag);
    }

    /**
     * 关闭自动渲染
     *
     * @Author : whoSafe
     *
     * @return mixed
     */
    public static function disableView()
    {
        return self::dispatcher()
            ->disableView();
    }

    /**
     * 开启自动渲染
     *
     * @Author : whoSafe
     *
     * @return mixed
     */
    public static function enableView()
    {
        return self::dispatcher()
            ->enableView();
    }

    /**
     * 分发请求
     *
     * @Author : whoSafe
     *
     * @param $request
     *
     * @return mixed
     */
    public static function dispatch($request)
    {
        return self::dispatcher()
            ->dispatch($request);
    }


    /**
     * 获取当前的请求实例
     *
     * @Author : whoSafe
     *
     * @return mixed
     */
    public static function request()
    {
        return self::dispatcher()
            ->getRequest();
    }

    /**
     * 注册一个插件
     *
     * @Author : whoSafe
     *
     * @param string $plugin 插件名称.
     *
     * @return mixed
     */
    public static function registerPlugin(string $plugin)
    {
        $plugin = $plugin . 'Plugin';

        return self::dispatcher()
            ->registerPlugin(new $plugin());
    }


    /**
     * 批量获取路由的参数
     *
     * @return mixed
     */
    public static function params()
    {
        return self::request()
            ->getParams();
    }

    /**
     * 判断访问方式
     *
     * @return mixed
     */
    public static function isCli()
    {
        return self::request()
            ->isCli();
    }

    /**
     * ajax 访问
     *
     * @Author : whoSafe
     *
     * @return mixed
     */
    public static function isAjax()
    {
        return self::request()
            ->isXmlHttpRequest();
    }

    /**
     * 获取路由的参数
     *
     * @param string $key 键
     *
     * @return mixed
     */
    public static function param($key)
    {
        return self::request()
            ->getParam($key);
    }

    /**
     * 添加路由配置.
     *
     * @Author : whoSafe
     *
     * @param array $routes_config 路由数据.
     *
     * @return mixed
     */
    public static function addConfig($routes_config)
    {

        return self::dispatcher()
            ->getRouter()
            ->addConfig($routes_config);
    }


    // dump
    public static function dump()
    {
        //$argc = func_num_args();
        $argv = func_get_args();
        if ( self::isCli() ) {
            $hr = str_pad('', 40, "=");
            echo "\n";
            foreach ( $argv as $arg ) {
                var_dump($arg);
                echo $hr;
            }
            echo "\n";
        } else {
            echo '<pre><br>';
            foreach ( $argv as $arg ) {
                var_dump($arg);
                echo '<hr>';
            }
            echo '</pre>';
        }

    }

}