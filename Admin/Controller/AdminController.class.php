<?php
namespace Admin\Controller;
use Think\Controller;
class AdminController extends CommonController {
    public function  login_test(){
        if(!$_SESSION[C('ADMIN_AUTH_KEY')]['id']){
            echo "<script>alert('请登录！');window.location.href='" . __MODULE__ . "/Admin/login.html';</script>";
            exit;
        }
    }
    public function per_test(){
//        $admin_user = M('permission')->where('employee_id='. $_SESSION[C('ADMIN_AUTH_KEY')]['id'].'')->find();
        if($_SESSION[C('ADMIN_AUTH_KEY')]['permission']!=1){
            echo "<script>alert('你没有权限！');window.location.href='" . __MODULE__ . "/Admin/info.html';</script>";
            exit;
        }
    }

    //后台登录页面
    public function login(){
        if(I('post.')) {
            $employee = D('Employee');
            $map['username'] = trim(I('post.username'));
            $map['pwd'] = $this->md5_pwd(trim(I('post.pwd')));
            $user_info = $employee->login_mod($map);
            if ($user_info && $user_info != '') {//登录成功的处理
                if($user_info['status']==0){
                    echo "<script>alert('账号被停用或非后台账号，无法登陆！');window.location.href='" . __MODULE__ . "/Admin/login.html';</script>";
                }else {
                    $admin_user = M('permission')->field('edit_per,add_per,del_per,nurse,news,job,destine,trainee,order,video')->where('employee_id='.$user_info['id'] .'')->find();
                    $user_info = array_merge($user_info,$admin_user);

                    $_SESSION[C('ADMIN_AUTH_KEY')] = $user_info;
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
                    echo "<script>alert('登录成功！');window.location.href='" . __MODULE__ . "/Admin/info.html';</script>";
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

    //添加管理员页面
    public function addEmployee(){
        $this->login_test();
        $this->per_test();
        if (I('post.')) {
            $post = I('post.');
            $username = M('employee')->where('username="'.$post['username'].'"')->find();
            if($username&&$username!=''){
                $back['code']=1002;
            }else {
                $employee_info['username'] = $post['username'];
                $employee_info['pwd'] = $this->md5_pwd('tlay123');
                $employee_info['permission'] = $post['permission'];
                $employee_info['real_name'] = $post['real_name'];
                $employee_info['phone'] = $post['phone'];
                $employee_info['sex'] = $post['sex'];
                $employee_info['remark'] = $post['remark'];
                $employee_info['birthday'] = $post['birthday'];
                $employee_info['status'] = 1;
                $employee_info['add_time'] = time();
                $employee_id = D('Employee')->add_mod($employee_info);

                $permission['employee_id'] = $employee_id;
                if( $post['permission']==1){
                    $permission['news']=1;
                    $permission['nurse']=1;
                    $permission['job']=1;
                    $permission['destine']=1;
                    $permission['del_per']=1;
                    $permission['edit_per']=1;
                    $permission['add_per']=1;
                    $permission['order']=1;
                    $permission['trainee']=1;
                    $permission['training']=1;
                    $permission['video']=1;
                }else{
                    $permission['add_per'] = $post['add_per'] ;
                    $permission['edit_per'] = $post['edit_per'] ;
                    $permission['del_per'] = $post['del_per'];
                    $permission['nurse'] = $post['nurse'];
                    $permission['news'] = $post['news'];
                    $permission['job'] = $post['job'];
                    $permission['destine'] = $post['destine'];
                    $permission['order'] = $post['order'];
                    $permission['trainee'] = $post['trainee'];
                    $permission['training'] = $post['training'];
                    $permission['video'] = $post['video'];

                }
                $permission_id = D('permission')->add_mod($permission);
                if ($employee_id && $permission_id) {
                    $back['code']=1000;
                } else {
                    $where_per['id']=$permission_id;
                    $where_emp['id']=$employee_id;
                    D('permission')->delete_mod($where_per);
                    D('Employee')->delete_mod($where_emp);
                    $back['code']=1001;
                }
            }
            echo json_encode($back);
        } else {
            $this->display();
        }
    }

//    重置密码

    public function reset_pwd(){
        $this->login_test();
        $this->per_test();
        $info = M('employee')->where('id='.I('post.id'))->find();
        if(!$info){
            echo "<script>alert('地址异常！');window.history.go(-1);</script>";
            exit;
        }
        $employee_info['pwd'] = $this->md5_pwd('tlay123');

        $save_mod = M('employee')->where('id='.I('post.id'))->save($employee_info);
        if($save_mod===false){
            M('employee')->where('id='.I('post.id'))->save($employee_info);
        }

        echo "<script>alert('重置成功！');window.history.go(-1);</script>";
        exit;



    }
    //删除员工
    public function delete_emp(){
        $this->login_test();
        $this->per_test();
        $where_per['employee_id']=I('get.id');
        $where_emp['id']=I('get.id');
        D('permission')->delete_mod($where_per);
        D('Employee')->delete_mod($where_emp);
        echo "<script>alert('删除成功！');window.location.href='".__MODULE__."/Admin/employee.html';</script>";

    }

    public function set_status(){
        $this->login_test();
        $this->per_test();
        $where['id']=I('get.id');
        $map['status']=I('get.status');
        $save_mod = D('employee')->save_mod($where,$map);
        if($save_mod!==false){
            echo "<script>alert('操作成功！');window.location.href='".__MODULE__."/Admin/employee.html';</script>";
        }else{
            echo "<script>alert('操作失败！');window.location.href='".__MODULE__."/Admin/employee.html';</script>";
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
            $info['sex']=$post['sex'];
            $info['birthday']=$post['birthday'];
            $info['remark']=$post['remark'];
            $employee = D('Employee');
            $save_mod = $employee->save_mod($where,$info);
            if($save_mod!==false){
                echo "<script>alert('修改成功！');window.location.href='".__MODULE__."/Admin/info/id/".$post['id'].".html';</script>";
            }else{
                echo "<script>alert('修改失败！');window.location.href='".__MODULE__."/Admin/info/id/".$post['id'].".html';</script>";
            }
        }else {
            if(I('get.id')){
                if($_SESSION[C('ADMIN_AUTH_KEY')]['permission']==1){
                    $user_info = M('employee')->where('id=' .I('get.id'). '')->find();
                }else{
                    if(I('get.id')==$_SESSION[C('ADMIN_AUTH_KEY')]['id']){
                        $user_info = M('employee')->where('id=' .I('get.id'). '')->find();
                    }else{
                        echo "<script>alert('您无权查看！');window.location.href='".__MODULE__."/Admin/login.html';</script>";
                    }
                }
            }else{
                $user_info = M('employee')->where('id=' . $_SESSION[C('ADMIN_AUTH_KEY')]['id'] . '')->find();
            }
            $per = array('','超级管理员','普通管理员','阿姨管理员');
            $user_info['per'] = $per[$user_info['permission']];
            $login_time = M('login_log')->field('login_time')->where('employee_id=' . $_SESSION[C('ADMIN_AUTH_KEY')]['id'] . '')->order('login_time desc')->limit(2)->select();
            $user_info['log_time'] = $login_time[1]['login_time'] ? date('Y-m-d H:i:s', $login_time[1]['login_time']) : '首次登录';
            $permission = M('permission')->where('employee_id='.$user_info['id'].'')->find();
            $this->assign('permission', $permission);
            $this->assign('user_info', $user_info);
            $this->display();
        }
    }

    //更改管理员权限
    public function changeper(){
        $post = I('post.');
        if($_SESSION[C('ADMIN_AUTH_KEY')]['permission']!=1){
            echo "<script>alert('无权限！');window.location.href='".__MODULE__."/Admin/info/id/".$post['id'].".html';</script>";
        }else{
            $this->login_test();
            $permission = D("Permission");
            $post['add_per']||$post['add_per']=0;
            $post['edit_per']||$post['edit_per']=0;
            $post['del_per']||$post['del_per']=0;
            $post['nurse']||$post['nurse']=0;
            $post['news']||$post['news']=0;
            $post['job']||$post['job']=0;
            $post['destine']||$post['destine']=0;
            $post['order']||$post['order']=0;
            $post['trainee']||$post['trainee']=0;
            $post['training']||$post['training']=0;
            $post['video']||$post['video']=0;
            $where['employee_id']=$post['employee_id'];
            $save_mod = $permission ->save_mod($where,$post);
            if($save_mod!==false){
                echo "<script>alert('修改成功！');window.location.href='".__MODULE__."/Admin/info/id/".$post['employee_id'].".html';</script>";
            }else{
                echo "<script>alert('修改失败！');window.location.href='".__MODULE__."/Admin/info/id/".$post['employee_id'].".html';</script>";
            }
        }
    }

    //员工列表
    public function employee(){
        $this->login_test();
        $this->per_test();
        $employee = D('employee');
        $count=$employee->count();
        $Page       = new \Think\Page($count,20);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show       = $Page->show();// 分页显示输出// 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $employee_list = $employee->limit($Page->firstRow.','.$Page->listRows)->select();



        $this->assign('employee',$employee_list);//
        $this->assign('page',$show);//
        $this->display();


    }

    //修改密码页面
    public function changepass(){
        $this->login_test();
        if(I('post.')){
            $post = I('post.');
            $user = M('employee')->where('id=' . $_SESSION[C('ADMIN_AUTH_KEY')]['id'] . '')->find();
            if($post['old']==''||$post['new']==''||$post['newre']==''){
                $back['code']=1001;//密码不能为空
            }elseif(strlen($post['new'])<6||strlen($post['new'])>18){
                $back['code']=1002;//密码长度不正确
            }elseif($user['pwd']!=$this->md5_pwd($post['old'])){
                $back['code']=1003;//原密码不正确
            }else{
                $where['id']=$_SESSION[C('ADMIN_AUTH_KEY')]['id'];
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
        $_SESSION[C('ADMIN_AUTH_KEY')]=null;
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


    //罗倩视频处理
    public function video(){
        if($_SESSION[C('ADMIN_AUTH_KEY')]['video']!=1){
            echo "<script>alert('你没有权限！');window.location.href='" . __MODULE__ . "/Admin/info.html';</script>";
            exit;
        }

        $count = M('video')->where('status!=10')->count();
        $Page       = new \Think\Page($count,20);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show       = $Page->showFront();// 分页显示输出// 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $message_list = M('video')->where('status!=10')->order('add_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        $level_name = ['','一星课程','二星课程','三星课程','四星课程','五星课程'];
        $type_name = ['','1','2','3','4','5','6'];
        foreach($message_list as $k=>$v){
            $message_list[$k]['add_time']=date('Y-m-d H:i:s',$v['add_time']);
            $message_list[$k]['level_name'] = $level_name[$v['level']];
//            $message_list[$k]['type_name'] = $type_name[$v['type']];
        }

        $this->assign('message_list',$message_list);
        $this->assign('page',$show);

        $this->display();
    }

    //添加视频页面
    public function addVideo(){

        $this->display();
    }

    //添加视频
    public function add_video(){
        $post = I('post.');
        if($_FILES['video']['tmp_name']) {
            $file = $_FILES['video'];
            $upload = new \Think\Upload();// 实例化上传类
            $upload->exts = array('avi', 'mp4','jpg', 'jpeg','gif','png');// 设置附件上传类型
            $upload->savePath = 'video/'; // 设置附件上传目录
            $upload->saveName = time() . '_' . mt_rand();
            $upload->maxSize = 500048576;// 设置附件上传大小
            $info = $upload->uploadOne($file);
            if (!$info) {
                $error = $upload->getError();
//                echo "<script>alert('".$error."');history.back();</script>";
            }else{
                $imgPath = $info['savepath'] . $info['savename'];
                $post['url'] = $imgPath;
            }
        }else{
            echo "<script>alert('请上传相应文件');history.go(-1);</script>";
            die;
        }

        if($_FILES['src']['tmp_name']) {
            $file = $_FILES['src'];
            $upload = new \Think\Upload();// 实例化上传类
            $upload->exts = array('jpg', 'jpeg','gif','png');// 设置附件上传类型
            $upload->savePath = 'video/'; // 设置附件上传目录
            $upload->saveName = time() . '_' . mt_rand();
            $upload->maxSize = 10048576;// 设置附件上传大小
            $info = $upload->uploadOne($file);
            if (!$info) {
                $error = $upload->getError();
//                echo "<script>alert('".$error."');history.back();</script>";
            }else{
                $imgPath = $info['savepath'] . $info['savename'];
                $post['src'] = $imgPath;
            }
        }else{
            echo "<script>alert('请上传头图文件');history.go(-1);</script>";
            die;
        }

        $post['add_time'] = time();
        $post['add_mac'] = $this->GetMacAddr(PHP_OS);
        $post['status'] = 1;
        $post['sort'] = 100;
        $post['pay_number'] = 0;

        $add_mod = M('video')->add($post);
        if($add_mod!==false){
            echo "<script>alert('上传成功');window.location.href='" . __MODULE__ . "/Admin/video.html';</script>";
            die;
        }else{
            echo "<script>alert('上传失败');history.go(-1);</script>";
            die;
        }
    }

    //修改视频页面
    public function editVideo(){
        if(!I('get.id')){
            echo "<script>alert('地址异常');history.go(-1);</script>";
            die;
        }

        $video = M('video')->where('id='.I('get.id'))->find();
        $this->assign('video',$video);
        $this->display();
    }

    //添加视频
    public function edit_video(){
        $post = I('post.');
        if($_FILES['video']['tmp_name']) {
            $file = $_FILES['video'];
            $upload = new \Think\Upload();// 实例化上传类
            $upload->exts = array('avi', 'mp4','jpg', 'jpeg','gif','png');// 设置附件上传类型
            $upload->savePath = 'video/'; // 设置附件上传目录
            $upload->saveName = time() . '_' . mt_rand();
            $upload->maxSize = 500048576;// 设置附件上传大小
            $info = $upload->uploadOne($file);
            if (!$info) {
                $error = $upload->getError();
                echo "<script>alert('".$error."');history.go(-1);</script>";
            }else{
                $imgPath = $info['savepath'] . $info['savename'];
                $post['url'] = $imgPath;
            }
        }

        if($_FILES['src']['tmp_name']) {
            $file = $_FILES['src'];
            $upload = new \Think\Upload();// 实例化上传类
            $upload->exts = array('jpg', 'jpeg','gif','png');// 设置附件上传类型
            $upload->savePath = 'video/'; // 设置附件上传目录
            $upload->saveName = time() . '_' . mt_rand();
            $upload->maxSize = 10048576;// 设置附件上传大小
            $info = $upload->uploadOne($file);
            if (!$info) {
                $error = $upload->getError();
                echo "<script>alert('".$error."');history.go(-1);</script>";
            }else{
                $imgPath = $info['savepath'] . $info['savename'];
                $post['src'] = $imgPath;
            }
        }

        $add_mod = M('video')->where('id='.$post['id'])->save($post);
        if($add_mod!==false){
            echo "<script>alert('修改成功');window.location.href='" . __MODULE__ . "/Admin/video.html';</script>";
            die;
        }else{
            echo "<script>alert('修改失败');history.go(-1);</script>";
            die;
        }
    }

    //删除
    public function del_video(){
        if(!I('get.id')){
            echo "<script>alert('地址异常');history.go(-1);</script>";
            die;
        }
        $video = M('video')->where('id='.I('get.id'))->find();
        if($video['url']){
            unlink('Uploads/'.$video['url']);
        }
        $del_mod = M('video')->where('id='.I('get.id'))->delete();
        if($del_mod!==false){
            echo "<script>alert('删除成功');window.location.href='" . __MODULE__ . "/Admin/video.html';</script>";
            die;
        }else{
            echo "<script>alert('删除失败');history.go(-1);</script>";
            die;
        }


    }



}