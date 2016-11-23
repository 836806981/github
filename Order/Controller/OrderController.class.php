<?php
namespace Order\Controller;
use Think\Controller;
class OrderController extends CommonController {
    public function  _initialize(){
        if(!$_SESSION[C('ORDER_AUTH_KEY')]['id']){
            echo "<script>alert('请登录！');window.location.href='" . __MODULE__ . "/Admin/login.html';</script>";
            exit;
        }
    }

    public function go_to(){

       for($i=0;$i<4;$i++){
           for($i=0;$i<4;$i++) {
               if ($i = 3) {
                   break 2;
               }
           }

       }
        echo $i;


    }

//    public function send(){
//        $statusStr = array(
//            "0" => "短信发送成功",
//            "-1" => "参数不全",
//            "-2" => "服务器空间不支持,请确认支持curl或者fsocket，联系您的空间商解决或者更换空间！",
//            "30" => "密码错误",
//            "40" => "账号不存在",
//            "41" => "余额不足",
//            "42" => "帐户已过期",
//            "43" => "IP地址限制",
//            "50" => "内容含有敏感词"
//        );
//        $smsapi = "http://api.smsbao.com/"; //短信网关
//        $user = "tianluoayi"; //短信平台帐号
//        $pass = md5("zxc153."); //短信平台密码
//        $content="1";//要发送的短信内容
//        $phone = '11123123123';//要发送短信的手机号码
//        $sendurl = $smsapi."sms?u=".$user."&p=".$pass."&m=".$phone."&c=".urlencode($content);
//        $result =file_get_contents($sendurl) ;
//        echo  $statusStr[$result];
//    }

    public function getList(){
        $post = I('post.');
        $currentpage = I("post.currentpage");
        $pagenum = I("post.pagenum");
        $start = ($currentpage - 1) * $pagenum;
        if($post['type']=='user'){
            $list = M('order_user')->where('id>1')->limit($start,$pagenum)->select();
            $count = M('order_user')->where('id>1')->count();

            $back['code'] = 1000;
            $back['data']['list'] =$list;
            $back['data']['num'] =$count;
            echo json_encode($back);
        }elseif($post['type']=='trainee'){
            $list = M('tra_trainee')->where('status=0')->limit($start,$pagenum)->order('add_time desc')->select();
            $count = M('tra_trainee')->where('status=0')->count();
            foreach ($list as $k=>$v) {
                $list[$k]['add_time'] = date('Y-m-d H:i:s',$v['add_time']);
            }

            $back['code'] = 1000;
            $back['data']['list'] =$list;
            $back['data']['num'] =$count;
            echo json_encode($back);
        }elseif(in_array($post['type'],[0,1,2,3,4,10])){

            $list = M('order_nurse')->where('status = '.$post['type'].' and type!=10')->order('add_time desc')->limit($start,$pagenum)->select();
            $count = M('order_nurse')->where('status = '.$post['type'].' and type!=10')->count();

            foreach($list as $k=>$v){

                $sales_name =  M('order_user')->field('real_name')->where('id='.$v['sales_id'].'')->find();
                $list[$k]['sales_name'] = $sales_name['real_name'];
                $type_name = ['','月嫂','育儿嫂','保姆','钟点工','月子导师'];
                $type_name[10] = '其他业务';
                $status_name = ['未派单','已派单','已签单','已上户','已下户','已结算'];
                $status_name[10] =  '已放弃';
                $list[$k]['type_name'] = $type_name[$v['type']];
                $list[$k]['status_name'] = $status_name[$v['status']];

                $list[$k]['add_time'] = date('Y-m-d H:i:s',$v['add_time']);
                $list[$k]['status_1_time'] = date('Y-m-d H:i:s',$v['status_1_time']);
                $list[$k]['status_2_time'] = date('Y-m-d H:i:s',$v['status_2_time']);
                $list[$k]['status_3_time'] = date('Y-m-d H:i:s',$v['status_3_time']);
                $list[$k]['status_20_time'] = date('Y-m-d H:i:s',$v['status_20_time']);
                $list[$k]['status_4_time'] = date('Y-m-d H:i:s',$v['status_4_time']);
                $list[$k]['status_5_time'] = date('Y-m-d H:i:s',$v['status_5_time']);
                if(in_array($post['type'],[2,3,4])){
                    $nurse_name = M('nurse')->field('nurse.name,nurse.phone,nurse.urgent_phone,is_training')->join('order_nurse_re ON order_nurse_re.nurse_id=nurse.id')->where('order_nurse_re.order_id='.$v['id'].'')->find();
                    $list[$k]['nurse_name'] = ($nurse_name['is_training']>0?'<i style="width: 15px; height: 15px; border-radius: 7px;display: inline-block;background: red;font-size:8px;color: #ffffff; line-height: 15px;text-align: center;" title="学员">学</i>':'').$nurse_name['name'];
                    $list[$k]['nurse_phone'] = $nurse_name['phone'];
                    $list[$k]['nurse_urgent_phone'] = $nurse_name['urgent_phone'];
                    $covenant = M('order_covenant')->where('order_nurse_id='.$v['id'].'')->find();
                    $list[$k]['expect_time_b'] = $covenant['expect_time_b'];
                    $list[$k]['expect_time_s'] = $covenant['expect_time_s'];
                    $list[$k]['true_expect_time_b'] = $covenant['true_expect_time_b'];
                    $list[$k]['true_expect_time_s'] = $covenant['true_expect_time_s'];
                }
            }
            $back['code'] = 1000;
            $back['data']['list'] =$list;
            $back['data']['num'] =$count;
            echo json_encode($back);
        }elseif($post['type']==5){//已结算
            $where = 'order_nurse.status=5';
            $join = 'join order_covenant ON order_covenant.order_nurse_id = order_nurse.id';
            $field = 'order_nurse.id,order_nurse.is_customer_service,order_nurse.type,sales_id,order_covenant.expect_time_b,expect_time_s,true_expect_time_b,true_expect_time_s,service_charge,balance_money';
            $list = M('order_nurse')->field($field)->join($join)->where($where)->limit($start,$pagenum)->order('order_covenant.expect_time_s asc')->select();
            $count = M('order_nurse')->join($join)->where($where)->count();
            foreach($list as $k=>$v){
                if($post['type']==5){
                    $nurse_name = M('nurse')->field('nurse.name,nurse.phone,nurse.urgent_phone,nurse.wechat')->join('order_nurse_re ON order_nurse_re.nurse_id=nurse.id')->where('order_nurse_re.order_id='.$v['id'].'')->find();
                    $list[$k]['nurse_name'] = $nurse_name['name'];
                    $list[$k]['phone'] = $nurse_name['phone']?$nurse_name['phone']:'';
                    $list[$k]['urgent_phone'] = $nurse_name['urgent_phone']?$nurse_name['urgent_phone']:'';
                    $list[$k]['wechat'] = $nurse_name['wechat'];
                    $list[$k]['true_expect_time_b'] = $v['true_expect_time_b']?$v['true_expect_time_b']:'-';

                    $type_name = ['','月嫂','育儿嫂','保姆','钟点工','月子导师'];
                    $type_name[10] = '其他业务';
                    $list[$k]['type_name'] = $type_name[$v['type']];

                    $sales_name =  M('order_user')->field('real_name')->where('id='.$v['sales_id'].'')->find();
                    $list[$k]['sales_name'] = $sales_name['real_name'];

                }
            }
            $back['code'] = 1000;
            $back['data']['list'] =$list;
            $back['data']['num'] =$count;
            echo json_encode($back);

        }elseif($post['type']==6){//需要回访的客户

//            $where = 'status = 2  OR status = 10 and  not EXISTS (select order_nurse_id from order_visit where next_time="")';concat(covenant_time,die_time) asc
            $where = '(status = 3  OR status = 4) and (ISNULL(visit_next_time ) or visit_next_time!="") ';

//            + INTERVAL 1 day  数据库加一天

            //已处理
            $list = M('order_nurse')->field('order_nurse.id,IF(ISNULL(visit_next_time),IF(status=3,order_covenant.true_expect_time_b + INTERVAL 1 day,order_covenant.true_expect_time_s + INTERVAL 1 day),visit_next_time) as time,visit_next_time,sales_id,type,name,status,add_employee,is_customer_service,order_nurse.add_time,contact_way,contact_number')->join('left join order_covenant ON order_covenant.order_nurse_id=order_nurse.id')->where($where)->order('time asc')->limit($start,$pagenum)->select();
//   echo M('order_nurse')->getLastSql();die;
//            $list = M('order_nurse')->where($where)->order('IF(ISNULL(visit_next_time),1,0),visit_next_time asc,true_expect_time_b asc')->limit($start,$pagenum)->select();
            $count = M('order_nurse')->where($where)->join('left join order_covenant ON order_covenant.order_nurse_id=order_nurse.id')->count();

            foreach($list as $k=>$v){
                $list[$k]['visit_next_time']?'':$list[$k]['visit_next_time']='';
                $sales_name =  M('order_user')->field('real_name')->where('id='.$v['sales_id'].'')->find();
                $list[$k]['sales_name'] = $sales_name['real_name'];
                $type_name = ['','月嫂','育儿嫂','保姆','钟点工','月子导师'];
                $type_name[10] = '其他业务';
                $status_name = ['未派单','已派单','已签单','已上户','已下户','已结算'];
                $status_name[10] =  '已放弃';
                $list[$k]['type_name'] = $type_name[$v['type']];
                $list[$k]['status_name'] = $status_name[$v['status']];

                $list[$k]['add_time'] = date('Y-m-d H:i:s',$v['add_time']);
                $covenant = M('order_covenant')->field('expect_time_b,expect_time_s,true_expect_time_b,true_expect_time_s')->where('order_nurse_id='.$v['id'].'')->find();
                if($v['status']==3){
                    $list[$k]['status_time'] = $covenant['true_expect_time_b'];
                }else{
                    $list[$k]['status_time'] = $covenant['true_expect_time_s'];
                }

            }
            $back['code'] = 1000;
            $back['data']['list'] =$list;
            $back['data']['num'] =$count;
            echo json_encode($back);


        }elseif($post['type']==7){//体检计划

            $seven_day = date('Y-m-d',strtotime('+14 day'));
            $where = ' (ISNULL(order_body_test.test_time) OR order_body_test.test_time="" OR ISNULL(order_body_test.test_img) OR order_body_test.test_img="") and order_nurse.status<10  and order_body_test.estimated_time<"'.$seven_day.'"';
            $join = 'left join order_body_test ON order_body_test.order_nurse_id = order_nurse.id';
            $field = 'order_nurse.id,order_body_test.estimated_time,order_nurse.is_customer_service';
            $list = M('order_nurse')->field($field)->join($join)->where($where)->limit($start,$pagenum)->order('order_body_test.estimated_time asc')->select();

            $count = M('order_nurse')->join($join)->where($where)->count();
            foreach($list as $k=>$v){
                if($post['type']==7){
                    $nurse_name = M('nurse')->field('nurse.name,nurse.phone,nurse.urgent_phone,nurse.wechat')->join('order_nurse_re ON order_nurse_re.nurse_id=nurse.id')->where('order_nurse_re.order_id='.$v['id'].'')->find();
                    $list[$k]['nurse_name'] = $nurse_name['name'];
                    $list[$k]['phone'] = $nurse_name['phone'];
                    $list[$k]['urgent_phone'] = $nurse_name['urgent_phone'];
                    $list[$k]['wechat'] = $nurse_name['wechat'];

                    $expect_time_b =  M('order_covenant')->field('expect_time_b')->where('order_nurse_id = '.$v['id'].'')->find();
                    $list[$k]['expect_time_b'] =$expect_time_b['expect_time_b'];

                }



            }
            $back['code'] = 1000;
            $back['data']['list'] =$list;
            $back['data']['num'] =$count;
            echo json_encode($back);

        }elseif($post['type']==8){//投保计划

            $seven_day = date('Y-m-d',strtotime('+7 day'));
            $where = ' (ISNULL(order_safe.true_safe_time) OR order_safe.true_safe_time="")  and order_nurse.status<10 and order_safe.buy_safe_time<"'.$seven_day.'"';
            $join = 'left join order_safe ON order_safe.order_nurse_id = order_nurse.id';
            $field = 'order_nurse.id,order_nurse.is_customer_service,order_safe.buy_safe_time';
            $list = M('order_nurse')->field($field)->join($join)->where($where)->limit($start,$pagenum)->order('order_safe.buy_safe_time asc')->select();
            $count = M('order_nurse')->join($join)->where($where)->count();
            foreach($list as $k=>$v){
                if($post['type']==8){
                    $nurse_name = M('nurse')->field('nurse.name,nurse.phone,nurse.urgent_phone,nurse.wechat')->join('order_nurse_re ON order_nurse_re.nurse_id=nurse.id')->where('order_nurse_re.order_id='.$v['id'].'')->find();
                    $list[$k]['nurse_name'] = $nurse_name['name'];
                    $list[$k]['phone'] = $nurse_name['phone'];
                    $list[$k]['urgent_phone'] = $nurse_name['urgent_phone'];
                    $list[$k]['wechat'] = $nurse_name['wechat'];
                    $expect_time_b =  M('order_covenant')->field('expect_time_b')->where('order_nurse_id = '.$v['id'].'')->find();
                    $list[$k]['expect_time_b'] =$expect_time_b['expect_time_b'];

                }
            }
            $back['code'] = 1000;
            $back['data']['list'] =$list;
            $back['data']['num'] =$count;
            echo json_encode($back);

        }elseif($post['type']==9){//客户付款
            $where = ' (ISNULL(order_expect_pay.pay_money) OR order_expect_pay.pay_money="" OR order_expect_pay.pay_money!=order_expect_pay.expect_money)  and order_nurse.status<10';
            $join = 'right join order_expect_pay ON order_expect_pay.order_nurse_id = order_nurse.id';
            $field = 'order_nurse.id,order_nurse.is_customer_service,sales_id,name,contact_way,contact_number,expect_money,expect_time';
            $list = M('order_nurse')->field($field)->join($join)->where($where)->order('expect_time asc')->limit($start,$pagenum)->select();
            $count = M('order_nurse')->join($join)->where($where)->count();
            foreach($list as $k=>$v){
                $sales_name =  M('order_user')->field('real_name')->where('id='.$v['sales_id'].'')->find();
                $list[$k]['sales_name'] = $sales_name['real_name'];
                $type_name = ['','月嫂','育儿嫂','保姆','钟点工','月子导师'];
                $type_name[10] = '其他业务';
                $status_name = ['未派单','已派单','已签单','已上户','已下户','已结算'];
                $status_name[10] =  '已放弃';
                $list[$k]['type_name'] = $type_name[$v['type']];
                $list[$k]['status_name'] = $status_name[$v['status']];
            }
            $back['code'] = 1000;
            $back['data']['list'] =$list;
            $back['data']['num'] =$count;
            echo json_encode($back);

        }elseif($post['type']==11){//上户提醒
            $where = 'order_nurse.status=2 and order_covenant.expect_time_b<="'.date('Y-m-d',strtotime('+15 days')).'"';
            $join = 'join order_covenant ON order_covenant.order_nurse_id = order_nurse.id';
            $field = 'order_nurse.id,order_nurse.type,sales_id,order_nurse.is_customer_service,order_covenant.expect_time_b,order_covenant.expect_time_b - INTERVAL 3 day as expect_time_b_3,order_covenant.expect_time_b - INTERVAL 15 day as expect_time_b_15,expect_time_s,true_expect_time_b';
            $list = M('order_nurse')->field($field)->join($join)->where($where)->limit($start,$pagenum)->order('order_covenant.expect_time_b asc')->select();
            $count = M('order_nurse')->join($join)->where($where)->count();
            foreach($list as $k=>$v){
                if($post['type']==11){
                    $nurse_name = M('nurse')->field('nurse.name,nurse.phone,nurse.urgent_phone,nurse.wechat,nurse.is_training')->join('order_nurse_re ON order_nurse_re.nurse_id=nurse.id')->where('order_nurse_re.order_id='.$v['id'].'')->find();
                    $list[$k]['nurse_name'] = ($nurse_name['is_training']>0?'<i style="width: 15px; height: 15px; border-radius: 7px;display: inline-block;background: red;font-size:8px;color: #ffffff; line-height: 15px;text-align: center;" title="学员">学</i>':'').$nurse_name['name'];
                    $list[$k]['phone'] = $nurse_name['phone']?$nurse_name['phone']:'';
                    $list[$k]['urgent_phone'] = $nurse_name['urgent_phone']?$nurse_name['urgent_phone']:'';
                    $list[$k]['wechat'] = $nurse_name['wechat'];
                    $list[$k]['true_expect_time_b'] = $v['true_expect_time_b']?$v['true_expect_time_b']:'-';

                    $type_name = ['','月嫂','育儿嫂','保姆','钟点工','月子导师'];
                    $type_name[10] = '其他业务';
                    $list[$k]['type_name'] = $type_name[$v['type']];

                    $sales_name =  M('order_user')->field('real_name')->where('id='.$v['sales_id'].'')->find();
                    $list[$k]['sales_name'] = $sales_name['real_name'];

                }
            }
            $back['code'] = 1000;
            $back['data']['list'] =$list;
            $back['data']['num'] =$count;
            echo json_encode($back);

        }elseif($post['type']==12){//下户提醒
            $where = 'order_nurse.status=3';
            $join = 'join order_covenant ON order_covenant.order_nurse_id = order_nurse.id';
            $field = 'order_nurse.id,order_nurse.type,order_nurse.is_customer_service,sales_id,order_covenant.expect_time_b,expect_time_s,true_expect_time_b';
            $list = M('order_nurse')->field($field)->join($join)->where($where)->limit($start,$pagenum)->order('order_covenant.expect_time_s asc')->select();
            $count = M('order_nurse')->join($join)->where($where)->count();
            foreach($list as $k=>$v){
                if($post['type']==12){
                    $nurse_name = M('nurse')->field('nurse.name,nurse.phone,nurse.urgent_phone,nurse.wechat,nurse.is_training')->join('order_nurse_re ON order_nurse_re.nurse_id=nurse.id')->where('order_nurse_re.order_id='.$v['id'].'')->find();
                    $list[$k]['nurse_name'] = ($nurse_name['is_training']>0?'<i style="width: 15px; height: 15px; border-radius: 7px;display: inline-block;background: red;font-size:8px;color: #ffffff; line-height: 15px;text-align: center;" title="学员">学</i>':'').$nurse_name['name'];
                    $list[$k]['phone'] = $nurse_name['phone']?$nurse_name['phone']:'';
                    $list[$k]['urgent_phone'] = $nurse_name['urgent_phone']?$nurse_name['urgent_phone']:'';
                    $list[$k]['wechat'] = $nurse_name['wechat'];
                    $list[$k]['true_expect_time_b'] = $v['true_expect_time_b']?$v['true_expect_time_b']:'-';

                    $type_name = ['','月嫂','育儿嫂','保姆','钟点工','月子导师'];
                    $type_name[10] = '其他业务';
                    $list[$k]['type_name'] = $type_name[$v['type']];

                    $sales_name =  M('order_user')->field('real_name')->where('id='.$v['sales_id'].'')->find();
                    $list[$k]['sales_name'] = $sales_name['real_name'];

                }
            }
            $back['code'] = 1000;
            $back['data']['list'] =$list;
            $back['data']['num'] =$count;
            echo json_encode($back);

        }elseif($post['type']==13){//面试计划
            $where = 'order_interview.is_complete=0';
            $join = 'join order_interview ON order_nurse.id=order_interview.order_nurse_id';
            $field = 'order_nurse.id,order_nurse.name as name,order_nurse.contact_way,order_nurse.contact_number,order_nurse.is_customer_service,order_nurse.type,sales_id,interview_time';
            $list = M('order_nurse')->field($field)->join($join)->where($where)->limit($start,$pagenum)->order('order_interview.interview_time asc')->select();
            $count = M('order_nurse')->join($join)->where($where)->count();
            foreach($list as $k=>$v){
                if($post['type']==13){
                    $nurse_name = M('nurse')->field('nurse.name,nurse.id')->join('order_nurse_re ON order_nurse_re.nurse_id=nurse.id')->where('order_nurse_re.order_id='.$v['id'].'')->select();
                    foreach($nurse_name as $ks=>$vs){
                        $list[$k]['nurse_name'] .='<a href="'.__MODULE__.'/Order/ayi_info/id/'.$vs['id'].'.html">'.$vs['name'].'</a>'.',';
                    }
                    $list[$k]['nurse_name'] = substr($list[$k]['nurse_name'],0,-1);
                    $type_name = ['','月嫂','育儿嫂','保姆','钟点工','月子导师'];
                    $type_name[10] = '其他业务';
                    $list[$k]['type_name'] = $type_name[$v['type']];

                    $sales_name =  M('order_user')->field('real_name')->where('id='.$v['sales_id'].'')->find();
                    $list[$k]['sales_name'] = $sales_name['real_name'];
                }
            }
            $back['code'] = 1000;
            $back['data']['list'] =$list;
            $back['data']['num'] =$count;
            echo json_encode($back);

        }elseif($post['type']==14){//督导计划
            $where = 'order_nurse.status>2 and order_nurse.next_supervisor_time!=""';
            $field = 'order_nurse.id,name,order_nurse.type,order_nurse.is_customer_service,sales_id,next_supervisor_time';
            $list = M('order_nurse')->field($field)->where($where)->limit($start,$pagenum)->order('order_nurse.next_supervisor_time asc')->select();
            $count = M('order_nurse')->where($where)->count();
            foreach($list as $k=>$v){
                if($post['type']==14){
                    $nurse_name = M('nurse')->field('nurse.name,nurse.phone,nurse.urgent_phone,nurse.wechat')->join('order_nurse_re ON order_nurse_re.nurse_id=nurse.id')->where('order_nurse_re.order_id='.$v['id'].'')->find();
                    $list[$k]['nurse_name'] = $nurse_name['name'];
                    $list[$k]['phone'] = $nurse_name['phone']?$nurse_name['phone']:'';
                    $list[$k]['urgent_phone'] = $nurse_name['urgent_phone']?$nurse_name['urgent_phone']:'';
                    $list[$k]['wechat'] = $nurse_name['wechat'];

                    $type_name = ['','月嫂','育儿嫂','保姆','钟点工','月子导师'];
                    $type_name[10] = '其他业务';
                    $list[$k]['type_name'] = $type_name[$v['type']];

                    $sales_name =  M('order_user')->field('real_name')->where('id='.$v['sales_id'].'')->find();
                    $list[$k]['sales_name'] = $sales_name['real_name'];

                }
            }
            $back['code'] = 1000;
            $back['data']['list'] =$list;
            $back['data']['num'] =$count;
            echo json_encode($back);

        }elseif($post['type']==33){//总订单
            $where = '1';
            if($post['keyword']!==''){
                $where .= ' and ( name LIKE "%'.$post['keyword'].'%" OR contact_number LIKE "%'.$post['keyword'].'%") ';
            }
            if($post['order_type']!==''){
                $where .= ' and type='.$post['order_type'];
            }
            if($post['order_status']!==''){
                $where .= ' and status='.$post['order_status'];
            }
            if($post['order_sales_id']!==''){
                $where .= ' and sales_id='.$post['order_sales_id'];
            }

            $list = M('order_nurse')->where($where)->order('add_time desc')->limit($start,$pagenum)->select();
            $count = M('order_nurse')->where($where)->count();

            foreach($list as $k=>$v){

                $sales_name =  M('order_user')->field('real_name')->where('id='.$v['sales_id'].'')->find();
                $list[$k]['sales_name'] = $sales_name['real_name']?$sales_name['real_name']:'';
                $type_name = ['','月嫂','育儿嫂','保姆','钟点工','月子导师'];
                $type_name[10] = '其他业务';
                $status_name = ['未派单','已派单','已签单','已上户','已下户','已结算'];
                $status_name[10] =  '已放弃';
                $status_name[20] =  '已售后';
                $list[$k]['type_name'] = $type_name[$v['type']];
                $list[$k]['status_name'] = $status_name[$v['status']];

                $list[$k]['add_time'] = date('Y-m-d H:i:s',$v['add_time']);
            }
            $back['code'] = 1000;
            $back['data']['list'] =$list;
            $back['data']['num'] =$count;
            echo json_encode($back);
        }
    }

