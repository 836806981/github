<?php
namespace Trainee\Controller;
use Think\Controller;
class TraineeController extends CommonController {
    public function  _initialize(){
        if(!$_SESSION[C('TRAINEE_AUTH_KEY')]['id']){
            echo "<script>alert('请登录！');window.location.href='" . __MODULE__ . "/Admin/login.html';</script>";
            exit;
        }
    }

    //添加月嫂基本信息页面以及添加功能
    public function addTrainee(){

        if(!($_SESSION[C('TRAINEE_AUTH_KEY')]['permission']==1||$_SESSION[C('TRAINEE_AUTH_KEY')]['add_per']==1)){
            echo "<script>alert('你没有权限！');window.location.href='" . __MODULE__ . "/Admin/login.html';</script>";
        }
        if(I("post.")){
            $post = I("post.");
            $map['source'] = $post['source'];
            $map['source_name'] = $post['source']=='客服'?'':$post['source_name'];
            $map['name'] = $post['name'];
            $map['age'] = $post['age'];
            $map['phone'] = $post['phone'];
            $map['status'] = $post['status'];
            $map['cover'] = '';
            $post['status']==4 && $map['cover'] = $post['cover'];
            $map['employee_name'] = $post['employee_name'];
            $map['detail'] = $post['detail'];
            $map['source'] = $post['source'];
            $map['next_time'] = $post['next_time'];
            $map['priority'] = $post['priority'];
            if($post['trainee_id']){
                $trainee_name = M('tra_trainee')->where('(name = "'.$post['name'].'" OR phone= "'.$post['phone'].'" ) and id!='.$post['trainee_id'].'')->find();
                if($trainee_name){
                    echo "<script>alert('已存在该姓名或电话号码的学员！');history.back();</script>";
                    exit;
                }
                M('tra_trainee')->where('id='.$post['trainee_id'].'')->save($map);
                $trainee_id = $post['trainee_id'];
            }else{
                $map['add_time'] = time();
                $trainee_name = M('tra_trainee')->where('name = "'.$post['name'].'" OR phone= "'.$post['phone'].'" ')->find();
                if($trainee_name){
                    echo "<script>alert('已存在该姓名或电话号码的学员！');history.back();</script>";
                    exit;
                }
                $trainee_id = M('tra_trainee')->add($map);
            }
            if($trainee_id){
                if($post['next_time']||$post['remark']){
                    $record['employee_name'] = $post['employee_name'];
                    $record['trainee_id'] = $trainee_id;
                    $record['add_time'] = time();
                    $record['next_time'] = $post['next_time'];
                    $record['remark'] = $post['remark'];
                    $record_id = M('tra_record')->add($record);
                }else{
                    $record_id=1;
                }
                if($record_id){
                    echo "<script>alert('上传成功！');window.location.href='" . __MODULE__ . "/Trainee/tlist/type/".$post['status'].".html';</script>";
                }else{
                    echo "<script>alert('上传失败！');window.location.href='" . __MODULE__ . "/Trainee/addTrainee.html';</script>";
                }
            }else{
                echo "<script>alert('上传失败！');window.location.href='" . __MODULE__ . "/Trainee/addTrainee.html';</script>";
            }
        }else{
            if($_GET['id']){
                $trainee = M('tra_trainee')->where('id='.$_GET['id'].'')->find();
                $this->assign('trainee_info',$trainee);
            }


            $this->display();
        }
    }

    public function editTrainee(){
        if(!($_SESSION[C('TRAINEE_AUTH_KEY')]['permission']==1||$_SESSION[C('TRAINEE_AUTH_KEY')]['edit_per']==1)){
            echo "<script>alert('你没有权限！');window.location.href='" . __MODULE__ . "/Admin/login.html';</script>";
        }
        if(I("post.")){
            $post = I("post.");
            $map['source'] = $post['source'];
            $map['source_name'] = $post['source']=='客服'?'':$post['source_name'];
            $map['name'] = $post['name'];
            $map['age'] = $post['age'];
            $map['phone'] = $post['phone'];
            $map['status'] = $post['status'];
            $map['cover'] = '';
            $post['status']==4 && $map['cover'] = $post['cover'];
            $map['detail'] = $post['detail'];
            $map['source'] = $post['source'];
            $map['priority'] = $post['priority'];
            $trainee_name = M('tra_trainee')->where('(name = "'.$post['name'].'" OR phone= "'.$post['phone'].'" ) and id!='.$post['id'].' ')->find();
            if($trainee_name){
                echo "<script>alert('已存在该姓名或电话号码的学员！');history.back();</script>";
                exit;
            }
            $where['id'] = $post['id'];

           $save = M('tra_trainee')->where($where)->save($map);
            if($save!==false){
                echo "<script>alert('保存成功！');window.location.href='" . __MODULE__ . "/Trainee/info/id/".$post['id']."';</script>";
            }else{
                echo "<script>alert('保存失败！');history.back();</script>";
            }

        }else{
            $trainee_info = M('tra_trainee')->where('id='.I('get.id').'')->find();
            $this->assign('trainee_info',$trainee_info);
            $this->display();
        }
    }
    public function tList(){
        $week = date('Y-m-d', strtotime("+1 week"));
        $where['next_time'] = array('between',"1,$week" );
        $number[0] = M('tra_trainee')->where($where)->count();
        $number[1] = M('tra_trainee')->where('status=1')->count();
        $number[2] = M('tra_trainee')->where('status=2')->count();
        $number[3] = M('tra_trainee')->where('status=3')->count();
        $number[4] = M('tra_trainee')->where('status=4')->count();
        $number[5] = M('tra_trainee')->where('status=5')->count();
        $number[7] = M('tra_trainee')->where('status=0 and employee_name!="不分配"')->count();
        foreach($number as $k=>$v){
            if($v!=0){
                $length = strlen($v)-2;
                $length>0&&$px = 'style="width:'.(20+$length*6).'px"';
                $number[$k]  =  '<i class="icon_number" '.$px.'>'.$v.'</i>';
            }else{
                $number[$k] =  '';
            }
        }


        $this->assign('number',$number);
        $this->display('list');
    }

