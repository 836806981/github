<?php
namespace Goal\Controller;
use Think\Controller;
class AdminController extends CommonController {
    public function  login_test(){
        if(!$_SESSION[C('GOAL_AUTH_KEY')]['id']){
            echo "<script>alert('请登录！');window.location.href='" . __MODULE__ . "/Admin/login.html';</script>";
            exit;
        }
    }
    public function per_test(){
//        $admin_user = M('permission')->where('employee_id='. $_SESSION[C('GOAL_AUTH_KEY')]['id'].'')->find();
        if($_SESSION[C('GOAL_AUTH_KEY')]['permission']!=1){
            echo "<script>alert('你没有权限！');window.location.href='" . __MODULE__ . "/Admin/info.html';</script>";
            exit;
        }
    }

    //后台登录页面
    public function login(){
        if(I('post.')) {
            $map['phone'] = trim(I('post.username'));
            $map['pwd'] = $this->md5_pwd(trim(I('post.pwd')));
            $user_info = M('goal_user')->where($map)->find();
            if ($user_info && $user_info != '') {//登录成功的处理
                $_SESSION[C('GOAL_AUTH_KEY')] = $user_info;
                $session_id = session_id();
                $ip = $this->getIP();
                $log['sessionid'] = $session_id;
                $log['login_ip'] = $ip;
                $log['employee_id'] = $user_info['id'];
                $log['login_time'] = time();
                $log['mac'] = $this->GetMacAddr(PHP_OS);//获取mac 地址
                $log['status'] = 1;
                $login_log = D('Loginlog');
                $login_log->add_mod($log);
                echo "<script>alert('登录成功！');window.location.href='" . __MODULE__ . "/Admin/info.html';</script>";
                exit;

            } else{
                $ip = $this->getIP();
                $log['sessionid']='';
                $log['login_ip']=$ip;
                $log['login_time']=time();
                $log['status'] = 2;
                $log['mac'] = $this->GetMacAddr(PHP_OS);//获取mac 地址
                $login_log = D('Loginlog');
                $login_log->add_mod($log);
                echo "<script>alert('用户不存在或密码错误！');history.go(-1);</script>";
                exit;
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
            $info['name']=$post['name'];
            $info['email']=$post['email'];
            $info['job']=$post['job'];
//            $info['phone']=$post['phone'];
            $save_mod = M('goal_user')->where($where)->save($info);
            if($save_mod!==false){
                echo "<script>alert('修改成功！');window.location.href='".__MODULE__."/Admin/info.html';</script>";
                exit;
            }else{
                echo "<script>alert('修改失败！');window.location.href='".__MODULE__."/Admin/info.html';</script>";
                exit;
            }
        }else {
            if(I('get.id')){
                $id = I('get.id');
            }else{
                $id = $_SESSION[C('GOAL_AUTH_KEY')]['id'];
            }


            $user_info = M('goal_user')->where('id=' . $id . '')->find();
            $login_time = M('goal_log')->field('login_time')->where('employee_id=' . $id . '')->order('login_time desc')->limit(2)->select();
            $user_info['log_time'] = $login_time[1]['login_time'] ? date('Y-m-d H:i:s', $login_time[1]['login_time']) : '首次登录';
            $this->assign('user_info', $user_info);
            $this->display();
        }
    }


    //修改密码页面
    public function changepass(){
        $this->login_test();
        if(I('get.id')){
            $user = M('goal_user')->where('id=' . I('get.id'). '')->find();
        }else{
            $user = M('goal_user')->where('id=' . $_SESSION[C('GOAL_AUTH_KEY')]['id'] . '')->find();
        }
        if(I('post.')){
            $post = I('post.');

            if($post['old']==''||$post['new']==''||$post['newre']==''){
                $back['code']=1001;//密码不能为空
            }elseif(strlen($post['new'])<6||strlen($post['new'])>18){
                $back['code']=1002;//密码长度不正确
            }elseif($user['pwd']!=$this->md5_pwd($post['old'])){
                $back['code']=1003;//原密码不正确
            }else{
                $where['id']=$_SESSION[C('GOAL_AUTH_KEY')]['id'];
                $info['pwd']=$this->md5_pwd($post['new']);
                $save =  M('goal_user')->where($where)->save($info);
                if($save!==false){
                    $back['code']=1000;//修改成功
                }else{
                    $back['code']=1004;//修改失败
                }
            }
            echo json_encode($back);
            exit;
        }else{
            $this->assign('user_info',$user);
            $this->display();
        }
    }

    public function resetPassword(){
        $this->login_test();
        $this->per_test();
        $where['id'] = I('get.id');
        $pwd['pwd'] = $this->md5_pwd('tlay123');
        $save = M('goal_user')->where($where)->save($pwd);
        if($save!==false){
            echo "<script>alert('重置密码成功，新密码为：tlay123');window.location.href='".__MODULE__."/Admin/user.html';</script>";
            exit;
        }else{
            echo "<script>alert('重置密码失败！');history.go(-1);;</script>";
            exit;
        }

    }

    public function add_employee(){
        $this->login_test();
        $this->per_test();
        if($_GET['pid']){
            $users_arr = M('goal_user')->where('permission!=1 and id='.$_GET['pid'])->find();

            $users =  "<option value='".$users_arr['id']."'>".$users_arr['name']."</option>";  // ".$str.$v['name']."</a><br/>";
            $have_pid = 0;
        }else{
            $users = $this->showUser(0);
            $have_pid = 1;
        }
        $this->assign('have_pid',$have_pid);
        $this->assign('users',$users);
        $this->display('add_user');
    }
    public function add_User(){
        $this->login_test();
        $this->per_test();
        $post = I('post.');
        if($post['pid']==0){
            $post['level'] = 0;
        }else{
            $level = M('goal_user')->where('id='.$post['pid'])->find();
            $post['level'] = $level['level']+1;
        }
        if($post['level']>3){
            echo "<script>alert('层级错误！');history.go(-1);</script>";
            exit;
        }
        $post['pwd'] = $this->md5_pwd('tlay123');
        $post['add_time'] = time();
        $post['permission'] = 2;

        $add_mod = M('goal_user')->add($post);
        if($add_mod){
            echo "<script>alert('添加成功！');window.location.href='".__MODULE__."/Admin/user.html';</script>";
            exit;
        }else{
            echo "<script>alert('添加失败！');history.go(-1);</script>";
            exit;
        }
    }

    //递归员工
    public function showUser($pid)
    {
        $html = '';
        $users = M('goal_user')->where('permission!=1 and level <3 and pid='.$pid )->select();
        foreach($users as $k=>$v){
            $str   = str_repeat("&nbsp;",$v['level']).($v['level']>0?"|_":'').str_repeat("_",0);
            $html .= "<option value='".$v['id']."'>".$str.$v['name']."</option>";  // ".$str.$v['name']."</a><br/>";
            $have_user = M('goal_user')->where('permission!=1 and pid='.$pid)->find();
           if($have_user) {
               $html .= $this->showUser($v['id']);
           }
        }
        return $html;
    }

    public function user(){
        $this->login_test();



        $users = $this->showUsers(0);

        $this->assign('users',$users);
        $this->display();
    }
    //递归员工2
    public function showUsers($pid)
    {
        $html = '';
        $users = M('goal_user')->where('permission!=1 and pid='.$pid)->select();
        foreach($users as $k=>$v){
            $str   = str_repeat("&nbsp;",$v['level']).($v['level']>0?"|_":'').str_repeat("_",0);
            if($v['level']<3&&$_SESSION[C('GOAL_AUTH_KEY')]['permission']==1){
                $add_down = "<a href='".__MODULE__."/Admin/add_employee/pid/".$v['id']."'class='name_class_add' title='添加下级员工'>＋</a>";
            }
            $html .= "<span style='color: #ccc;'>".$str."</span>"."<a href='".__MODULE__."/Goal/goal/id/".$v['id']."'class='name_class'>".$v['name']."</a><span style='font-size:18px;color:#666'>(".$v['job'].")</span><a style='color: #aaa;' href='".__MODULE__."/Admin/info/id/".$v['id']."'>".$v['phone']."</a>".$add_down." <br/>";  // ".$str.$v['name']."</a><br/>";
            $have_user = M('goal_user')->where('permission!=1 and pid='.$pid)->find();
            if($have_user) {
                $html .= $this->showUsers($v['id']);
            }
        }
        return $html;
    }
    //退出登录
    public function login_out(){
        $_SESSION[C('GOAL_AUTH_KEY')]=null;
        echo "<script>alert('已成功退出！');window.location.href='".__MODULE__."/Admin/login.html';</script>";
        exit;
    }



    //目标管理提醒bat位置
    //目标邮件提醒
    public function remind(){
        $goal = M('goal_goal')->field('goal_goal.id,goal_user.name as user_name,goal_user.email,goal_goal.s_time,goal_goal.b_time,goal_goal.name')->join('left join goal_user ON goal_user.id=goal_goal.user_id')->where('goal_goal.status<3 and s_time <="'.date('Y-m-d',strtotime('+2 day')).'" and send_email!=1')->select();
        $project = M('goal_project')->field('goal_project.id,goal_user.name as user_name,goal_user.email,goal_project.name,goal_project.s_time,goal_project.b_time')->join('left join goal_user ON goal_user.id=goal_project.user_id')->where('goal_project.status<3 and s_time <="'.date('Y-m-d',strtotime('+2 day')).'" and send_email!=1')->select();

        $send['send_email'] = 1;
        foreach($goal as $k=>$v){
            $name = '目标管理系统';
            $title = '目标交互提醒通知-'.$v['name'] . $v['s_time'];
            $content = '<style>
                    .tianxie-content{
                    width: 700px;
                    height: auto;
                    font-family: "microsoft yahei";
                    font-size: 14px;
                    margin:10px 0 0 80px;
                    color: #666666;
                    }
                    </style>
                    <div class="tianxie-content">
						'.$v['user_name'].',<br/><br/>
						你的目标在两天内即将到达目标计划完成时间，请及时交互，完成目标若已交互，请让直接上级进行审核<br/><br/>
						目标名称：'.$v['name'].'<br/>
						计划完成时间：'.$v['s_time'].'<br/>
						计划开始时间：'.$v['b_time'].'<br/>
						<br/><br/>
						如有疑问请反馈开发部！
					</div>';
            $no_html =$v['user_name'].',<br/><br/>
						你的目标在两天内即将到达目标计划完成时间，请及时交互，完成目标若已交互，请让直接上级进行审核<br/><br/>
						目标名称：'.$v['name'].'<br/>
						计划完成时间：'.$v['s_time'].'<br/>
						计划开始时间：'.$v['b_time'].'<br/>
						<br/><br/>
						如有疑问请反馈开发部！';
            $this->send_email($v['email'],$name,$title,$content,$no_html);//收件人、发件人姓名、标题、内容

            M('goal_goal')->where('id='.$v['id'])->save($send);
        }

        foreach($project as $k=>$v){
            $name = '目标管理系统';
            $title = '项目交互提醒通知-'.$v['name'] . $v['s_time'];
            $content = '<style>
                    .tianxie-content{
                    width: 700px;
                    height: auto;
                    font-family: "microsoft yahei";
                    font-size: 14px;
                    margin:10px 0 0 80px;
                    color: #666666;
                    }
                    </style>
                    <div class="tianxie-content">
						'.$v['user_name'].',<br/><br/>
						你的项目在两天内即将到达项目计划完成时间，请及时通知参与人员，尽快完成并交互<br/><br/>
						项目名称：'.$v['name'].'<br/>
						计划完成时间：'.$v['s_time'].'<br/>
						计划开始时间：'.$v['b_time'].'<br/>
						<br/><br/>
						如有疑问请反馈开发部！
					</div>';
            $no_html =$v['user_name'].',<br/><br/>
						你的项目在两天内即将到达项目计划完成时间，请及时通知参与人员，尽快完成并交互<br/><br/>
						项目名称：'.$v['name'].'<br/>
						计划完成时间：'.$v['s_time'].'<br/>
						计划开始时间：'.$v['b_time'].'<br/>
						<br/><br/>
						如有疑问请反馈开发部！';
            $this->send_email($v['email'],$name,$title,$content,$no_html);//收件人、发件人姓名、标题、内容
            M('goal_project')->where('id='.$v['id'])->save($send);
        }

        echo "<script>alert('提醒成功');history.go(-1);</script>";
        exit;

    }







}