    //修改上户或下户日期
    public function change_time(){
        $id = I('get.id');
        $expect_time_b = I('get.expect_time_b');
        $expect_time_s = I('get.expect_time_s');
        if($expect_time_b){
            $save['expect_time_b'] = $expect_time_b;
        }

        if($expect_time_s){
            $save['expect_time_s'] = $expect_time_s;
        }

        $save_mod = M('order_covenant')->where('order_nurse_id='.$id.'')->save($save);
        if($save_mod!==false){
            echo "<script>alert('修改成功！');history.back();</script>";
            exit;
        }else{
            echo "<script>alert('修改失败！');history.back();</script>";
            exit;
        }

    }

    public function oList(){

        $this->display();
    }

    public function addOrder(){
        if(I('post.')){
            $post = I('post.');
            $post['is_customer_service'] = 0;
            $have_order = M('order_nurse')->where('contact_number="'.$post['contact_number'].'"')->find();
            if($have_order){
                echo "<script>alert('已存在该联系方式的订单！');history.back();</script>";
                exit;
            }
            $post['status'] = 0;
            $post['add_time'] = time();
            $post['price_area'] = $post['price_l'].'-'.$post['price_h'];
            $post['add_employee'] = $_SESSION[C('ORDER_AUTH_KEY')]['real_name'] ;
            $post['add_mac'] = $this->GetMacAddr(PHP_OS) ;
            $add = M('order_nurse')->add($post);
            if($add){
                if($post['type']==10){
                    echo "<script>alert('添加成功！');window.location.href='" . __MODULE__ . "/Order/oList/type/15.html';</script>";
                    exit;
                }
                if($post['type']==1||$post['type']==5){
                    $b_time = $post['base_information1'];
                    $s_time = date('Y-m-d',strtotime('+26 days',strtotime($b_time)));

                    $where =' not EXISTS (SELECT nurse_id from nurse_use where (("'.$b_time.'" > b_time and "'.$b_time.'" < s_time OR "'.$s_time.'" > b_time and "'.$s_time.'"< s_time) OR ("'.$b_time.'" <= b_time  and  "'.$s_time.'" >= s_time)) and nurse_use.nurse_id=nurse.id  )';
                    $where .= ' and status < 10  and type=1';
                    $field = 'nurse.id';
                    $order = 'nurse.l_price asc';
                    $count = M("nurse")->where($where)->count();
                    if($count>5){
                        $price = ($post['price_l']+$post['price_l'])/2;
                        $order = 'abs('.$price.'-(h_price+l_price)/2) asc ';
                    }

                    $nurse = M("nurse")->field($field)->where($where)->order($order)->limit(5)->select();
                    foreach($nurse as $k=>$v){
                        $add_re[$k]['order_id'] = $add;
                        $add_re[$k]['nurse_id'] = $v['id'];
                    }
                    $add_nurse_re = M('order_nurse_re')->addAll($add_re);
                    if($add_nurse_re===false){
                        M('order_nurse_re')->addAll($add_re);
                    }

                }elseif(in_array($post['type'],[2,3])){
                    $b_time = date('Y-m-d');
                    $s_time = date('Y-m-d',strtotime('+30 days',strtotime($b_time)));

                    $where =' not EXISTS (SELECT nurse_id from nurse_use where (("'.$b_time.'" > b_time and "'.$b_time.'" < s_time OR "'.$s_time.'" > b_time and "'.$s_time.'"< s_time) OR ("'.$b_time.'" <= b_time  and  "'.$s_time.'" >= s_time)) and nurse_use.nurse_id=nurse.id  )';
                    $where .= ' and status < 10  and type='.$post['type'].'';
                    $field = 'nurse.id';
                    $order = 'nurse.l_price asc';
                    $count = M("nurse")->where($where)->count();
                    if($count>5){
                        $price = ($post['price_l']+$post['price_l'])/2;
                        $order = 'abs('.$price.'-(h_price+l_price)/2) asc ';
                    }

                    $nurse = M("nurse")->field($field)->where($where)->order($order)->limit(5)->select();
                    foreach($nurse as $k=>$v){
                        $add_re[$k]['order_id'] = $add;
                        $add_re[$k]['nurse_id'] = $v['id'];
                    }
                    $add_nurse_re = M('order_nurse_re')->addAll($add_re);
                    if($add_nurse_re===false){
                        M('order_nurse_re')->addAll($add_re);
                    }
                }
                echo "<script>alert('添加成功前去派单！');window.location.href='" . __MODULE__ . "/Order/distribution/id/".$add.".html';</script>";
                exit;
            }else{
                echo "<script>alert('添加失败！');history.back();</script>";
                exit;
            }

        }else{
            $this->display();
        }
    }