    public function getTraineeList(){
        $post = I("post.");
        $currentpage = I("post.currentpage");
        $pagenum = I("post.pagenum");
        $start = ($currentpage - 1) * $pagenum;
        $post['type'] || $post['type']=6;

        if($post['keyword']&&$post['keyword']!=''){
            $where = 'name LIKE "%'.$post['keyword'].'%" OR phone LIKE "%'.$post['keyword'].'%"';
            $nurse = M('tra_trainee')->where($where)->limit($start, $pagenum)->select();
            $count = M('tra_trainee')->where($where)->count();
        }else {
            $where = '1';
            if(in_array($post['type'],['1','2','6'])){
                if($post['priority']!=0){
                    $where .= ' and priority = '.$post['priority'];
                }
            }
            if ($post['type'] == 6) {
                $week = date('Y-m-d', strtotime("+1 week"));
                $where .= ' and next_time >=1 and next_time<= "'.$week.'"';
                $order = 'next_time asc';
            } else {
                if($post['type']==7){
                    $where .= ' and status = 0';

                }else{
                    $where .= ' and status = '.$post['type'];
                }

                $order = 'add_time desc';
            }
            if($post['employee_name']!= '0'){
                $where .= ' and employee_name = "'.$post['employee_name'].'"';
            }
            $nurse = M('tra_trainee')->where($where.' and employee_name!="不分配"')->order($order)->limit($start, $pagenum)->select();
            $count = M('tra_trainee')->where($where.' and employee_name!="不分配"')->count();
        }
        foreach($nurse as $k=>$v){
            if($v['next_time']&&$v['next_time']!=''){
                $remark = M('tra_record')->where('trainee_id='.$v['id'].'')->order('add_time desc')->find();
            }
            $nurse[$k]['remark'] = $remark['remark']?$remark['remark']:'';
            $nurse[$k]['record_employee_name'] = $remark['employee_name']?$remark['employee_name']:'';
            if(M('tra_training')->field('id')->where('trainee_id='.$v['id'].'')->find()){
                $nurse[$k]['cover_get'] = '(已接收)';
            }else{
                $nurse[$k]['cover_get'] = '(未接收)';
            }


        }

        $back['code'] = 1000;
        $back['data']['list'] =$nurse;
        $back['data']['num'] =$count;
        $back['data']['status'] =$post['type'];
        echo json_encode($back);
    }

    public function info(){

        $info = M('tra_trainee')->where('id='.I('get.id').'')->find();
        if(!$info||$info==''){
            $info = M('tra_trainee')->find();
        }
        $status_arr = ['','新意向学员','跟进中学员','已缴费学员','已报到学员','已放弃学员'];
        $priority_arr = ['','非常有意向','比较有意向','一般','没有意向'];
        $info['status'] = $status_arr[$info['status']];
        $info['priority'] = $priority_arr[$info['priority']];

        $info['source_name'] = $info['source_name']?'('.$info['source_name'].')':'';

        $record = M('tra_record')->where('trainee_id = '.$info['id'].'')->order('add_time desc')->select();

        $this->assign('info',$info);
        $this->assign('record',$record);
        $this->display();
    }


    public function addRecord(){

        $post = I("post.");
        $post['add_time'] = time();
        $have = M('tra_record')->where('remark="'.$post['remark'].'" and next_time="'.$post['next_time'].'" and trainee_id='.$post['trainee_id'].'')->find();
        if($have){
            $back['code'] = 1001;
            echo json_encode($back);
            die;
        }
        $record = M('tra_record')->add($post);
        if($record){
            $next_time['next_time'] = $post['next_time'];
            $where['id'] = $post['trainee_id'];
            $save = M('tra_trainee')->where($where)->save($next_time);
            if($save!==false){
                $back['code'] = 1000;
                echo json_encode($back);
                die;
            }else{
                M('tra_recode')->where('id='.$record.'')->delete();
                $back['code'] = 1001;
                echo json_encode($back);
                die;
            }

        }else{
            $back['code'] = 1002;
            echo json_encode($back);
            die;
        }


    }


}