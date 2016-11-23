<?php
namespace Goal\Controller;
use Think\Controller;
class GoalController extends CommonController {
    public function  _initialize(){
        if(!$_SESSION[C('GOAL_AUTH_KEY')]['id']){
            echo "<script>alert('请登录！');window.location.href='" . __MODULE__ . "/Admin/login.html';</script>";
            exit;
        }
    }


    //添加目标
    public function add_Goal(){
        if(I('get.id')){
            $id = I('get.id');
        }else{
            $id = $_SESSION[C('GOAL_AUTH_KEY')]['id'];
        }
        $goal_user = M('goal_user')->where('id='.$id)->find();
        if($goal_user['pid']!=$_SESSION[C('GOAL_AUTH_KEY')]['id']&&$goal_user['id']!=$_SESSION[C('GOAL_AUTH_KEY')]['id']){
            echo "<script>alert('你不是".$goal_user['name']."的直接上级，无法为Ta添加目标！');history.go(-1);</script>";
            exit;
        }
        $this->assign('to_user','为'.$goal_user['name']);
        $this->assign('id',$id);
        $this->display();
    }


//添加目标
    public function add_goal_data(){
        $post = I('post.');
        $post['add_time'] = time();
        $post['mac'] = $this->GetMacAddr(PHP_OS);
        $post['status'] = 1;
        $post['project'] = 0;
        $post['file_type'] =  $post['file_type_1']. $post['file_type_2']. $post['file_type_3']. $post['file_type_4']. $post['file_type_5']. $post['file_type_6']. $post['file_type_7']. $post['file_type_8'];
        $post['file_type'] = substr($post['file_type'],0,-1);

        $post['s_time'] = date('Y-m-d',strtotime($post['b_time'].'+'.$post['day_number'].' days'));
        $add_mod = M('goal_goal')->add($post);
        if($add_mod){

            if($post['user_id'] != $_SESSION[C('GOAL_AUTH_KEY')]['id']){
                $goal_user = M('goal_user')->where('id='.$post['user_id'] )->find();
                $name = '目标管理系统';
                $title = '新目标已建立-'.$post['name'];
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
						'.$goal_user['name'].',<br/><br/>
						你的目标已建立，请登录<a href="http://www.tianluoayi.com/goal.php/Admin/login">田螺阿姨项目管理系统</a>查看:<br/><br/>
						目标名称：'.$post['name'].'<br/>
						目标建立者：'.$_SESSION[C('GOAL_AUTH_KEY')]['name'].'<br/>
						开始时间：'.$post['b_time'].'<br/>
						计划完成时间：'.$post['s_time'].'<br/>
						<br/><br/>
						如有疑问请反馈开发部！
					</div>';
                $no_html = ''.$goal_user['name'].',<br/><br/>
						你的目标已建立，请登录<a href="http://www.tianluoayi.com/goal.php/Admin/login">田螺阿姨项目管理系统</a>查看:<br/><br/>
						目标名称：'.$post['name'].'<br/>
						目标建立者：'.$_SESSION[C('GOAL_AUTH_KEY')]['name'].'<br/>
						开始时间：'.$post['b_time'].'<br/>
						计划完成时间：'.$post['s_time'].'<br/>
						<br/><br/>
						如有疑问请反馈开发部！';
                $this->send_email($goal_user['email'],$name,$title,$content,$no_html);//收件人、发件人姓名、标题、内容

            }
            echo "<script>alert('添加目标成功');window.location.href='".__MODULE__."/Goal/goal/id/".$post['user_id']."';</script>";
            exit;
        }else{
            echo "<script>alert('添加目标失败');history.go(-1);</script>";
            exit;
        }
    }

    //修改目标
    public function edit_Goal(){
        if(I('get.id')){
            $goal = M('goal_goal')->where('id='.I('get.id'))->find();
            $goal_user = M('goal_user')->where('id='.$goal['user_id'])->find();

//            $list[$k]['is_boss'] = $user_info['pid']==0?($_SESSION[C('GOAL_AUTH_KEY')]['permission']==1?1:0):($user_info['pid'] == $_SESSION[C('GOAL_AUTH_KEY')]['id']?1:0);
            if(!$goal_user['pid']==0?($_SESSION[C('GOAL_AUTH_KEY')]['permission']==1?1:0):($goal_user['pid'] == $_SESSION[C('GOAL_AUTH_KEY')]['id']?1:0)){
                echo "<script>alert('你不是".$goal_user['name']."的直接上级，无法为Ta修改目标！');history.go(-1);</script>";
                exit;
            }
            $this->assign('to_user','为'.$goal_user['name']);
        }else{
            echo "<script>alert('地址异常');history.go(-1);</script>";
            exit;
        }



        $this->assign('goal',$goal);
        $this->display();
    }