    public function editOrder(){
        if(I('post.')){
            $post = I('post.');
            $post['is_customer_service'] = 0;
            $have_order = M('order_nurse')->where(' contact_number="'.$post['contact_number'].'" and id!='.$post['id'].'')->find();
            if($have_order){
                echo "<script>alert('已存在该联系方式的订单！');history.back();</script>";
                exit;
            }
            $post['price_area'] = $post['price_l'].'-'.$post['price_h'];
            $add = M('order_nurse')->where('id='.$post['id'].'')->save($post);
            if($add!==false){
                echo "<script>alert('修改成功！');window.location.href='" . __MODULE__ . "/Order/info/id/".$post['id'].".html';</script>";
                exit;
            }else{
                echo "<script>alert('修改失败！');history.back();</script>";
                exit;
            }
        }else{
            $info = M('order_nurse')->where('id='.I('get.id').'')->find();
            if($info){

                $this->assign('info',$info);
                $this->display();
            }else{
                echo "<script>alert('地址异常！');history.back();</script>";
                exit;
            }
        }
    }


//联系记录
    public function record(){
        if(!I('get.id')){
            echo "<script>alert('地址异常！');history.back();</script>";
            exit;
        }
        $info = M('order_nurse')->where('id='.I('get.id').'')->find();
        if(!$info){
            echo "<script>alert('地址异常！');history.back();</script>";
            exit;
        }

        $sales_name = M('order_user')->where('id='.$info['sales_id'].'')->find();

        $type = ['','月嫂','育儿嫂','保姆','钟点工','月子导师'];
        $type[10] = '其他业务';
        $info['type_name'] = $type[$info['type']];
        $info['sales_name'] = $sales_name['real_name']?$sales_name['real_name']:'还未派单点击→';

        $nurses = M('order_nurse_re')->field('order_nurse_re.id,nurse.name')->join('left join nurse ON nurse.id=order_nurse_re.nurse_id')->where('order_nurse_re.order_id='.I('get.id').'')->select();

        $record = M('order_record')->where('order_id='.I('get.id').'')->order('add_time desc')->select();
        $this->assign('nurses',$nurses);
        $this->assign('info',$info);
        $this->assign('record',$record);
        $this->display();
    }

    //添加联系记录
    public function addRecord(){
        $post = I('post.');
        if($post){
            $post['order_status'] < 3?$post['record_type']='销售跟进':$post['record_type']='售后联系';
            $post['add_time'] = time();
            $post['employee_name'] = $_SESSION[C('ORDER_AUTH_KEY')]['real_name'];
            $post['mac'] = $this->GetMacAddr(PHP_OS);
            $add = M('order_record')->add($post);
            if($add){
                echo "<script>alert('添加成功！');window.location.href='" . __MODULE__ . "/Order/record/id/".$post['order_id'].".html';</script>";
                exit;
            }else{
                echo "<script>alert('添加失败！');history.back();</script>";
                exit;
            }
        }else{
            echo "<script>alert('网络异常！');history.back();</script>";
            exit;
        }
    }



//联系记录
    public function visit(){
        if(!I('get.id')){
            echo "<script>alert('地址异常！');history.back();</script>";
            exit;
        }
        $info = M('order_nurse')->where('id='.I('get.id').'')->find();
        if(!$info){
            echo "<script>alert('地址异常！');history.back();</script>";
            exit;
        }

        $sales_name = M('order_user')->where('id='.$info['sales_id'].'')->find();

        $type = ['','月嫂','育儿嫂','保姆','钟点工','月子导师'];
        $type[10] = '其他业务';
        $info['type_name'] = $type[$info['type']];
        $info['sales_name'] = $sales_name['real_name']?$sales_name['real_name']:'还未派单点击→';

        $nurses = M('order_nurse_re')->field('order_nurse_re.id,nurse.name')->join('left join nurse ON nurse.id=order_nurse_re.nurse_id')->where('order_nurse_re.order_id='.I('get.id').'')->select();

        $visit = M('order_visit')->where('order_nurse_id='.I('get.id').'')->order('visit_time desc')->select();


        $this->assign('visit',$visit);
        $this->assign('nurses',$nurses);
        $this->assign('info',$info);
        $this->display();
    }

    //添加联系记录
    public function addVisit(){
        $post = I('post.');
        if($post){
            $post['mac'] = $this->GetMacAddr(PHP_OS);
            $post['visit_time'] = date('Y-m-d H:i:s');

            if($post['is_contact']=='否'){
                $post['score']='未接通';
            }
            $add = M('order_visit')->add($post);
            if($add){
                $save['visit_next_time'] = $post['next_time'];
                $save_next = M('order_nurse')->where('id='.$post['order_nurse_id'].'')->save($save);
                if($save_next===false){
                    M('order_nurse')->where('id='.$post['order_nurse_id'].'')->save($save);
                }

                if($post['next_time']==''){
                    echo "<script>alert('回访完成！');window.location.href='" . __MODULE__ . "/Order/oList/type/6.html';</script>";
                    exit;
                }else{
                    echo "<script>alert('添加成功！');window.location.href='" . __MODULE__ . "/Order/visit/id/".$post['order_nurse_id'].".html';</script>";
                    exit;
                }

            }else{
                echo "<script>alert('添加失败！');history.back();</script>";
                exit;
            }
        }else{
            echo "<script>alert('网络异常！');history.back();</script>";
            exit;
        }
    }



//  派单页面数据
    public function distribution(){
        $have_order = M('order_nurse')->where('id="'.I('get.id').'"')->find();
        if($have_order['status']!=10){
            if($have_order['sales_id']!=0){
                $real_name = M('order_user')->field('real_name')->where('id='.$have_order['sales_id'].'')->find();
                $have_order['sales_name'] = '已派单给：'.$real_name['real_name'];
            }else{
                $have_order['sales_name'] = '未派单';
            }

            $permission3 = M('order_user')->where('permission=3')->select();

            $this->assign('permission3',$permission3);
            $this->assign('info',$have_order);
            $this->display();
        }else{
            echo "<script>alert('订单异常或属于已放弃订单！');history.back();</script>";
            exit;
        }
    }

    //派单
    public function distribution_to(){
        $where['id'] = I('post.id');
        $save['sales_id'] = I('post.sales_id');
        $status = M('order_nurse')->where($where)->find();
        $save['status_1_time'] = time();
        $status['status']==0?($save['status'] = 1):'';
        $save_mod = M('order_nurse')->where($where)->save($save);

        if($save_mod!==false){
            echo "<script>alert('派单成功！');window.location.href='" . __MODULE__ . "/Order/oList/type/1.html';</script>";
            exit;
        }else{
            echo "<script>alert('派单失败！');history.back();</script>";
            exit;
        }

    }

    public function addNurse(){
        if(!I('get.id')){
            echo "<script>alert('地址异常！');history.back();</script>";
            exit;
        }
        $nurse = M('order_nurse')->where('id='.I('get.id').'')->find();
        if(!$nurse){
            echo "<script>alert('地址异常！');history.back();</script>";
            exit;
        }
        if($nurse['status']!=1){
            echo "<script>alert('不是已派单状态，不能匹配阿姨！');history.back();</script>";
            exit;
        }
        $type = ['','月嫂','育儿嫂','保姆','钟点工','月子导师'];
        $type[10] = '其他业务';
        $nurse['type_name'] = $type[$nurse['type']];
        $this->assign('nurse',$nurse);
        $this->display();
    }

    public function info(){
        if(!I('get.id')){
            echo "<script>alert('地址异常！');history.back();</script>";
            exit;
        }
        $info = M('order_nurse')->where('id='.I('get.id').'')->find();
        if(!$info){
            echo "<script>alert('地址异常！');history.back();</script>";
            exit;
        }

        $sales_name = M('order_user')->where('id='.$info['sales_id'].'')->find();

        $type = ['','月嫂','育儿嫂','保姆','钟点工','月子导师'];
        $type[10] = '其他业务';
        $info['type_name'] = $type[$info['type']];
        $info['sales_name'] = $sales_name['real_name']?$sales_name['real_name']:'还未派单点击→';

        $nurse_re = M('order_nurse_re')->field('order_nurse_re.id as id,order_nurse_re.nurse_id,nurse.name as name,nurse.id as nurse_id')->join('left join nurse ON nurse.id=order_nurse_re.nurse_id')->where('order_nurse_re.order_id='.I('get.id').'')->select();

        $nurse_count = M('order_nurse_re')->where('order_id='.I('get.id').'')->count();

        $interview = M('order_interview')->where('order_nurse_id='.I('get.id').'')->order('add_time desc')->select();//面试情况
        foreach($interview as $k=>$v){
            $interview[$k]['interview_info'] = M('order_interview_info')->where('interview_id='.$v['id'].'')->order('nurse_id asc')->select();
        }
        $this->assign('interview',$interview);
        $this->assign('nurse_count',$nurse_count);
        $this->assign('nurse_re',$nurse_re);
        $this->assign('info',$info);
        $this->display();
    }

    //新增面试计划
    public function add_interview(){
        $post = I('post.');
        $post['mac'] = $this->GetMacAddr(PHP_OS);
        $post['add_time'] = time();
        $post['is_complete'] = 0;
        $post['interview_time'] = $post['interview_time'].' '.$post['interview_time_h'].':'.$post['interview_time_i'];
        $add = M('order_interview')->add($post);
        if($add){
            echo "<script>alert('添加成功！');window.location.href='" . __MODULE__ . "/Order/info/id/".$post['order_nurse_id'].".html';</script>";
            exit;
        }else{
            echo "<script>alert('添加失败！');history.back();</script>";
            exit;
        }
    }
    //新增计划详细
    public function add_interview_info(){
        $post = I('post.');
        $mac = $this->GetMacAddr(PHP_OS);
        $add_time = time();
        for($i=0;$i<sizeof($post['nurse_id']);$i++){
            $add[$i]['interview_id'] = $post['interview_id'];
            $add[$i]['nurse_id'] = $post['nurse_id'][$i];
            $add[$i]['nurse_name'] = $post['name'][$i];
            $add[$i]['interview_time'] = $post['interview_time'][$i];
            $add[$i]['address'] = $post['address'][$i];
            $add[$i]['expression'] = $post['expression'][$i];
            $add[$i]['is_pass'] = $post['is_pass'][$i];
            $add[$i]['mac'] = $mac;
            $add[$i]['add_time'] = $add_time;
        }
        $add_mod = M('order_interview_info')->addAll($add);
        if($add_mod){
            $save['is_complete'] = 1;
            $save_mod = M('order_interview')->where('id='.$post['interview_id'].'')->save($save);
            if($save_mod===false){
                M('order_interview')->where('id='.$post['interview_id'].'')->save($save);
            }
            echo "<script>alert('添加成功！');window.location.href='" . __MODULE__ . "/Order/info/id/".$post['order_nurse_id'].".html';</script>";
            exit;
        }else{
            echo "<script>alert('添加失败！');history.back();</script>";
            exit;
        }
    }


    public function getAyilist(){
        $post = I("post.");
        $currentpage = I("post.currentpage");
        $pagenum = I("post.pagenum");
        $start = ($currentpage - 1) * $pagenum;

        $post['type']==5?($post['type']=1):'';
        $where = 'type='.$post['type'];
        $post['jianzhi']==1&&$where = ' others LIKE "%'.$post['type'].'%"';

        $post['age1']&&$where .= ' and right(left(id_card,10),4) <= '.(date('Y')-$post['age1']);



        $post['age2']&&$where .= ' and right(left(id_card,10),4) >= '.(date('Y')-$post['age2']);

        if($post['l_price']&&$post['h_price']){
            $where .= ' and ('.$post['l_price'].' <= h_price  OR   '.$post['h_price'].' >= l_price )';
        }else if($post['l_price']){
            $where .= ' and ('.$post['l_price'].' <= h_price )';
        }elseif($post['h_price']){
            $where .= ' and ( '.$post['h_price'].' >= l_price  )';
        }

        if($post['zodiac']&&$post['zodiac']!='0'){
            $where .= ' and zodiac="'.$post['zodiac'].'"';
        }
        if($post['b_time']&&$post['s_time']){
            $where .=' and  not EXISTS (SELECT nurse_id from nurse_use where (("'.$post['b_time'].'" > b_time and "'.$post['b_time'].'" < s_time OR "'.$post['s_time'].'" > b_time and "'.$post['s_time'].'"< s_time) OR ("'.$post['b_time'].'" <= b_time  and  "'.$post['s_time'].'" >= s_time)) and nurse_use.nurse_id=nurse.id  )';
        }
        if($post['add_time_s']){
            $where .= '  and nurse.add_time <'.strtotime($post['add_time_s']);
        }
        if($post['add_time_b']){
            $where .= '  and nurse.add_time >'.strtotime($post['add_time_b']);
        }

        if($post['name']&&$post['name']!=''){
            $where = '(type='.$post['type'] .' OR others LIKE "%'.$post['type'].'%") and name LIKE "%'.$post['name'].'%" ';
        }



        $where .= ' and status < 10  ';

        $field = 'id,number,title_img,name,id_card,l_price,h_price,zodiac,work_date,type,others,status,specialty,add_time as add_time_nurse';
        $order = 'sort desc';


        $nurse = M("nurse")->field($field)->where($where)->order($order)->limit($start,$pagenum)->select();
        $count = M("nurse")->where($where)->count();

        $belong = array(
            '', '月嫂', '育儿嫂', '保姆'
        );
        foreach($nurse as $k=>$v){

            $our_nurse = M('order_nurse_re')->where('order_id='.$post['order_id'].' and nurse_id='.$v['id'].'')->find();// 是否选择了这个阿姨
            if($our_nurse){
                $nurse[$k]['our_nurse'] = 1;
            }else{
                $nurse[$k]['our_nurse'] = 0;
            }
            $nurse[$k]['add_time_nurse'] = date('Y-m-d H:i:s',$v['add_time_nurse']);
            $nurse[$k]['age'] = date('Y',time())-substr($v['id_card'],6,4);
            $nurse_use = M("nurse_use")->field('b_time,s_time')->where('nurse_id='.$v['id'].' and s_time>'.date('Y-m-d').'')->order('b_time desc')->select();
            $nurse[$k]['nurse_use'] = $nurse_use;
            $type_name = $belong[$v['type']];
            $others = explode(',',$v['others']);
            $others_name1 = $belong[$others[0]];
            $others_name2 = $belong[$others[1]];

            $educational = ['','小学','初中','高中','大专','本科'];

            $nurse[$k]['educational'] = $educational[$v['educational']];
            $nurse[$k]['type_name'] = $type_name;
            $nurse[$k]['others_name1'] = $others_name1?$others_name1:'';
            $others_name2&&$others_name2='、'.$others_name2;
            $nurse[$k]['others_name2'] = $others_name2?$others_name2:'';
        }

        $back['code'] = 1000;
        $back['data']['list'] =$nurse;
        $back['data']['type'] = $post['type'];
        $back['data']['num'] =$count;
        echo json_encode($back);
    }



