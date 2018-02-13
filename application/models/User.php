<?php
/**
 * @name UserModel
 * @desc sample数据获取类, 可以访问数据库，文件，其它系统等
 * @author root
 */
class UserModel {
    public $errno = 0;
    public $errmsg = '';
    private $_db = null;

    public function __construct() {
        $this->_db = new PDO("mysql:host=127.0.0.1;dbname=yafAPI;", "root", "");
    }

    /**
     * 用户注册数据入库
     */
    public function register($uname, $pwd) {
        $query = $this->_db->prepare("SELECT COUNT(*) AS c FROM `user` WHERE `name` = ? ");
        $query->execute([$uname]);
        $count = $query->fetchAll();

        if($count[0]['c'] != 0) {
            $this->errno = -1005;
            $this->errmsg = '用户名已存在';
            return False;
        }

        $password = "";
        if(strlen($pwd) < 8) {
            $this->errno = -1006;
            $this->errmsg = '密码太短，请设置不少于8位的密码';
            return false;
        } else {
            $password = $this->_password_generate($pwd);
        }
        // var_dump(date('Y-m-d H:i:s'));exit;
        $query = $this->_db->prepare("insert into `user` (`id`, `name`, `pwd`, `reg_time`) VALUES (null, ?, ?, ?)");
        $ret = $query->execute(array($uname, $password, date('Y-m-d H:i:s')));
        if(!$ret) {
            $this->errno = -1007;
            $this->errmsg = '注册失败，写入数据失败';
            return False;
        }

        return true;
    }

    /**
     * 验证用户登录逻辑
     */
    public function login( $uname, $pwd ) {
        $query = $this->_db->prepare("select `pwd`, `id` from `user` where `name` = ? ");
        $query->execute([$uname]);
        $ret = $query->fetchAll();
        if(!$ret || count($ret) != 1) {
            $this->errno = -1003;
            $this->errmsg = "用户查找失败";
            return False;
        }
        $user_info = $ret[0];
        if( $this->_password_generate($pwd) != $user_info['pwd'] ) {
            $this->errno = -1004;
            $this->errmsg = "密码错误";
            return False;
        }
        return intval($user_info[1]);
    }

    // 密码加密
    private function _password_generate($password) {
        $pwd = md5("salt" . $password);
        return $pwd;
    }

}
