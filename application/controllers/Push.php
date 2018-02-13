<?php
/**
 * @name PushController
 * @author Colorful
 * @desc 推送服务接口
 */
class PushController extends Yaf_Controller_Abstract {

    public function singleAction() {
        if(!$this->_isAdmin()) {
            echo json_encode(['errno' => -7001, 'errmsg' => "只有管理员才可以进行此操作"]);
            return false;
        }
        $cid = $this->getRequest()->getQuery('cid', '');
        $msg = $this->getRequest()->getQuery('msg', '');
        if(!$cid || !$msg) {
            echo json_encode(['errno' => -7002, 'errmsg' => '请输入用户推送的设别id与要推送的内容']);
            return false;
        }

        // 调用Model
        $model = new PushModel();
        if($model->single($cid, $msg)) {
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

    public function toAllAction() {
        // 验证是否是
        if(!$this->_isAdmin()) {
            echo json_encode(['errno' => -7004, 'errmsg' => "只有管理员才可以进行此操作"]);
            return false;
        }
        $msg = $this->getRequest()->getQuery('msg', '');
        if(!$msg) {
            echo json_encode(['errno' => -7005, 'errmsg' => '请输入要推送的内容']);
            return false;
        }

        // 调用Model
        $model = new PushModel();
        if($model->toAll( $msg )) {
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

    private function _isAdmin() {
        return true;
    }
}
