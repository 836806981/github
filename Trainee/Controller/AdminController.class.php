<?php
namespace Trainee\Controller;
use Think\Controller;
class AdminController extends CommonController {
    public function  login_test(){
        if(!$_SESSION[C('TRAINEE_AUTH_KEY')]['id']){
            echo "<script>alert('请登录！');window.location.href='" . __MODULE__ . "/Admin/login.html';</script>";
        }
    }
    public function per_test(){
//        $admin_user = M('permission')->where('employee_id='. $_SESSION[C('TRAINEE_AUTH_KEY')]['id'].'')->find();
        if($_SESSION[C('TRAINEE_AUTH_KEY')]['permission']!=1){
            echo "<script>alert('你没有权限！');window.location.href='" . __MODULE__ . "/Admin/info.html';</script>";
        }
    }

    public function  _initialize(){

        echo '--';
       echo  is_integer(1);

        echo '--';
    }


    public function test(){
//        $name = 1;
//        $name_1 = & $name;
//        $name_1 = 2;
//        echo $name;
//
//        print $this->getfirstchar('abc');

    }
    //后台登录页面
    public function login(){
        if(I('post.')) {
            $employee = D('Employee');
            $map['username'] = trim(I('post.username'));
            $map['pwd'] = $this->md5_pwd(trim(I('post.pwd')));
            $user_info = $employee->login_mod($map);
            if ($user_info && $user_info != '') {//登录成功的处理
                $admin_user = M('permission')->field('edit_per,add_per,del_per,nurse,news,job,destine,trainee,order')->where('employee_id='.$user_info['id'] .'')->find();
                if($user_info['status']==0||$admin_user['trainee']!=1){
                    echo "<script>alert('账号被停用或非招生管理账号，无法登陆！');window.location.href='" . __MODULE__ . "/Admin/login.html';</script>";
                }else {

                    $user_info = array_merge($user_info,$admin_user);
                    $_SESSION[C('TRAINEE_AUTH_KEY')] = $user_info;
                    $session_id = session_id();
                    $ip = $this->getIP();
                    $log['sessionid'] = $session_id;
                    $log['login_ip'] = $ip;
                    $log['employee_id'] = $user_info['id'];
                    $log['login_time'] = time();
                    $log['mac'] = $this->GetMacAddr(PHP_OS);//获取mac 地址
                    switch ($user_info['status']) {
                        case 1:
                            $log['status'] = 1;
                            break;
                        case 2:
                            $log['status'] = 2;
                            break;
                    }
                    $login_log = D('Loginlog');
                    $login_log->add_mod($log);
                    echo "<script>alert('登录成功！');window.location.href='" . __MODULE__ . "/Trainee/tlist/type/6.html';</script>";
                }
            } else{
                $ip = $this->getIP();
                $log['sessionid']='';
                $log['login_ip']=$ip;
                $log['login_time']=time();
                $log['mac'] = $this->GetMacAddr(PHP_OS);//获取mac 地址
                $login_log = D('Loginlog');

                $login_log->add_mod($log);
                echo "<script>alert('用户不存在或密码错误！');window.location.href='".__MODULE__."/Admin/login.html'</script>";
            }
        }else{
            $this->display();
        }
    }


    //员工详情页
    public function info(){
        $this->login_test();
        if(I('post.')){
            $post = I('post.');
            $where['id']=$post['id'];
            $info['real_name']=$post['real_name'];
            $info['phone']=$post['phone'];
            $info['birthday']=$post['birthday'];
            $info['remark']=$post['remark'];
            $employee = D('Employee');
            $save_mod = $employee->save_mod($where,$info);
            if($save_mod!==false){
                echo "<script>alert('修改成功！');window.location.href='".__MODULE__."/Admin/info.html';</script>";
            }else{
                echo "<script>alert('修改失败！');window.location.href='".__MODULE__."/Admin/info.html';</script>";
            }
        }else {
            $user_info = M('employee')->where('id=' . $_SESSION[C('TRAINEE_AUTH_KEY')]['id'] . '')->find();
            $login_time = M('login_log')->field('login_time')->where('employee_id=' . $_SESSION[C('TRAINEE_AUTH_KEY')]['id'] . '')->order('login_time desc')->limit(2)->select();
            $user_info['log_time'] = $login_time[1]['login_time'] ? date('Y-m-d H:i:s', $login_time[1]['login_time']) : '首次登录';
            $this->assign('user_info', $user_info);
            $this->display();
        }
    }


    //修改密码页面
    public function changepass(){
        $this->login_test();
        if(I('post.')){
            $post = I('post.');
            $user = M('employee')->where('id=' . $_SESSION[C('TRAINEE_AUTH_KEY')]['id'] . '')->find();
            if($post['old']==''||$post['new']==''||$post['newre']==''){
                $back['code']=1001;//密码不能为空
            }elseif(strlen($post['new'])<6||strlen($post['new'])>18){
                $back['code']=1002;//密码长度不正确
            }elseif($user['pwd']!=$this->md5_pwd($post['old'])){
                $back['code']=1003;//原密码不正确
            }else{
                $where['id']=$_SESSION[C('TRAINEE_AUTH_KEY')]['id'];
                $info['pwd']=$this->md5_pwd($post['new']);
                $save = D('Employee')->save_mod($where,$info);
                if($save!==false){
                    $back['code']=1000;//修改成功
                }else{
                    $back['code']=1004;//修改失败
                }
            }
            echo json_encode($back);
            exit;
        }else{
            $this->display();
        }
    }

    //退出登录
    public function login_out(){
        $_SESSION[C('TRAINEE_AUTH_KEY')]=null;
        echo "<script>alert('已成功退出！');window.location.href='".__MODULE__."/Admin/login.html';</script>";
    }


    //模拟添加管理员
    public function employee_test(){
        $employee = D('Employee');
        $map['username']='Admin';
        $map['pwd']=$this->md5_pwd('Admin');
        $map['permission']=1;
        $map['real_name']='Admin';
        $map['sex']=0;
        $map['birthday']='1992-12-22';
        $map['remark']='第一个管理员';
        $map['status']=1;
        $map['add_time']=time();
        $map['phone']='18380213303';
        $employee->add_mod($map);
    }

}