    //修改目标
    public function edit_goal_data(){
        $post = I('post.');
        $post['s_time'] = date('Y-m-d',strtotime($post['b_time'].'+'.$post['day_number'].' days'));
        $post['file_type'] =  $post['file_type_1']. $post['file_type_2']. $post['file_type_3']. $post['file_type_4']. $post['file_type_5']. $post['file_type_6']. $post['file_type_7']. $post['file_type_8'];
        $post['file_type'] = substr($post['file_type'],0,-1);

        $add_mod = M('goal_goal')->where('id='.$post['id'])->save($post);
        if($add_mod){

            $goal_user = M('goal_user')->where('id='.$post['user_id'] )->find();
            $name = '目标管理系统';
            $title = '目标已被修改-'.$post['name'];
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
						'.$goal_user['name'].',<br/><br/>
						你的目标修改，请登录<a href="http://www.tianluoayi.com/goal.php/Admin/login">田螺阿姨项目管理系统</a>查看:<br/><br/>
						目标名称：'.$post['name'].'<br/>
						目标建立者：'.$_SESSION[C('GOAL_AUTH_KEY')]['name'].'<br/>
						开始时间：'.$post['b_time'].'<br/>
						计划完成时间：'.$post['s_time'].'<br/>
						<br/><br/>
						如有疑问请反馈开发部！
					</div>';
            $no_html = ''.$goal_user['name'].',<br/><br/>
						你的目标已修改，请登录<a href="http://www.tianluoayi.com/goal.php/Admin/login">田螺阿姨项目管理系统</a>查看:<br/><br/>
						目标名称：'.$post['name'].'<br/>
						目标建立者：'.$_SESSION[C('GOAL_AUTH_KEY')]['name'].'<br/>
						开始时间：'.$post['b_time'].'<br/>
						计划完成时间：'.$post['s_time'].'<br/>
						<br/><br/>
						如有疑问请反馈开发部！';
            $this->send_email($goal_user['email'],$name,$title,$content,$no_html);//收件人、发件人姓名、标题、内容



            echo "<script>alert('修改目标成功');window.location.href='".__MODULE__."/Goal/goal/id/".$post['user_id']."';</script>";
            exit;
        }else{
            echo "<script>alert('修改目标失败');history.go(-1);</script>";
            exit;
        }
    }


    //邮件提醒列表页
    public function email_list(){
        if($_SESSION[C('GOAL_AUTH_KEY')]['permission']!=1){
            echo "<script>alert('无权限！');history.go(-1);</script>";
            exit;
        }

        $this->display();
    }

    public function get_email_list(){
        if($_SESSION[C('GOAL_AUTH_KEY')]['permission']!=1){
            echo "<script>alert('无权限！');history.go(-2);</script>";
            exit;
        }


        $post = I("post.");
        $currentpage = I("post.currentpage");
        $pagenum = I("post.pagenum");
        $start = ($currentpage - 1) * $pagenum;
        if($post['type']==1){
            $list = M('goal_goal')->where('status<3 and s_time <="'.date('Y-m-d',strtotime('+2 day')).'"')->order('send_email asc,s_time asc')->select();
            $count = M('goal_goal')->where('status<3 and s_time <="'.date('Y-m-d',strtotime('+2 day')).'"')->count();
        }elseif($post['type']==2){
            $list = M('goal_project')->where('status<3 and s_time <="'.date('Y-m-d',strtotime('+2 day')).'"')->order('send_email asc,s_time asc')->select();
            $count = M('goal_project')->where('status<3 and s_time <="'.date('Y-m-d',strtotime('+2 day')).'"')->count();
        }
        $status_name = ['未通过','进行中','已完成(未审核)','已完成'];
        foreach($list as $k=>$v){
            $user_info = M('goal_user')->field('name,pid')->where('id='.$v['user_id'])->find();
            $list[$k]['user_name'] = $user_info['name'];
            $list[$k]['status_name'] = $status_name[$v['status']];
        }
        $back['code'] = 1000;
        $back['data']['list'] = $list;
        $back['data']['num'] =$count;
        $back['data']['type'] =$post['type'];
        echo json_encode($back);
    }

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