    //阿姨详细页面数据
    public  function ayi_info(){

        $nurse_info = M("nurse")->where('id='.I('get.id').'')->find();

        $educational = array('','小学', '初中', '高中', '本科', '本科以上');
        $type = array('','月嫂', '育儿嫂', '保姆');
        $others = explode(',',$nurse_info['others']);

        $nurse_info['others'] = $type[$others[0]]? $type[$others[0]].($type[$others[1]]?'、'.$type[$others[1]]:''):'无';


        $nurse_info['educational'] = $educational[$nurse_info['educational']];
        $nurse_info['ayi_type'] = $nurse_info['type'];
        $nurse_info['type'] = $type[$nurse_info['type']];

        $nurse_info['age'] = date('Y',time())-substr($nurse_info['id_card'],6,4);

        $do_word = explode(',',$nurse_info['do_word']);
        $do_video= explode(',',$nurse_info['do_video']);
        $experience= explode('------',$nurse_info['experience']);//行业经验
        $character= explode('------',$nurse_info['character']);//性格评价
        $family= explode('------',$nurse_info['family']);//性格评价
        $test_img= explode(',',$nurse_info['test_img']);//性格评价
        foreach($do_word as $k=>$v){
            if($v){
                $video[$k]['word'] = $v;
                $video[$k]['video'] = $do_video[$k];
            }
        }
        //and s_time>'.date('Y-m-d').'    历史上单记录。暂时显示
        $nurse_use = M("nurse_use")->where('nurse_id='.I('get.id').' and s_time>'.date('Y-m-d').' ')->order('b_time desc')->select();

        $record = M("nurse_record")->where('nurse_id='.I('get.id').'')->order('add_time desc')->limit(0,5)->select();


        $this->assign('record',$record);

        $this->assign('nurse_use',$nurse_use);
        $this->assign('video',$video);
        $this->assign('experience',$experience);
        $this->assign('character',$character);
        $this->assign('family',$family);
        $this->assign('test_img',$test_img);
        $this->assign('info',$nurse_info);
        $this->display();
    }


    //修改选中阿姨
    public function change_nurse_re(){
        $post = I('post.');
        if($post['action']==1){
           $add =  M('order_nurse_re')->add($post);
            $nurse_re_time['nurse_re_time'] = time();
            M('order_nurse')->where('id='.$post['order_id'].' and (nurse_re_time<100 OR ISNULL(nurse_re_time))')->save($nurse_re_time);


        }else{
            $add =  M('order_nurse_re')->where('order_id='.$post['order_id'].' and nurse_id='.$post['nurse_id'].'')->delete();
        }
    }

    //点击删除这个匹配阿姨
    public function del_order_nurse(){
        $status = M('order_nurse')->field('order_nurse.status,order_nurse.id')->join('right join order_nurse_re ON order_nurse_re.order_id=order_nurse.id')->where('order_nurse_re.id='.I('post.id'))->find();
        if($status['status']==1){

            $is_complete = M('order_interview')->field('is_complete')->where('order_nurse_id='.$status['id'].' and is_complete=0')->find();
            if($is_complete){
                $count = M('order_nurse_re')->where('order_id='.$status['id'].'')->count();
                if($count==1){
                    $back['code'] = 1003;
                    echo json_encode($back);
                    exit;
                }
            }

            $delete = M('order_nurse_re')->where('id='.I('post.id'))->delete();
            if($delete){
                $back['code'] = 1000;
                echo json_encode($back);
            }else{
                $back['code'] = 1001;
                echo json_encode($back);
            }
        }else{
            $back['code'] = 1002;
            echo json_encode($back);
        }
    }
    //点击选择这个匹配阿姨
    public function choose_order_nurse(){
        $status = M('order_nurse')->field('order_nurse.status')->join('right join order_nurse_re ON order_nurse_re.order_id=order_nurse.id')->where('order_nurse_re.id='.I('post.id'))->find();
        if($status['status']==1){
            $order_nurse_re = M('order_nurse_re')->where('id!='.I('post.id').'')->find();
            $delete = M('order_nurse_re')->where('id!='.I('post.id').'  and order_id='.$order_nurse_re['order_id'].'')->delete();
            if($delete!==false){
                $back['code'] = 1000;
                echo json_encode($back);
            }else{
                $back['code'] = 1001;
                echo json_encode($back);
            }
        }else{
            $back['code'] = 1002;
            echo json_encode($back);
        }
    }



//签订合同页面
    public function covenant(){
        if(!I('get.id')){
            echo "<script>alert('地址异常！');history.back();</script>";
            exit;
        }
        $info = M('order_nurse')->where('id='.I('get.id').'')->find();
        if(!$info){
            echo "<script>alert('地址异常！');history.back();</script>";
            exit;
        }
        $is_complete = M('order_interview')->where('order_nurse_id='.I('get.id').' and is_complete=0')->find();
        if($is_complete){
            echo "<script>alert('有未处理的面试！');history.back();</script>";
            exit;
        }

        $is_complete = M('order_interview')->where('order_nurse_id='.I('get.id').'')->find();
        if(!$is_complete){
            echo "<script>alert('还未面试，不能签到合同！');history.back();</script>";
            exit;
        }

        $have_covenant = M('order_covenant')->where('order_nurse_id='.I('get.id').'')->find();
        if($have_covenant){
            echo "<script>alert('已经签订了合同！');history.back();</script>";
            exit;
        }

        $nurse_count = M('order_nurse_re')->where('order_id='.I('get.id').'')->count();
        if($nurse_count!=1){
            echo "<script>alert('未选择签单阿姨或选择了多个阿姨！');history.back();</script>";
            exit;
        }
//        if($info['type']==1||$info['type']==5){//只判断月嫂育儿嫂。。要判断所以删除这个if
            $nurse = M('nurse')->field('nurse.id')->join('right join order_nurse_re ON order_nurse_re.nurse_id=nurse.id ')->where('order_nurse_re.order_id='.I('get.id').'')->find();
            $nurse_use = M('nurse_use')->where('nurse_id='.$nurse['id'].'')->select();
            foreach($nurse_use as $k=>$v){
                if($info['base_information1']<$v['s_time']&&$info['base_information1']>$v['b_time']){
                    echo "<script>alert('所选签单阿姨档期交叉！');history.back();</script>";
                    exit;
                }
            }
//        }

        $sales_name = M('order_user')->where('id='.$info['sales_id'].'')->find();

        $type = ['','月嫂','育儿嫂','保姆','钟点工','月子导师'];
        $type[10] = '其他业务';
        $info['type_name'] = $type[$info['type']];
        $info['sales_name'] = $sales_name['real_name']?$sales_name['real_name']:'还未派单点击→';

        $nurses = M('order_nurse_re')->field('order_nurse_re.id,nurse.name')->join('left join nurse ON nurse.id=order_nurse_re.nurse_id')->where('order_nurse_re.order_id='.I('get.id').'')->select();

        $this->assign('nurses',$nurses);
        $this->assign('info',$info);
        $this->display();
    }

    //签订合同
    public function addCovenant(){

        $post = I('post.');
        $have_covenant = M('order_covenant')->where('order_nurse_id='.$post['order_nurse_id'].'')->find();
        if($have_covenant){
            echo "<script>alert('已经签订合同！');window.location.href='" . __MODULE__ . "/Order/info/id/".$post['order_nurse_id']."';</script>";
            exit;
        }

        $post['add_mac'] = $this->GetMacAddr(PHP_OS);
        $post['add_time'] = time();



        if($_FILES['covenant_img1']['tmp_name']) {
            $upload = new \Think\Upload();// 实例化上传类
            $upload->maxSize = 1048576;// 设置附件上传大小
            $upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
            $upload->savePath = 'Order/covenant/'; // 设置附件上传目录
            $upload->subName = ''; // 设置附件上传目录
            $info = $upload->uploadOne($_FILES['covenant_img1']);
            if (!$info) {
                $error = $upload->getError();
                echo "<script>alert('".$error."');</script>";
            } else {
                $imgPath = $info['savepath'] . $info['savename'];
                $post['covenant_img1'] .= $imgPath.',';
            }
        }

        if($_FILES['covenant_img2']['tmp_name']) {
            $upload = new \Think\Upload();// 实例化上传类
            $upload->maxSize = 1048576;// 设置附件上传大小
            $upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
            $upload->savePath = 'Order/covenant/'; // 设置附件上传目录
            $upload->subName = ''; // 设置附件上传目录
            $info = $upload->uploadOne($_FILES['covenant_img2']);
            if (!$info) {
                $error = $upload->getError();
                echo "<script>alert('".$error."');</script>";
            } else {
                $imgPath = $info['savepath'] . $info['savename'];
                $post['covenant_img2'] .= $imgPath.',';
            }
        }


        $order_covenant = M('order_covenant')->add($post);

        if(!$order_covenant){
            M('order_covenant')->add($post);
        }

        for($i=0;$i<sizeof($post['expect_time']);$i++){
            $order_expect_pay[$i]['covenant_id'] = $order_covenant;
            $order_expect_pay[$i]['order_nurse_id'] = $post['order_nurse_id'];
            $order_expect_pay[$i]['expect_time'] = $post['expect_time'][$i];
            $order_expect_pay[$i]['expect_money'] = $post['expect_money'][$i];
            $order_expect_pay[$i]['add_time'] = $post['add_time'];
            $order_expect_pay[$i]['mac'] = $post['add_mac'];
        }



        $expect_pay = M('order_expect_pay')->addAll($order_expect_pay);
        if(!$expect_pay){
            M('order_expect_pay')->addAll($order_expect_pay);
        }

        $body_test_add['order_nurse_id'] = $post['order_nurse_id'];
        $body_test_add['estimated_time'] = $post['body_test_time'];
        $body_test_add['add_time'] = time();
        $body_test_add['mac'] = $post['add_mac'];

        $body_test = M('order_body_test')->add($body_test_add);
        if(!$body_test){
            M('order_body_test')->add($body_test_add);
        }



        $order_safe['order_nurse_id'] = $post['order_nurse_id'];
        $order_safe['buy_safe_time'] = $post['buy_safe_time'];
        $order_safe['add_time'] =  $post['add_time'];
        $order_safe['mac'] = $post['add_mac'];

        $safe =  M('order_safe')->add($order_safe);

        if(!$safe){
            M('order_safe')->add($order_safe);
        }
        $order_nurse['status'] = 2;
        $order_nurse['covenant_time'] = $post['covenant_time'];
        $order_nurse['status_2_time'] =  $post['add_time'];
        $save_order = M('order_nurse')->where('id='.$post['order_nurse_id'].'')->save($order_nurse);
        if(!$save_order){
            M('order_nurse')->where('id='.$post['order_nurse_id'].'')->save($order_nurse);
        }

        $order_nurse_re = M('order_nurse_re')->where('order_id='.$post['order_nurse_id'].'')->find();
        $nurse['nurse_id'] = $order_nurse_re['nurse_id'];
        $nurse['add_time'] = $post['add_time'];
        $nurse['b_time'] = $post['expect_time_b'];
        $nurse['s_time'] = $post['expect_time_s'];
        $nurse['remark'] = '订单管理系统上单记录';
        $save_nurse = M('nurse_use')->add($nurse);
        if(!$save_nurse){
            M('nurse_use')->add($nurse);
        }

        echo "<script>alert('签定成功！');window.location.href='" . __MODULE__ . "/Order/covenant_info/id/".$post['order_nurse_id']."';</script>";
        exit;



    }
    //合同修改
    public function edit_covenant(){
        $post = I('post.');
        $have_covenant = M('order_covenant')->where('order_nurse_id='.$post['order_nurse_id'].'')->find();
        if(!$have_covenant){
            echo "<script>alert('还未签订合同！');window.location.href='" . __MODULE__ . "/Order/info/id/".$post['order_nurse_id']."';</script>";
            exit;
        }
        $post['add_mac'] = $this->GetMacAddr(PHP_OS);
        $order_covenant = M('order_covenant')->where('id='.$post['order_covenant_id'].'')->save($post);

        if($order_covenant===false){
            M('order_covenant')->where('id='.$post['order_covenant_id'].'')->save($post);
        }

        $delete = M('order_expect_pay')->where('covenant_id='.$post['order_covenant_id'].'')->delete();
        if($delete===false){
            M('order_expect_pay')->where('covenant_id='.$post['order_covenant_id'].'')->delete();
        }

        $add_time['add_time'] = time();
        for($i=0;$i<sizeof($post['expect_time']);$i++){
            $order_expect_pay[$i]['covenant_id'] = $post['order_covenant_id'];
            $order_expect_pay[$i]['order_nurse_id'] = $post['order_nurse_id'];
            $order_expect_pay[$i]['expect_time'] = $post['expect_time'][$i];
            $order_expect_pay[$i]['expect_money'] = $post['expect_money'][$i];
            $order_expect_pay[$i]['add_time'] = $add_time['add_time'];
            $order_expect_pay[$i]['mac'] = $post['add_mac'];
        }


        $expect_pay = M('order_expect_pay')->addAll($order_expect_pay);
        if(!$expect_pay){
            M('order_expect_pay')->addAll($order_expect_pay);
        }

        $order_nurse_re = M('order_nurse_re')->where('order_id='.$post['order_nurse_id'].'')->find();
        $nurse['s_time'] = $post['expect_time_s'];
        $save_nurse = M('nurse_use')->where('nurse_id='.$order_nurse_re['nurse_id'].' and add_time="'.$have_covenant['add_time'].'"')->save($nurse);
        if(!$save_nurse){
            M('nurse_use')->where('nurse_id='.$order_nurse_re['nurse_id'].' and add_time="'.$have_covenant['add_time'].'"')->save($nurse);
        }

        echo "<script>alert('修改成功！');window.location.href='" . __MODULE__ . "/Order/covenant_info/id/".$post['order_nurse_id']."';</script>";
        exit;

    }


// 合同详情
    public function covenant_info(){
        if(!I('get.id')){
            echo "<script>alert('地址异常！');history.back();</script>";
            exit;
        }

        $covenant = M('order_covenant')->where('order_nurse_id='.I('get.id').'')->find();
        if(!$covenant){
            echo "<script>alert('地址异常！');history.back();</script>";
            exit;
        }
        $info = M('order_nurse')->where('id='.I('get.id').'')->find();
        $sales_name = M('order_user')->where('id='.$info['sales_id'].'')->find();

        $type = ['','月嫂','育儿嫂','保姆','钟点工','月子导师'];
        $type[10] = '其他业务';
        $info['type_name'] = $type[$info['type']];
        $info['sales_name'] = $sales_name['real_name']?$sales_name['real_name']:'还未派单点击→';
        $covenant['covenant_img1'] = explode(',',$covenant['covenant_img1']);
        $covenant['covenant_img2'] = explode(',',$covenant['covenant_img2']);
        $nurses = M('order_nurse_re')->field('order_nurse_re.id,nurse.name')->join('left join nurse ON nurse.id=order_nurse_re.nurse_id')->where('order_nurse_re.order_id='.I('get.id').'')->select();

        $this->assign('nurses',$nurses);
        $this->assign('covenant',$covenant);
        $this->assign('info',$info);
        $this->display();
    }
    public function covenant_edit(){
        if(!I('get.id')){
            echo "<script>alert('地址异常！');history.back();</script>";
            exit;
        }

        $covenant = M('order_covenant')->where('order_nurse_id='.I('get.id').'')->find();
        if(!$covenant){
            echo "<script>alert('地址异常！');history.back();</script>";
            exit;
        }
        $info = M('order_nurse')->where('id='.I('get.id').'')->find();
        $sales_name = M('order_user')->where('id='.$info['sales_id'].'')->find();

        $type = ['','月嫂','育儿嫂','保姆','钟点工','月子导师'];
        $type[10] = '其他业务';
        $info['type_name'] = $type[$info['type']];
        $info['sales_name'] = $sales_name['real_name']?$sales_name['real_name']:'还未派单点击→';
        $covenant['covenant_img1'] = explode(',',$covenant['covenant_img1']);
        $covenant['covenant_img2'] = explode(',',$covenant['covenant_img2']);
        $nurses = M('order_nurse_re')->field('order_nurse_re.id,nurse.name')->join('left join nurse ON nurse.id=order_nurse_re.nurse_id')->where('order_nurse_re.order_id='.I('get.id').'')->select();
        $covenant['expect_pay'] = M('order_expect_pay')->where('order_nurse_id='.I('get.id').'')->select();
        $this->assign('nurses',$nurses);
        $this->assign('covenant',$covenant);
        $this->assign('info',$info);
        $this->display();
    }


