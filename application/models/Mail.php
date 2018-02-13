<?php
/**
 * @name MailModel
 * @author Colorful
 * @desc 邮件发送Model
 */
require __DIR__ . '/../../vendor/autoload.php';
use Nette\Mail\Message;

class MailModel {
    public $errno = 0;
    public $errmsg = "";
    private $_db = null;

    public function __construct() {
        $this->_db = new PDO("mysql:host=127.0.0.1;dbname=yafAPI", 'root', '');
    }

    public function send($uid, $title, $contents) {
        $query = $this->_db->prepare("select `email` from `user` where `id` = ? ");
        $query->execute([$uid]);
        $ret = $query->fetchAll();
        if( !$ret || count($ret) !=1 || !$ret[0]['email'] ) {
            $this->errno = -3003;
            $this->errmsg = "用户邮箱信息查找失败";
            return false;
        }
        $user_email = $ret[0]['email'];

        if( !filter_var($user_email, FILTER_VALIDATE_EMAIL) ) {
            $this->errno = -3004;
            $this->errmsg = "用户的邮箱地址不符合标准，地址为：" . $user_email;
            return false;
        }

        $mail = new Message;
        $mail->setFrom('fujiale3@126.com')
            ->addTo($user_email)
            ->setSubject($title)
            ->setBody($contents)
            ->setHTMLBody('<b>'.$contents.'</b> <img src="http://image.phpcomposer.com/logo/phpcomposer.png">');
        $mailer = new Nette\Mail\SmtpMailer([
            'host' => 'smtp.126.com',
            'username' => 'fujiale3@126.com',
            'password' => '19940909fu',
            'secure' => 'ssl',
        ]);
        $rep = $mailer->send($mail);
        return true;
        /**
         * 收集发送出去的邮件的送达效果
         * 思路：让邮件支持html，在内容中插入很小的透明的图片，图片的url是自己域名开头
         * 例如：http://colorful3.com/images/1,png?uid=1&&sendtime=&title=***&body=***
         * 这个时候就可以通过服务器日志来查看送达效果了
         */
    }
}
