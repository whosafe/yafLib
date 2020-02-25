<?php
/**
 * Created by PhpStorm.
 *
 * @author 曾洪亮<zenghongl@126.com>
 * @email  zenghongl@126.com
 * User: whosafe
 * Date: 2018/6/25
 * Time: 上午11:37
 */

namespace Base;

use BaseYaf as Y;
use \Respect\Validation\Validator as v;

/**
 * Base Controller.
 */
class BaseControllers extends \Yaf\Controller_Abstract
{

    protected $rule = [];   // 验证规则列表.

    private $js = [];   // js列表.

    private $css = [];   // css 列表

    protected $errorMsg = []; // 错误信息.

    private $validation = null; // 验证实例

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
     * 设置css.
     *
     * @Author : whoSafe
     *
     * @param string $css Css Uri 路径.
     *
     * @return $this
     */
    protected function setCss($css)
    {
        $this->css[] = $css;

        return $this;
    }

    /**
     * 获取CSS.
     *
     * @Author : whoSafe
     *
     * @return array
     */
    protected function getCss()
    {
        return array_filter(array_unique($this->css));
    }

    /**
     * 设置Js.
     *
     * @Author : whoSafe
     *
     * @param string $js Js URI 路径.
     *
     * @return $this
     */
    protected function setJs($js)
    {
        $this->js[] = $js;

        return $this;
    }

    /**
     * 获取Js
     *
     * @Author : whoSafe
     *
     * @return array
     */
    protected function getJs()
    {
        return array_filter(array_unique($this->js));
    }

    /**
     * 模板添加参数.
     *
     * @Author : whoSafe
     *
     * @param string $item  键
     * @param mixed  $value 值
     *
     * @return $this
     */
    protected final function assign(string $item, $value)
    {
        $this->_view->assign($item, $value);

        return $this;
    }

    /**
     * 获取Validation
     *
     * @Author : whoSafe
     *
     * @return \Respect\Validation\Validator
     */
    protected final function getValidation(){
        if (is_null($this->validation)){
            $this->validation = new \Respect\Validation\Validator;
        }
        return $this->validation;
    }

