<?php
/**
 * @name PushModel
 * @desc 推送服务model
 * @author Colorful
 */

// 引入个推的lib
$push_lib_path = dirname(__FILE__) . '/../library/ThirdParty/Getui/';
require_once($push_lib_path . 'IGt.Push.php');
require_once($push_lib_path . 'igetui/IGt.AppMessage.php');
require_once($push_lib_path . 'igetui/IGt.APNPayload.php');
require_once($push_lib_path . 'igetui/template/IGt.BaseTemplate.php');
require_once($push_lib_path . 'IGt.Batch.php');
require_once($push_lib_path . 'ietui/utils/AppConditions.php');

define('APPKEY','otKcaKDMu16ug0HZGuZlwA');
define('APPID','AcmF1iyKJm8K6Cpp92ZVS8');
define('MASTERSECRET','1ZNnFHzqGOAyHRUUNCRK26');
define('HOST','http://sdk.open.api.igexin.com/apiex.htm');

class PushModel {
    public $errno = 0;
    public $errmsg = "";
    private $_db = null;

    public function single($cid, $msg="Colorful的Push") {
        
    }
}

