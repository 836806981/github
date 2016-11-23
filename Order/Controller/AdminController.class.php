<?php
namespace Order\Controller;
use Think\Controller;
class AdminController extends CommonController {
    public function  login_test(){
        if(!$_SESSION[C('ORDER_AUTH_KEY')]['id']){
            echo "<script>alert('请登录！');window.location.href='" . __MODULE__ . "/Admin/login.html';</script>";
        }
    }
    public function per_test(){
//        $admin_user = M('permission')->where('employee_id='. $_SESSION[C('ORDER_AUTH_KEY')]['id'].'')->find();
        if($_SESSION[C('ORDER_AUTH_KEY')]['permission']!=1){
            echo "<script>alert('你没有权限！');window.location.href='" . __MODULE__ . "/Admin/info.html';</script>";
        }
    }


    public function weixin(){

//        $code = $_GET['code'];//获取code
//        $weixin =  file_get_contents("https://api.weixin.qq.com/sns/oauth2/access_token?appid=这里是你的APPID&secret=这里是你的SECRET&code=".$code."&grant_type=authorization_code");//通过code换取网页授权access_token
//        $jsondecode = json_decode($weixin); //对JSON格式的字符串进行编码
//        $array = get_object_vars($jsondecode);//转换成数组
//        $openid = $array['openid'];//输出openid

    }

    public function test(){

        $Model = M('employee');



        $username = "admin";
        $pwd = "fdsafda' or '1'='1";  //前面的密码是瞎填的..后来用or关键字..意思就是无所谓密码什么都执行
        $sql = "SELECT * FROM employee WHERE username = '{$username}' AND pwd = '{$pwd}'";

        $list1 = $Model->query($sql);
        echo $Model->getLastSql();

        $where['username'] =$username;
        $where['pwd'] = $pwd;
        $list = $Model->where($where)->select();
        echo $Model->getLastSql();
        print_r($list1);
        print_r($list);
    }
    //后台登录页面
    public function login(){
        if(I('post.')) {
            $map['username'] = trim(I('post.username'));
            $map['pwd'] = $this->md5_pwd(trim(I('post.pwd')));
            $user_info = M('order_user')->where($map)->find();
            if ($user_info && $user_info != '') {//登录成功的处理
                    $_SESSION[C('ORDER_AUTH_KEY')] = $user_info;
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
                    M('order_log')->add($log);
                    echo "<script>alert('登录成功！');window.location.href='" . __MODULE__ . "/Admin/info.html';</script>";
            } else{
                $ip = $this->getIP();
                $log['sessionid']='';
                $log['login_ip']=$ip;
                $log['login_time']=time();
                $log['mac'] = $this->GetMacAddr(PHP_OS);//获取mac 地址
                M('order_log')->add($log);
                echo "<script>alert('用户不存在或密码错误！');window.location.href='".__MODULE__."/Admin/login.html'</script>";
            }
        }else{
            $this->display();
        }
    }

    //重置密码
    public function rePermission(){
        if($_SESSION[C('ORDER_AUTH_KEY')]['permission']!=1){
            echo "<script>alert('无权限！');history.back();</script>";
            exit;
        }
        $post['pwd'] = $this->md5_pwd('tlay123');
        $save = M('order_user')->where('id='.I('get.id').'')->save($post);
        if($save!==false){
            echo "<script>alert('重置成功！');history.back();</script>";
            exit;
        }else{
            echo "<script>alert('重置失败！');history.back();</script>";
            exit;
        }
    }

