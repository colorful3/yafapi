<?php
/**
 * @name SmsController
 * @author Colorful
 * @desc 短息处理控制器
 */
class SmsController extends Yaf_Controller_Abstract {

    public function indexAction() {

    }

    /**
     * 短信发送接口
     */
    public function sendAction() {
        $submit = $this->getRequest()->getQuery('submit', '0');
        if( $submit != "1" ) {
            echo json_encode([
                'errno' => -4001,
                'errmsg' => '请通过正规渠道提交'
            ]);
            return false;
        }
        // 获取客户端提交过来的参数
        $uid = $this->getRequest()->getPost('uid', false);
        $template_id = $this->getRequest()->getPost('template_id', false);
        if(!$uid || !$template_id) {
            echo json_encode([
                'errno' => -4002,
                'errmsg' => '用户ID，模板id均不能为空'
            ]);
            return false;
        }

        $model = new SmsModel();
        if( $model->send(intval($uid), intval($template_id) ) ) {
            echo json_encode([
                'errno' => 0,
                'errmsg' => ""
            ]);
        } else {
            echo json_encode([
                'errno' => $model->errno,
                'errmsg' => $model->errmsg
            ]);
        }
        return false;
    }

    /**
     * 批量发送短信接口
     */
    public function sendAllAction() {
        $model = new SmsModel();
        // TODO 要做严格校验
        $model->sendAll();
    }
}