    //单个邮件提醒
    public function send_user_id(){
        $send['send_email'] = 1;
       if($_GET['type']==1){
           $goal = M('goal_goal')->field('goal_goal.id,goal_user.name as user_name,goal_user.email,goal_goal.s_time,goal_goal.b_time,goal_goal.name')->join('left join goal_user ON goal_user.id=goal_goal.user_id')->where('goal_goal.id='.$_GET['id'])->find();
           $name = '目标管理系统';
           $title = '目标交互提醒通知-'.$goal['name'] . $goal['s_time'];
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
						'.$goal['user_name'].',<br/><br/>
						你的目标在两天内即将到达目标计划完成时间，请及时交互，完成目标若已交互，请让直接上级进行审核<br/><br/>
						目标名称：'.$goal['name'].'<br/>
						计划完成时间：'.$goal['s_time'].'<br/>
						计划开始时间：'.$goal['b_time'].'<br/>
						<br/><br/>
						如有疑问请反馈开发部！
					</div>';
           $no_html =$goal['user_name'].',<br/><br/>
						你的目标在两天内即将到达目标计划完成时间，请及时交互，完成目标若已交互，请让直接上级进行审核<br/><br/>
						目标名称：'.$goal['name'].'<br/>
						计划完成时间：'.$goal['s_time'].'<br/>
						计划开始时间：'.$goal['b_time'].'<br/>
						<br/><br/>
						如有疑问请反馈开发部！';
           $this->send_email($goal['email'],$name,$title,$content,$no_html);//收件人、发件人姓名、标题、内容

           M('goal_goal')->where('id='.$goal['id'])->save($send);


       }elseif($_GET['type']==2){
           $project = M('goal_project')->field('goal_project.id,goal_user.name as user_name,goal_user.email,goal_project.name,goal_project.s_time,goal_project.b_time')->join('left join goal_user ON goal_user.id=goal_project.user_id')->where('goal_project.id='.$_GET['id'])->find();

           $name = '目标管理系统';
           $title = '项目交互提醒通知-'.$project['name'] . $project['s_time'];
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
						'.$project['user_name'].',<br/><br/>
						你的项目在两天内即将到达项目计划完成时间，请及时通知参与人员，尽快完成并交互<br/><br/>
						项目名称：'.$project['name'].'<br/>
						计划完成时间：'.$project['s_time'].'<br/>
						计划开始时间：'.$project['b_time'].'<br/>
						<br/><br/>
						如有疑问请反馈开发部！
					</div>';
           $no_html =$project['user_name'].',<br/><br/>
						你的项目在两天内即将到达项目计划完成时间，请及时通知参与人员，尽快完成并交互<br/><br/>
						项目名称：'.$project['name'].'<br/>
						计划完成时间：'.$project['s_time'].'<br/>
						计划开始时间：'.$project['b_time'].'<br/>
						<br/><br/>
						如有疑问请反馈开发部！';
           $this->send_email($project['email'],$name,$title,$content,$no_html);//收件人、发件人姓名、标题、内容
           M('goal_project')->where('id='.$project['id'])->save($send);
       }
        echo "<script>alert('提醒成功');history.go(-1);</script>";
        exit;

    }




    //目标详情
    public function goal_info(){
        if(!I('get.id')){
            echo "<script>alert('地址异常');history.go(-1);</script>";
            exit;
        }
        $status_name = ['未通过','进行中','已完成(未审核)','已完成'];
        $goal_info = M('goal_goal')->where('id='.I('get.id'))->find();
        $goal_user = M('goal_user')->where('id='.$goal_info['user_id'])->find();
        $goal_info['user_name'] = $goal_user['name'];
        $goal_info['type'] = 0;
        $goal_info['status_name'] = $status_name[$goal_info['status']];
        $goal_info['true_day_number'] = $goal_info['s_time_true']?floor((strtotime($goal_info['s_time_true']) - strtotime($goal_info['b_time']))/86400):'';


        $goal_info['file'] = explode(',',$goal_info['file']);
        foreach($goal_info['file']  as $k=>$v){
            $type_goal = '';
            if($v){
                $type_goal = pathinfo($v)['extension'];
                if (in_array($type_goal, ['jpg', 'png', 'jpeg', 'gif'])){
                    $file[$k]['type'] = 1;
                    $file[$k]['url'] = $v;
                }else{
                    $file[$k]['type'] = 2;
                    $file[$k]['type_name'] = '.'.$type_goal;
                    $file[$k]['url'] = $v;
                }
            }
        }
        $goal_info['file'] = $file;


        if($goal_info['project_id']){
            $project = M('goal_project')->where('id='.$goal_info['project_id'])->find();
            $project_user = M('goal_user')->where('id='.$project['user_id'])->find();
            $project['user_name'] = $project_user['name'];
            $project['status_name'] = $status_name[$project['status']];
            $project['status_name'] = $status_name[$project['status']].($project['s_time_true']>date('Y-m-d H:i:s',strtotime($project['s_time'])+86400)?'(超时)':'');
            $project['goal'] = M('goal_goal')->where('project_id='.$project['id'])->select();
            $can_not_status_3 = 1;

            $project['true_day_number'] =$project['s_time_true']?floor((strtotime($project['s_time_true']) - strtotime($project['b_time']))/86400):'';
            foreach($project['goal'] as $k=>$v){
                $user_name_p = M('goal_user')->where('id='.$v['user_id'])->find();
                $project['goal'][$k]['user_name'] = $user_name_p['name'];
                $project['goal'][$k]['status_name'] = $status_name[$v['status']];
                $project['goal'][$k]['type'] = 0;

                $project['goal'][$k]['add_time'] = $v['add_time']?date('Y-m-d H:i:s',$v['add_time']):'';
                if($v['status']<3){
                    $can_not_status_3 = 0;
                }
                if($v['status']==3){
                    $project['goal'][$k]['status_name'] =  $status_name[$v['status']].($v['s_time_true']>date('Y-m-d H:i:s',strtotime($v['s_time'])+86400)?'(超时)':'');
                }
                $user_id[] = $v['user_id'];


                $files = explode(',',$v['file']);
                foreach($files  as $kl=>$vl){
                    if($vl){
                        $type_goal = pathinfo($vl)['extension'];
                        if (in_array($type_goal, ['jpg', 'png', 'jpeg', 'gif'])){
                            $project['goal'][$k]['files'][$kl]['type'] = 1;
                            $project['goal'][$k]['files'][$kl]['url'] = $vl;
                        }else{
                            $project['goal'][$k]['files'][$kl]['type'] = 2;
                            $project['goal'][$k]['files'][$kl]['type_name'] = '.'.$type_goal;
                            $project['goal'][$k]['files'][$kl]['url'] = $vl;
                        }
                    }
                }

            }

            if(in_array($_SESSION[C('GOAL_AUTH_KEY')]['id'],$user_id)){
                $user_project_goal_info = M('goal_goal')->where('user_id='.$_SESSION[C('GOAL_AUTH_KEY')]['id'].' and project_id='.$goal_info['project_id'])->find();
                $this->assign('i_have',1);
                $this->assign('user_project_goal_info',$user_project_goal_info);
            }
        }
//        print_r($project['goal']);die;



        $this->assign('can_not_status_3',$can_not_status_3);
        $this->assign('goal_info',$goal_info);
        $this->assign('project',$project);
        $this->display();
    }



