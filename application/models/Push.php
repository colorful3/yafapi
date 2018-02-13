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
require_once($push_lib_path . 'igetui/utils/AppConditions.php');

define('APPKEY','otKcaKDMu16ug0HZGuZlwA');
define('APPID','AcmF1iyKJm8K6Cpp92ZVS8');
define('MASTERSECRET','1ZNnFHzqGOAyHRUUNCRK26');
define('HOST','http://sdk.open.api.igexin.com/apiex.htm');

class PushModel {
    public $errno = 0;
    public $errmsg = "";
    // private $_db = null;

    public function __construct() {
         // $this->_db = new PDO('mysql:host=127.0.0.1;dbname=yafAPI;port=8889', 'root', 'root');
    }

    public function single($cid, $msg="Colorful的Push") {
        // TODO Push服务的送达率、真实送达率、打开率的获得
        // 1、服务商统计，2、让客户端在接收到推送后，回到后端一个接口，接口被请求的次数就是推送送达率。打开率也相同。
        // nginx的1px的model。参考文章 https://www.cnblogs.com/yjf512/p/3773196.html
        $igt = new IGeTui(HOST, APPKEY, MASTERSECRET);

        $template = $this->_IGtNotyPopLoadTemplateDemo($msg);

        //个推信息体
        $message = new IGtSingleMessage();
        $message->set_isOffline(true);//是否离线
        $message->set_offlineExpireTime(3600*12*1000);//离线时间
        $message->set_data($template);//设置推送消息类型
        $message->set_PushNetWorkType(0);//设置是否根据WIFI推送消息，1为wifi推送，0为不限制推送
        //接收方
        $target = new IGtTarget();
        $target->set_appId(APPID);
        $target->set_clientId($cid);
        // $target->set_alias(Alias);

        try {
            $rep = $igt->pushMessageToSingle($message, $target);
        }catch(RequestException $e){
            $requstId = $e->getRequestId();
            $rep = $igt->pushMessageToSingle($message, $target,$requstId);
            $this->errno = -7003;
            $this->errmsg = $rep['result'];
        }
        return true;
    }

    // 给所有用户推送
    public function toAll($msg) {
        $igt = new IGeTui(HOST,APPKEY,MASTERSECRET);
        $template = $this->_IGtNotyPopLoadTemplateDemo($msg);
        //$template = IGtLinkTemplateDemo();
        //个推信息体
        //基于应用消息体
        $message = new IGtAppMessage();
        $message->set_isOffline(true);
        $message->set_offlineExpireTime(10 * 60 * 1000);//离线时间单位为毫秒，例，两个小时离线为3600*1000*2
        $message->set_data($template);

        $appIdList=array(APPID);
        $phoneTypeList=array('ANDROID');
        // $provinceList=array('浙江');
        //用户属性
        //$age = array("0000", "0010");


        $cdt = new AppConditions();
        $cdt->addCondition(AppConditions::PHONE_TYPE, $phoneTypeList);
        // $cdt->addCondition(AppConditions::REGION, $provinceList);
        //$cdt->addCondition(AppConditions::TAG, $tagList);
        //$cdt->addCondition("age", $age);

        $message->set_appIdList($appIdList);
        $message->condition = $cdt;
//        $message->set_conditions($cdt->getCondition());
        $igt->pushMessageToApp($message);
        return true;
    }

    // 消息模板
    private function _IGtNotyPopLoadTemplateDemo($msg) {
        $template =  new IGtTransmissionTemplate();
        $template->set_appId(APPID);//应用appid
        $template->set_appkey(APPKEY);//应用appkey
        $template->set_transmissionType(1);//透传消息类型
        $template->set_transmissionContent($msg);//透传内容

        $message = new IGtSingleMessage();

        //APN高级推送
        $apn = new IGtAPNPayload();
        $alertmsg=new DictionaryAlertMsg();
        $alertmsg->body="body";
        $alertmsg->actionLocKey="ActionLockey";
        $alertmsg->locKey="LocKey";
        $alertmsg->locArgs=array("locargs");
        $alertmsg->launchImage="launchimage";
//        IOS8.2 支持
        $alertmsg->title="Title";
        $alertmsg->titleLocKey="TitleLocKey";
        $alertmsg->titleLocArgs=array("TitleLocArg");

        $apn->alertMsg=$alertmsg;
        $apn->badge=7;
        $apn->sound="";
        $apn->add_customMsg("payload","payload");
        $apn->contentAvailable=1;
        $apn->category="ACTIONABLE";
        $template->set_apnInfo($apn);

        return $template;
    }
}

