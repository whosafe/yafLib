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

use PDO,PDOException,PDOStatement;
use BaseYaf as Y;
/**
 * Db Mysql.
 */
class Mysql
{
    /**
     * Instances of the derived classes.
     * @var array
     */
    protected static $instance ;
    // 配置文件.
    protected static $config;
    // MySql PDO 链接.
    protected $connection ;
    // MySql资源.
    protected $connections = array() ;
    // 当前链接配置文件.
    protected $connectionConfig = array();
    // sql句柄
    protected $stmt = array();
    //最后执行的sql语句
    public $lastSql;
    //定位字段
    protected $position;
    // sql列表
    protected $sqlList = [];

    const INSERT_ON_DUPLICATE_UPDATE = 'ondup_update';
    const INSERT_ON_DUPLICATE_UPDATE_BUT_SKIP = 'ondup_exclude';
    const INSERT_ON_DUPLICATE_IGNORE = 'ondup_ignore';

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
        }
        return static::$instance;
    }

    /**
     * 只读类.
     *
     * @Author : whoSafe
     *
     * @return mixed
     * @throws \Exception
     */
    public function read(){
        if(!isset($this->connections['read'])){
            $config = $this->getConfig('read');
            if(!$config){
                throw new \Exception('数据配置错误');
            }
            $this->connections['read'] = clone $this;
            $this->connections['read']->connect($config);
        }
        return $this->connections['read'];
    }

    /**
     * 写类.
     *
     * @Author : whoSafe
     *
     * @return mixed
     * @throws \Exception
     */
    public function write(){
        if(!isset($this->connections['write'])){
            $config = $this->getConfig('write');
            if(!$config){
                throw new \Exception('数据配置错误');
            }
            $this->connections['write'] = clone $this;
            $this->connections['write']->connect($config);

        }
        return $this->connections['write'];
    }

    /**
     * 回去配置文件.
     *
     * @Author : whoSafe
     *
     * @param string $type 类型.
     *
     * @return mixed
     */
    protected function getConfig($type){
        if(empty(static::$config[$type]))
        {
            static::$config[$type] = Y::config("mysql")[$type];
        }
        return static::$config[$type];
    }

    /**
     * 创建链接.
     *
     * @Author : whoSafe
     *
     * @param $config
     *
     * @return int
     * @throws \Exception
     */
    protected function connect($config){
        if ( !extension_loaded( 'pdo' ) ) {
            throw new \Exception("mysql PDO模块未加载" , __FILE__ . ':' . __LINE__ . "行");
        }
        if($config){
            $this->connectionConfig = $config;
        }else{
            $config = $this->connectionConfig;
        }
        try {
            if ( $config['persistent'] ) {
                $this->connection = new PDO($config['dsn'] , $config['user'] , $config['password'] , array(PDO::ATTR_PERSISTENT => true));
            } else {
                $this->connection = new PDO($config['dsn'] , $config['user'] , $config['password'] , array(PDO::ATTR_PERSISTENT => false));
            }
            //自己写代码捕获Exception
            $this->connection->setAttribute( PDO::ATTR_ERRMODE , PDO::ERRMODE_EXCEPTION );
            //回复列的默认显示格式
            $this->connection->setAttribute( PDO::ATTR_CASE , PDO::CASE_NATURAL );
        } catch (PDOException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * 执行sql
     *
     * @param null $params
     * @param int  $position
     *
     * @return $this|bool
     */
    public function query($params = null, $position = 1)
    {

        for ($i = 0;$i<2;$i++){
            try{
                if (!empty($this->stmt[$position]) && $this->stmt[$position] instanceof PDOStatement) {
                    if (!is_null($params) && is_array($params)) {
                        $this->bind($params, $position);
                    }

                    @$this->stmt[$position]->execute();
                    unset($this->sqlList[$position]);
                }
                return $this;
            }catch (PDOException $p){

                if (stripos($p->getMessage(),'MySQL server has gone away')) {
                    $this->connect([]);
                    $this->createSql($this->sqlList[$position],$position);
                }else{
                    throw new PDOException($p);
                }

            }
        }

        return $this;
    }

    /**
     * 获取IN绑定时的绑定列表和绑定数据.
     *
     * @param string $var     绑定变量名.
     * @param array  $srcData 绑定的具体数据数组.
     * @param array  &$return 返回数组.
     *
     * @return 绑定名称
     */
    public static function getBindKey($var, array $srcData, &$return)
    {
        $bindKey = '';
        foreach ($srcData as $key => $val) {
            $name = ':'. $var. '_';
            $bindKey .= ','. $name. $key;
            $return[$name. $key] = $val;
        }
        $bindKey = trim($bindKey, ',');

        return $bindKey;
    }

    /**
     * 创建预处理sql
     *
     * @param     $sql
     * @param int $position
     *
     * @return $this
     */
    public function createSql($sql, $position = 1)
    {
        $this->sqlList[$position] = $sql;
        $this->stmt[$position] = $this->connection->prepare($sql);

        return $this;
    }

    /**
     *  从结果集中的下一行返回单独的一列
     *
     * @Author : whoSafe
     *
     * @Example:$model->fetchColumn()
     *
     * @param int $column
     * @param int $position
     *
     * @return mixed
     */
    public function fetchColumn($column = 0, $position = 1)
    {
        if (!empty($this->stmt[$position]) && $this->stmt[$position] instanceof PDOStatement) {
            return $this->stmt[$position]->fetchColumn($column);
        }
        return array();
    }

    /**
     * 游标方式获取数据
     *
     * @param string $fetchAction
     * @param int    $position
     *
     * @return mixed
     */
    public function fetch($fetchAction = "assoc", $position = 1)
    {
        if (!empty($this->stmt[$position]) && $this->stmt[$position] instanceof PDOStatement) {
            $this->fetchAction($fetchAction, $position);
            return $this->stmt[$position]->fetch()?:array();
        }

        return array();
    }

    /**
     * 获取全部信息
     *
     * @param string $fetchAction
     * @param int    $position
     *
     * @return mixed
     */
    public function fetchAll($fetchAction = "assoc", $position = 1)
    {
        if (!empty($this->stmt[$position]) && $this->stmt[$position] instanceof PDOStatement) {
            $this->fetchAction($fetchAction, $position);

            return $this->stmt[$position]->fetchAll();
        }

        return array();
    }

    /**
     * 返回影响行数，update,delete,insert    对select获取到的结果不能保证准确
     *
     * @param int $position
     *
     * @return int
     */
    public function rowCount($position = 1)
    {
        if (!empty($this->stmt[$position]) && $this->stmt[$position] instanceof PDOStatement) {
            return $this->stmt[$position]->rowCount();
        }

        return 0;
    }

    /**
     * 获取最后插入的id
     *
     * @param string $name
     *
     * @return bool|string
     */
    public function lastInsertId($name = "")
    {
        if (!empty($this->connection) && $this->connection instanceof PDO) {
            return $this->connection->lastInsertId($name);
        }

        return 0;
    }

    /**
     * 设置获取数据的方式
     *
     * @param     $fetchAction
     * @param int $position
     */
    private function fetchAction($fetchAction, $position = 1)
    {

        switch ($fetchAction) {
            case "assoc":
                $get_fetch_action = PDO::FETCH_ASSOC; //asso array
                break;
            case "num":
                $get_fetch_action = PDO::FETCH_NUM; //num array
                break;
            case "object":
                $get_fetch_action = PDO::FETCH_OBJ; //object array
                break;
            case "both":
                $get_fetch_action = PDO::FETCH_BOTH; //assoc array and num array
                break;
            default:
                $get_fetch_action = PDO::FETCH_ASSOC;
                break;
        }
        $this->stmt[$position]->setFetchMode($get_fetch_action);
    }

    /**
     * 数据绑定
     *
     * @param array $params
     * @param int   $position
     */
    private function bind(array $params, $position = 1)
    {

        $this->lastSql = $this->stmt[$position]->queryString;

        foreach ($params as $key => $val) {
            if (strstr($key, ":") === false) {
                continue;
            }
            switch (gettype($val)) {
                case "integer":
                    $type = PDO::PARAM_INT;
                    $this->lastSql = str_replace($key, $val, $this->lastSql);
                    break;
                case "boolean":
                    $type = PDO::PARAM_BOOL;
                    $this->lastSql = str_replace($key, $val, $this->lastSql);
                    break;
                case "NULL":
                    $type = PDO::PARAM_NULL;
                    $this->lastSql = str_replace($key, $val, $this->lastSql);
                    break;
                default:
                    $type = PDO::PARAM_STR;
                    $this->lastSql = str_replace($key, "'" . $val . "'", $this->lastSql);
                    break;
            }

            $this->stmt[$position]->bindParam($key, $params[$key], $type);
        }
    }

    /**
     * 插入数据.
     *
     * @Author : whoSafe
     *
     * @param string $table 表名.
     * @param array $params 参数.
     * @param null $onDup
     *
     * @return bool|string
     */
    public function insert($table, $params, $onDup = null)
    {
        $fields = array_keys($params);
        $bindParams = array();

        foreach ($params as $column => $value) {
            $bindParams[':' . $column . '_0'] = $value;
        }

        $columns = join(',', $fields);
        $values = join(',', array_keys($bindParams));

        $sql_part_ignore = '';
        $sql_part_on_dup = '';

        switch ($onDup) {
            case self::INSERT_ON_DUPLICATE_IGNORE:
                $sql_part_ignore = 'IGNORE';
                break;
            case self::INSERT_ON_DUPLICATE_UPDATE:
                $update_params = (func_num_args() >= 4) ? func_get_arg(3) : $params;
                if ($update_params) {
                    $updates = array_keys($update_params);
                    foreach ($update_params as $column => $value) {
                        $updates[] = $this->quoteObj($column) . "=:" . $this->quoteObj($column) . '_1';
                        $bindParams[':' . $this->quoteObj($column) . '_1'] = $value;
                    }
                    $sql_part_on_dup = 'ON DUPLICATE KEY UPDATE ' . join(",", $updates);
                }
                break;
            case self::INSERT_ON_DUPLICATE_UPDATE_BUT_SKIP:
                $noUpdateColumnNames = func_get_arg(3);
                if (!is_array($noUpdateColumnNames)) {
                    throw new Exception('invalid INSERT_ON_DUPLICATE_UPDATE_BUT_SKIP argument');
                }
                $updates = array();
                foreach ($params as $column => $value) {
                    if (!in_array($column, $noUpdateColumnNames)) {
                        $updates[] = $this->quoteObj($column) . "=:" . $this->quoteObj($column) . '_2';
                        $bindParams[':' . $this->quoteObj($column) . '_2'] = $value;
                    }
                }
                $sql_part_on_dup = 'ON DUPLICATE KEY UPDATE ' . join(",", $updates);
                break;
            default:
        }

        $table = $this->quoteObj($table);
        $sql = "INSERT $sql_part_ignore INTO $table ($columns) VALUES ($values) $sql_part_on_dup";
        try {
            $this->createSql($sql)->query($bindParams);
            $id = $this->lastInsertId();
            if ($id) {
                return $id;
            }
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * 数据重组.
     *
     * @Author : whoSafe
     *
     * @param $objName
     *
     * @return array|mixed|string
     */
    protected function quoteObj($objName) {
        if ( is_array($objName) ) {
            $return = array ();
            foreach ( $objName as $k => $v ) {
                $return[] = $this->quoteObj($v);
            }

            return $return;
        } else {
            $v = trim($objName);
            $v = str_replace('`', '', $v);
            $v = preg_replace('# +AS +| +#i', ' ', $v);
            $v = explode(' ', $v);
            foreach ( $v as $k_1 => $v_1 ) {
                $v_1 = trim($v_1);
                if ( $v_1 == '' ) {
                    unset($v[ $k_1 ]);
                    continue;
                }
                if ( strpos($v_1, '.') ) {
                    $v_1 = explode('.', $v_1);
                    foreach ( $v_1 as $k_2 => $v_2 ) {
                        $v_1[ $k_2 ] = '`' . trim($v_2) . '`';
                    }
                    $v[ $k_1 ] = implode('.', $v_1);
                } else {
                    $v[ $k_1 ] = '`' . $v_1 . '`';
                }
            }
            $v = implode(' AS ', $v);

            return $v;
        }
    }

    /**
     * Begin transaction
     *
     */
    public function beginTransaction() {
        if($this->connection){
            try{
                $this->connection->beginTransaction();
            }catch (\PDOException $p){
                if (stripos($p->getMessage(),'MySQL server has gone away')) {
                    $this->connect([]);
                    $this->connection->beginTransaction();
                }else{
                    throw new PDOException($p);
                }
            }
        }else{
            throw new \Exception('mysql链接错误');
        }
    }

    /**
     * Commit transaction
     *
     */
    public function commit() {
        $this->connection->commit();
    }

    /**
     * Rollback
     *
     */
    public function rollBack() {
        $this->connection->rollback();
    }

}