    //完成目标
    public function up_goal_data(){
        $post = I('post.');
        $goal_goal = M('goal_goal')->where('id='.$post['id'])->find();
        $count_img = count($_FILES['file_goal']['name']);
        if($count_img<$goal_goal['file_number']){
            echo "<script>alert('附件数量太少，请少传不少于".$goal_goal['file_number']."个附件');history.go(-1);</script>";
            exit;
        }

        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize = 5048576;// 设置附件上传大小
//        $upload->exts = array('jpg', 'gif', 'png', 'jpeg','xls','doc','docx','xlsx','pptx','ppt');// 设置附件上传类型
        $upload->savePath = 'goal/'.date('Y-m-d').'/'; // 设置附件上传目录
        $upload->subName = ''; // 设置附件上传目录
        $info = $upload->upload();
        if (!$info) {// 上传错误提示错误信息
            echo "<script>alert('附件上传失败');history.go(-1);</script>";
            exit;
        }

        $post['file'] = '';
        foreach($info as $k=>$v){
            $post['file'] .= $v['savepath'].$v['savename'].',';

        }
        $post['file'] = substr($post['file'],0,-1);
        $post['s_time_true'] = date('Y-m-d H:i:s');
        $post['status'] = 2;

        $save = M('goal_goal')->where('id='.$post['id'])->save($post);
        if($save!==false){
            echo "<script>alert('上传成功');window.location.href='".__MODULE__."/Goal/goal_info/id/".$post['id']."';</script>";
            exit;
        }else{
            echo "<script>alert('上传失败');history.go(-1);</script>";
            exit;
        }

    }

    //审核通过
    public function edit_goal_status_3(){
        if(!I('get.id')){
            echo "<script>alert('地址异常');history.go(-1);</script>";
            exit;
        }
        $goal_info = M('goal_goal')->field('goal_user.pid,goal_user.name,goal_user.id')->join('left join goal_user ON goal_goal.user_id=goal_user.id')->where('goal_goal.id='.I('get.id'))->find();
        if($goal_info['pid']!=$_SESSION[C('GOAL_AUTH_KEY')]['id']){
            echo "<script>alert('你不是".$goal_info['name']."的直接上级，无法为Ta审核目标！');history.go(-1);</script>";
            exit;
        }
        $status['status'] = 3;
        $save_mod = M('goal_goal')->where('id='.I('get.id'))->save($status);
        if($save_mod!==false){
            echo "<script>alert('审核成功');window.location.href='".__MODULE__."/Goal/goal/id/".$goal_info['id']."';</script>";
            exit;
        }else{
            echo "<script>alert('审核失败');history.go(-1);</script>";
            exit;
        }

    }

    //

    //添加项目目标
    public function add_project(){

        $goal_user = M('goal_user')->where('permission!=1')->select();

        $this->assign('goal_user',$goal_user);
        $this->display();
    }




