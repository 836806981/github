<?php
namespace NAdmin\Controller;
use Think\Controller;
class PayController extends Controller {
    public function _initialize(){
        if(!$_SESSION[C('N_ADMIN_AUTH_KEY')]['id']){
            echo "<script>alert('请登录！');window.location.href='" . __MODULE__ . "/Admin/login.html';</script>";
            exit;
        }else{
            $admin_user = M('n_permission')->where('employee_id='. $_SESSION[C('N_ADMIN_AUTH_KEY')]['id'].'')->find();
            if($admin_user['pay']!=1){
                echo "<script>alert('你没有权限！');window.location.href='" . __MODULE__ . "/Admin/info.html';</script>";
                exit;
            }
        }
    }


    //支付 列表页
    public function index(){
        $where = '1';
        if($_GET['k']){
            addslashes($_GET['k']);
            $where = 'phone LIKE "%'.$_GET['k'].'%"  OR buyer_email LIKE "%'.$_GET['k'].'%"  OR buy_name LIKE "%'.$_GET['k'].'%" ';
        }
        $count = M('n_alipay_info')->where($where)->count();
        $Page = new \Think\Page($count,20);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show = $Page->showFront();// 分页显示输出// 进行分页数据查询 注意limit方法的参数要使用Page类的属性

        $alipay_list = M('n_alipay_info')->where($where)->order('trade_status_number desc,order_status asc,add_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();

        $success_stauts = ['TRADE_FINISHED','TRADE_SUCCESS'];
        foreach($alipay_list as $k=>$v){
            $alipay_list[$k]['add_time'] = date('Y-m-d H:i:s',$v['add_time']);
            $alipay_list[$k]['order_status_name'] = $v['order_status']==1?'<span style="color: #0000cc">已处理</span>':'<span style="color: red">未处理</span>';
            $alipay_list[$k]['trade_status_name'] = in_array($v['trade_status'],$success_stauts)?'<span style="color: #0000cc">已付款</span>':'<span style="color: red">未付款</span>';
        }
        $this->assign('alipay_list',$alipay_list);


        $trade_status_number = M('n_alipay_info')->where('trade_status_number=1')->count('distinct phone');
        $sub_total_fee = M('n_alipay_info')->where('trade_status_number=1')->sum('total_fee_real');

        $this->assign('trade_status_number',$trade_status_number);
        $this->assign('sub_total_fee',$sub_total_fee);
        $this->assign('page',$show);
        $this->display('payList');
    }

//支付详情

    public function info(){
        if(I('get.id')&&is_numeric(I('get.id'))){
            $info = M('n_alipay_info')->where('id='.I('get.id').'')->find();
            if(!$info){
                echo "<script>alert('地址异常！');history.go(-1);</script>";
                exit;
            }
            $this->assign('info',$info);
            $this->display();

        }else{
            echo "<script>alert('地址异常！');history.go(-1);</script>";
            exit;
        }
    }




    //修改友链
    public function deal_order(){
        if(I('get.id')&&is_numeric(I('get.id'))){
            $info = M('n_alipay_info')->where('id='.I('get.id').'')->find();
            if(!$info){
                echo "<script>alert('地址异常！');history.go(-1);</script>";
                exit;
            }

            $save['order_status'] = 1;
            $save_mod = M('n_alipay_info')->where('id='.I('get.id').'')->save($save);

            if($save_mod!==false){
                echo "<script>alert('处理成功！');window.location.href='".__MODULE__."/Pay/info/id/".I('get.id').".html';</script>";
                exit;
            }else{
                echo "<script>alert('处理失败！');history.back();</script>";
                exit;
            }

        }else{
            echo "<script>alert('地址异常！');history.go(-1);</script>";
            exit;
        }


    }



}