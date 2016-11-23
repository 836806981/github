<?php
namespace NAdmin\Controller;
use Think\Controller;
class DestineController extends Controller {
    public function _initialize(){
        if(!$_SESSION[C('ADMIN_AUTH_KEY')]['id']){
            echo "<script>alert('请登录！');window.location.href='" . __MODULE__ . "/Admin/login.html';</script>";
            exit;
        }else{
            $admin_user = M('permission')->where('employee_id='. $_SESSION[C('ADMIN_AUTH_KEY')]['id'].'')->find();
            if($admin_user['destine']!=1){
                echo "<script>alert('你没有权限！');window.location.href='" . __MODULE__ . "/Admin/info.html';</script>";
                exit;
            }
        }
    }
    //留言列表页
    public function index(){
        $message = D('Destine');
        $count=$message->where('status!=10')->count();
        $Page       = new \Think\Page($count,20);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show       = $Page->showFront();// 分页显示输出// 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $message_list = $message->where('status!=10')->order('status asc,add_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        foreach($message_list as $k=>$v){
            $nurse = M('nurse')->field('name,title_img')->where('id='.$v['nurse_id'].'')->find();
            $message_list[$k]['nurse_name']=$nurse['name'];
            $message_list[$k]['title_img']=$nurse['title_img'];
            $message_list[$k]['add_time']=date('Y-m-d H:i:s',$v['add_time']);
            $message_list[$k]['status']=$v['status']==1?'<span style="color: #00b7ee;">已处理</span>':'<span style="color: red;;">未处理</span>';
        }
        $this->assign('message_list',$message_list);
        $this->assign('page',$show);
        $this->display('Destine/list');
    }


    //留言查看页面
    public function editDestine(){


        if(is_numeric(I('get.id'))){
            $message = M('destine')->where('id='.I('get.id').'')->find();
            if($message&&$message!=''){
                $message['add_time'] =   $message['add_time']?date('Y-m-d H:i:s',  $message['add_time']):'--';
                $message['last_modified'] =   $message['last_modified']?date('Y-m-d H:i:s',  $message['last_modified']):'';

                $nurse = M('nurse')->field('name,title_img')->where('id='.$message['nurse_id'].'')->find();

                $message['nurse_name']=$nurse['name'];
                $message['title_img']=$nurse['title_img'];

                $this->assign('destine',$message);
                $this->display();
            }else{
                echo "<script>alert('地址异常');history.back();</script>";
            }

        }else{
            echo "<script>alert('地址异常');history.back();</script>";
        }
    }

    // 删除留言、逻辑删除


    public function deleteDestine(){
        $admin_user = M('permission')->where('employee_id='. $_SESSION[C('ADMIN_AUTH_KEY')]['id'].'')->find();
        if($admin_user['del_per']!=1){
            echo "<script>alert('你没有权限这么做');history.back();</script>";
        }else{
            $map['status']=10;
            $delete = M('destine')->where('id='.I('get.id').'')->save($map);
            if($delete!==false){
                if(I('get.p')){
                    echo "<script>alert('删除成功');window.location.href='".__MODULE__."/Destine/index/p/".I('get.p').".html';</script>";
                }else{
                    echo "<script>alert('删除成功');window.location.href='".__MODULE__."/Destine/index/p/1.html';</script>";
                }
            }else{
                echo "<script>alert('删除失败');history.back();</script>";
            }
        }
    }

    //修改留言状态
    public function setStatus(){
        $message = D("Destine");
        $where['id']=I("get.id");
        $map['status']=I("get.st");
        $map['last_modified']=time();
        $save_mod = $message->save_mod($where,$map);
        if($save_mod!==false){
            echo "<script>alert('设置成功');window.location.href='".__MODULE__."/Destine/editDestine/id/".I('get.id').".html';</script>";
        }else{
            echo "<script>alert('设置失败');window.location.href='".__MODULE__."/Destine/editDestine/id/".I('get.id').".html';</script>";
        }
    }

    //修改备注
    public function remark(){
        $message = D("Destine");
        $where['id']=I("post.id");
        $map['remark']=I("post.remark");

        $save_mod = $message->save_mod($where,$map);
        if($save_mod!==false){
            echo "<script>alert('修改成功');window.location.href='".__MODULE__."/Destine/editDestine/id/".I('post.id').".html';</script>";
        }else{
            echo "<script>alert('修改失败');window.location.href='".__MODULE__."/Destine/editDestine/id/".I('post.id').".html';</script>";
        }



    }


    //招聘留言接收邮箱
    public function email(){
        $email = D('Email');
        $count=$email->where('state=2')->count();
        $Page       = new \Think\Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show       = $Page->showFront();// 分页显示输出// 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $email_list = $email->where('state=2')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('email_list',$email_list);
        $this->assign('page',$show);
        $this->display();
    }

    public function addEmail(){
        $this->display();
    }
    public function addEmail_p(){
        $post = I("post.");
        $email = D("Email");
        $post['state']=2;
        $add_mod = $email->add_mod($post);
        if($add_mod){
            echo "<script>alert('添加成功');window.location.href='".__MODULE__."/Destine/email.html';</script>";
        }else{
            echo "<script>alert('添加失败');window.location.href='".__MODULE__."/Destine/email.html';</script>";
        }
    }

    public function deleteEmail(){
        $admin_user = M('permission')->where('employee_id='. $_SESSION[C('ADMIN_AUTH_KEY')]['id'].'')->find();
        if($admin_user['del_per']!=1){
            echo "<script>alert('你没有权限这么做');history.back();</script>";
        }else{
            $where['id']=I("get.id");
            $delete = D('Email')->delete_mod($where);
            if($delete!==false){
                if(I('get.p')){
                    echo "<script>alert('删除成功');window.location.href='".__MODULE__."/Destine/email/p/".I('get.p').".html';</script>";
                }else{
                    echo "<script>alert('删除成功');window.location.href='".__MODULE__."/Destine/email/p/1.html';</script>";
                }
            }else{
                echo "<script>alert('删除失败');history.back();</script>";
            }
        }
    }

    public function editEmail(){
        $email  = M("email_receiver")->where('id='.I("get.id").'')->find();

        $this->assign('email',$email);
        $this->display();
    }
    public function editEmail_p(){
        $post = I("post.");
        $email = D("Email");
        $where['id']=$post['id'];
        $map['name'] = $post['name'];
        $map['email'] = $post['email'];
        $add_mod = $email->save_mod($where,$map);
        if($add_mod!==false){
            if(I('get.p')){
                echo "<script>alert('修改成功');window.location.href='".__MODULE__."/Destine/email/p/".I('get.p').".html';</script>";
            }else{
                echo "<script>alert('修改成功');window.location.href='".__MODULE__."/Destine/email/p/1.html';</script>";
            }
        }else{
            echo "<script>alert('删除失败');history.back();</script>";
        }
    }



}