    //添加项目
    public function add_project_data(){
        $post = I('post.');

        $post['add_time'] = time();
        $post['mac'] = $this->GetMacAddr(PHP_OS);
        $post['status'] = 1;
        $post['user_id'] = $_SESSION[C('GOAL_AUTH_KEY')]['id'];
        $post['s_time'] = date('Y-m-d',strtotime($post['b_time'].'+'.$post['day_number'].' days'));

        $project_id = M('goal_project')->add($post);
        if(!$project_id){
            echo "<script>alert('添加项目失败!');history.go(-1);</script>";
            exit;
        }

        $count_users_id = count($post['users_id']);
        for($i=0;$i<$count_users_id;$i++){
            $save[$i]['user_id'] = $post['users_id'][$i];
            $save[$i]['data'] = $post['detail'][$i];
            $save[$i]['name'] = $post['name'];
            $save[$i]['project_id'] = $project_id;
            $save[$i]['add_time'] = $post['add_time'];
            $save[$i]['b_time'] = $post['b_time'];
            $save[$i]['s_time'] = $post['s_time'];
            $save[$i]['day_number'] = $post['day_number'];
            $save[$i]['mac'] = $post['mac'];
            $save[$i]['status'] = 1;
//            $save[$i]['file_type'] = $post['file_type_1_'.$post['users_id'][$i].''];
            $save[$i]['file_number'] = $post['file_number'][$i];
            for($j=1;$j<=8;$j++){
                $save[$i]['file_type'] .= $post['file_type_'.$j.'_'.$post['users_id'][$i].''];
            }
            $save[$i]['file_type'] = substr($save[$i]['file_type'],0,-1);
        }
        $add_all = M('goal_goal')->addAll($save);
        if(!$add_all){
            M('goal_project')->where('id='.$project_id)->delete();
            echo "<script>alert('添加项目失败');history.go(-1);</script>";
            exit;
        }
        for($i=0;$i<$count_users_id;$i++) {

            $goal_user = M('goal_user')->where('id=' . $post['users_id'][$i])->find();
            $name = '目标管理系统';
            $title = '新项目已建立-' . $post['name'];
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
						' . $goal_user['name'] . ',<br/><br/>
						你的项目已建立，请登录<a href="http://www.tianluoayi.com/goal.php/Admin/login">田螺阿姨项目管理系统</a>查看自己职责:<br/><br/>
						项目名称：' . $post['name'] . '<br/>
						项目发起者：' . $_SESSION[C('GOAL_AUTH_KEY')]['name'] . '<br/>
						开始时间：' . $post['b_time'] . '<br/>
						计划完成时间：' . $post['s_time'] . '<br/>
						<br/><br/>
						如有疑问请反馈开发部！
					</div>';
            $no_html =  $goal_user['name'] . ',<br/><br/>
						你的项目已建立，请登录<a href="http://www.tianluoayi.com/goal.php/Admin/login">田螺阿姨项目管理系统</a>查看自己职责:<br/><br/>
						项目名称：' . $post['name'] . '<br/>
						项目发起者：' . $_SESSION[C('GOAL_AUTH_KEY')]['name'] . '<br/>
						开始时间：' . $post['b_time'] . '<br/>
						计划完成时间：' . $post['s_time'] . '<br/>
						<br/><br/>
						如有疑问请反馈开发部！';
            $this->send_email($goal_user['email'], $name, $title, $content, $no_html);//收件人、发件人姓名、标题、内容


        }


//print_r($save);die;
        echo "<script>alert('添加项目成功');window.location.href='".__MODULE__."/Goal/project/id/".$post['user_id']."';</script>";
        exit;

    }





    //修改项目目标
    public function edit_project(){
        if(I('get.id')){
            $goal_project = M('goal_project')->where('id='.I('get.id'))->find();
//            if($goal_project['user_id']!=$_SESSION[C('GOAL_AUTH_KEY')]['id']){
//                echo "<script>alert('你不是项目发起者，不能修改');history.go(-1);</script>";
//                exit;
//            }
            $goal_project['goal'] = M('goal_goal')->where('project_id='.$goal_project['id'])->select();
            foreach( $goal_project['goal'] as $k=>$v){
                $user_name = M('goal_user')->where('id='.$v['user_id'])->find();
                $goal_project['goal'][$k]['user_name'] = $user_name['name'];
                $users_id[] = $v['user_id'];
                $goal_project['goal'][$k]['file_type_1'] = (strpos($v['file_type'],'图片')!==false?1:0);
                $goal_project['goal'][$k]['file_type_2'] = (strpos($v['file_type'],'WORD')!==false?1:0);
                $goal_project['goal'][$k]['file_type_3'] = (strpos($v['file_type'],'EXCEL')!==false?1:0);
                $goal_project['goal'][$k]['file_type_4'] = (strpos($v['file_type'],'PPT')!==false?1:0);
                $goal_project['goal'][$k]['file_type_5'] = (strpos($v['file_type'],'PDF')!==false?1:0);
                $goal_project['goal'][$k]['file_type_6'] = (strpos($v['file_type'],'VISIO')!==false?1:0);
                $goal_project['goal'][$k]['file_type_7'] = (strpos($v['file_type'],'MindManager')!==false?1:0);
                $goal_project['goal'][$k]['file_type_8'] = (strpos($v['file_type'],'其他')!==false?1:0);

            }

            $goal_user = M('goal_user')->where('permission!=1')->select();

            $this->assign('users_id',$users_id);
            $this->assign('goal_project',$goal_project);
            $this->assign('goal_user',$goal_user);
            $this->display();
        }else{
            echo "<script>alert('地址异常');history.go(-1);</script>";
            exit;
        }



    }

