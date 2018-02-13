<?php
/**
 * @name ArtController
 * @author Colorful
 * @desc 文章控制器
 */
class ArtController extends Yaf_Controller_Abstract {

    public function indexAction() {
        return $this->listAction();
    }

    /**
     * 添加文章
     */
    public function addAction($art_id=0) {
        if( !$this->_isAdmin() ) {
            echo json_decode([
                'errno' => -2000,
                'errmsg' => '需要管理员权限才可以操作'
            ]);
            return False;
        }

        // 检测提交方式
        $submit = $this->getRequest()->getQuery('submit', '0');
        if($submit != '1') {
            echo json_encode([
                'errno' => -2001,
                'errmsg' => '请通过正确渠道提交'
            ]);
            return False;
        }

        // 接收客户端提交的参数
        $title = $this->getRequest()->getPost('title', false);
        $contents = $this->getRequest()->getPost('contents', false);
        $author = $this->getRequest()->getPost('author', false);
        $cate = $this->getRequest()->getPost('cate', false);

        // 验证这些参数
        if(!$title || !$contents || !$author || !$cate) {
            echo json_encode([
                'errno' => -2002,
                'errmsg' => '提交的数据不完整'
            ]);
            return False;
        }

        $model = new ArtModel();

        $last_id = $model->add(trim($title), trim($contents), trim($author), trim($cate), $art_id);
        if($last_id) {
            echo json_encode([
                'errno' => 0,
                'errmsg' => '',
                'data' => ['last_id' => $last_id]
            ]);
        } else {
            echo json_encode([
                'errno' => $model->errno,
                'errmsg' => $model->errmsg
            ]);
        }

        return FALSE;
    }

    /**
     * 编辑文章
     */
    public function editAction() {
        if( !$this->_isAdmin() ) {
            echo json_encode([
                'errno' => -2000,
                'errmsg' => '具有管理员权限才能操作'
            ]);
            return False;
        }
        $art_id = $this->getRequest()->getQuery('aid', "0");
        if( is_numeric($art_id) && $art_id ) {
            return $this->addAction($art_id);
        } else {
            echo json_encode([
                'error' => -2003,
                'errmsg' => '请传递文章的id参数'
            ]);
        }
        return False;
    }

    /**
     * 删除文章
     */
    public function delAction() {
        // 验证是否是管理员
        if( !$this->_isAdmin() ) {
            echo json_encode([
                'errno' => -2000,
                'errmsg' => '需要管理员权限才能执行此操作'
            ]);
        }
        $aid = $this->getRequest()->getQuery('aid', '0');
        if(is_numeric($aid) && $aid) {
            $model = new ArtModel();
            if($model->del($aid)) {
                 echo json_encode([
                    'errno' => 0,
                    'errmsg' => ""
                ]);
                return true;
            } else {
                echo json_encode([
                    'errno' => $model->errno,
                    'errmsg' => $model->errmsg
                ]);
                return false;
            }
        } else {
            echo json_encode([
                'errmo' => -2003,
                'errmsg' => '缺少必要的文章ID参数'
            ]);
        }
        return false;
    }

    /**
     * 改变文章状态
     */
    public function statusAction() {
        if( !$this->_isAdmin() ) {
            echo json_encode([
                'errno' => -2000,
                'errmsg' => '需要管理员权限才可以执行此操作'
            ]);
        }
        $aid = $this->getRequest()->getQuery('aid', "0");
        $status = $this->getRequest()->getQuery('status', 'offline');

        if(is_numeric($aid) && $aid) {
            $model = new ArtModel();
            if($model->status($aid, $status)) {
                echo json_encode([
                    'errno' => 0,
                    'errmsg' => ''
                ]);
            } else {
                echo json_encode([
                    'errno' => $model->errno,
                    'errmsg' => $model->errmsg
                ]);
            }
        } else {
            echo json_encode([
                'errno' => -2003,
                'errmsg' => '缺少必要的文章ID参数'
            ]);
        }
        return false;
    }

    /**
     * 文章详情
     */
    public function getAction() {
        $aid = $this->getRequest()->getQuery('aid', "0");
        if(!$aid || !is_numeric($aid)) {
            echo json_encode([
                'errno' => -2007,
                'errmsg' => "请传入文章的ID"
            ]);
            return false;
        }

        $model = new ArtModel();
        $data = $model->get($aid);
        if(!$data) {
            echo json_encode([
                'errno' => $model->errno,
                'errmsg' => $model->errmsg
            ]);
            return false;
        } else {
            echo json_encode([
                'errno' => 0,
                'errmsg' => '',
                'data' => $data
            ]);
        }
        return False;
    }

    /**
     * 文章列表
     */
    public function listAction() {
        $pageNo = $this->getRequest()->getQuery("pageNo", "1");
        $pageSize = $this->getRequest()->getQuery("pageSize", '10');
        $cate = $this->getRequest()->getQuery('cate', '0');
        $status = $this->getRequest()->getQuery('status', 'online');

        // 获取数据
        $model = new ArtModel();
        if( $data = $model->list( $pageNo, $pageSize, $cate, $status ) ) {
            echo json_encode([
                'errno' => 0,
                'errmsg' => '',
                'data' => $data
            ]);
        } else {
            echo json_encode([
                'errno' => -2012,
                'errmsg' => '获取文章列表失败'
            ]);
        }
        return False;
    }

    // 判断用户是否是管理员
    private function _isAdmin() {
        return True;
    }
}