    public function body_test(){
            if(!I('get.id')){
                echo "<script>alert('地址异常！');history.back();</script>";
                exit;
            }
            $info = M('order_nurse')->where('id='.I('get.id').'')->find();
            if(!$info){
                echo "<script>alert('地址异常！');history.back();</script>";
                exit;
            }

            $sales_name = M('order_user')->where('id='.$info['sales_id'].'')->find();

            $type = ['','月嫂','育儿嫂','保姆','钟点工','月子导师'];
            $type[10] = '其他业务';
            $info['type_name'] = $type[$info['type']];
            $info['sales_name'] = $sales_name['real_name']?$sales_name['real_name']:'还未派单点击→';

            $nurses = M('order_nurse_re')->field('order_nurse_re.id,nurse.name')->join('left join nurse ON nurse.id=order_nurse_re.nurse_id')->where('order_nurse_re.order_id='.I('get.id').'')->select();

            $order_body_test_have_undill=0;
            $order_body_test = M('order_body_test')->where('order_nurse_id='.I('get.id').'')->order('add_time desc')->select();
            foreach($order_body_test as $k=>$v){
                $order_body_test[$k]['test_img'] = explode(',',$v['test_img']);
                if(!$v['test_time']){
                    $order_body_test_have_undill = 1;
                }
                if(!$v['test_img']){
                    $order_body_test_have_undill = 1;
                }
            }
            $this->assign('order_body_test_have_undill',$order_body_test_have_undill);
            $this->assign('nurses',$nurses);
            $this->assign('info',$info);
            $this->assign('order_body_test',$order_body_test);
            $this->display();

    }

    public function edit_body_test(){
        if(!I('post.order_nurse_id')){
            echo "<script>alert('地址异常！');history.back();</script>";
            exit;
        }
        $post = I('post.');
        if(!$post['order_body_test_id']){
            $have_body_test = M('order_body_test')->where('order_nurse_id='.$post['order_nurse_id'].' and  (ISNULL(test_time) OR test_time="")')->find();
            if($have_body_test){
                echo "<script>alert('有未被处理的体检报告！');history.back();</script>";
                exit;
            }

            if($_FILES['test_img1']['tmp_name']) {
                $upload = new \Think\Upload();// 实例化上传类
                $upload->maxSize = 1048576;// 设置附件上传大小
                $upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
                $upload->savePath = 'Order/covenant/'; // 设置附件上传目录
                $upload->subName = ''; // 设置附件上传目录
                $info = $upload->uploadOne($_FILES['test_img1']);
                if (!$info) {
                    $error = $upload->getError();
                    echo "<script>alert('".$error."');</script>";
                } else {
                    $imgPath = $info['savepath'] . $info['savename'];
                    $post['test_img'] .= $imgPath.',';
                }
            }
            $post['add_time'] = time();
            $post['mac'] = $this->GetMacAddr(PHP_OS);

            $save = M('order_body_test')->add($post);

        }else{

            if($_FILES['test_img1']['tmp_name']) {
                $upload = new \Think\Upload();// 实例化上传类
                $upload->maxSize = 1048576;// 设置附件上传大小
                $upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
                $upload->savePath = 'Order/covenant/'; // 设置附件上传目录
                $upload->subName = ''; // 设置附件上传目录
                $info = $upload->uploadOne($_FILES['test_img1']);
                if (!$info) {
                    $error = $upload->getError();
                    echo "<script>alert('".$error."');</script>";
                } else {
                    $imgPath = $info['savepath'] . $info['savename'];
                    $post['test_img'] .= $imgPath.',';
                }
            }

            $where['id'] = $post['order_body_test_id'];
            $save = M('order_body_test')->where($where)->save($post);
        }

        if($save!==false){

            echo "<script>alert('保存成功！');window.location.href='" . __MODULE__ . "/Order/body_test/id/".$post['order_nurse_id']."';</script>";
            exit;
        }else{
            echo "<script>alert('保存失败！');history.back();</script>";
            exit;

        }
    }


    // 投保
    public function safe(){
        if(!I('get.id')){
            echo "<script>alert('地址异常！');history.back();</script>";
            exit;
        }
        $info = M('order_nurse')->where('id='.I('get.id').'')->find();
        if(!$info){
            echo "<script>alert('地址异常！');history.back();</script>";
            exit;
        }

        $sales_name = M('order_user')->where('id='.$info['sales_id'].'')->find();

        $type = ['','月嫂','育儿嫂','保姆','钟点工','月子导师'];
        $type[10] = '其他业务';
        $info['type_name'] = $type[$info['type']];
        $info['sales_name'] = $sales_name['real_name']?$sales_name['real_name']:'还未派单点击→';

        $nurses = M('order_nurse_re')->field('order_nurse_re.id,nurse.name')->join('left join nurse ON nurse.id=order_nurse_re.nurse_id')->where('order_nurse_re.order_id='.I('get.id').'')->select();

        $order_safe_test_have_un=0;
        $order_safe = M('order_safe')->where('order_nurse_id='.I('get.id').'')->order('add_time desc')->select();
        foreach($order_safe as $k=>$v){
            $order_safe[$k]['test_img'] = explode(',',$v['test_img']);
            if(!$v['true_safe_time']){
                $order_safe_test_have_un = 1;
            }
        }
        $this->assign('order_safe_test_have_un',$order_safe_test_have_un);
        $this->assign('nurses',$nurses);
        $this->assign('info',$info);
        $this->assign('order_safe',$order_safe);
        $this->display();

    }



    public function edit_safe(){
        if(!I('post.order_nurse_id')){
            echo "<script>alert('地址异常！');history.back();</script>";
            exit;
        }
        $post = I('post.');
        if(!$post['order_safe_id']){
            $have_body_test = M('order_safe')->where('order_nurse_id='.$post['order_nurse_id'].' and  (ISNULL(buy_safe_time) OR buy_safe_time="")')->find();
            if($have_body_test){
                echo "<script>alert('有未被处理的投报计划！');history.back();</script>";
                exit;
            }

            if($_FILES['test_img1']['tmp_name']) {
                $upload = new \Think\Upload();// 实例化上传类
                $upload->maxSize = 1048576;// 设置附件上传大小
                $upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
                $upload->savePath = 'Order/covenant/'; // 设置附件上传目录
                $upload->subName = ''; // 设置附件上传目录
                $info = $upload->uploadOne($_FILES['test_img1']);
                if (!$info) {
                    $error = $upload->getError();
                    echo "<script>alert('".$error."');</script>";
                } else {
                    $imgPath = $info['savepath'] . $info['savename'];
                    $post['test_img'] .= $imgPath.',';
                }
            }
            $post['add_time'] = time();
            $post['mac'] = $this->GetMacAddr(PHP_OS);

            $save = M('order_safe')->add($post);

        }else{

            if($_FILES['test_img1']['tmp_name']) {
                $upload = new \Think\Upload();// 实例化上传类
                $upload->maxSize = 1048576;// 设置附件上传大小
                $upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
                $upload->savePath = 'Order/covenant/'; // 设置附件上传目录
                $upload->subName = ''; // 设置附件上传目录
                $info = $upload->uploadOne($_FILES['test_img1']);
                if (!$info) {
                    $error = $upload->getError();
                    echo "<script>alert('".$error."');</script>";
                } else {
                    $imgPath = $info['savepath'] . $info['savename'];
                    $post['test_img'] .= $imgPath.',';
                }
            }

            $where['id'] = $post['order_safe_id'];
            $save = M('order_safe')->where($where)->save($post);
        }

        if($save!==false){

            echo "<script>alert('保存成功！');window.location.href='" . __MODULE__ . "/Order/safe/id/".$post['order_nurse_id']."';</script>";
            exit;
        }else{
            echo "<script>alert('保存失败！');history.back();</script>";
            exit;

        }
    }

    //催款

    public function expect(){
        if(!I('get.id')){
            echo "<script>alert('地址异常！');history.back();</script>";
            exit;
        }
        $info = M('order_nurse')->where('id='.I('get.id').'')->find();
        if(!$info){
            echo "<script>alert('地址异常！');history.back();</script>";
            exit;
        }

        $sales_name = M('order_user')->where('id='.$info['sales_id'].'')->find();

        $type = ['','月嫂','育儿嫂','保姆','钟点工','月子导师'];
        $type[10] = '其他业务';
        $info['type_name'] = $type[$info['type']];
        $info['sales_name'] = $sales_name['real_name']?$sales_name['real_name']:'还未派单点击→';

        $nurses = M('order_nurse_re')->field('order_nurse_re.id,nurse.name')->join('left join nurse ON nurse.id=order_nurse_re.nurse_id')->where('order_nurse_re.order_id='.I('get.id').'')->select();

        $order_expect_pay = M('order_expect_pay')->where('order_nurse_id='.I('get.id').'')->order('add_time desc,id asc')->select();

        foreach($order_expect_pay as $k=>$v){
            $order_expect_pay[$k]['is_eq'] = $order_expect_pay[0]['expect_money']<=$order_expect_pay[0]['pay_money']?1:0;
            $order_expect_pay[$k]['press'] = M('order_expect_press')->where('expect_id='.$v['id'].'')->order('add_time desc')->select();
        }
        $this->assign('nurses',$nurses);
        $this->assign('info',$info);
        $this->assign('order_expect_pay',$order_expect_pay);
        $this->assign('today',date('Y-m-d'));
        $this->display();
    }

    //添加催款
    public function addExpect_press(){
        $post = I('post.');
        $post['add_time'] = time();
        $post['mac'] = $this->GetMacAddr(PHP_OS);
        $post['employee_name'] = $_SESSION[C('ORDER_AUTH_KEY')]['real_name'];
        $add =  M('order_expect_press')->add($post);
        if($add){
            if($post['is_expect']==1){
                $pay = M('order_expect_pay')->where('id='.$post['expect_id'].'')->find();
                $save['pay_money'] = $post['pay_money']+$pay['pay_money'];
                $save_mod = M('order_expect_pay')->where('id='.$post['expect_id'].'')->save($save);
                if($save_mod===false){
                    M('order_expect_pay')->where('id='.$post['expect_id'].'')->save($save);
                }
            }
            echo "<script>alert('保存成功！');window.location.href='" . __MODULE__ . "/Order/expect/id/".$post['nurse_id']."';</script>";
            exit;
        }else{
            echo "<script>alert('保存失败！');history.back();</script>";
            exit;
        }
    }
    //添加付款
    public function addExpect(){
        $post = I('post.');
        if(!$post['expect_id_add']){
            $covenant_id = M('order_covenant')->field('id')->where('order_nurse_id='.$post['order_nurse_id'].'')->find();
            $post['covenant_id'] = $covenant_id;
            $post['add_time'] = time();
            $post['mac'] = $this->GetMacAddr(PHP_OS);
            $add = M('order_expect_pay')->add($post);
        }else{
            $add = M('order_expect_pay')->where('id='.$post['expect_id_add'].'')->save($post);
        }

        if($add!==false){
            echo "<script>alert('保存成功！');window.location.href='" . __MODULE__ . "/Order/expect/id/".$post['order_nurse_id']."';</script>";
            exit;
        }else{
            echo "<script>alert('保存失败！');history.back();</script>";
            exit;
        }

    }

