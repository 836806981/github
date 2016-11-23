<?php
namespace Video\Controller;
use Think\Controller;
class IndexController extends Controller {
    public function index(){
        $this->display('login');
    }
    public function login(){
//        $code = $_GET['code'];//获取code
//        $weixin =  file_get_contents("https://api.weixin.qq.com/sns/oauth2/access_token?appid=这里是你的APPID&secret=这里是你的SECRET&code=".$code."&grant_type=authorization_code");//通过code换取网页授权access_token
//        $jsondecode = json_decode($weixin); //对JSON格式的字符串进行编码
//        $array = get_object_vars($jsondecode);//转换成数组
//        $openid = $array['openid'];//输出openid
//

        $this->display('login');
    }




    public function login_mod(){
        if($_POST['phone']==''||$_POST['pwd']==''){
            echo "<script>alert('请填写用户名和密码！');history.go(-1);</script>";
            exit;
        }

        $info = M('tra_training')->where('phone="'.trim($_POST['phone']).'" and pwd="'.trim($_POST['pwd']).'"  and status!=5')->find();
        if(!$info){
            echo "<script>alert('用户不存在或密码错误！');history.go(-1);</script>";
            exit;
        }
        $_SESSION[C('VIDEO_AUTH_KEY')] = $info;

        echo "<script>alert('登录成功！');window.location.href='" . __MODULE__ . "/Index/video_list.html';</script>";
    }


    public function video_list(){
        if(!$_SESSION[C('VIDEO_AUTH_KEY')]['id']){
            echo "<script>alert('请登录！');window.location.href='" . __MODULE__ . "/Index/login.html';</script>";
            exit;
        }
        $where = '1';
        $count = M('video')->where($where)->count();
        $Page = new \Think\Page($count,20);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show = $Page->showFront();// 分页显示输出// 进行分页数据查询 注意limit方法的参数要使用Page类的属性

        $news_list = M('video')->where($where)->order('add_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        foreach($news_list as $k=>$v){
            $news_list[$k]['add_time'] = date('Y-m-d',$v['add_time']);

        }


        $this->assign('news_list',$news_list);
        $this->assign('page',$show);

        $this->display('list');
    }


    public function content(){
//        if(!$_SESSION[C('VIDEO_AUTH_KEY')]['id']){
//            echo "<script>alert('请登录！');window.location.href='" . __MODULE__ . "/Index/login.html';</script>";
//            exit;
//        }
        if(!$_GET['id']){
            echo "<script>alert('地址异常！');history.go(-1);</script>";
            exit;
        }
        $video = M('video')->where('id='.$_GET['id'])->find();
        if(!$video){
            echo "<script>alert('地址异常！');history.go(-1);</script>";
            exit;
        }

        if(in_array(substr(strrchr($video['url'],'.'),1),['avi','mp4'])){
            $video['url_type'] = 1;
        }else{
            $video['url_type'] = 2;
        }

        $this->assign('video',$video);
        $this->display();
    }


}