    //修改项目
    public function edit_project_data(){
        $post = I('post.');
        $post['s_time'] = date('Y-m-d',strtotime($post['b_time'].'+'.$post['day_number'].' days'));

        $project_id = M('goal_project')->where('id='.$post['id'])->save($post);
        if($project_id===false){
            echo "<script>alert('修改项目失败');history.go(-1);</script>";
            exit;
        }

        $count_users_id = count($post['users_id_edit']);
        if($count_users_id) {
            for ($i = 0; $i < $count_users_id; $i++) {
                $save['user_id'] = $post['users_id_edit'][$i];
                $save['data'] = $post['detail_edit'][$i];
                $save['name'] = $post['name'];
                $save['project_id'] = $post['id'];
                $save['b_time'] = $post['b_time'];
                $save['s_time'] = $post['s_time'];
                $save['day_number'] = $post['day_number'];
                $save['file_number'] = $post['file_number'][$i];
                for ($j = 1; $j <= 8; $j++) {
                    $save['file_type'] .= $post['file_type_' . $j . '_' . $post['users_id_edit'][$i] . ''];
                }
                $save['file_type'] = substr($save['file_type'], 0, -1);
                M('goal_goal')->where('user_id='.$save['user_id'].' and project_id='.$post['id'])->save($save);
            }
        }


        $post['mac'] = $this->GetMacAddr(PHP_OS);
        $count_users_id = count($post['users_id']);
//        echo $count_users_id;die;
        if($count_users_id) {
            for ($i = 0; $i < $count_users_id; $i++) {
                $add[$i]['user_id'] = $post['users_id'][$i];
                $add[$i]['data'] = $post['detail'][$i];
                $add[$i]['name'] = $post['name'];
                $add[$i]['project_id'] = $post['id'];
                $add[$i]['add_time'] = time();
                $add[$i]['b_time'] = $post['b_time'];
                $add[$i]['s_time'] = $post['s_time'];
                $add[$i]['day_number'] = $post['day_number'];
                $add[$i]['mac'] = $post['mac'];
                $add[$i]['status'] = 1;
                $add[$i]['file_number'] = $post['file_number'][$i];
                for($j=1;$j<=8;$j++){
                    $add[$i]['file_type'] .= $post['file_type_'.$j.'_'.$post['users_id'][$i].''];
                }
                $add[$i]['file_type'] = substr($add[$i]['file_type'],0,-1);
            }

//            print_r($save);die;
            $add_all = M('goal_goal')->addAll($add);
//            die;
            if ($add_all === false) {
                M('goal_project')->where('id=' . $project_id)->delete();
                echo "<script>alert('修改项目失败！');history.go(-1);</script>";
                exit;
            }
        }
        if($post['del_id']){
            $delete = M('goal_goal')->delete($post['del_id']);
            if(!$delete){
                M('goal_goal')->delete($post['del_id']);
            }
        }

//print_r($save);die;
        echo "<script>alert('修改项目成功');window.location.href='".__MODULE__."/Goal/project_info/id/".$post['id']."';</script>";
        exit;


    }


//项目详情
    public function project_info(){
        if(!I('get.id')){
            echo "<script>alert('地址异常');history.go(-1);</script>";
            exit;
        }
        $status_name = ['未通过','进行中','已完成(未审核)','已完成'];
        $project = M('goal_project')->where('id='.I('get.id'))->find();
        $project_user = M('goal_user')->where('id='.$project['user_id'])->find();
        $project['user_name'] = $project_user['name'];

        $project['status_name'] = $status_name[$project['status']].($project['s_time_true']>date('Y-m-d H:i:s',strtotime($project['s_time'])+86400)?'(超时)':'');

        $project['goal'] = M('goal_goal')->where('project_id='.$project['id'])->select();
        $can_not_status_3 = 1;
        foreach($project['goal'] as $k=>$v){
            $user_name_p = M('goal_user')->where('id='.$v['user_id'])->find();
            $project['goal'][$k]['user_name'] = $user_name_p['name'];

            $project['goal'][$k]['status_name'] = $status_name[$v['status']];
            $project['goal'][$k]['type'] = 0;
            $files = explode(',',$v['file']);
            foreach($files  as $kl=>$vl){
                if($vl){
                    $type_goal = pathinfo($vl)['extension'];
                    if (in_array($type_goal, ['jpg', 'png', 'jpeg', 'gif'])){
                        $project['goal'][$k]['files'][$kl]['type'] = 1;
                        $project['goal'][$k]['files'][$kl]['url'] = $vl;
                    }else{
                        $project['goal'][$k]['files'][$kl]['type'] = 2;
                        $project['goal'][$k]['files'][$kl]['type_name'] = '.'.$type_goal;
                        $project['goal'][$k]['files'][$kl]['url'] = $vl;
                    }
                }
            }
            $project['goal'][$k]['add_time'] = $v['add_time']?date('Y-m-d H:i:s',$v['add_time']):'';
            if($v['status']<3){
                $can_not_status_3 = 0;
            }
            if($v['status']==3){
                $project['goal'][$k]['status_name'] =  $status_name[$v['status']].($v['s_time_true']>date('Y-m-d H:i:s',strtotime($v['s_time'])+86400)?'(超时)':'');
            }
            $user_id[] = $v['user_id'];
        }


        if(in_array($_SESSION[C('GOAL_AUTH_KEY')]['id'],$user_id)){
            $user_project_goal_info = M('goal_goal')->where('user_id='.$_SESSION[C('GOAL_AUTH_KEY')]['id'].' and project_id='.I('get.id'))->find();
            $file_goal = explode(',',$user_project_goal_info['file']);
            foreach($file_goal  as $kl=>$vl){
                $type_goal = '';
                if($vl){
                    $type_goal = pathinfo($vl)['extension'];
                    if (in_array($type_goal, ['jpg', 'png', 'jpeg', 'gif'])){
                        $user_project_goal_info['files'][$kl]['type'] = 1;
                        $user_project_goal_info['files'][$kl]['url'] = $vl;
                    }else{
                        $user_project_goal_info['files'][$kl]['type'] = 2;
                        $user_project_goal_info['files'][$kl]['type_name'] = '.'.$type_goal;
                        $user_project_goal_info['files'][$kl]['url'] = $vl;
                    }
                }
            }
            $this->assign('i_have',1);
            $this->assign('user_project_goal_info',$user_project_goal_info);
        }
        $this->assign('can_not_status_3',$can_not_status_3);
        $this->assign('project',$project);
        $this->display();
    }