    //上户前培训页面
    public function training(){
        if(!I('get.id')){
            echo "<script>alert('地址异常！');history.back();</script>";
            exit;
        }
        $info = M('order_nurse')->where('id='.I('get.id').'')->find();
        if(!$info){
            echo "<script>alert('地址异常！');history.back();</script>";
            exit;
        }

        $sales_name = M('order_user')->where('id='.$info['sales_id'].'')->find();

        $type = ['','月嫂','育儿嫂','保姆','钟点工','月子导师'];
        $type[10] = '其他业务';
        $info['type_name'] = $type[$info['type']];
        $info['sales_name'] = $sales_name['real_name']?$sales_name['real_name']:'还未派单点击→';

        $nurses = M('order_nurse_re')->field('order_nurse_re.id,nurse.name')->join('left join nurse ON nurse.id=order_nurse_re.nurse_id')->where('order_nurse_re.order_id='.I('get.id').'')->select();

        $training = M('order_training')->where('order_nurse_id='.I('get.id').'')->order('add_time desc')->select();
        $this->assign('nurses',$nurses);
        $this->assign('info',$info);
        $this->assign('training',$training);
        $this->display();
    }

    //添加上户培训
    public function addTraining(){
        $post = I('post.');
        $post['add_time'] = time();
        $post['mac'] = $this->GetMacAddr(PHP_OS);
        $add = M('order_training')->add($post);
        if($add){
            echo "<script>alert('添加成功！');window.location.href='" . __MODULE__ . "/Order/training/id/".$post['order_nurse_id']."';</script>";
            exit;
        }else{
            echo "<script>alert('添加失败！');history.back();</script>";
            exit;
        }
    }


    //提醒上户
    public function go_customer(){
        $nurse_status = M('order_nurse')->where('id='.I('get.id').'')->find();
        if($nurse_status['status']!=2){
            echo "<script>alert('已上户或者未达到上户要求！');history.back();</script>";
            exit;
        }

        $save['status'] = 3;
        $save['status_3_time'] = time();
        $save_status = M('order_nurse')->where('id='.I('get.id').'')->save($save);
        if($save_status!==false){
            $save['true_expect_time_b'] = I('get.true_expect_time_b');
            $save_mod = M('order_covenant')->where('order_nurse_id='.I('get.id').'')->save($save);
            if($save_mod===false){
                M('order_covenant')->where('order_nurse_id='.I('get.id').'')->save($save);
            }

            $have_covenant = M('order_covenant')->where('order_nurse_id='.I('get.id').'')->find();
            $order_nurse_re = M('order_nurse_re')->where('order_id='.I('get.id').'')->find();


            $save_nurse_supervisor['next_supervisor_time'] = date('Y-m-d',strtotime(I('get.true_expect_time_b'))+86400);
            M('order_nurse')->where('id='.I('get.id').'')->save($save_nurse_supervisor);//上户后的督导时间


            $nurse['b_time'] = $save['true_expect_time_b'];
            $save_nurse = M('nurse_use')->where('nurse_id='.$order_nurse_re['nurse_id'].' and add_time="'.$have_covenant['add_time'].'"')->save($nurse);
            if(!$save_nurse){
                M('nurse_use')->where('nurse_id='.$order_nurse_re['nurse_id'].' and add_time="'.$have_covenant['add_time'].'"')->save($nurse);
            }

            if(I('get.type')=='training'){
                echo "<script>alert('上户成功！');window.location.href='" . __MODULE__ . "/Order/training/id/".I('get.id')."';</script>";
                exit;
            }
            echo "<script>alert('上户成功！');window.location.href='" . __MODULE__ . "/Order/oList/type/3';</script>";
            exit;
        }else {
            echo "<script>alert('操作失败，请重试！');history.back();</script>";
            exit;
        }
    }


    //督导
    public function supervisor(){
        if(!I('get.id')){
            echo "<script>alert('地址异常！');history.back();</script>";
            exit;
        }
        $info = M('order_nurse')->where('id='.I('get.id').'')->find();
        if(!$info){
            echo "<script>alert('地址异常！');history.back();</script>";
            exit;
        }

        $sales_name = M('order_user')->where('id='.$info['sales_id'].'')->find();

        $type = ['','月嫂','育儿嫂','保姆','钟点工','月子导师'];
        $type[10] = '其他业务';
        $info['type_name'] = $type[$info['type']];
        $info['sales_name'] = $sales_name['real_name']?$sales_name['real_name']:'还未派单点击→';

        $nurses = M('order_nurse_re')->field('order_nurse_re.id,nurse.name')->join('left join nurse ON nurse.id=order_nurse_re.nurse_id')->where('order_nurse_re.order_id='.I('get.id').'')->select();

        $supervisor = M('order_supervisor')->where('order_nurse_id='.I('get.id').'')->order('add_time desc')->select();
        $this->assign('nurses',$nurses);
        $this->assign('info',$info);
        $this->assign('supervisor',$supervisor);
        $this->assign('today',date('Y-m-d'));
        $this->assign('today_add_7',date('Y-m-d',strtotime('+ 1week')));
        $this->display();
    }



    //添加督导
    public function addSupervisor(){
        $post = I('post.');
        if($post){
            $post['add_time'] = time();
            $post['employee_name'] = $_SESSION[C('ORDER_AUTH_KEY')]['real_name'];
            $post['mac'] = $this->GetMacAddr(PHP_OS);
            $add = M('order_supervisor')->add($post);
            if($add){
                $next_supervisor_time['next_supervisor_time'] = $post['next_supervisor_time'];
                $save =  M('order_nurse')->where('id='.$post['order_nurse_id'].'')->save($next_supervisor_time);
                if($save===false){
                    M('order_nurse')->where('id='.$post['order_nurse_id'].'')->save($next_supervisor_time);
                }
                echo "<script>alert('添加成功！');window.location.href='" . __MODULE__ . "/Order/supervisor/id/".$post['order_nurse_id'].".html';</script>";
                exit;
            }else{
                echo "<script>alert('添加失败！');history.back();</script>";
                exit;
            }
        }else{
            echo "<script>alert('网络异常！');history.back();</script>";
            exit;
        }
    }


//变更售后单页面
    public function to_customer_service(){
        if(!I('get.id')){
            echo "<script>alert('地址异常！');history.back();</script>";
            exit;
        }
        $info = M('order_nurse')->where('id='.I('get.id').'')->find();
        if(!$info){
            echo "<script>alert('地址异常！');history.back();</script>";
            exit;
        }

        $sales_name = M('order_user')->where('id='.$info['sales_id'].'')->find();

        $type = ['','月嫂','育儿嫂','保姆','钟点工','月子导师'];
        $type[10] = '其他业务';
        $info['type_name'] = $type[$info['type']];
        $info['sales_name'] = $sales_name['real_name']?$sales_name['real_name']:'还未派单点击→';

        $nurse_re = M('order_nurse_re')->field('order_nurse_re.id as id,nurse.name as name')->join('left join nurse ON nurse.id=order_nurse_re.nurse_id')->where('order_nurse_re.order_id='.I('get.id').'')->select();

        $nurse_count = M('order_nurse_re')->where('order_id='.I('get.id').'')->count();
        $permission3 = M('order_user')->where('permission=3')->select();
        $covenant = M('order_covenant')->where('order_nurse_id='.I('get.id').'')->find();
        if($covenant['customer_service_time']<date('Y-m-d')){
            $covenant['can']=1;
        }

        $this->assign('permission3',$permission3);
        $this->assign('nurse_count',$nurse_count);
        $this->assign('nurse_re',$nurse_re);
        $this->assign('covenant',$covenant);
        $this->assign('info',$info);
        $this->display();
    }
    public function add_customer_service(){
        $old_order = M('order_nurse')->where('id='.I('post.is_customer_service').'')->find();
        unset($old_order['id']);
        unset($old_order['covenant_time']);
        unset($old_order['visit_next_time']);
        $old_order['service_reason'] = I('post.service_reason');
        $old_order['service_ask'] = I('post.service_ask');
        $old_order['sales_id'] = I('post.sales_id');
        $old_order['is_customer_service'] = I('post.is_customer_service');
        $old_order['add_time'] = time();
        $old_order['status'] = 1;
        $old_order['add_employee'] = $_SESSION[C('ORDER_AUTH_KEY')]['real_name'] ;
        $old_order['add_mac'] = $this->GetMacAddr(PHP_OS) ;

        $add = M('order_nurse')->add($old_order);
        if($add){



            $order_20['status'] = 20;
            $order_20['status_20_time'] = time();
            $save = M('order_nurse')->where('id='.I('post.is_customer_service').'')->save($order_20);
            if($save===false){
                M('order_nurse')->where('id='.I('post.is_customer_service').'')->save($order_20);
            }

            $order_nurse_re['status'] = 1;
            $save_re = M('order_nurse_re')->where('order_id='.I('post.is_customer_service').'')->save($order_nurse_re);
            if($save_re===false){
                M('order_nurse')->where('order_id='.I('post.is_customer_service').'')->save($order_nurse_re);
            }

            $have_covenant = M('order_covenant')->where('order_nurse_id='.I('post.is_customer_service').'')->find();
            $order_nurse_re = M('order_nurse_re')->where('order_id='.I('post.is_customer_service').'')->find();
            $nurse['s_time'] = date('Y-m-d');
            $save_nurse = M('nurse_use')->where('nurse_id='.$order_nurse_re['nurse_id'].' and add_time="'.$have_covenant['add_time'].'"')->save($nurse);
            if(!$save_nurse){
                M('nurse_use')->where('nurse_id='.$order_nurse_re['nurse_id'].' and add_time="'.$have_covenant['add_time'].'"')->save($nurse);
            }

            echo "<script>alert('生成成功，即将跳转到新的订单！');window.location.href='" . __MODULE__ . "/Order/info/id/".$add."';</script>";
            exit;
        }else{
            echo "<script>alert('重新生成失败，请重试！');history.back();</script>";
            exit;
        }
    }

    //提醒下户
    public function out_customer(){
        $nurse_status = M('order_nurse')->where('id='.I('get.id').'')->find();
        if($nurse_status['status']!=3){
            echo "<script>alert('已下户或者未达到下户要求！');history.back();</script>";
            exit;
        }

        $save['status'] = 4;
        $save['status_4_time'] = time();
        $save_status = M('order_nurse')->where('id='.I('get.id').'')->save($save);
        if($save_status!==false){
            $save['true_expect_time_s'] = I('get.true_expect_time_s');
            $save_mod = M('order_covenant')->where('order_nurse_id='.I('get.id').'')->save($save);
            if($save_mod===false){
                M('order_covenant')->where('order_nurse_id='.I('get.id').'')->save($save);
            }
            $nurse_re['status'] = 1;
            $save_mod_re = M('order_nurse_re')->where('order_id='.I('get.id').'')->save($nurse_re);
            if($save_mod_re===false){
                M('order_nurse_re')->where('order_id='.I('get.id').'')->save($nurse_re);
            }



            $have_covenant = M('order_covenant')->where('order_nurse_id='.I('get.id').'')->find();
            $order_nurse_re = M('order_nurse_re')->where('order_id='.I('get.id').'')->find();
            $nurse['s_time'] = $save['true_expect_time_s'];
            $save_nurse = M('nurse_use')->where('nurse_id='.$order_nurse_re['nurse_id'].' and add_time="'.$have_covenant['add_time'].'"')->save($nurse);
            if(!$save_nurse){
                M('nurse_use')->where('nurse_id='.$order_nurse_re['nurse_id'].' and add_time="'.$have_covenant['add_time'].'"')->save($nurse);
            }

            echo "<script>alert('下户成功！');window.location.href='" . __MODULE__ . "/Order/info/id/".I('get.id')."';</script>";
            exit;
        }else {
            echo "<script>alert('操作失败，请重试！');history.back();</script>";
            exit;
        }
    }
    //结算
    public function balance(){
        if(!I('get.id')){
            echo "<script>alert('地址异常！');history.back();</script>";
            exit;
        }
        $info = M('order_nurse')->where('id='.I('get.id').'')->find();
        if(!$info){
            echo "<script>alert('地址异常！');history.back();</script>";
            exit;
        }

        $sales_name = M('order_user')->where('id='.$info['sales_id'].'')->find();

        $type = ['','月嫂','育儿嫂','保姆','钟点工','月子导师'];
        $type[10] = '其他业务';
        $info['type_name'] = $type[$info['type']];
        $info['sales_name'] = $sales_name['real_name']?$sales_name['real_name']:'还未派单点击→';

        $nurses = M('order_nurse_re')->field('order_nurse_re.id,nurse.name')->join('left join nurse ON nurse.id=order_nurse_re.nurse_id')->where('order_nurse_re.order_id='.I('get.id').'')->select();
        $covenant = M('order_covenant')->where('order_nurse_id='.I('get.id').'')->find();
        $this->assign('nurses',$nurses);
        $this->assign('info',$info);
        $this->assign('covenant',$covenant);
        $this->assign('today',date('Y-m-d'));
        $this->display();
    }

    //结算操作
    public function balance_over(){
        if(I('post.order_status')!=4){
            echo "<script>alert('未达到结算要求！');history.back();</script>";
            exit;
        }
        $post = I('post.');

        $where['order_nurse_id'] = $post['order_nurse_id'];
        $post['balance_mac'] = $this->GetMacAddr(PHP_OS);

        $save = M('order_covenant')->where($where)->save($post);
        if($save===false){
            echo "<script>alert('结算失败，请重试！');history.back();</script>";
            exit;
        }else{
            $nurse['status'] = 5;
            $nurse['status_5_time'] = time();
            $save_nurse = M('order_nurse')->where('id='.$post['order_nurse_id'].'')->save($nurse);
            if($save_nurse===false){
                M('order_nurse')->where('id='.$post['order_nurse_id'].'')->save($nurse);
            }

            echo "<script>alert('结算成功');window.location.href='".__MODULE__."/Order/oList/type/5.html';</script>";
            exit;
        }



    }

