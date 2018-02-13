<?php
/**
 * @name SmsModel
 * @desc 短信操作Model类，使用的是sms.cn服务，账号colorful3,密码19940909fu
 * @author Colorful
 */
class SmsModel {
    public $errno = 0;
    public $errmsg = '';
    private $_db = null;

    public function __construct() {
        $this->_db = new PDO("mysql:host=127.0.0.1;dbname=yafAPI;", "root", "");
    }

    /**
     * 发送短信方法
     * @param $uid:用户id
     * @param $template_id:模板id
     */
    public function send($uid, $template_id) {
        $query = $this->_db->prepare("select `mobile` from `user` where `id` = ? ");
        $query->execute([$uid]);
        $ret = $query->fetchAll();
        if(!$ret || count($ret) != 1) {
            $this->errno = -4003;
            $this->errmsg = "用户手机号信息查找失败";
            return false;
        }
        $user_mobile = $ret[0]['mobile'];
        if( !$user_mobile || !is_numeric($user_mobile) || strlen($user_mobile) != 11 ) {
            $this->errno = -4004;
            $this->errmsg = "用户手机号不符合标准，手机号为：" . (!$user_mobile ? "空" : $user_mobile);
            return false;
        }

        $sms_uid = '******';
        $sms_pwd = '******';
        $sms = new ThirdParty_Sms( $sms_uid, $sms_pwd );

        $param = ['code' => rand(1000, 9999)];
        $template = $template_id;
        $result = $sms->send($user_mobile, $param, $template);

        if($result['stat'] == 100) {
            // 记录短信发送状态
            $query = $this->_db->prepare("insert into `sms_record` (`uid`, `mobile`, `contents`, `template`, `code`) VALUES (?, ?, ?, ?, ?) ");
            $query->execute([$uid, $user_mobile, json_encode($param), $template, $param['code']]);
            if(!$ret) {
                $this->errno = -4006;
                $this->errmsg = "消息发送成功，但发送记录失败";
                return false;
            }
            return true;
        } else {
            $this->errno = -4005;
            $this->errmsg = '发送失败' . $result['stat'] . '(' . $result['message'] . ')';
            return false;
        }
    }

}
