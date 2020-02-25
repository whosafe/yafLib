<?php
/**
 * Created by PhpStorm.
 *
 * @author 曾洪亮<zenghongl@126.com>
 * @email  zenghongl@126.com
 * User: whoSafe
 * Date: 2018/6/26
 * Time: 下午1:54
 */

namespace Base;

/**
 * Base BaseModels.
 */
class BaseModels
{

    public static $instances;

    /**
     * 实例化Model.
     *
     * @Author : whoSafe
     *
     * @return mixed
     */
    public static function instance()
    {
        $className = get_called_class();
        if(empty(self::$instances[$className]))
        {
            self::$instances[$className] = new $className();
        }
        return self::$instances[$className];
    }

    /**
     * 创建Redis连接
     *
     * @Author : whoSafe
     *
     * @param bool $isRedis true：读库，false：写库
     *
     * @return mixed
     */
    protected function getRedis($isRedis = true){
        return \DB\Redis::instance()
            ->getDataBase($isRedis);
    }

    /**
     * 创建数据库链接.
     *
     * @param boolean $isRead 是否只读.
     *
     * @return mixed
     */
    protected function getDataBase($isRead = true)
    {
        if ( $isRead ) {
            return \Db\Mysql::instance()
                ->read();
        }
        return \Db\Mysql::instance()
            ->write();
    }

}