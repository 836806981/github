<?php
namespace Phone\Controller;
use Think\Controller;
class CommonController extends Controller {

    //所请求的模块不存在时，默认执行的模块
    public function index(){
        header("HTTP/1.0 404 Not Found");//404状态码
        $this->display("Index/404"); //显示自定义的404页面模版
    }

    function _empty(){
        header("HTTP/1.0 404 Not Found");//404状态码
        $this->display("Index/404");//显示自定义的404页面模版
    }

    public function wordlength($str)
    {
        if(empty($str)){
            return 0;
        }
        if(function_exists('mb_strlen')){
            return mb_strlen($str,'utf-8');
        }
        else {
            preg_match_all("/./u", $str, $ar);
            return count($ar[0]);
        }
    }

//获取IP
    public function getIP(){
        if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
            $ip = getenv("HTTP_CLIENT_IP");
        else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
            $ip = getenv("REMOTE_ADDR");
        else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
            $ip = $_SERVER['REMOTE_ADDR'];
        else
            $ip = "unknown";
        return($ip);
    }

    //获取mac
    var $return_array = array(); // 返回带有MAC地址的字串数组
    var $mac_addr;
    public function GetMacAddr($os_type){
        switch ( strtolower($os_type) ){
            case "linux":
                $this->forLinux();
                break;
            case "solaris":
                break;
            case "unix":
                break;
            case "aix":
                break;
            default:
                $this->forWindows();
                break;

        }
        $temp_array = array();
        foreach ( $this->return_array as $value ){

            if (
            preg_match("/[0-9a-f][0-9a-f][:-]"."[0-9a-f][0-9a-f][:-]"."[0-9a-f][0-9a-f][:-]"."[0-9a-f][0-9a-f][:-]"."[0-9a-f][0-9a-f][:-]"."[0-9a-f][0-9a-f]/i",$value,
                $temp_array ) ){
                $this->mac_addr = $temp_array[0];
                break;
            }

        }
        unset($temp_array);
        return  $this->mac_addr;
    }

    public function send_email($receiver,$name,$title,$content,$no_html){//收件人、发件人姓名、标题、内容
        require_once('class.phpmailer.php');
        $mail = new \PHPMailer(true);
        $mail->IsSMTP();
        $send = 'kaifabu@tianluoayi.com';
        $pwd = 'Zxc153.';
//        $receiver = '836806981@qq.com';
//        $name = '网站';
//        $title ='标题' ;
//        $content = '内容';
        $mail->CharSet='UTF-8'; //设置邮件的字符编码，这很重要，不然中文乱码
        $mail->SMTPAuth = true; //开启认证
        $mail->Port = 25;
        $mail->SMTPDebug=true;
        $mail->Host = "smtp.exmail.qq.com";
        $mail->Username = $send;
        $mail->Password = $pwd;
//$mail->IsSendmail(); //如果没有sendmail组件就注释掉，否则出现“Could not execute: /var/qmail/bin/sendmail ”的错误提示
        $mail->AddReplyTo($send,$name);//回复地址
        $mail->From = $send;
        $mail->FromName = $name;
        $rece_array = explode(',',$receiver);
        foreach($rece_array as $k=>$v){
            $mail->AddAddress($v);
        }
        $mail->Subject = $title;
        $mail->Body = $content;
        $mail->AltBody = $no_html; //当邮件不支持html时备用显示，可以省略
        $mail->WordWrap = 80; // 设置每行字符串的长度
//$mail->AddAttachment("f:/test.png"); //可以添加附件
        $mail->IsHTML(true);
        $mail->Send();


    }

}