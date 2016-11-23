<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends CommonController {
    //支付宝

    public function dealAl() {
        vendor('Alipay.Corefunction');
        vendor('Alipay.Md5function');
        vendor('Alipay.Notify');
        vendor('Alipay.Submit');
    }
    public function doalipay(){
        $this->dealAl();

        $alipay = I('post.');
        if($alipay['total_fee']==''|| !is_numeric($alipay['total_fee'])||$alipay['buy_name']==''||$alipay['phone']==''){
            echo "<script>alert('订单异常');window.location.history.go(-1);</script>";
            exit;
        }
        if($alipay['scale']==1){
            $scale_str = '全款';
        }else{
            $scale_str = '预付'.($alipay['scale']*100).'%';
        }
        if($alipay['ask']=='请认真填写，提交购买后会有工作人员与您联系'){
            $alipay['ask'] = '无';
        }
        $alipay['add_time'] = time();
        switch($alipay['total_fee_choice']){
            case 5880:
                $alipay['product'] = '小田螺（5880）'.$scale_str;
                $alipay['body'] = '小田螺5880元,六大基础护理+2*儿保+PICC百万保险一份+产房监测+待产包,小田螺：5-10个宝宝护理经验。服务周期：26天';
                break;
            case 7880:
                $alipay['product'] = '大田螺（7880）'.$scale_str;
                $alipay['body'] = '大田螺7880元,六大基础护理+4*儿保+PICC百万保险一份+产房监测+待产包+产后美容+中医通乳,大田螺，10-30个宝宝护理经验。服务周期：26天';
                break;
            case 9880:
                $alipay['product'] = '金牌田螺（9880）'.$scale_str;
                $alipay['body'] = '金牌田螺9880元,六大基础护理+4*儿保+PICC百万保险一份+产房监测+待产包+产后美容+中医通乳+1次孕检陪护+ 营养师定制食谱,金牌田螺，30-50个宝宝护理经验。服务周期：26天';
                break;
            case 12880:
                $alipay['product'] = '超级田螺（12880）'.$scale_str;
                $alipay['body'] = '超级田螺12880元,六大基础护理+4*儿保+PICC百万保险一份+产房监测+待产包+产后美容+中医通乳+2*孕检陪护+ 营养师定制食谱+心理疏导,超级田螺，50个以上宝宝护理经验。服务周期：26天';
                break;

        }

//        $product = array(5880=>'小田螺（5880）',7880=>'大田螺（7880）',9880=>'金牌田螺（9880）',12880=>'超级田螺（超级田螺）');
//        $product_body = array(5880=>'小田螺5880元,六大基础护理+2*儿保+PICC百万保险一份+产房监测+待产包,小田螺：5-10个宝宝护理经验。服务周期：26天',
//            7880=>'大田螺7880元,六大基础护理+4*儿保+PICC百万保险一份+产房监测+待产包+产后美容+中医通乳,大田螺，10-30个宝宝护理经验。服务周期：26天',
//            9880=>'金牌田螺9880元,六大基础护理+4*儿保+PICC百万保险一份+产房监测+待产包+产后美容+中医通乳+1次孕检陪护+ 营养师定制食谱,金牌田螺，30-50个宝宝护理经验。服务周期：26天',
//            12880=>'超级田螺12880元,六大基础护理+4*儿保+PICC百万保险一份+产房监测+待产包+产后美容+中医通乳+2*孕检陪护+ 营养师定制食谱+心理疏导,超级田螺，50个以上宝宝护理经验。服务周期：26天');
        $alipay_info = M('n_alipay_info')->add($alipay);
        if(!$alipay_info){
            echo "<script>alert('订单异常');window.location.history.go(-1);</script>";
            exit;
        }


        /*********************************************************
        把alipayapi.php中复制过来的如下两段代码去掉，
        第一段是引入配置项，
        第二段是引入submit.class.php这个类。
        为什么要去掉？？
        第一，配置项的内容已经在项目的Config.php文件中进行了配置，我们只需用C函数进行调用即可；
        第二，这里调用的submit.class.php类库我们已经在PayAction的_initialize()中已经引入；所以这里不再需要；
         *****************************************************/
        // require_once("alipay.config.php");
        // require_once("lib/alipay_submit.class.php");

        //这里我们通过TP的C函数把配置项参数读出，赋给$alipay_config；
        $alipay_config=C('alipay_config');

        /**************************请求参数**************************/
        $payment_type = "1"; //支付类型 //必填，不能修改
        $notify_url = C('alipay.notify_url'); //服务器异步通知页面路径
        $return_url = C('alipay.return_url'); //页面跳转同步通知页面路径
        $seller_email = C('alipay.seller_email');//卖家支付宝帐户必填
        $out_trade_no = $alipay_info;//$_POST['trade_no'];//商户订单号 通过支付页面的表单进行传递，注意要唯一！
        $subject = $alipay['product'];//$_POST['ordsubject'];  //订单名称 //必填 通过支付页面的表单进行传递
        $total_fee = $alipay['total_fee'];//$_POST['ordtotal_fee'];   //付款金额  //必填 通过支付页面的表单进行传递
        $body = $alipay['body'];//$_POST['ordbody'];  //订单描述 通过支付页面的表单进行传递
        $show_url = 'http://www.tianluoayi.com/yuesao.html';  //商品展示地址 通过支付页面的表单进行传递
        $anti_phishing_key = "";//防钓鱼时间戳 //若要使用请调用类文件submit中的query_timestamp函数
        $exter_invoke_ip = get_client_ip(); //客户端的IP地址
        /************************************************************/


        //构造要请求的参数数组，无需改动
        $parameter = array(
            "service" => "create_direct_pay_by_user",
            "partner" => trim($alipay_config['partner']),
            "payment_type"    => $payment_type,
            "notify_url"    => $notify_url,
            "return_url"    => $return_url,
            "seller_email"    => $seller_email,
            "out_trade_no"    => $out_trade_no,
            "subject"    => $subject,
            "total_fee"    => $total_fee,
            "body"            => $body,
            "show_url"    => $show_url,
            "anti_phishing_key"    => $anti_phishing_key,
            "exter_invoke_ip"    => $exter_invoke_ip,
            "_input_charset"    => trim(strtolower($alipay_config['input_charset']))
        );
        //建立请求
        $alipaySubmit = new \AlipaySubmit($alipay_config);
        $html_text = $alipaySubmit->buildRequestForm($parameter,"post", "向支付宝提交请求中");
        echo $html_text;
    }

    function notifyurl(){
        $this->dealAl();
        $alipay_config=C('alipay_config');
//计算得出通知验证结果
        $alipayNotify = new \AlipayNotify($alipay_config);
        $verify_result = $alipayNotify->verifyNotify();
        if($verify_result) {
//验证成功
//获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表
            $out_trade_no = $_POST['out_trade_no']; //商户订单号
            $trade_no = $_POST['trade_no']; //支付宝交易号
            $trade_status = $_POST['trade_status']; //交易状态
            $total_fee = $_POST['total_fee']; //交易金额
            $notify_id = $_POST['notify_id']; //通知校验ID。
            $notify_time = $_POST['notify_time']; //通知的发送时间。格式为yyyy-MM-dd HH:mm:ss。
            $buyer_email = $_POST['buyer_email']; //买家支付宝帐号；
            $parameter = array(
                "out_trade_no" => $out_trade_no, //商户订单编号；
                "trade_no" => $trade_no, //支付宝交易号；
                "total_fee" => $total_fee, //交易金额；
                "trade_status" => $trade_status, //交易状态
                "notify_id" => $notify_id, //通知校验ID。
                "notify_time" => $notify_time, //通知的发送时间。
                "buyer_email" => $buyer_email, //买家支付宝帐号；
            );
            if($_POST['trade_status'] == 'TRADE_FINISHED') {

            }else if ($_POST['trade_status'] == 'TRADE_SUCCESS') {
                $alipay_info = M('n_alipay_info')->where('id='.$out_trade_no.'')->find();
                if($alipay_info['trade_status_number'] ==1){

                    //实际付款存在并且不为空
                }else{
                    $save['total_fee_real'] = $total_fee;
                    $save['trade_status'] = $trade_status;
                    $save['trade_status_number'] = 1;
                    $save['notify_id'] = $notify_id;
                    $save['notify_time'] = $notify_time;
                    $save['buyer_email'] = $buyer_email;
                    $save_mod = M('n_alipay_info')->where('id='.$out_trade_no.'')->save($save);
                    if($save_mod===false){
                        M('n_alipay_info')->where('id='.$out_trade_no.'')->save($save);
                    }


                    $receiver = 'wenwen@tianluoayi.com,zhangm@tianluoayi.com';
                    $name = '开发部';
                    $title = '官网支付提醒';
                    $content = '<style>
                    .tianxie-content{
                    width: 700px;
                    height: auto;
                    }
                    .tianxie-content ul li{
                    width: 350px;
                    height: 40px;
                    font-family: "microsoft yahei";
                    font-size: 20px;
                    margin:10px 0 0 80px;
                    color: #666666;
                    float: left;
                    }
                    </style>
                    <div class="tianxie-content">
						<ul>
							<li>产品：'.$alipay_info['product'].'</li>
							<li>应付金额：'.$alipay_info['total_fee'].'</li>
							<li>实付金额：'.$total_fee.'</li>
							<li>支付宝账号：'.$buyer_email.'</li>
							<li>电话：'.$alipay_info['phone'].'</li>
							<li>支付时间：'.$notify_time.' </li>
							<li>登录官网后台查看：<a href="http://www.tianluoayi.com/nadmin.php/Admin/login">http://www.tianluoayi.com/nadmin.php/Admin/login</a></li>
						</ul>
					</div>';
                    $no_html = '产品：'.$alipay_info['product'].'
							应付金额：'.$alipay_info['total_fee'].'
							实付金额：'.$total_fee.'
							支付宝账号：'.$buyer_email.'
							电话：'.$alipay_info['phone'].'
							支付时间：'.$notify_time.'
							登录官网后台查看：http://www.tianluoayi.com/nadmin.php/Admin/login';
                    $this->send_email($receiver,$name,$title,$content,$no_html);//收件人、发件人姓名、标题、内容

                }
            }
            echo "success"; //请不要修改或删除
        }else {
//验证失败
            echo "fail".C('alipay_config.cacert');
        }
    }


    function returnurl(){
        $this->dealAl();
        $alipay_config=C('alipay_config');
        $alipayNotify = new \AlipayNotify($alipay_config);//计算得出通知验证结果
        $verify_result = $alipayNotify->verifyReturn();
        if($verify_result) {
//验证成功
//获取支付宝的通知返回参数，可参考技术文档中页面跳转同步通知参数列表
            $out_trade_no = $_GET['out_trade_no']; //商户订单号
            $trade_no = $_GET['trade_no']; //支付宝交易号
            $trade_status = $_GET['trade_status']; //交易状态
            $total_fee = $_GET['total_fee']; //交易金额
            $notify_id = $_GET['notify_id']; //通知校验ID。
            $notify_time = $_GET['notify_time']; //通知的发送时间。
            $buyer_email = $_GET['buyer_email']; //买家支付宝帐号；

            $parameter = array(
                "out_trade_no" => $out_trade_no, //商户订单编号；
                "trade_no" => $trade_no, //支付宝交易号；
                "total_fee" => $total_fee, //交易金额；
                "trade_status" => $trade_status, //交易状态
                "notify_id" => $notify_id, //通知校验ID。
                "notify_time" => $notify_time, //通知的发送时间。
                "buyer_email" => $buyer_email, //买家支付宝帐号
            );
            if($_GET['trade_status'] == 'TRADE_FINISHED' || $_GET['trade_status'] == 'TRADE_SUCCESS') {
                $alipay_info = M('n_alipay_info')->where('id='.$out_trade_no.'')->find();
                if($alipay_info['trade_status_number'] == 1){

                    //实际付款存在并且不为空
                }else{
                    $save['total_fee_real'] = $total_fee;
                    $save['trade_status'] = $trade_status;
                    $save['trade_status_number'] = 1;
                    $save['notify_id'] = $notify_id;
                    $save['notify_time'] = $notify_time;
                    $save['buyer_email'] = $buyer_email;
                    $save_mod = M('n_alipay_info')->where('id='.$out_trade_no.'')->save($save);
                    if($save_mod===false){
                        M('n_alipay_info')->where('id='.$out_trade_no.'')->save($save);
                    }

                    $receiver = 'wenwen@tianluoayi.com,zhangm@tianluoayi.com';
                    $name = '开发部';
                    $title = '官网支付提醒';
                    $content = '<style>
                    .tianxie-content{
                    width: 700px;
                    height: auto;
                    }
                    .tianxie-content ul li{
                    width: 350px;
                    height: 40px;
                    font-family: "microsoft yahei";
                    font-size: 20px;
                    margin:10px 0 0 80px;
                    color: #666666;
                    float: left;
                    }
                    </style>
                    <div class="tianxie-content">
						<ul>
							<li>产品：'.$alipay_info['product'].'</li>
							<li>应付金额：'.$alipay_info['total_fee'].'</li>
							<li>实付金额：'.$total_fee.'</li>
							<li>支付宝账号：'.$buyer_email.'</li>
							<li>电话：'.$alipay_info['phone'].'</li>
							<li>支付时间：'.$notify_time.' </li>
							<li>登录官网后台查看：<a href="http://www.tianluoayi.com/nadmin.php/Admin/login">http://www.tianluoayi.com/nadmin.php/Admin/login</a></li>
						</ul>
					</div>';
                    $no_html = '产品：'.$alipay_info['product'].'
							应付金额：'.$alipay_info['total_fee'].'
							实付金额：'.$total_fee.'
							支付宝账号：'.$buyer_email.'
							电话：'.$alipay_info['phone'].'
							支付时间：'.$notify_time.'
							登录官网后台查看：http://www.tianluoayi.com/nadmin.php/Admin/login';
                    $this->send_email($receiver,$name,$title,$content,$no_html);//收件人、发件人姓名、标题、内容


                }
                $this->redirect(C('alipay.successpage'));//跳转到配置项中配置的支付成功页面；
            }else {
                echo "trade_status=".$_GET['trade_status'];
                $this->redirect(C('alipay.errorpage'));//跳转到配置项中配置的支付失败页面；
            }
        }else {
//验证失败
//如要调试，请看alipay_notify.php页面的verifyReturn函数
            echo "支付失败！";
        }
    }




    public function success(){

        $this->display();
    }

    public function error(){

        $this->display();
    }




    public function getList(){

        $currentpage = $_POST['currentpage'];
        $pagenum = $_POST['pagenum'];
        $start = ($currentpage - 1) * $pagenum;

        $list['start']=$start;
        $list['pagenum']=$pagenum;

        $back['code'] = 1000;
        $back['data']['list'] =$list;
        $back['data']['num'] =105;
        echo json_encode($back);
    }



    public function index(){



        //首页新闻展示
        $news = M('n_news')->where('index_show=1')->find();

        $news['add_time']=date('Y-m-d',$news['add_time']);

        $news_list = M('n_news')->where('recommend=1')->order('sort desc')->limit(0,5)->select();


        $belong_arr = ['yuesao','yzc','tr','wybj','jkhf','jqbyx','yxys'];
        $belong_arr [21]= 'newsEvents';;
        $belong_arr [22]= 'notice';;
        foreach($news_list as $k=>$v){
            $news_list[$k]['add_time']=date('Y-m-d',$v['add_time']);
            if($v['belong']>10){
                $news_list[$k]['belong_url']= $belong_arr[$v['belong']];
            }else{
                $news_list[$k]['belong_url']=$belong_arr[0].'/'.$belong_arr[$v['belong']];
            }

        }
        if($news['belong']>10){
            $news['belong_url'] = $belong_arr[$news['belong']];
        }else{
            $news['belong_url']=$belong_arr[0].'/'.$belong_arr[$news['belong']];
        }


        $friend = M('n_friendship_link')->where('status=1')->order('sort desc')->select();


        $banner = M('n_banner')->where('belong=11 and status=1')->order('sort asc')->select();



        $this->assign('banner',$banner);

        $this->assign('friend',$friend);
        $this->assign('news_list',$news_list);
        $this->assign('news',$news);

        $this->display();
    }

    public function map(){
        $this->display();
    }
    public function newsEvents(){//新闻动态
        $news_list = M('n_news')->where('detail_show=1 and belong=21')->order('sort desc')->limit(0,4)->select();

        $banner = M('n_banner')->where('belong=21 and status=1')->order('sort asc')->select();

        $this->assign('banner',$banner);
        $this->assign('belong',21);
        $this->assign('news_list',$news_list);
        $this->display();
    }


    public function notice(){//公告
        $banner = M('n_banner')->where('belong=22  and status=1')->order('sort asc')->select();

        $this->assign('banner',$banner);
        $this->assign('belong',22);
        $this->display('newsEvents');
    }



    //获取新闻列表ajax
    public function getNewsList(){
        $currentpage = $_POST['currentpage'];
        $pagenum = $_POST['pagenum'];
        $start = ($currentpage - 1) * $pagenum;
        $belong = I("post.belong");

        $keywords = I('post.keywords');
        if($keywords&&$keywords!=''){
            $where = '(title LIKE "%'.$keywords.'%"  OR keyword LIKE "%'.$keywords.'%" ) and belong <10';
        }else{
            $where = 'belong='.$belong.'';
            if($belong==21){
                $where .= ' and detail_show = 0 ';
            }
        }
        $list = M('n_news')->where($where)->order('sort desc')->limit($start,$pagenum)->select();
        $count = M('n_news')->where($where)->count();

        $belong_arr = ['yuesao','yzc','tr','wybj','jkhf','jqbyx','yxys'];
        foreach($list as $k=>$v){
            $list[$k]['add_time']=date('Y-m-d',$v['add_time']);
            $list[$k]['keyword']=explode(',',$v['keyword']);
            $list[$k]['belong_url']=$belong_arr[0].'/'.$belong_arr[$v['belong']];
        }

        $back['code'] = 1000;
        $back['data']['list'] = $list;
        $back['data']['num'] = $count;
        $back['data']['list_num'] =count($list);
        echo json_encode($back);
    }
    public function newsEventCnt(){
        if(I('get.id')&&I('get.id')!=''&&is_numeric(I('get.id'))){
            $news_info = M("n_news")->where('id='.I("get.id").' ')->find();
            if(!$news_info||$news_info==''){
                echo "<script>alert('地址异常');window.location.href='".__ROOT__."/';</script>";
                exit;
            }
            $news_info['add_time'] =  date("Y-m-d",$news_info['add_time']);
            $up_news = M("n_news")->field('id,title,belong')->where('belong='.$news_info['belong'].' and sort>'.$news_info['sort'].'')->order('sort asc')->limit(1)->select();
            $down_news = M("n_news")->field('id,title,belong')->where('belong='.$news_info['belong'].' and sort<'.$news_info['sort'].'')->order('sort desc')->limit(1)->select();

            $banner = M('n_banner')->where('belong='.$news_info['belong'].'  and status=1')->order('sort asc')->select();
            if($news_info['belong']==21) {
                $belong_url = 'newsEvents';
            }elseif($news_info['belong']==22){
                $belong_url = 'notice';
            }

            $this->assign('belong_url',$belong_url);
            $this->assign('banner',$banner);
            $this->assign('up_news',$up_news);
            $this->assign('down_news',$down_news);
            $this->assign('news_info',$news_info);
            $this->display();
        }else{
            echo "<script>alert('地址异常');window.location.href='".__ROOT__."/';</script>";
            exit;
        }
    }

    //新闻详细页
    public function newsCnt(){
        $news_info = M("n_news")->where('id='.I("get.id").'')->find();
        if(!$news_info||$news_info==''){
            echo "<script>alert('地址异常');window.location.href='".__ROOT__."/';</script>";
            exit;
        }

        $banner = M('n_banner')->where('belong=1  and status=1')->order('sort asc')->select();
        $this->assign('banner',$banner);

        $news_info['add_time'] =  date("Y-m-d",$news_info['add_time']);
        $keyword = explode(',',$news_info['keyword']);

        $belong_arr = ['yuesao','yzc','tr','wybj','jkhf','jqbyx','yxys'];
        $nav_name = ['','月子餐','通乳','宝宝喂养与保健','妈妈健康与恢复','锦旗与表扬信','优秀月嫂'];

        $belong_url= $belong_arr[0].'/'.$belong_arr[$news_info['belong']];

        $nav_name_url = '<a href="'.__ROOT__.'/'.$belong_url.'.html">'.$nav_name[$news_info['belong']].'</a>&gt;';

        $up_news = M("n_news")->field('id,title,belong')->where('belong='.$news_info['belong'].' and sort>'.$news_info['sort'].'')->order('sort asc')->limit(1)->select();
        $down_news = M("n_news")->field('id,title,belong')->where('belong='.$news_info['belong'].' and sort<'.$news_info['sort'].'')->order('sort desc')->limit(1)->select();

        //推荐阅读

        $news_right_list = M('n_news')->field('id,belong,title,add_time')->where('belong<10')->order('add_time desc')->limit(0,6)->select();

        foreach($news_right_list as $k=>$v){
            $news_right_list[$k]['belong_url'] = $belong_arr[0].'/'.$belong_arr[$v['belong']];
            $news_right_list[$k]['add_time'] = date('Y-m-d',$v['add_time']);
        }
        $this->assign('up_news',$up_news);
        $this->assign('down_news',$down_news);
        $this->assign('keyword',$keyword);
        $this->assign('news_info',$news_info);
        $this->assign('belong_url',$belong_url);
        $this->assign('news_right_list',$news_right_list);
        $this->assign('nav_name_url',$nav_name_url);
        $this->display();
    }




    //yuesao文章推荐
    public function yuesao(){
        $banner = M('n_banner')->where('belong=1  and status=1')->order('sort asc')->select();
        if(I("get.type")){
            $article_type = array('yzc'=>1,'tr'=>2,'wybj'=>3,'jkhf'=>4,'jqbyx'=>5,'yxys'=>6);
            if($article_type[I("get.type")]<=6&&$article_type[I("get.type")]>0){

                $keywords['keywords'] =  I('get.key');
                $keywords['type'] = 1;
                if($keywords['keywords']&&$keywords['keywords']!=''){
                    $keywords['type'] = 0;
                }
                $news_count = M('n_news')->where('belong='.$article_type[I("get.type")].'')->count();
                $px = $news_count<=20?$news_count*200:2000;
                $px<400&&$px=400;
                $px+=200;

                $nav_arr = ['','月子餐','通乳','宝宝喂养与保健','妈妈健康与恢复','锦旗与表扬信','优秀月嫂'];
                if($keywords['type']==0){//是搜索的页面
                    $nav = '搜索&gt;'.$keywords['keywords'];
                }else{
                    $nav = '<a href="'.__ROOT__.'/yuesao.html">月嫂</a>&gt;'.$nav_arr[$article_type[I("get.type")]];
                }

                $this->assign('px','style="height:'.$px.'px;"');
                $this->assign('nav',$nav);
                $this->assign('belong',$article_type[I("get.type")]);
                $this->assign('keywords',$keywords);
                $this->assign('banner',$banner);
                $this->display('newsList');
            }else{
                echo "<script>alert('地址异常');window.location.href='".__ROOT__."/';</script>";
                exit;
            }
        }else{
            $this->assign('banner',$banner);

            $this->display();
        }
    }

    //baomu文章推荐
    public function yuersao(){
        $belong = 3;
        if(I("get.type")=='article'){
            if(I("get.id")&&I("get.id")!=''){
                $news_info = M("news")->where('id='.I("get.id").' and belong='.$belong.'')->find();
                $news_info['add_time'] =  date("Y-m-d",$news_info['add_time']);
                $keyword = explode(',',$news_info['keyword']);
                $up_news = M("news")->field('id,title,belong')->where('belong='.$news_info['belong'].' and sort>'.$news_info['sort'].'')->order('sort asc')->limit(1)->select();
                $down_news = M("news")->field('id,title,belong')->where('belong='.$news_info['belong'].' and sort<'.$news_info['sort'].'')->order('sort desc')->limit(1)->select();
                $news_list = M('n_news')->field('id,title,belong')->where('belong='.$news_info['belong'].' and detail_show=1')->order('sort desc')->limit(0,12)->select();
                $this->assign('news_list',$news_list);

                $this->assign('up_news',$up_news);
                $this->assign('down_news',$down_news);
                $this->assign('keyword',$keyword);
                $this->assign('news_info',$news_info);
                $this->assign('belong',$belong);
                $this->display('Index/articleInfo');
            }else{
                $this->assign('belong',$belong);
                $this->display('Index/article');
            }
        }else {
            $news_recommend = M('n_news')->where('belong=' . $belong . ' and detail_show=1')->order('sort desc')->limit(0, 4)->select();
            foreach ($news_recommend as $k => $v) {
                $news_recommend[$k]['add_time'] = date('Y-m-d', $v['add_time']);
                $news_recommend[$k]['keyword'] = explode(',', $v['keyword']);
            }
            $news_list = M('n_news')->where('belong=' . $belong . ' and detail_show=1')->order('sort desc')->limit(0, 12)->select();

            $this->assign('news_recommend', $news_recommend);
            $this->assign('news_list', $news_list);
            $this->display();
        }
    }

    //baomu文章推荐
    public function baomu(){
        $belong = 4;
        if(I("get.type")=='article'){
            if(I("get.id")&&I("get.id")!=''){
                $news_info = M("news")->where('id='.I("get.id").' and belong='.$belong.'')->find();
                $news_info['add_time'] =  date("Y-m-d",$news_info['add_time']);
                $keyword = explode(',',$news_info['keyword']);
                $up_news = M("news")->field('id,title,belong')->where('belong='.$news_info['belong'].' and sort>'.$news_info['sort'].'')->order('sort asc')->limit(1)->select();
                $down_news = M("news")->field('id,title,belong')->where('belong='.$news_info['belong'].' and sort<'.$news_info['sort'].'')->order('sort desc')->limit(1)->select();
                $news_list = M('n_news')->field('id,title,belong')->where('belong='.$news_info['belong'].' and detail_show=1')->order('sort desc')->limit(0,12)->select();
                $this->assign('news_list',$news_list);

                $this->assign('up_news',$up_news);
                $this->assign('down_news',$down_news);
                $this->assign('keyword',$keyword);
                $this->assign('news_info',$news_info);
                $this->assign('belong',$belong);
                $this->display('Index/articleInfo');
            }else{
                $this->assign('belong',$belong);
                $this->display('Index/article');
            }
        }else {
            $news_recommend = M('n_news')->where('belong=' . $belong . ' and detail_show=1')->order('sort desc')->limit(0, 4)->select();
            foreach ($news_recommend as $k => $v) {
                $news_recommend[$k]['add_time'] = date('Y-m-d', $v['add_time']);
                $news_recommend[$k]['keyword'] = explode(',', $v['keyword']);
            }
            $news_list = M('n_news')->where('belong=' . $belong . '  and index_show=1')->order('sort desc')->limit(0, 12)->select();

            $this->assign('news_recommend', $news_recommend);
            $this->assign('news_list', $news_list);
            $this->display();
        }
    }


    public function jiajiao(){
        $belong = 5;
        if(I("get.type")=='article'){
            if(I("get.id")&&I("get.id")!=''){
                $news_info = M("news")->where('id='.I("get.id").' and belong='.$belong.'')->find();
                $news_info['add_time'] =  date("Y-m-d",$news_info['add_time']);
                $keyword = explode(',',$news_info['keyword']);
                $up_news = M("news")->field('id,title,belong')->where('belong='.$news_info['belong'].' and sort>'.$news_info['sort'].'')->order('sort asc')->limit(1)->select();
                $down_news = M("news")->field('id,title,belong')->where('belong='.$news_info['belong'].' and sort<'.$news_info['sort'].'')->order('sort desc')->limit(1)->select();
                $news_list = M('n_news')->field('id,title,belong')->where('belong='.$news_info['belong'].' and detail_show=1')->order('sort desc')->limit(0,12)->select();
                $this->assign('news_list',$news_list);

                $this->assign('up_news',$up_news);
                $this->assign('down_news',$down_news);
                $this->assign('keyword',$keyword);
                $this->assign('news_info',$news_info);
                $this->assign('belong',$belong);
                $this->display('Index/articleInfo');
            }else{
                $this->assign('belong',$belong);
                $this->display('Index/article');
            }
        }else {
            $news_recommend = M('n_news')->where('belong=' . $belong . ' and detail_show=1')->order('sort desc')->limit(0, 4)->select();
            foreach ($news_recommend as $k => $v) {
                $news_recommend[$k]['add_time'] = date('Y-m-d', $v['add_time']);
                $news_recommend[$k]['keyword'] = explode(',', $v['keyword']);
            }
            $news_list = M('n_news')->where('belong=' . $belong . '  and index_show=1')->order('sort desc')->limit(0, 12)->select();

            $this->assign('news_recommend', $news_recommend);
            $this->assign('news_list', $news_list);
            $this->display();
        }
    }



    //提交招聘留言
    public function addMessage(){
        $post = I('post.');
        if($post['phone']==''){
            echo "<script>alert('请填写手机号！');history.go(-1);</script>";
            exit;
        }
        $where['name'] =  $post['name'];
        $where['phone'] =  $post['phone'];
        $where['content'] =  $post['content'];
        $have_message = M('n_message')->where($where)->find();
        if($have_message){
            echo "<script>alert('您已经提交成功，请勿重复提交！');history.go(-1);</script>";
            exit;
        }

        $post['add_time']=time();
        $add_mod = D("Message")->add_mod($post);
        if($add_mod!==false){

            $receiver_table = M("n_email_receiver")->where('state=1')->select();
            if($receiver_table) {

                foreach ($receiver_table as $k => $v) {
                    $receiver_a[] = $v['email'];
                }
                $receiver = implode(',', $receiver_a);
//            $receiver = '836806981@qq.com,huangw@tianluoayi.com';
                $name = '开发部';
                $title = '招聘留言';
                $content = '<style>
                    .tianxie-content{
                    width: 700px;
                    height: auto;
                    }
                    .tianxie-content ul li{
                    width: 350px;
                    height: 40px;
                    font-family: "microsoft yahei";
                    font-size: 20px;
                    margin:10px 0 0 80px;
                    color: #666666;
                    float: left;
                    }
                    </style>
                    <div class="tianxie-content">
						<ul>
							<li>姓名：' . $post['name'] . '</li>
							<li>电话：' . $post['phone'] . '</li>
							<li>内容：' . $post['content'] . ' </li>
							<li>留言时间： ' . date('Y-m-d H:i:s', $post['add_time']) . '</li>
						</ul>
					</div>';
                $no_html = '姓名：' . $post['name'] . '
							电话：' . $post['phone'] . '
							内容：' . $post['content'] . '
							留言时间： ' . date('Y-m-d H:i:s', $post['add_time']);
                $this->send_email($receiver, $name, $title, $content, $no_html);//收件人、发件人姓名、标题、内容

            }
            if($post['type']==2){
                echo "<script>alert('提交成功');window.history.go(-1);</script>";
            }else {
                echo "<script>alert('提交成功');window.history.go(-1);</script>";
            }
        }else{
            echo "<script>alert('提交失败');history.back();</script>";
        }
    }

    //提交招聘留言
    public function addDestine(){
        $post = I('post.');
        $post['add_time']=time();
        if($post['phone']==''){
            echo "<script>alert('请填写手机号！');history.go(-1);</script>";
            exit;
        }
        $add_mod = D("Destine")->add_mod($post);
        if($add_mod!==false){

            $receiver_table = M("n_email_receiver")->where('state=2')->select();
            foreach($receiver_table as $k=>$v){
                $receiver_a[] = $v['email'];
            }
            $receiver = implode(',',$receiver_a);

            $nurse = M("nurse")->field('name,title_img')->where('id='.$post['nurse_id'].'')->find();
            //留言成功发送邮件
//            $receiver = 'huangw@tianluoayi.com';
            $name = '开发部';
            $title = '阿姨预约';
            $content = '<style>
                    .tianxie-content{
                    width: 700px;
                    height: auto;
                    }
                    .tianxie-content ul li{
                    width: 350px;
                    font-family: "microsoft yahei";
                    font-size: 20px;
                    margin:10px 0 0 80px;
                    color: #666666;
                    float: left;
                    }
                    </style>
                    <div class="tianxie-content">
						<ul>
							<li>姓名：'.$post['name'].'</li>
                            <li>电话：'.$post['phone'].'</li>
                            <li>预产期：'.$post['due_date'].'</li>
                            <li>宝宝数：'.$post['baby_num'].'</li>
                            <li>预约阿姨：'.$nurse['name'].'</li>
                            <li>阿姨头像：<img src="http://tianluoayi.com/Uploads/'.$nurse['title_img'].'" style="max-width:250px;"/></li>
							<li>留言时间： '.date('Y-m-d H:i:s',$post['add_time']).'</li>
						</ul>
					</div>';
            $no_html = '姓名：'.$post['name'].'
							电话：'.$post['phone'].'
							预产期：'.$post['due_date'].'
							宝宝数：'.$post['baby_num'].'
							预约阿姨：'.$nurse['name'].'
							留言时间： '.date('Y-m-d H:i:s',$post['add_time']);
            $this->send_email($receiver,$name,$title,$content,$no_html);//收件人、发件人姓名、标题、内容
            echo "<script>alert('提交成功');window.location.href='".__ROOT__."/ayi/".$post['nurse_id'].".html';</script>";
        }else{
            echo "<script>alert('提交失败');history.back();</script>";
        }
    }




    public function about(){
        $banner = M('n_banner')->where('belong=2 and status=1')->order('sort asc')->select();

        $this->assign('banner',$banner);
        $this->display();
    }


    public function contact(){
        $banner = M('n_banner')->where('belong=3')->order('sort asc')->select();
        $this->assign('banner',$banner);
        $this->display();
    }





	public function tongru(){
        $this->display('Index/cuiru');
    }
    public function zhaopin(){
        $this->display('Index/crzp');
    }
    public function jianjie(){
        $this->display();
    }

    public function rencai(){
        $this->display();
    }

	public function shop(){
        $this->display();
    }

    public function ayimulu(){
        if(I('post.')){
            $name = I("post.name");
        }
        $this->assign('name',$name);
        $count = M('nurse')->count();
        $px =  $count<=10?$count*350:3500;
        $this->assign('count',$count);
        $this->assign('px',$px);
        $this->display();
    }

    public function ayiList(){
        $currentpage = I("post.currentpage");
        $pagenum = I("post.pagenum");
        $start = ($currentpage - 1) * $pagenum;
        $keyword = I("post.keyword");
        $feild = 'id,title_img,name,hight,age,weight,native_place,level,zodiac,constellation,marriage,birth';
        if($keyword&&$keyword!=''){
            $list = M('nurse')->field($feild)->where('LOCATE("'.$keyword.'",name)>0 ')->limit($start,$pagenum)->select();
            $count = M('nurse')->where('LOCATE("'.$keyword.'",name)>0 ')->count();
        }else{
            $list = M('nurse')->field($feild)->order('sort desc')->limit($start,$pagenum)->select();
            $count = M('nurse')->count();
        }
        foreach($list as $k=>$v){
            $list[$k]['age']=date('Y',time())-date('Y',strtotime($v['age']));
            $list[$k]['add_time']=date('Y-m-d',$v['add_time']);
            switch($v['level']){
                case 1:
                    $list[$k]['level']='初级';
                    break;
                case 2:
                    $list[$k]['level']='中级';
                    break;
                case 3:
                    $list[$k]['level']='高级';
                    break;
                case 4:
                    $list[$k]['level']='星级';
                    break;
                case 5:
                    $list[$k]['level']='金牌';
                    break;
            }
        }
        $back['code'] = 1000;
        $back['data']['list'] =$list;
        $back['data']['num'] =$count;
        echo json_encode($back);
    }
    public function ayi(){
        $id = I("get.id");
        $nurse_list = M("nurse")->where('is_recommend=1')->order('recommend_time desc')->select();
        foreach($nurse_list as $k=>$v){
            $nurse_list[$k]['age']=date('Y',time())-date('Y',strtotime($v['age']));
            switch($v['level']){
                case 1:
                    $nurse_list[$k]['level']='初级';
                    break;
                case 2:
                    $nurse_list[$k]['level']='中级';
                    break;
                case 3:
                    $nurse_list[$k]['level']='高级';
                    break;
                case 4:
                    $nurse_list[$k]['level']='星级';
                    break;
                case 5:
                    $nurse_list[$k]['level']='金牌';
                    break;
            }
        }
        $nurse = M("nurse")->where('id='.$id.'')->find();
        if($nurse&&$nurse!=''){
            $nurse['age']=date('Y',time())-date('Y',strtotime($nurse['age']));
            $do_word = explode(',',$nurse['do_word']);
            $do_video= explode(',',$nurse['do_video']);
            $experience= explode('------',$nurse['experience']);//行业经验
            $character= explode('------',$nurse['character']);//性格评价
            foreach($do_word as $k=>$v){
                if($v){
                    $video[$k]['word'] = $v;
                    $video[$k]['video'] = $do_video[$k];
                }
            }
            switch($nurse['level']){
                case 1:
                    $nurse['level']='初级';
                    break;
                case 2:
                    $nurse['level']='中级';
                    break;
                case 3:
                    $nurse['level']='高级';
                    break;
                case 4:
                    $nurse['level']='星级';
                    break;
                case 5:
                    $nurse['level']='金牌';
                    break;
            }

            $this->assign('nurse_list',$nurse_list);
            $this->assign('nurse',$nurse);
            $this->assign('video',$video);
            $this->assign('experience',$experience);
            $this->assign('character',$character);
            $this->display();
        }else{
            echo "<script>alert('地址异常');window.location.href='".__ROOT__."/';</script>";
        }


    }


    public function kst(){
        $this->display();
    }


    //网站目录页
    public function sitemap(){

        $this->display();
    }


    //优惠券领取页面
    public function yhq_receive(){
        if (I('post.')) {
            a:
            $code = rand(10000000,99999999);
            $yhq = M('coupon_number')->where('number="'.$code.'"')->find();
            if($yhq){
                goto a;
            }
            $add['phone'] = I('post.phone');
            $add['number'] = $code;
            $add['add_time'] = time();
            $add['ip'] = $this->getIP();
            $coupon_number = M('coupon_number')->add($add);
            if($coupon_number) {
                $statusStr = array(
                    "0" => "短信发送成功",
                    "-1" => "参数不全",
                    "-2" => "服务器空间不支持,请确认支持curl或者fsocket，联系您的空间商解决或者更换空间！",
                    "30" => "密码错误",
                    "40" => "账号不存在",
                    "41" => "余额不足",
                    "42" => "帐户已过期",
                    "43" => "IP地址限制",
                    "50" => "内容含有敏感词"
                );
                $smsapi = "http://api.smsbao.com/"; //短信网关
                $user = "tianluoayi"; //短信平台帐号
                $pass = md5("zxc153."); //短信平台密码
                $content = "【田螺阿姨】您领取的3998超值月嫂优惠码：" . $code . "。您可凭此优惠码享受我们的3998超值月嫂服务，到店还可免费领取惠氏s-26金装幼儿乐3段奶粉200g。点击 www.tianluoayi.com/yhq/" . $coupon_number . " 查看优惠券信息。";//要发送的短信内容
                $phone = I('post.phone');//要发送短信的手机号码
                $sendurl = $smsapi . "sms?u=" . $user . "&p=" . $pass . "&m=" . $phone . "&c=" . urlencode($content);
                $result = file_get_contents($sendurl);
                if ($result != 0) {
                    $result = file_get_contents($sendurl);
                }
                echo "<script>;window.location.href='".__ROOT__."/yhq/$coupon_number';</script>";

            }else{
                echo "<script>alert('网络异常');window.location.href='".__ROOT__."/yhq';</script>";
                exit;
            }
        } else {
            $this->display();
        }
    }

    //优惠券页面
    public function yhq(){
        $yhq = M('coupon_number')->where('id='.$_GET['id'])->find();
        $yhq['phone'] = substr($yhq['phone'],0,3).'****'.substr($yhq['phone'],-4);
        $this->assign('yhq',$yhq);
        $this->display();
    }




    //竞价页面


    public function ys(){

        $this->display('Index/Auction/jingjia-yuesao');
    }

    public function yes(){

        $this->display('Index/Auction/jingjia-yuersao');
    }

    public function bm(){

        $this->display('Index/Auction/jingjia-baomu');
    }

    public function zp(){

        $this->display('Index/Auction/jingjia-zhaopin');
    }


    public function jiameng(){

        $this->display('Index/jiameng');
    }

    public function tejiayuesao(){

        $this->display('Index/activity3980');
    }


    //提交招聘留言
    public function addMessage_auction(){
        $post = I('post.');
        if($post['phone']==''){
            echo "<script>alert('请填写手机号！');history.go(-1);</script>";
            exit;
        }
        $post['add_time']=time();
        $add_mod = D("Message")->add_mod($post);
        if($add_mod!==false){

            $receiver_table = M("n_email_receiver")->where('state=1')->select();
            foreach($receiver_table as $k=>$v){
                $receiver_a[] = $v['email'];
            }
            $receiver = implode(',',$receiver_a);
            //留言成功发送邮件
//            $receiver = '836806981@qq.com,huangw@tianluoayi.com';
            $name = '开发部';
            $title = '竞价页面留言';
            $content = '<style>
                    .tianxie-content{
                    width: 700px;
                    height: auto;
                    }
                    .tianxie-content ul li{
                    width: 350px;
                    height: 40px;
                    font-family: "microsoft yahei";
                    font-size: 20px;
                    margin:10px 0 0 80px;
                    color: #666666;
                    float: left;
                    }
                    </style>
                    <div class="tianxie-content">
						<ul>
							<li>姓名：'.$post['name'].'</li>
							<li>应聘岗位：'.$post['job'].'</li>
							<li>年龄：'.$post['age'].'</li>
							<li>工作经验：'.$post['experience'].'</li>
							<li>电话：'.$post['phone'].'</li>
							<li>有无证书：'.$post['certificate'].' </li>
							<li>留言时间： '.date('Y-m-d H:i:s',$post['add_time']).'</li>
						</ul>
					</div>';
            $no_html = '姓名：'.$post['name'].'
							应聘岗位：'.$post['job'].'
							年龄：'.$post['age'].'
							工作经验：'.$post['experience'].'
							电话：'.$post['phone'].'
							有无证书：'.$post['certificate'].'
							留言时间： '.date('Y-m-d H:i:s',$post['add_time']);
            $this->send_email($receiver,$name,$title,$content,$no_html);//收件人、发件人姓名、标题、内容

//            if($post['type']==2){
//                echo "<script>alert('提交成功');window.location.href='" . __ROOT__ . "/m/zhaopin.html';</script>";
//            }else {
                echo "<script>alert('提交成功');history.back();</script>";
//            }
        }else{
            echo "<script>alert('提交失败');history.go(-1);</script>";
        }
    }


}