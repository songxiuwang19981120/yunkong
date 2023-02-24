<?php

namespace app\api\middleware;

use app\api\model\Apiuser as User;
use app\api\model\Apiuserrule as UserRule;
use think\facade\Event;

class Auth
{
    protected static $request = null;
    protected static $instance = null;
    protected $_error = '';
    protected $_logined = false;
    protected $_user = null;
    protected $_token = '';
    //Token默认有效时长
    protected $keeptime = 2592000;
    protected $requestUri = '';
    protected $rules = [];
    //默认配置
    protected $config = [];
    protected $options = [];
    protected $allowFields = ['id', 'username', 'nickname', 'mobile', 'avatar', 'score'];

    public function __construct($options = [])
    {
        $this->options = array_merge($this->config, $options);
    }

    /**
     *
     * @param array $options 参数
     * @return Auth
     */
    public static function instance($request, $options = [])
    {
        self::$request = $request;
        if (is_null(self::$instance)) {
            self::$instance = new static($options);
        }

        return self::$instance;
    }

    /**
     * 获取User模型
     * @return User
     */
    public function getUser()
    {
        return $this->_user;
    }

    /**
     * 兼容调用user模型的属性
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return null;
    }

    /**
     * 兼容调用user模型的属性
     */
    public function __isset($name)
    {
        return isset($this->_user) && isset($this->_user->$name);
    }

    public function isSuperAdmin()
    {
        return '*' == $this->_user->group->rules;
    }

    /**
     * 根据Token初始化
     *
     * @param string $token Token
     * @return boolean
     */
    public function init($token)
    {
        if ($this->_logined) {
            return true;
        }
        if ($this->_error) {
            return false;
        }

        $user_id = intval(self::$request->uid);
        if ($user_id > 0) {
            $user = User::where("id", $user_id)->find();
            if (!$user) {
                $this->setError('账户不存在');
                return false;
            }
            if ($user->expire_date > time()) {
                $user->status = 'disable';
                $user->save();
                $this->setError('账户已过期');
                return false;
            }
            if ($user['status'] != 'normal') {
                $this->setError('账户已锁定');
                return false;
            }
            $this->_user = $user;
            $this->_logined = true;
            $this->_token = $token;

            //初始化成功的事件
            //Hook::listen("user_init_successed", $this->_user);
            Event::listen("user_init_successed", $this->_user);

            return true;
        } else {
            $this->setError('你未登录');
            return false;
        }
    }

    /**
     * 检测是否是否有对应权限
     * @param string $path 控制器/方法
     * @param string $module 模块 默认为当前模块
     * @return boolean
     */
    public function check($path = null, $module = null)
    {
        if (!$this->_logined) {
            return false;
        }

        $ruleList = $this->getRuleList();

        if (in_array('*', $ruleList)) {
            return true;
        }

        $rules = [];
        foreach ($ruleList as $k => $v) {
            $rules[] = $v['name'];
        }
        $url = ($module ?: "api") . '/' . (is_null($path) ? $this->getRequestUri() : $path);
        $url = strtolower(str_replace('.', '/', $url));
        return in_array($url, $rules);
    }

    /**
     * 判断是否登录
     * @return boolean
     */
    public function isLogin()
    {
        if ($this->_logined) {
            return true;
        }
        return false;
    }

    /**
     * 获取当前Token
     * @return string
     */
    public function getToken()
    {
        return $this->_token;
    }


    /**
     * 获取会员组别规则列表
     * @return array
     */
    public function getRuleList()
    {
        if ($this->rules) {
            return $this->rules;
        }
        $group = $this->_user->group;
        if (!$group) {
            return [];
        }
        if ($group->rules == '*') {
            $this->rules = ["*"];
//            $this->rules = UserRule::where('status', 'normal')->field('id,pid,name,title,ismenu')->select()->toArray();
        } else {
            $rules = explode(',', $group->rules);
            $this->rules = UserRule::where('status', 'normal')->where('id', 'in', $rules)->field('id,pid,name,title,ismenu')->select()->toArray();
        }
        return $this->rules;
    }

    /**
     * 获取当前请求的URI
     * @return string
     */
    public function getRequestUri()
    {
        return $this->requestUri;
    }

    /**
     * 设置当前请求的URI
     * @param string $uri
     */
    public function setRequestUri($uri)
    {
        $this->requestUri = $uri;
    }

    /**
     * 获取允许输出的字段
     * @return array
     */
    public function getAllowFields()
    {
        return $this->allowFields;
    }

    /**
     * 设置允许输出的字段
     * @param array $fields
     */
    public function setAllowFields($fields)
    {
        $this->allowFields = $fields;
    }


    /**
     * 获取密码加密后的字符串
     * @param string $password 密码
     * @param string $salt 密码盐
     * @return string
     */
    public function getEncryptPassword($password, $salt = '')
    {
        return md5(md5($password) . $salt);
    }

    /**
     * 检测当前控制器和方法是否匹配传递的数组
     *
     * @param array $arr 需要验证权限的数组
     * @return boolean
     */
    public function match($arr = [])
    {
        $request = self::$request;
        $arr = is_array($arr) ? $arr : explode(',', $arr);
        if (!$arr) {
            return false;
        }
        $arr = array_map(function ($path) {
            return ltrim($path, "/");
        }, $arr);
        $arr = array_map('strtolower', $arr);
        // 是否存在
        if (in_array(strtolower($request->controller()) . '/' . strtolower($request->action()), $arr) || in_array('*', $arr)) {
            return true;
        }

        // 没找到匹配
        return false;
    }

    /**
     * 设置会话有效时间
     * @param int $keeptime 默认为永久
     */
    public function keeptime($keeptime = 0)
    {
        $this->keeptime = $keeptime;
    }

    /**
     * 渲染用户数据
     * @param array $datalist 二维数组
     * @param mixed $fields 加载的字段列表
     * @param string $fieldkey 渲染的字段
     * @param string $renderkey 结果字段
     * @return array
     */
    public function render(&$datalist, $fields = [], $fieldkey = 'user_id', $renderkey = 'userinfo')
    {
        $fields = !$fields ? ['id', 'nickname', 'level', 'avatar'] : (is_array($fields) ? $fields : explode(',', $fields));
        $ids = [];
        foreach ($datalist as $k => $v) {
            if (!isset($v[$fieldkey])) {
                continue;
            }
            $ids[] = $v[$fieldkey];
        }
        $list = [];
        if ($ids) {
            if (!in_array('id', $fields)) {
                $fields[] = 'id';
            }
            $ids = array_unique($ids);
            $selectlist = User::where('id', 'in', $ids)->column($fields);
            foreach ($selectlist as $k => $v) {
                $list[$v['id']] = $v;
            }
        }
        foreach ($datalist as $k => &$v) {
            $v[$renderkey] = $list[$v[$fieldkey]] ?? null;
        }
        unset($v);
        return $datalist;
    }

    /**
     * 退出
     *
     * @return boolean
     */
    public function logout()
    {
        if (!$this->_logined) {
            $this->setError('You are not logged in');
            return false;
        }
        //设置登录标识
        $this->_logined = false;
        return true;
    }

    /**
     * 设置错误信息
     *
     * @param string $error 错误信息
     * @return Auth
     */
    public function setError($error)
    {
        $this->_error = $error;
        return $this;
    }

    /**
     * 获取错误信息
     * @return string
     */
    public function getError()
    {
        return $this->_error ?: '';
    }
}