    //员工列表页
    public function user(){
        if(!$_SESSION[C('ORDER_AUTH_KEY')]['id']){
            echo "<script>alert('请登录！');window.location.href='" . __MODULE__ . "/Admin/login.html';</script>";
            exit;
        }elseif($_SESSION[C('ORDER_AUTH_KEY')]['permission']!=1){
            echo "<script>alert('无权限！');history.back();</script>";
            exit;
        }
        $this->display();
    }
//添加员工
    public function addUser(){
        $this->login_test();
        if(I('post.')){
            $post = I('post.');
            if($post['username']==''){
                echo "<script>alert('用户名不能为空！');history.back();</script>";
                exit;
            }else{
                $have_user = M('order_user')->where('username = "'.$post['username'].'"  OR real_name = "'.$post['real_name'].'"  ')->find();
                $have_permission5 = M('order_user')->where('permission=5 ')->find();

                if($have_user){
                    echo "<script>alert('用户名或昵称已存在！');history.back();</script>";
                    exit;
                }
                if($have_permission5&&$post['permission']==5){
                    echo "<script>alert('财务只能存在一个！');history.back();</script>";
                    exit;
                }

                $post['permission']==1&&$post['permission']=2;
                $post['add_time'] = time();
                $post['sex'] = 1;
                $post['status'] = 1;
                $post['pwd'] = $this->md5_pwd('tlay123');


                $add_user = M('order_user')->add($post);
                if($add_user){
                    echo "<script>alert('添加成功！');window.location.href='".__MODULE__."/Admin/user.html';</script>";
                    exit;
                }else{
                    echo "<script>alert('添加失败！');history.back();</script>";
                    exit;
                }
            }
        }else{
            $this->display();
        }
    }

//修改员工信息
    public function editUser(){
        $this->login_test();
        if(I('post.')){
            $post = I('post.');
            if($post['username']==''){
                echo "<script>alert('用户名不能为空！');history.back();</script>";
                exit;
            }else{
                $have_user = M('order_user')->where('(username = "'.$post['username'].'"  OR real_name = "'.$post['real_name'].'" )  and id!='.$post['id'].'')->find();

                if($have_user){
                    echo "<script>alert('用户名或昵称已存在！');history.back();</script>";
                    exit;
                }


                $add_user = M('order_user')->where('id='.$post['id'].'')->save($post);
                if($add_user!==false){
                    echo "<script>alert('修改成功！');window.location.href='".__MODULE__."/Admin/user.html';</script>";
                    exit;
                }else{
                    echo "<script>alert('修改失败！');history.back();</script>";
                    exit;
                }
            }
        }else{
            $info = M('order_user')->where('id='.I('get.id').'')->find();
            if($info){
                $this->assign('info',$info);
                $this->display();
            }else{
                echo "<script>alert('地址异常！');history.back();</script>";
                exit;
            }
        }
    }


    //员工详情页+修改自己
    public function info(){
        $this->login_test();
        $this->login_test();
        if(I('post.')){
            $post = I('post.');
            $where['id']=$post['id'];

            $have_user = M('order_user')->where('real_name = "'.$post['real_name'].'"  and id!='.$post['id'].'')->find();
            if($have_user){
                echo "<script>alert('昵称已存在！');history.back();</script>";
                exit;
            }
            $info['real_name']=$post['real_name'];
            $info['phone']=$post['phone'];
            $info['birthday']=$post['birthday'];
            $info['remark']=$post['remark'];
            $save_mod = M('order_user')->where($where)->save($info);
            if($save_mod!==false){
                echo "<script>alert('修改成功！');window.location.href='".__MODULE__."/Admin/info.html';</script>";
            }else{
                echo "<script>alert('修改失败！');window.location.href='".__MODULE__."/Admin/info.html';</script>";
            }
        }else {
            $user_info = M('order_user')->where('id=' . $_SESSION[C('ORDER_AUTH_KEY')]['id'] . '')->find();
            $login_time = M('order_log')->field('login_time')->where('employee_id=' . $_SESSION[C('ORDER_AUTH_KEY')]['id'] . '')->order('login_time desc')->limit(2)->select();
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
            $user = M('order_user')->where('id=' . $_SESSION[C('ORDER_AUTH_KEY')]['id'] . '')->find();
            if($post['old']==''||$post['new']==''||$post['newre']==''){
                $back['code']=1001;//密码不能为空
            }elseif(strlen($post['new'])<6||strlen($post['new'])>18){
                $back['code']=1002;//密码长度不正确
            }elseif($user['pwd']!=$this->md5_pwd($post['old'])){
                $back['code']=1003;//原密码不正确
            }else{
                $where['id']=$_SESSION[C('ORDER_AUTH_KEY')]['id'];
                $info['pwd']=$this->md5_pwd($post['new']);
                $save = M('order_user')->where($where)->save($info);
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
        $_SESSION[C('ORDER_AUTH_KEY')]=null;
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