    //完成项目
    public function up_project_data(){
        $post = I('post.');
        $goal_goal = M('goal_goal')->where('id='.$post['id'])->find();
        $count_img = count($_FILES['file_goal']['name']);
        if($count_img<$goal_goal['file_number']){
            echo "<script>alert('附件数量太少，请少传不少于".$goal_goal['file_number']."个附件');history.go(-1);</script>";
            exit;
        }

        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize = 5048576;// 设置附件上传大小
//        $upload->exts = array('jpg', 'gif', 'png', 'jpeg','xls','doc','docx','xlsx','pptx','ppt');// 设置附件上传类型
        $upload->savePath = 'goal/'.date('Y-m-d').'/'; // 设置附件上传目录
        $upload->subName = ''; // 设置附件上传目录
        $info = $upload->upload();
        if (!$info) {// 上传错误提示错误信息
            echo "<script>alert('附件上传失败');history.go(-1);</script>";
            exit;
        }

        $post['file'] = '';
        foreach($info as $k=>$v){
            $post['file'] .= $v['savepath'].$v['savename'].',';
        }
        $post['file'] = substr($post['file'],0,-1);
        $post['s_time_true'] = date('Y-m-d H:i:s');
        $post['status'] = 2;


        $save = M('goal_goal')->where('id='.$post['id'])->save($post);
        if($save!==false){
            echo "<script>alert('上传成功');window.location.href='".__MODULE__."/Goal/goal_info/id/".$post['id']."';</script>";
            exit;
        }else{
            echo "<script>alert('上传失败');history.go(-1);</script>";
            exit;
        }

    }


    //审核项目通过
    public function edit_project_status_3(){
        if(!I('get.id')){
            echo "<script>alert('地址异常');history.go(-1);</script>";
            exit;
        }
        $goal_info = M('goal_goal')->field('goal_project.user_id')->join('left join goal_project ON goal_project.id = goal_goal.project_id')->where('goal_goal.id='.I('get.id'))->find();
        if($goal_info['user_id']!=$_SESSION[C('GOAL_AUTH_KEY')]['id']){
            echo "<script>alert('你不是该项目发起者，不能审核！');history.go(-1);</script>";
            exit;
        }
        $status['status'] = 3;
        $save_mod = M('goal_goal')->where('id='.I('get.id'))->save($status);
        if($save_mod!==false){
            echo "<script>alert('审核成功');window.location.href='".__MODULE__."/Goal/goal_info/id/".I('get.id')."';</script>";
            exit;
        }else{
            echo "<script>alert('审核失败');history.go(-1);</script>";
            exit;
        }

    }


