<?php
/**
 * Created by PhpStorm.
 *
 * @author 曾洪亮<zenghongl@126.com>
 * @email  zenghongl@126.com
 * User: whoSafe
 * Date: 2018/6/25
 * Time: 上午11:24
 */

namespace Db;

use \BaseYaf as Y;

/**
 * Db Redis.
 */
class Redis
{
    /**
     * Instances of the derived classes.
     * @var array
     */
    protected static $instance = array();
    // 配置文件.
    protected $config = array();
    // 链接池.
    protected $connections = array();

    /**
     * 实例化Mysql.
     *
     * @Author : whoSafe
     *
     * @return mixed
     */
    public static function instance()
    {
        if(!static::$instance)
        {
            static::$instance = new static();
            static::$instance->getConfig();
        }
        return static::$instance;
    }

    /**
     * 获取redis链接.
     *
     * @Author : whoSafe
     *
     * @param bool $isRedis 选择读写 true：读，false：写
     *
     * @return mixed
     */
    public function getDataBase($isRedis = true){

        if($isRedis){
            $type = 'slave';
        }else{
            $type = 'master';
        }

        if(!isset($this->connections[$type]) || $this->connections[$type]->ping() != 'PONG'){
            $this->connection($type);
        }
        return $this->connections[$type];
    }

    /**
     * 创建链接.
     *
     * @Author : whoSafe
     *
     * @param string $type 链接类型.
     */
    protected function connection($type){
        $this->connections[$type] = new \Redis();
        $this->connections[$type]->connect($this->config[$type]['ip'], $this->config[$type]['port']);
        if(!empty($this->config[$type]['secret'])){
            $this->connections[$type]->auth($this->config[$type]['secret']);
        }
        if(isset($this->config[$type]['db'])){
            $this->connections[$type]->select($this->config[$type]['db']);
        }
    }

    /**
     * 获取redis配置文件.
     *
     * @Author : whoSafe
     *
     */
    public function getConfig(){
        $this->config = Y::config('redis');
    }

}