    // 新增招生
    public function addTrainee(){
        if(I('post.')){
            $post = I("post.");
            if($post['trainee_id']){
                $map['source'] = $post['source'];
                $map['source_name'] = $post['source']=='客服'?'':$post['source_name'];
                $map['name'] = $post['name'];
                $map['age'] = $post['age'];
                $map['phone'] = $post['phone'];
                $map['detail'] = $post['detail'];
                $map['employee_name'] = $post['employee_name'];
                $map['status'] = 0;//未转换
                $map['is_order'] = 1;//订单转换
                $trainee_name = M('tra_trainee')->where('name = "'.$post['name'].'" and phone = "'.$post['phone'].'"   and id!='.$post['trainee_id'].'')->find();
                if($trainee_name){
                    echo "<script>alert('已存在该姓名或电话号码的学员！');history.back();</script>";
                    exit;
                }
                $trainee_id = M('tra_trainee')->where('id='.$post['trainee_id'].'')->save($map);

            }else{
                $map['source'] = $post['source'];
                $map['source_name'] = $post['source']=='客服'?'':$post['source_name'];
                $map['name'] = $post['name'];
                $map['age'] = $post['age'];
                $map['phone'] = $post['phone'];
                $map['detail'] = $post['detail'];
                $map['employee_name'] = $post['employee_name'];
                $map['status'] = 0;//未转换
                $map['is_order'] = 1;//订单转换
                $map['add_time'] = time();
                $trainee_name = M('tra_trainee')->where('name = "'.$post['name'].'" and phone = "'.$post['phone'].'" ')->find();
                if($trainee_name){
                    echo "<script>alert('已存在该姓名或电话号码的学员！');history.back();</script>";
                    exit;
                }
                $trainee_id = M('tra_trainee')->add($map);
            }

            if($trainee_id!==false){
                echo "<script>alert('添加成功');window.location.href='".__MODULE__."/Order/trainee.html';</script>";
                exit;

            }else{
                echo "<script>alert('添加失败！');history.back();</script>";
                exit;
            }
        }else{
            if(I('get.id')){
                $trainee = M('tra_trainee')->where('id='.I('get.id').'')->find();
                $this->assign('trainee',$trainee);
            }
            $this->display();

        }

    }

    //放弃订单
    public function edit_die_time(){
        if(I('post.die_id')){
            $post = I('post.');

            $post['die_time'] = date('Y-m-d H:i:s');
            $post['die_mac'] = $this->GetMacAddr(PHP_OS);
            $post['status'] = 10;

            $save = M('order_nurse')->where('id='.$post['die_id'].'')->save($post);

            if($save!==false){
                $have_covenant = M('order_covenant')->where('order_nurse_id='.$post['die_id'].'')->find();
                if($have_covenant) {
                    $order_nurse_re = M('order_nurse_re')->where('order_id=' . $post['die_id'] . '')->find();
                    $nurse['s_time'] = date('Y-m-d');
                    $save_nurse = M('nurse_use')->where('nurse_id=' . $order_nurse_re['nurse_id'] . ' and add_time="' . $have_covenant['add_time'] . '"')->save($nurse);
                    if (!$save_nurse) {
                        M('nurse_use')->where('nurse_id=' . $order_nurse_re['nurse_id'] . ' and add_time="' . $have_covenant['add_time'] . '"')->save($nurse);
                    }

                    $order_nurse_re['status'] = 1;
                    $save_re = M('order_nurse_re')->where('order_id=' . $post['die_id'] . '')->save($order_nurse_re);
                    if ($save_re === false) {
                        M('order_nurse')->where('order_id=' . $post['die_id'] . '')->save($order_nurse_re);
                    }
                }

                echo "<script>alert('放弃成功！');window.location.href='" . __MODULE__ . "/Order/info/id/".$post['die_id']."';</script>";
                exit;
            }else{
                echo "<script>alert('放弃失败！');history.back();</script>";
                exit;
            }
        }else{
            echo "<script>alert('地址异常！');history.back();</script>";
            exit;
        }


    }


    //导出excel页面
    public function excel(){
        if($_SESSION[C('ORDER_AUTH_KEY')]['permission']!=1){
            echo "<script>alert('无权限！');history.back();</script>";
            exit;
        }

        $user_2 = M('order_user')->where('permission=1 OR permission = 2')->select();
        $user_3 = M('order_user')->where('permission=1 OR permission = 3')->select();
        $user_4 = M('order_user')->where('permission=1 OR permission = 4')->select();


        $this->assign('user_4',$user_4);
        $this->assign('user_3',$user_3);

        $this->assign('user_2',$user_2);
        $this->display();
    }

    public function excel_1(){

      if(I('get.type')==1){
         $where = 'status< 10 ';
          if(I('post.employee_name_1')){
              $where .= ' and add_employee ="'.I('post.employee_name_1').'"';
          }
          if(I('post.time_b_1')){
              $where .= ' and add_time >"'.strtotime(I('post.time_b_1')).'"';
          }
          if(I('post.time_s_1')){
              $where .= ' and add_time <"'.(strtotime(I('post.time_s_1'))+86400).'"';
          }
          if(I('post.type_1')){
              $where .= ' and type = '.I('post.type_1').'';
          }
          if(I('post.source_1')){
              $where .= ' and source ="'.I('post.source_1').'"';
          }

          $order_nurse = M('order_nurse')->where($where)->order('add_time desc')->select();
          $type = ['','月嫂','育儿嫂','保姆','钟点工','月子导师'];
          $type[10] = '其他业务';
          $str = "渠道来源\t客户姓名\t建单时间\t派单时间\t客户顾问\t销售顾问\t首次联系时间\t首次匹配时间\t签单时间\t服务费\t管理费\t上户日期\t下户日期\t是否回访\t是否售后\t是否购买保险\t是否体检\t订单类型\t联系方式\t最低价位\t最高价位\t放弃时间\t放弃原因\t\n";
          foreach($order_nurse as $k=>$v){
              $order_nurse[$k]['sales_name'] = M('order_user')->where('id='.$v['sales_id'].'')->find();
              $order_nurse[$k]['order_record'] = M('order_record')->where('order_id='.$v['id'].'')->order('add_time asc')->find();
              $order_nurse[$k]['order_covenant'] = M('order_covenant')->where('order_nurse_id='.$v['id'].'')->order('add_time asc')->find();
              $order_nurse[$k]['order_visit'] = M('order_visit')->where('order_nurse_id='.$v['id'].'')->find();
              $order_nurse[$k]['order_body_test'] = M('order_body_test')->where('order_nurse_id='.$v['id'].' and test_time!="" and test_img !=""')->order('add_time asc')->find();
              $order_nurse[$k]['order_safe'] = M('order_safe')->where('order_nurse_id='.$v['id'].' and true_safe_time!=""')->order('add_time asc')->find();
          }
          foreach($order_nurse as $k=>$v){
              $str .=  $v['source']."\t". $v['name']."\t".($v['add_time']?date('Y-m-d H:i:s',$v['add_time']):'')."\t".($v['status_1_time']?date('Y-m-d H:i:s',$v['status_1_time']):'')."\t".$v['add_employee']."\t".$v['sales_name']['real_name']."\t".($v['order_record']['add_time']?date('Y-m-d H:i:s',$v['order_record']['add_time']):'')."\t".($v['nurse_re_time']?date('Y-m-d H:i:s',$v['nurse_re_time']):'')."\t".($v['status_2_time']?date('Y-m-d H:i:s',$v['status_2_time']):'')."\t".$v['order_covenant']['service_charge']."\t".$v['order_covenant']['management_charge']."\t".$v['order_covenant']['true_expect_time_b']."\t".$v['order_covenant']['true_expect_time_s']."\t".($v['order_visit']?'是':'否')."\t".($v['is_customer_service']>0?'是售后':'否')."\t".($v['true_safe_time']?'已投保':'否')."\t".($v['order_body_test']?'已体检':'否')."\t".$type[$v['type']]."\t".$v['contact_way'].":".$v['contact_number']."\t".$v['price_l']."\t".$v['price_h']."\t".$v['die_time']."\t".$v['die_reason']."\t\n";
          }
          $str = iconv('utf-8','GBK',$str);


          $type_name = I('post.type_1')?$type[I('post.type_1')]:'';
          $filename = '呼叫中心'.I('post.time_b_1').'----'.I('post.time_s_1').$type_name.'详细报表.xls';

      }elseif(I('get.type')==2){
          $str = "客服顾问\t日期\t派单量\t总单量\t邀约成功率\t\n";
          $where = '(status = 1 OR status = 0) ';
          if(I('post.employee_name_1')){
              $where .= ' and add_employee ="'.I('post.employee_name_1').'"';
          }
          if(I('post.time_b_1')){
              $where .= ' and add_time >"'.strtotime(I('post.time_b_1')).'"';
          }
          if(I('post.time_s_1')){
              $where .= ' and add_time <"'.strtotime(I('post.time_s_1')).'"';
          }
          if(I('post.type_1')){
              $where .= ' and type = '.I('post.type_1').'';
          }
          if(I('post.source_1')){
              $where .= ' and source ="'.I('post.source_1').'"';
          }
          $field = 'add_employee,count(id) as id, sum(if(status= 1,1,0))  as is_1, FROM_UNIXTIME(add_time, "%Y-%m-%d") as date';
          $order_nurse = M('order_nurse')->field($field)->where($where)->group('add_employee,FROM_UNIXTIME(add_time, "%Y-%m-%d")')->select();
            foreach($order_nurse as $k=>$v){
                $one_h = round(($v['is_1']/$v['id']),4)*100;
                $str .=  $v['add_employee']."\t". $v['date']."\t".$v['is_1']."\t".$v['id']."\t".$one_h."%\t\n";
            }
          $str = iconv('utf-8','GBK',$str);
          $type = ['','月嫂','育儿嫂','保姆','钟点工','月子导师'];
          $type[10] = '其他业务';
          $type_name = I('post.type_1')?$type[I('post.type_1')]:'';
          $filename = '呼叫中心'.I('post.time_b_1').'----'.I('post.time_s_1').$type_name.'统计报表.xls';
      }

        $this->exportExcel($filename,$str);
    }

    public function excel_2()
    {
        if (I('get.type') == 1) {
            $str = "订单状态\t渠道来源\t客户姓名\t建单时间\t派单时间\t客户顾问\t销售顾问\t首次联系时间\t首次匹配时间\t签单时间\t服务费\t管理费\t上户日期\t下户日期\t是否回访\t是否售后\t是否购买保险\t是否体检\t订单类型\t联系方式\t最低价位\t最高价位\t\n";
            $where = 'status > 1 ';
            if (I('post.employee_name_1')) {
                $where .= ' and add_employee ="' . I('post.employee_name_1') . '"';
            }
            if (I('post.time_b_1')) {
                $where .= ' and add_time >"' . strtotime(I('post.time_b_1')) . '"';
            }
            if (I('post.time_s_1')) {
                $where .= ' and add_time <"' . strtotime(I('post.time_s_1')) . '"';
            }
            if (I('post.type_1')) {
                $where .= ' and type = ' . I('post.type_1') . '';
            }
            if (I('post.source_1')) {
                $where .= ' and source ="' . I('post.source_1') . '"';
            }

            $order_nurse = M('order_nurse')->where($where)->order('status asc')->select();
            $type = ['', '月嫂', '育儿嫂', '保姆', '钟点工', '月子导师'];
            $type[10] = '其他业务';
            $status_name = ['未派单','已派单','已签单','已上户','已下户','已结算'];
            $status_name[10] =  '已放弃';
            $status_name[20] =  '已售后';
            foreach($order_nurse as $k => $v){
                $order_nurse[$k]['sales_name'] = M('order_user')->where('id=' . $v['sales_id'] . '')->find();
                $order_nurse[$k]['order_record'] = M('order_record')->where('order_id=' . $v['id'] . '')->order('add_time asc')->find();
                $order_nurse[$k]['order_covenant'] = M('order_covenant')->where('order_nurse_id=' . $v['id'] . '')->find();
                $order_nurse[$k]['order_visit'] = M('order_visit')->where('order_nurse_id=' . $v['id'] . '')->find();
                $order_nurse[$k]['order_body_test'] = M('order_body_test')->where('order_nurse_id=' . $v['id'] . ' and test_time!="" and test_img !=""')->order('add_time asc')->find();
                $order_nurse[$k]['order_safe'] = M('order_safe')->where('order_nurse_id=' . $v['id'] . ' and true_safe_time!=""')->order('add_time asc')->find();
            }
            foreach ($order_nurse as $k => $v) {
                $str .= $status_name[$v['status']] . "\t" . $v['source'] . "\t" . $v['name'] . "\t" . ($v['add_time'] ? date('Y-m-d H:i:s', $v['add_time']) : '') . "\t" . ($v['status_1_time'] ? date('Y-m-d H:i:s', $v['status_1_time']) : '') . "\t" . $v['add_employee'] . "\t" . $v['sales_name']['real_name'] . "\t" . ($v['order_record']['add_time'] ? date('Y-m-d H:i:s', $v['order_record']['add_time']) : '') . "\t" . ($v['nurse_re_time'] ? date('Y-m-d H:i:s', $v['nurse_re_time']) : '') . "\t" . ($v['status_2_time'] ? date('Y-m-d H:i:s', $v['status_2_time']) : '') . "\t" . $v['order_covenant']['service_charge'] . "\t" . $v['order_covenant']['management_charge'] . "\t" . $v['order_covenant']['true_expect_time_b'] . "\t" . $v['order_covenant']['true_expect_time_s'] . "\t" . ($v['order_visit'] ? '是' : '否') . "\t" . ($v['is_customer_service'] > 0 ? '是售后' : '否') . "\t" . ($v['order_safe']? '已投保' : '否') . "\t" . ($v['order_body_test'] ? '已体检' : '否') . "\t" . $type[$v['type']] . "\t" . $v['contact_way'] . ':' . $v['contact_number']. "\t" . $v['price_l']. '\t' . $v['price_h']. "\t\n";
            }
            $str = iconv('utf-8', 'GBK', $str);

            $type_name = I('post.type_1') ? $type[I('post.type_1')] : '';
            $filename = '销售' . I('post.time_b_1') . '----' . I('post.time_s_1') . $type_name . '详细报表.xls';

        } elseif (I('get.type') == 2) {
            $str = "日期\t完成服务费\t完成管理费\t已收管理费\t派单总量\t签单总量\t跟单量\t售后单量\t掉单量\t签单率\t掉单率\t售后率\t\n";
            $where = '';

            if (I('post.employee_name_2')) {
                $where .= ' and order_nurse.sales_id =' . I('post.employee_name_2');
            }

            if (I('post.type_2')) {
                $where .= ' and order_nurse.type = ' . I('post.type_2') . '';
            }
            if (I('post.source_2')) {
                $where .= ' and order_nurse.source ="' . I('post.source_2') . '"';
            }
            I('post.time_s_2')>date('Y-m-d')?($time_s = date('Y-m-d')):($time_s = I('post.time_s_2'));
            for($day=I('post.time_b_2');$day<=$time_s;$day=date('Y-m-d',(strtotime($day)+86400))){
                $excel_1 = M('order_covenant')->field('sum(service_charge) as number')->join('order_nurse ON order_nurse.id=order_covenant.order_nurse_id')->where('order_covenant.add_time<"'.(strtotime($day)+86400).'"'.$where)->find();
                $excel_2 = M('order_covenant')->field('sum(management_charge) as number')->join('order_nurse ON order_nurse.id=order_covenant.order_nurse_id')->where('order_covenant.add_time<"'.(strtotime($day)+86400).'"'.$where)->find();
                $excel_3 = M('order_expect_pay')->field('sum(pay_money) as number')->join('order_nurse ON order_nurse.id=order_expect_pay.order_nurse_id')->where('order_expect_pay.add_time<"'.(strtotime($day)+86400).'"'.$where)->find();
                $excel_4 = M('order_nurse')->field('count(id) as number')->where('status_1_time<"'.(strtotime($day)+86400).'" and status<10'.$where)->find();
                $excel_5 = M('order_covenant')->field('count(order_covenant.id) as number')->join('order_nurse ON order_nurse.id=order_covenant.order_nurse_id')->where('order_covenant.add_time<"'.(strtotime($day)+86400).'"'.$where)->find();
                $excel_6 = M('order_nurse')->field('count(id) as number')->where('status_1_time<"'.(strtotime($day)+86400).'" and status_1_time>"'.strtotime($day).'"'.$where)->find();
                $excel_7 = M('order_nurse')->field('count(id) as number')->where('add_time<"'.(strtotime($day)+86400).'" and is_customer_service>0'.$where)->find();
                $excel_8 = M('order_nurse')->field('count(id) as number')->where('die_time<"'.date('Y-m-d H:i:s',(strtotime($day)+86400)).'" and status=10'.$where)->find();
                $excel_9 = round($excel_5['number']/$excel_4['number']*100,2);
                $excel_10 = round($excel_8['number']/$excel_4['number']*100,2);
                $excel_11 = round($excel_7['number']/$excel_5['number']*100,2);
                $str .= $day. "\t" .$excel_1['number'] . "\t" .$excel_2['number'] . "\t" .$excel_3['number'] . "\t" .$excel_4['number'] . "\t" .$excel_5['number'] . "\t" .$excel_6['number'] . "\t" .$excel_7['number'] . "\t" .$excel_8['number'] . "\t" .$excel_9 .'%'. "\t" .$excel_10.'%'. "\t" .$excel_11.'%'. "\t\n" ;
            }
            $str = iconv('utf-8', 'GBK', $str);
            $type = ['', '月嫂', '育儿嫂', '保姆', '钟点工', '月子导师'];
            $type[10] = '其他业务';
            $employee_name = M('order_user')->where('id='.I('post.employee_name_2').'')->find();
            $filename = '销售' . I('post.time_b_2') . '----' . I('post.time_s_2') .$employee_name['real_name'] . $type[I('post.type_2')].I('post.source_2').'统计报表.xls';
        }
        $this->exportExcel($filename, $str);
    }