    //项目完成
    public function edit_project_complete(){
        if(!I('get.id')){
            echo "<script>alert('地址异常');history.go(-1);</script>";
            exit;
        }
        $project_info = M('goal_project')->where('id='.I('get.id'))->find();
        if($project_info['user_id']!=$_SESSION[C('GOAL_AUTH_KEY')]['id']){
            echo "<script>alert('你不是项目的发起者，不能完结项目！');history.go(-1);</script>";
            exit;
        }
        $is_complete = M('goal_goal')->where('status<3 and project_id='.I('get.id'))->find();
        if($is_complete){
            echo "<script>alert('还有成员未完成项目，不能完结项目！');history.go(-1);</script>";
            exit;
        }
        $status['status'] = 3;
        $status['s_time_true'] = date('Y-m-d H:i:s');
        $save_mod = M('goal_project')->where('id='.I('get.id'))->save($status);
        if($save_mod!==false){
            echo "<script>alert('完结成功');window.location.href='".__MODULE__."/Goal/project_info/id/".I('get.id')."';</script>";
            exit;
        }else{
            echo "<script>alert('完结失败');history.go(-1);</script>";
            exit;
        }
    }


    public function goal(){
        if(I('get.id')){
            $user_info = M('goal_user')->where('id='.I('get.id'))->find();
        }
//        else{
//            $user_info = M('goal_user')->where('id='.$_SESSION[C('GOAL_AUTH_KEY')]['id'])->find();
//        }
        $this->assign('user_info',$user_info);
        $this->display();
    }

    public function getGoal(){
        $post = I("post.");
        $currentpage = I("post.currentpage");
        $pagenum = I("post.pagenum");
        $start = ($currentpage - 1) * $pagenum;
        $where = '';
        if($post['user_id']>0){
            $where .= 'user_id='.$post['user_id'].'';
        }

        $list = M('goal_goal')->where($where)->order('status asc,s_time asc')->limit($start,$pagenum)->select();

        $count = M('goal_goal')->where($where)->count();

        $status = ['上级拒绝','进行中','已完成（待审核）','已完成'];
        foreach($list as $k=>$v){
            $list[$k]['type'] = 0;
            if($v['file']) {
                $type = pathinfo($v['file'])['extension'];
                if (in_array($type, ['jpg', 'png', 'jpeg', 'gif'])) {
                    $list[$k]['type'] = 1;
                }else{
                    $list[$k]['type'] = 2;
                    $list[$k]['type_name'] = '.'.$type;
                }
            }
            $user_info = M('goal_user')->field('name,pid')->where('id='.$v['user_id'])->find();
            $list[$k]['user_name'] = $user_info['name'];
            if($v['project_id']==0){
                $list[$k]['project_name'] = '无';
            }else{
                $project_name = M('goal_project')->field('name')->where('id='.$v['project_id'])->find();
                $list[$k]['project_name'] = $project_name['name'];
            }
            if($v['status']==3){
                $list[$k]['s_time_true'] = $v['s_time_true'];

                $list[$k]['time_true'] = $v['s_time_true']>date('Y-m-d H:i:s',strtotime($v['s_time'])+86400)?1:0;
            }else{
                $list[$k]['s_time_true'] = '';
            }
            $list[$k]['status_name'] = $status[$v['status']];
            $list[$k]['is_boss'] = $user_info['pid']==0?($_SESSION[C('GOAL_AUTH_KEY')]['permission']==1?1:0):$user_info['pid'] == $_SESSION[C('GOAL_AUTH_KEY')]['id']?1:0;


        }
        $back['code'] = 1000;
        $back['data']['list'] = $list;
        $back['data']['num'] =$count;
        echo json_encode($back);
    }

    public function project(){
        if(I('get.id')){
            $user_info = M('goal_user')->where('id='.I('get.id'))->find();
        }
//        else{
//            $user_info = M('goal_user')->where('id='.$_SESSION[C('GOAL_AUTH_KEY')]['id'])->find();
//        }
        $this->assign('user_info',$user_info);
        $this->display();
    }
    public function getProject(){
        $post = I("post.");
        $currentpage = I("post.currentpage");
        $pagenum = I("post.pagenum");
        $start = ($currentpage - 1) * $pagenum;
        $where = '';
        if($post['user_id']>0){
            $where .= 'user_id='.$post['user_id'].'';
        }
        $list = M('goal_project')->where($where)->order('status asc,s_time asc')->limit($start,$pagenum)->select();

        $count = M('goal_project')->where($where)->count();

        $status = ['上级拒绝','进行中','已完成（待审核）','已完成'];
        foreach($list as $k=>$v){
            $user_info = M('goal_user')->field('name')->where('id='.$v['user_id'])->find();
            $list[$k]['user_name'] = $user_info['name'];
            $list[$k]['status_name'] = $status[$v['status']];
            $list[$k]['s_time_true'] = $v['s_time_true']?$v['s_time_true']:'';
            if($v['status']==3){
                $list[$k]['time_true'] = $v['s_time_true']>date('Y-m-d H:i:s',strtotime($v['s_time'])+86400)?1:0;
            }
            if($v['user_id']==$_SESSION[C('GOAL_AUTH_KEY')]['id']){
                $list[$k]['is_boss'] = 1;
            }

        }
        $back['code'] = 1000;
        $back['data']['list'] = $list;
        $back['data']['num'] =$count;
        echo json_encode($back);
    }


}