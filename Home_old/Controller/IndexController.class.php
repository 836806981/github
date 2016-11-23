<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends CommonController {


    public function nurse_tas(){
        $mysql_server_name='122.114.95.40'; //改成自己的mysql数据库服务器

        $mysql_username='root'; //改成自己的mysql数据库用户名

        $mysql_password='root'; //改成自己的mysql数据库密码

        $mysql_database='nurse'; //改成自己的mysql数据库名


        $conn=mysql_connect($mysql_server_name,$mysql_username,$mysql_password) or die("error connecting") ; //连接数据库

        mysql_query("set names 'utf8'"); //数据库输出编码 应该与你的数据库编码保持一致.南昌网站建设公司百恒网络PHP工程师建议用UTF-8 国际标准编码.

        mysql_select_db($mysql_database); //打开数据库

        $sql ="select * from nurse where status=1 OR status=2"; //SQL语句

        $result = mysql_query($sql,$conn); //查询
        while($rs = mysql_fetch_assoc($result)){
            $row[] = $rs;
        }
        foreach($row as $k=>$v){

            $have = M('nurse')->where('phone = "'.$v['phone'].'"')->find();
            if(!$have) {
                $type = explode(',', $v['others']);
                $add['type'] = $type[0];
                if (!$add['type']) {
                    $add['type'] = $type[1];
                }
                if (!$add['type']) {
                    $add['type'] = $type[2];
                }


                $add['status'] = 1;
                $add['is_training'] = 0;
                $add['number'] = '修改后显示';
                $add['specialty'] = $v['remark'] . '价格' . $v['price_one'] . '/' . $v['price_two'];
                $add['name'] = $v['name'];
                $add['age'] = $v['age'];
                $add['title_img'] = $v['title_img'];
                $add['native_place'] = $v['native_place'];
                $add['experience'] = $v['experience'];
                $add['add_time'] = $v['add_time'];
                $add['phone'] = $v['phone'];
                M('nurse')->add($add);
            }
        }
        echo 'done';
    }


    public function test(){
        $memcache = new Memcache;
        $memcache->connect('localhost', 11211) or die ("Could not connect");
        $version = $memcache->getVersion();
        echo "Server's version: ".$version."\n";
        $tmp_object = new stdClass;
        $tmp_object->str_attr = 'test';
        $tmp_object->int_attr = 123;
        $memcache->set('key', $tmp_object, false, 10) or die ("Failed to save data at the server");
        echo "Store data in the cache (data will expire in 10 seconds)\n";
        $get_result = $memcache->get('key');
        echo "Data from the cache:\n";
        var_dump($get_result);

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
        $news_list = M('news')->where('belong=1 and index_show=1')->order('sort desc')->limit(0,4)->select();
        foreach($news_list as $k=>$v){
            $news_list[$k]['add_time']=date('Y-m-d ',$v['add_time']);
            $news_list[$k]['keyword'] = explode(',',$v['keyword']);
        }
        $friend = M('friendship_link')->where('status=1')->order('sort desc')->select();
        $this->assign('friend',$friend);
        $this->assign('news_list',$news_list);

        $this->display();
    }

    public function news(){
        //新闻展示
//        if(I("get.key")){
//            $count= M("news")->where('belong=1 and keyword LIKE "%'.I("get.key").'%"')->count();
//            $Page       = new \Think\Page($count,20);// 实例化分页类 传入总记录数和每页显示的记录数(25)
//            $show       = $Page->showFront();// 分页显示输出// 进行分页数据查询 注意limit方法的参数要使用Page类的属性
//            $news_list = M("news")->where('belong=1 and keyword LIKE "%'.I("get.key").'%"')->order('sort desc')->limit($Page->firstRow.','.$Page->listRows)->select();
//        }else{
//            $count= M("news")->where('belong=1')->count();
//            $Page       = new \Think\Page($count,20);// 实例化分页类 传入总记录数和每页显示的记录数(25)
//            $show       = $Page->showFront();// 分页显示输出// 进行分页数据查询 注意limit方法的参数要使用Page类的属性
//            $news_list = M("news")->where('belong=1')->order('sort desc')->limit($Page->firstRow.','.$Page->listRows)->select();
//
//        }
//        foreach($news_list as $k=>$v){
//            $news_list[$k]['add_time']=date('Y-m-d',$v['add_time']);
//        }
//        $this->assign('news_list',$news_list);
//        $this->assign('page',$show);
        $belong=1;
        $count = M('news')->where('belong='.$belong.'')->count();
        $px =  $count<=10?$count*220:2200;
        $this->assign('px',$px);
        $this->assign('belong',$belong);
        $this->display();
    }

    //获取新闻列表ajax
    public function getNewsList(){
        $currentpage = $_POST['currentpage'];
        $pagenum = $_POST['pagenum'];
        $start = ($currentpage - 1) * $pagenum;
        $belong = I("post.belong");
        $list = M('news')->where('belong='.$belong.'')->order('sort desc')->limit($start,$pagenum)->select();
        $count = M('news')->where('belong='.$belong.'')->count();
        foreach($list as $k=>$v){
            $list[$k]['add_time']=date('Y-m-d',$v['add_time']);
            $list[$k]['keyword']=explode(',',$v['keyword']);
        }

        $back['code'] = 1000;
        $back['data']['list'] =$list;
        $back['data']['num'] =$count;
        $back['data']['list_num'] =count($list);
        echo json_encode($back);
    }

    //新闻详细页
    public function newsInfo(){
        $news_info = M("news")->where('id='.I("get.id").'')->find();
        $news_info['add_time'] =  date("Y-m-d",$news_info['add_time']);
        $keyword = explode(',',$news_info['keyword']);

        $up_news = M("news")->field('id,title,belong')->where('belong='.$news_info['belong'].' and sort>'.$news_info['sort'].'')->order('sort asc')->limit(1)->select();
        $down_news = M("news")->field('id,title,belong')->where('belong='.$news_info['belong'].' and sort<'.$news_info['sort'].'')->order('sort desc')->limit(1)->select();


        $news_list = M('news')->field('id,title,belong')->where('belong=1 and detail_show=1')->order('sort desc')->limit(0,12)->select();
        $this->assign('news_list',$news_list);

        $this->assign('up_news',$up_news);
        $this->assign('down_news',$down_news);
        $this->assign('keyword',$keyword);
        $this->assign('news_info',$news_info);
        $this->display();
    }



    //内页文章模板
    public function article(){
        $belong = 2;


        $news_info = M("news")->where('id='.I("get.id").'')->find();
        $news_info['add_time'] =  date("Y-m-d",$news_info['add_time']);
        $keyword = explode(',',$news_info['keyword']);

        $up_news = M("news")->field('id,title,belong')->where('belong='.$news_info['belong'].' and sort>'.$news_info['sort'].'')->order('sort asc')->limit(1)->select();
        $down_news = M("news")->field('id,title,belong')->where('belong='.$news_info['belong'].' and sort<'.$news_info['sort'].'')->order('sort desc')->limit(1)->select();



        $news_show = M('news')->field('id,title,belong')->where('belong='.$belong.' and recommend=1')->order('sort desc')->select();

        $news_all = M('news')->field('id,title,belong')->where('belong='.$belong.'')->order('sort desc')->limit(0,12)->select();

        $this->assign('news_show',$news_show);
        $this->assign('news_all',$news_all);
        $this->assign('up_news',$up_news);
        $this->assign('down_news',$down_news);
        $this->assign('keyword',$keyword);
        $this->assign('news_info',$news_info);
        $this->display();
    }



    //yuesao文章推荐
    public function yuesao(){
        $belong = 2;
        if(I("get.type")=='article'){
            if(I("get.id")&&I("get.id")!=''){
                $news_info = M("news")->where('id='.I("get.id").' and belong='.$belong.'')->find();
                $news_info['add_time'] =  date("Y-m-d",$news_info['add_time']);
                $keyword = explode(',',$news_info['keyword']);
                $up_news = M("news")->field('id,title,belong')->where('belong='.$news_info['belong'].' and sort>'.$news_info['sort'].'')->order('sort asc')->limit(1)->select();
                $down_news = M("news")->field('id,title,belong')->where('belong='.$news_info['belong'].' and sort<'.$news_info['sort'].'')->order('sort desc')->limit(1)->select();
                $news_list = M('news')->field('id,title,belong')->where('belong='.$news_info['belong'].' and detail_show=1')->order('sort desc')->limit(0,12)->select();
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
        }else{
            $news_recommend = M('news')->where('belong='.$belong.' and index_show=1')->order('sort desc')->limit(0,4)->select();
            foreach($news_recommend as $k=>$v){
                $news_recommend[$k]['add_time']=date('Y-m-d',$v['add_time']);
                $news_recommend[$k]['keyword'] = explode(',',$v['keyword']);
            }
            $news_list = M('news')->where('belong='.$belong.' and detail_show=1')->order('sort desc')->limit(0,12)->select();

            $this->assign('news_recommend',$news_recommend);
            $this->assign('news_list',$news_list);
            $this->display();
        }
    }

    //baomu文章推荐
    public function yuersao(){
        $belong = 3;
        disk_free_space('123');
        if(I("get.type")=='article'){
            if(I("get.id")&&I("get.id")!=''){
                $news_info = M("news")->where('id='.I("get.id").' and belong='.$belong.'')->find();
                $news_info['add_time'] =  date("Y-m-d",$news_info['add_time']);
                $keyword = explode(',',$news_info['keyword']);
                $up_news = M("news")->field('id,title,belong')->where('belong='.$news_info['belong'].' and sort>'.$news_info['sort'].'')->order('sort asc')->limit(1)->select();
                $down_news = M("news")->field('id,title,belong')->where('belong='.$news_info['belong'].' and sort<'.$news_info['sort'].'')->order('sort desc')->limit(1)->select();
                $news_list = M('news')->field('id,title,belong')->where('belong='.$news_info['belong'].' and detail_show=1')->order('sort desc')->limit(0,12)->select();
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
            $news_recommend = M('news')->where('belong=' . $belong . ' and detail_show=1')->order('sort desc')->limit(0, 4)->select();
            foreach ($news_recommend as $k => $v) {
                $news_recommend[$k]['add_time'] = date('Y-m-d', $v['add_time']);
                $news_recommend[$k]['keyword'] = explode(',', $v['keyword']);
            }
            $news_list = M('news')->where('belong=' . $belong . ' and detail_show=1')->order('sort desc')->limit(0, 12)->select();

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
                $news_list = M('news')->field('id,title,belong')->where('belong='.$news_info['belong'].' and detail_show=1')->order('sort desc')->limit(0,12)->select();
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
            $news_recommend = M('news')->where('belong=' . $belong . ' and detail_show=1')->order('sort desc')->limit(0, 4)->select();
            foreach ($news_recommend as $k => $v) {
                $news_recommend[$k]['add_time'] = date('Y-m-d', $v['add_time']);
                $news_recommend[$k]['keyword'] = explode(',', $v['keyword']);
            }
            $news_list = M('news')->where('belong=' . $belong . '  and index_show=1')->order('sort desc')->limit(0, 12)->select();

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
                $news_list = M('news')->field('id,title,belong')->where('belong='.$news_info['belong'].' and detail_show=1')->order('sort desc')->limit(0,12)->select();
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
            $news_recommend = M('news')->where('belong=' . $belong . ' and detail_show=1')->order('sort desc')->limit(0, 4)->select();
            foreach ($news_recommend as $k => $v) {
                $news_recommend[$k]['add_time'] = date('Y-m-d', $v['add_time']);
                $news_recommend[$k]['keyword'] = explode(',', $v['keyword']);
            }
            $news_list = M('news')->where('belong=' . $belong . '  and index_show=1')->order('sort desc')->limit(0, 12)->select();

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
        $post['add_time']=time();
        $add_mod = D("Message")->add_mod($post);
        if($add_mod!==false){

            $receiver_table = M("email_receiver")->where('state=1')->select();
            foreach($receiver_table as $k=>$v){
                $receiver_a[] = $v['email'];
            }
            $receiver = implode(',',$receiver_a);
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

            if($post['type']==2){
                echo "<script>alert('提交成功');window.location.href='" . __ROOT__ . "/m/zhaopin.html';</script>";
            }else {
                echo "<script>alert('提交成功');window.location.href='" . __ROOT__ . "/zhaopin.html';</script>";
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

            $receiver_table = M("email_receiver")->where('state=2')->select();
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


    public function zhaopin(){
        $this->display();
    }

    public function jianjie(){
        $this->display();
    }
	public function tongru(){
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

            $receiver_table = M("email_receiver")->where('state=1')->select();
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