    /**
     * IP地址获取
     *
     * @Author : whoSafe
     *
     * @return string
     */
    protected function getClientIp()
    {
        $unknown = 'unknown';

        if ( getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), $unknown) ) {
            $ip = getenv('HTTP_CLIENT_IP');
        } else if ( getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), $unknown) ) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        } else if ( getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), $unknown) ) {
            $ip = getenv('REMOTE_ADDR');
        } else if ( isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], $unknown) ) {
            $ip = $_SERVER['REMOTE_ADDR'];
        } else {
            $ip = '';
        }

        return $ip;
    }

    /**
     * 获取get数据.
     *
     * @Author : whoSafe
     *
     * @param string $key     键.
     * @param null   $default 默认值.
     *
     * @return bool|null|string
     */
    protected final function get(string $key, $default = null)
    {
        $value = null;

        $params = Y::params();
        if ( isset($params[ $key ]) ) {
            $value = $params[ $key ];
        } elseif ( isset($_GET[ $key ]) ) {
            $value = $_GET[ $key ];
        } elseif ( !is_null($default) ) {
            return $default;
        }

        // 数据验证.
        return self::validate($key, $value, $default);
    }

    /**
     * 获取post数据.
     *
     * @Author : whoSafe
     *
     * @param string $key     键.
     * @param null   $default 默认值.
     *
     * @return bool|null|string
     */
    protected final function post(string $key, $default = null)
    {
        $value = null;
        if ( isset($_POST[ $key ]) ) {
            $value = $_POST[ $key ];
        } elseif ( !is_null($default) ) {
            return $default;
        }

        return self::validate($key, $value, $default);
    }

    /**
     * 获取cookie数据.
     *
     * @Author : whoSafe
     *
     * @param string $key     键.
     * @param null   $default 默认值
     *
     * @return bool|null|string
     */
    protected final function cookie(string $key, $default = null)
    {
        $value = null;
        if ( isset($_COOKIE[ $key ]) ) {
            $value = $_COOKIE[ $key ];
        } elseif ( !is_null($default) ) {
            return $default;
        }

        return self::validate($key, $value, $default);
    }

    /**
     * 返回数据。
     *
     * @Author : whoSafe
     *
     * @param mixed $data 值.
     *
     * @return mixed
     */
    protected function success($data = null)
    {
        return $this->outPut(0, $data);
    }

    /**
     * 返回错误数据.
     *
     * @Author : whoSafe
     *
     * @param integer $code 错误吗
     * @param mixed   $data 值.
     *
     * @return mixed
     */
    protected function error($code, $data = null)
    {
        return $this->outPut($code, $data);
    }

    /**
     * 获取配置文件信息.
     *
     * @Author : whoSafe
     *
     * @param $key
     *
     * @return mixed
     */
    protected final function getConfig($key)
    {
        return Y::config($key);
    }

    /**
     * 关闭视图
     *
     * @Author : whoSafe
     *
     * @return mixed
     */
    protected final function disableView()
    {
        return Y::disableView();
    }

    /**
     * 开启视图.
     *
     * @Author : whoSafe
     *
     * @return mixed
     */
    protected function enableView()
    {
        return Y::enableView();
    }

    /**
     * Json 编码.
     *
     * @param mixed $data 任何类型数据.
     *
     * @return string
     */
    protected final function jsonEncode($data)
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Json 解码.
     *
     * @param string $jsonData Json数据.
     *
     * @return array
     */
    protected final function jsonDecode($jsonData)
    {
        return json_decode($jsonData, true);
    }

    /**
     * 检查是否是ajax请求.
     *
     * @Author : whoSafe
     *
     * @return bool
     */
    protected final function isAjax()
    {
        return Y::isAjax();
    }

    /**
     * 自定义严重规则
     *
     * @param string $key 需要获取的key.
     *
     * @return $this|bool
     * @throws \Exception
     */
    protected function getCustomRule($key)
    {
        switch ( $key ) {
            case 'string':
                $rule = v::regex('/^[\x{4e00}-\x{9fa5}A-Za-z0-9_\- ()（）、。－\&\/\+\.\[\]\,\*\#\，\—\“\s]+$/u')
                    ->setTemplate('必须是中文数字_- ()（）、－&/+.[],*');
                break;
            case 'mobile':
                $rule = v::regex('/^(13[0-9]|15[012356789]|18[0123456789]|14[57]|17[0-9])[0-9]{8}$/')
                    ->setTemplate('手机号码不正确');
                break;
            case 'tel':
                $rule = v::regex('/^((0[0-9]{2,3}\-)?[2-9][0-9]{6,7}|((00852|\+852)\-)?([2-3][0-9]{7}))+(\-[0-9]{1,4})?$/')
                    ->setTemplate('电话不正确');
                break;
            default:
                throw new \Exception('未获取到自定义验证规则');
                break;
        }

        return $rule;
    }

    /**
     * 验证数据.
     *
     * @param string $key     Key
     * @param mixed  $value   值
     * @param mixed  $default 默认值.
     *
     * @return bool|null|string
     */
    private function validate(string $key, $value, $default = null)
    {
        // 检测是否有校验规则.
        if ( !isset($this->rule[ $key ]) ) {
            $this->errorMsg[ $key ] = '没有配置验证规则';

            return false;
        }
        // 清除值左右两边的空格
        $value = trim($value);
        // 参数校验
        try {
            $this->rule[ $key ]->assert($value);

            return $value;
        } catch ( \Throwable $t ) {
            // 校验错误 检测是否有默认值.
            if ( is_null($default) ) {
                $this->errorMsg[ $key ] = $t->getMessage();

                return false;
            } else {
                return $default;
            }
        }
    }

    /**
     * 输出数据.
     *
     * @Author : whoSafe
     *
     * @param integer $code 错误码.
     * @param mixed   $data 返回值.
     *
     * @return bool
     */
    private final function OutPut(int $code, $data = null)
    {
        // 关闭模板.
        $this->disableView();
        // 设置返回头信息.
        header("Content-type: application/json;charset=utf-8");
        $return = array (
            'code' => 0,
            'msg'  => '成功',
        );
        // 重组错误码.
        if ( $code ) {
            $errorClass = '\\Error\\' . ucwords(SITE);
            $error      = new $errorClass;

            if ( isset($error->errorCode[ $code ]) ) {
                $return = array (
                    'code' => $code,
                    'msg'  => $error->errorCode[ $code ],
                );
            } else {
                $return = array (
                    'code' => 100000000,
                    'msg'  => '错误码不存在！',
                );
            }

        }
        // 检测是否需要添加data参数.
        !is_null($data) && $return['data'] = $data;
        // 输出信息.
        echo self::jsonEncode($return);

        return true;

    }

}