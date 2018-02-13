<?php
/**
 * @name UserController
 * @author Colorful
 * @desc 用户控制器
 * @see http://www.php.net/manual/en/class.yaf-controller-abstract.php
 */
class UserController extends Yaf_Controller_Abstract {

    public function indexAction() {

    }

	/**
     * 用户注册接口
     * Yaf支持直接把Yaf_Request_Abstract::getParam()得到的同名参数作为Action的形参
     * 对于如下的例子, 当访问http://yourhost/api/index/index/index/name/root 的时候, 你就会发现不同
     */
    public function registerAction() {
        // 获取参数
        $uname = $this->getRequest()->getPost('uname', false);
        $pwd = $this->getRequest()->getPost('pwd', false);
        if(!$uname || !$pwd) {
            echo json_encode([
                'errno' => -1002,
                'errmsg' => '用户名和密码必须传递'
            ]);
            return FALSE;
        }

        // 调用Model，执行注册相关逻辑
        $model = new UserModel();
        if( $model->register( trim($uname), trim($pwd) ) ) {
            echo json_encode([
                'errno' => 0,
                'errmsg' => '',
                'data' => ['name' => $uname]
            ]);
        } else {
            echo json_encode([
                'errno' => $model->errno,
                'errmsg' => $model->errmsg
            ]);
        }
        return False;
    }

    /**
     * 用户登录接口
     */
    public function loginAction() {
        // 与客户端约定加密
        $submit = $this->getRequest()->getQuery("submit", "0");
        if($submit != "1") {
            echo json_encode(['errno' => -1001, 'errmsg' => '请通过正规渠道登录']);
            return False;
        }

        // 获取客户端提交参数
        $uname = $this->getRequest()->getPost('uname', false);
        $pwd = $this->getRequest()->getPost('pwd', false);
        if(!$uname || !$pwd) {
            echo json_encode(['errno' => -1002, 'errmsg' => '用户名与密码必须传递']);
            return false;
        }

        // 实例化Model，做登录验证
        $model = new UserModel();
        $uid = $model->login(trim($uname), trim($pwd));
        if($uid) {
            // 使用session保存用户登录态
            session_start();
            $_SESSION['user_token'] = md5('salt' . $_SERVER['REQUEST_TIME'] . $uid);
            $_SESSION['user_token_time'] = $_SERVER['REQUEST_TIME'];
            $_SESSION['user_id'] = $uid;
            echo json_encode([
                'errno' => 0,
                'errmsg' => '',
                'data' => ['name'=>$uname]
            ]);
        } else {
            echo json_encode([
                'errno' => $model->errno,
                'errmsg' => $model->errmsg
            ]);
        }
        return False;
    }


}
