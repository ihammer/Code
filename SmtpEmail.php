<?php

/**
 * Created by PhpStorm.
 * User: 武德安
 * Date: 2017/9/5 0005
 * Time: 15:35
 */
class SmtpEmail
{
    private $_smtp = '';
    private $_port = 25;
    private $_sort = '';
    private $_user = '';
    private $_password = '';
    private $_from = '';
    private $_to = '';
    private $_toNickname = '';
    private $_subject = '';
    private $_data = '';
    private $_charset = 'UTF-8';
    private $_type = 'text/plain';

    public function setSmtp($value) {
        $this->_smtp = $value;
    }
    public function setUser($value) {
        $this->_user = base64_encode($value);
    }
    public function setPassword($value) {
        $this->_password = base64_encode($value);
    }
    public function setFrom($value) {
        $this->_from = base64_encode($value);
    }
    public function setTo($value) {
        $this->_to = $value;
    }
    public function setToNickname($value) {
        $this->_toNickname = base64_encode($value);
    }
    public function setSubject($value) {
        $this->_subject = base64_encode($value);
    }
    public function setData($value) {
        $this->_data = $value;
    }
    public function setCharset($value) {
        $this->_charset = $value;
    }
    public function setMime($value) {
        switch($value) {
            case 1:
                $this->_type = 'text/plain';
                break;
            case 2:
                $this->_type = 'text/html';
                break;
        }
    }
    public function send() {
        $fp = $this->_sock();
        $from = base64_decode($this->_user).'@'.substr($this->_smtp, 5);
        $content = "MIME-Version:1.0\r\n";
        $content .= "Content-type:$this->_type;charset=$this->_charset\r\n";
        $content .= "from:=?$this->_charset?B?$this->_from?=<$from>\r\n";
        $content .= "to:=?$this->_charset?B?$this->_toNickname?=<$this->_to>\r\n";
        $content .= "subject:=?$this->_charset?B?$this->_subject?=\r\n";
        $content .= "\r\n";
        $content .= $this->_data;
        $content .= "\r\n.\r\n";
        if(false == $fp) {
            return false;
        } elseif(false == $this->_cmd($fp, "HELO $this->_smtp\r\n", '250')) {
            return false;
        } elseif(false == $this->_cmd($fp, "auth login\r\n", '334')) {
            return false;
        } elseif(false == $this->_cmd($fp, "$this->_user\r\n", '334')) {
            return false;
        } elseif(false == $this->_cmd($fp, "$this->_password\r\n", '235')) {
            return false;
        } elseif(false == $this->_cmd($fp, "MAIL FROM:<$from>\r\n", '250')) {
            return false;
        } elseif(false == $this->_cmd($fp, "RCPT TO:<$this->_to>\r\n", '250')) {
            return false;
        } elseif(false == $this->_cmd($fp, "DATA\r\n", '354')) {
            return false;
        } elseif(false == $this->_cmd($fp, $content, '250')) {
            echo 'error';
            return false;
        } elseif(false == $this->_cmd($fp, "QUIT\r\n", '221')) {
            return false;
        } else {
            return true;
        }
    }
    private function _sock() {
        $fp = fsockopen($this->_smtp, $this->_port);
        $response = fgets($fp);
        if (false != strstr($response, '220')) {
            return $fp;
        } else {
            return false;
        }
    }
    private function _cmd($handle, $cmd, $status) {
        fputs($handle, $cmd);
        $response = fgets($handle);
        if (false != strstr($response, $status)) {
            return true;
        } else {
            return false;
        }
    }
}
//eg
//$m = new Smtp();
//$m->setSmtp(服务器地址);
//$m->setUser(用户名);
//$m->setPassword(密码);
//$m->setFrom(发件人昵称);//部分服务器生效
//$m->setTo(收件人邮箱);
//$m->setToNickname(收件人昵称);//部分服务器生效
//$m->setSubject(主题);
//$m->setData(内容);
//$m->send();
//eg end
?>