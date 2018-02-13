<?php
/**
 * @name ArtModel
 * @desc 文章操作Model类
 * @author Colorful
 */
Class ArtModel {
    public $errno = 0;
    public $errmsg = "";
    private $_db = null;

    public function __construct() {
        $this->_db = new PDO("mysql:host=127.0.0.1;dbname=yafAPI;", "root", "");
        // 不设置下边这行的话，PDO会在拼接SQL的时候，把int0转换为string 0
        $this->_db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    }

    // 编辑和添加文章入库
    public function add($title, $contents, $author, $cate, $art_id) {
        $is_edit = false;
        if(is_numeric($art_id) && $art_id != 0) {
            // edit
            $query = $this->_db->prepare('select count(*) from art where id = ? ');
            $query->execute([$cate]);
            $ret = $query->fetchAll();
            if(!$ret && count($ret) != 1) {
                $this->errno = -2004;
                $this->errmsg = '找不到你要编辑的文章';
                return false;
            }
            $is_edit = true;
        } else {
            // add
            $query = $this->_db->prepare('select count(*) from cate where id = ? ');
            $query->execute([$cate]);
            $ret = $query->fetchAll();
            if(!$ret || $ret[0][0] == 0) {
                $this->errno = -2005;
                $this->errmsg = "找不到对应ID的分类信息，cate_id:{$cate}，请先创建分类。";
                return false;
            }
        }
        // 执行数据库的增加或修改操作
        $data = [$title, $contents,  $author, intval($cate)];
        if(!$is_edit) {
            $query = $this->_db->prepare("insert into art (`title`, `contents`, `author`, `cate`, `ctime`) VALUES (?, ?, ?, ?, ?)");
            $data[] = date('Y-m-d H:i:s');
        } else {
            $query = $this->_db->prepare("update art set `title`=?, `contents`=?, `author`=?, `cate`=? where `id` = ?");
            $data[] = $art_id;
        }
        $ret = $query->execute($data);
        if(!$ret) {
            $this->errno = -2006;
            $this->errmsg = "操作文章数据表失败，ErrInfo：" . end($query->errorInfo());
            return false;
        }
        // 返回操作文章的id
        if(!$is_edit) {
            return intval($this->_db->lastInsertId());
        } else {
            return intval($art_id);
        }

    }

    // 删除文章操作
    public function del($aid) {
        $query = $this->_db->prepare("update `art` set status = 'delete' where id = ?");
        $ret = $query->execute([$aid]);
        if(!$ret) {
            $this->errno = -2007;
            $this->errmsg = "删除失败，ErrInfo：" . end($query->errorInfo());
            return false;
        }
    }

    public function status($aid, $status='offline') {
        $query = $this->_db->prepare("update `art` set `status` = ? where id = ? ");
        $ret = $query->execute([$status, $aid]);
        if(!$ret) {
            $this->errno = -2008;
            $this->errmsg = "更新文章状态失败，ErrInfo：" . end($query->errorInfo());
            return false;
        }
        return true;
    }

    // 获取当个文章详情
    public function get($aid) {
        $query = $this->_db->prepare("select `title`, `contents`, `author`, `cate`, `ctime`, `mtime`, `status` from `art` where id = ? ");
        $status = $query->execute([$aid]);
        $ret = $query->fetchAll();
        if( !$status || !$ret ) {
            $this->errno = -2009;
            $this->errmsg = '查询失败，ErrInfo：' . end($query->errorInfo());
            return false;
        }
        $art_info = $ret[0];

        // 获取分类信息
        $query = $this->_db->prepare("select `name` from `cate` where id = ?");
        $query->execute([$art_info['cate']]);
        $ret = $query->fetchAll();
        if(!$ret) {
            echo json_encode([
                $this->errno = -2010,
                $this->errmsg = "获取分类信息失败，ErrInfo：" . end($query->errorInfo())
            ]);
            return false;
        }
        $art_info['cate_name'] = $ret[0]['name'];
        // 拼装要返回的数据
        $data = [
            'id' => intval($aid),
            'title' => $art_info['title'],
            'contents' => $art_info['contents'],
            'author' => $art_info['author'],
            'cate_name' => $art_info['cate_name'],
            'cate_id' => $art_info['cate'],
            'ctime' => $art_info['ctime'],
            'mtime' => $art_info['mtime'],
            'status' => $art_info['status']
        ];
        return $data;
    }

    /**
     * 获取文章列表
     */
    public function list( $pageNo=1, $pageSize=10, $cate=0, $status='online' ) {
        // $start = $pageNo * $pageSize + ( $pageNo=0?0:1 );
        $start = ($pageNo - 1) * $pageSize;
        if( $cate == 0 ) {
            $filter = [$status, intval($start), intval($pageSize)];
            $query = $this->_db->prepare("select `id`, `title`, `contents`, `author`, `cate`, `ctime`, `mtime`, `status` from `art` where `status` = ? order by `ctime` desc limit ?,? ");
        } else {
            $filter = [intval($cate), $status, intval($start), intval($pageSize)];
            $query = $this->_db->prepare("select `id`, `title`, `contents`, `author`, `cate`, `ctime`, `mtime`, `status` from `art` where `cate` = ? and `status` = ? order by `ctime` desc limit ?,? ");
        }
        $stat = $query->execute($filter);
        $ret = $query->fetchAll();
        if(!$ret) {
            $this->errno = -2011;
            @$this->errmsg = "获取文章列表失败，ErrInfo：" . end($query->errorInfo());
            return false;
        }

        // 做数据的拼装
        $data = [];
        $cate_info = [];
        // var_dump($ret);exit;
        foreach($ret as $item) {
            // 获取分类信息
            if(isset($cate_info[$item['cate']])) {
                $cate_name = $cate_info[$item['cate']];
            } else {
                $query = $this->_db->prepare("select `name` from `cate` where `id` = ? ");
                $query->execute([$item['cate']]);
                $ret_cate = $query->fetchAll();
                if(!$ret_cate) {
                    $this->errno = -2010;
                    $this->errmsg = "获取分类信息失败，ErrInfo：" . end($query->errInfo());
                    return false;
                }
                $cate_name = $cate_info[$item['cate']] = $ret_cate[0]['name'];
            }
            // 正文剪切
            $contents = mb_strlen($item['contents']) > 30 ? mb_substr($item['contents'], 0, 30) . '...' : $item['contents'];

            $data[] = [
                'id' => intval($item['id']),
                'title' => $item['title'],
                'contents' => $contents,
                'author' => $item['author'],
                'cate_name' => $cate_name,
                'cate_id' => intval($item['cate']),
                'ctime' => $item['ctime'],
                'mtime' => $item['mtime'],
                'status' => $item['status']
            ];
        }
        return $data;
    }

}