    public function excel_3(){
        $where = '';
        $post = I('post.');
        if (I('post.type_3')) {
            $where .= ' and order_nurse.type = ' . I('post.type_3') . '';
        }
        if (I('post.source_3')) {
            $where .= ' and order_nurse.source ="' . I('post.source_3') . '"';
        }
        if (I('post.employee_name_3')) {
            $where .= ' and order_supervisor.employee_name ="' . I('post.employee_name_3') . '"';
        }
        if(I('get.type')==1){
            $str = "督导日期\t业务类别\t督导老师\t护理人员\t是否适应\t督导情况\t\n";
            $type = ['', '月嫂', '育儿嫂', '保姆', '钟点工', '月子导师'];
            $type[10] = '其他业务';

            $excel_1 = M('order_supervisor')->field('supervisor_time,order_nurse.type,supervisor_teacher,is_suit,order_supervisor.content,order_supervisor.order_nurse_id,nurse.name')->join('left join order_nurse ON order_nurse.id=order_supervisor.order_nurse_id')->join('left join order_nurse_re ON order_nurse_re.order_id=order_nurse.id')->join('left join nurse ON nurse.id=order_nurse_re.nurse_id')->where('order_supervisor.supervisor_time<="'.I('post.time_s_3').'"  and order_supervisor.supervisor_time>="'.I('post.time_b_3').'"')->order('supervisor_time asc')->select();
//            echo  M('order_supervisor')->getLastSql();die;
            foreach($excel_1 as $k=>$v){
                $str .= $v['supervisor_time']."\t".$type[$v['type']]."\t".$v['supervisor_teacher']."\t".$v['name']."\t".$v['is_suit']."\t".$v['content']."\t\n";
            }


            $str = iconv('utf-8', 'GBK', $str);

            $filename = '督导' . I('post.time_b_3') . '----' . I('post.time_s_3') .I('post.employee_name_3').$type[I('post.type_3')].I('post.source_3').'详细报表.xls';

        }else{
            $str = "日期\t督导次数\t适应次数\t适应率\t\n";
            I('post.time_s_3')>date('Y-m-d')?($time_s = date('Y-m-d')):($time_s = I('post.time_s_3'));
            for($day=I('post.time_b_3');$day<=$time_s;$day=date('Y-m-d',(strtotime($day)+86400))) {
                $excel_1 = M('order_supervisor')->field('count(order_supervisor.id) as number,count(if(is_suit="是",1,null)) as do')->join('order_nurse ON order_nurse.id=order_supervisor.order_nurse_id')->where('supervisor_time="'.$day.'"'.$where)->find();

                $excel_3 = round($excel_1['do']/$excel_1['number']*100,2);
                $str .= $day."\t".$excel_1['number']."\t".$excel_1['do']."\t".$excel_3.'%'."\t\n";

            }
            $str = iconv('utf-8', 'GBK', $str);
            $type = ['', '月嫂', '育儿嫂', '保姆', '钟点工', '月子导师'];
            $type[10] = '其他业务';
            $filename = '督导' . I('post.time_b_3') . '----' . I('post.time_s_3') .I('post.employee_name_3').$type[I('post.type_3')].I('post.source_3').'统计报表.xls';
        }

        $this->exportExcel($filename, $str);
    }


    public function excel_4(){
        $post = I('post.');

        if(I('get.type')==1){

        }else{

            $str = "日期\t体检次数\t体检合格次数\t体检合格率\t\n";
            $where = '';
            if($post['type_4']){
                $where = ' and order_nurse.type='.$post['type_4'];
            }
            if($post['nurse_name']){
                $body_test = M('order_body_test')->field('count(order_body_test.id) as test_number,count(if(order_body_test.is_pass="是",1,null)) as pass_number')->join('order_nurse ON order_nurse.id=order_body_test.order_nurse_id')->join('left join order_nurse_re ON order_nurse_re.order_id=order_body_test.order_nurse_id')->join('left join nurse ON nurse.id=order_nurse_re.nurse_id')->where('nurse.name="'.$post['nurse_name'].'" and  order_body_test.test_time<="'.I('post.time_s_4').'"  and order_body_test.test_time>="'.I('post.time_b_4').'"'.$where)->find();
            }else{
                $body_test = M('order_body_test')->field('count(order_body_test.id) as test_number,count(if(order_body_test.is_pass="是",1,null)) as pass_number')->join('order_nurse ON order_nurse.id=order_body_test.order_nurse_id')->where('order_body_test.test_time<="'.I('post.time_s_4').'"  and order_body_test.test_time>="'.I('post.time_b_4').'"'.$where)->find();
            }
            $odds = round($body_test['pass_number']/$body_test['test_number']*100,2);
            $str .= I('post.time_b_4').'~'.I('post.time_s_4')."\t".$body_test['test_number']."\t".$body_test['pass_number']."\t".$odds."\t\n";



            $str = iconv('utf-8', 'GBK', $str);
            $type = ['', '月嫂', '育儿嫂', '保姆', '钟点工', '月子导师'];
            $type[10] = '其他业务';
            if($post['nurse_name']!=''){
                $nurse_name = $post['nurse_name'];
            }
            $filename = $nurse_name.'体检' . I('post.time_b_4') . '----' . I('post.time_s_4') .$type[I('post.type_4')].'统计报表.xls';
        }
        $this->exportExcel($filename, $str);
    }



    public function excel_5()
    {
        if (I('get.type') == 1) {
            $str = "面试日期\t业务类别\t母婴顾问\t护理人员\t是否通过\t面试情况\t\n";



            $where = 'order_interview_info.interview_time >="'.I('post.time_b_5').'" and order_interview_info.interview_time <="'.I('post.time_s_5').'" and nurse_id>0';

            if (I('post.employee_name_5')) {
                $where .= ' and order_nurse.sales_id =' . I('post.employee_name_5');
            }

            if (I('post.type_5')) {
                $where .= ' and order_nurse.type = ' . I('post.type_5') . '';
            }
            if(I('post.nurse_name_5')){
                $where .= ' and order_interview_info.nurse_name="'.I('post.nurse_name_5').'"';
            }
            $field = 'order_interview_info.interview_time,order_nurse.type,order_user.real_name,order_interview_info.nurse_name,order_interview_info.is_pass,order_interview_info.expression';
            $type = ['', '月嫂', '育儿嫂', '保姆', '钟点工', '月子导师'];
            $type[10] = '其他业务';
            $interview_info = M('order_interview_info')->field($field)->join('order_interview ON order_interview_info.interview_id=order_interview.id')->join('order_nurse ON order_nurse.id=order_interview.order_nurse_id')->join('left join order_user ON order_user.id=order_nurse.sales_id')->where($where)->select();
            foreach($interview_info as $k=>$v){
                $str .= $v['interview_time']."\t".$type[$v['type']]."\t".$v['real_name']."\t".$v['nurse_name']."\t".($v['is_pass']==1?'通过':'未通过')."\t".$v['expression']."\t\n";
            }

            $str = iconv('utf-8', 'GBK', $str);

            $filename = '面试' . I('post.time_b_5') . '----' . I('post.time_s_5')  . $type[I('post.type_5')].I('post.nurse_name_5').'详细报表.xls';

        } elseif (I('get.type') == 2) {
            $str = "日期\t面试次数\t面试成功次数\t面试成功率\t\n";
            $where = 'order_interview_info.interview_time >="'.I('post.time_b_5').'" and order_interview_info.interview_time <="'.I('post.time_s_5').'" and nurse_id>0';

            if (I('post.employee_name_5')) {
                $where .= ' and order_nurse.sales_id =' . I('post.employee_name_5');
            }

            if (I('post.type_5')) {
                $where .= ' and order_nurse.type = ' . I('post.type_5') . '';
            }
            if(I('post.nurse_name_5')){
                $where .= ' and order_interview_info.nurse_name="'.I('post.nurse_name_5').'"';
            }


            $interview_info = M('order_interview_info')->field('count(interview_id) as number,count(if(order_interview_info.is_pass=1,1,null)) as pass_number')->join('order_interview ON order_interview_info.interview_id=order_interview.id')->join('order_nurse ON order_nurse.id=order_interview.order_nurse_id')->where($where)->find();

            $odds = round($interview_info['pass_number']/$interview_info['number']*100,2);
            $str .=  I('post.time_b_5').'~'.I('post.time_s_5')."\t".$interview_info['number']."\t".$interview_info['pass_number']."\t".$odds."%\t\n";

            $str = iconv('utf-8', 'GBK', $str);
            $type = ['', '月嫂', '育儿嫂', '保姆', '钟点工', '月子导师'];
            $type[10] = '其他业务';
            if(I('post.employee_name_5')) {
                $employee_name = M('order_user')->where('id=' . I('post.employee_name_5') . '')->find();
            }
            $filename = '面试' . I('post.time_b_5') . '----' . I('post.time_s_5')  . $type[I('post.type_5')].I('post.nurse_name_5').'统计报表.xls';
        }
        $this->exportExcel($filename, $str);
    }


    public function excel_6(){
        if (I('get.type') == 1) {


            $str = "业务类别\t客户姓名\t母婴顾问\t护理人员\t售后原因\t售后客户要求\t\n";

            $where = 'order_nurse.status_20_time >="'.strtotime(I('post.time_b_5')).'" and order_nurse.status_20_time <="'.(strtotime(I('post.time_s_5'))+86400).'"  and order_nurse.status=20';

            if (I('post.employee_name_6')) {
                $where .= ' and order_nurse.sales_id =' . I('post.employee_name_6');
            }

            if (I('post.type_6')) {
                $where .= ' and order_nurse.type = ' . I('post.type_6') . '';
            }

            $field = 'order_nurse.id,order_nurse.type,order_nurse.name as order_name,order_user.real_name,nurse.name as nurse_name';
            $type = ['', '月嫂', '育儿嫂', '保姆', '钟点工', '月子导师'];
            $type[10] = '其他业务';
            if(I('post.nurse_name_6')){
                $where .= ' and nurse.name="'.I('post.nurse_name_6').'"';
               $interview_info = M('order_nurse')->field($field)->join('left join order_user ON order_user.id=order_nurse.sales_id')->join('left join order_nurse_re ON order_nurse_re.order_id=order_nurse.id')->join('left join nurse ON nurse.id=order_nurse_re.nurse_id')->where($where)->select();

            }else{
                $interview_info = M('order_nurse')->field($field)->join('left join order_user ON order_user.id=order_nurse.sales_id')->join('left join order_nurse_re ON order_nurse_re.order_id=order_nurse.id')->join('left join nurse ON nurse.id=order_nurse_re.nurse_id')->where($where)->select();

            }

            foreach($interview_info as $k=>$v){
                $service = M('order_nurse')->field('order_nurse.service_reason,order_nurse.service_ask')->where('is_customer_service='.$v['id'].'')->find();
                $str .= $type[$v['type']]."\t".$v['order_name']."\t".$v['real_name']."\t".$v['nurse_name']."\t".$service['service_reason']."\t".$service['service_ask']."\t\n";
            }

            $str = iconv('utf-8', 'GBK', $str);
            if(I('post.employee_name_6')) {
                $employee_name = M('order_user')->where('id=' . I('post.employee_name_6') . '')->find();
            }

            $filename = $employee_name['real_name'].'售后' . I('post.time_b_6') . '----' . I('post.time_s_6')  . $type[I('post.type_6')].I('post.nurse_name_6').'详细报表.xls';

        } elseif (I('get.type') == 2) {

        }
        $this->exportExcel($filename, $str);
    }

}