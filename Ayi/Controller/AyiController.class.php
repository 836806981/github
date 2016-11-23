<?php
namespace Ayi\Controller;
use Think\Controller;
class AyiController extends CommonController {
    public function  _initialize(){
        if(!$_SESSION[C('AYI_AUTH_KEY')]['id']){
            echo "<script>alert('请登录！');window.location.href='" . __MODULE__ . "/Admin/login.html';</script>";
            exit;
        }
    }

    //添加月嫂基本信息页面
    public function addNurse_1(){

        if(!($_SESSION[C('AYI_AUTH_KEY')]['permission']==1||$_SESSION[C('AYI_AUTH_KEY')]['add_per']==1)){
            echo "<script>alert('你没有权限！');window.location.href='" . __MODULE__ . "/Admin/info.html';</script>";
        }
        $sort = M('nurse')->field('sort')->order('sort desc')->limit(1)->find();
        $sort_num = $sort['sort']?$sort['sort']+1:100;

        if(I('get.id')){
            $training = M('tra_training')->where('id='.I('get.id').'')->find();
            $training['skill'] = explode(',',$training['skill']);
            $this->assign('training',$training);
        }

        $this->assign('sort_num',$sort_num);
        $this->display();
    }

    //修改月嫂基本信息页面
    public function editNurse_1(){

        if(!($_SESSION[C('AYI_AUTH_KEY')]['permission']==1||$_SESSION[C('AYI_AUTH_KEY')]['edit_per']==1)){
            echo "<script>alert('你没有权限！');window.location.href='" . __MODULE__ . "/Admin/info.html';</script>";
        }
        $id = I("get.id");
        $nurse = M('nurse')->where('id='.$id.'')->find();

        $this->assign('nurse',$nurse);
        $this->display();
    }

    //修改月嫂基本信息
    public function editInfo(){
        $post = I('post.');

        $have = M('nurse')->where('phone="'.I("post.phone").'"  and id!='.$post['id'].'')->find();
        if($have){
            echo "<script>alert('手机号已存在！');history.go(-1);</script>";
            exit;
        }

        $Nurse = D('Nurse');
        if ($_FILES['title_img']['tmp_name']) {
            $name = $this->pinyin_long($post['name']);
            $upload = new \Think\Upload();// 实例化上传类
            $upload->maxSize = 1048576;// 设置附件上传大小
            $upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
            $upload->savePath = 'nurse/'.$post['id'] .'_'. $name . '/head/'; // 设置附件上传目录
            $upload->subName = ''; // 设置附件上传目录
            $info = $upload->uploadOne($_FILES['title_img']);
            if (!$info) {// 上传错误提示错误信息
                echo "<script>alert('图片上传失败');window.location.href='" . __MODULE__ . "/Ayi/editNurse_1/id/" . $post['id'] . "';</script>";
            } else {// 上传成功

                $imgPath = $info['savepath'] . $info['savename'];
                $post['title_img'] = $imgPath;
            }
        }
        $where['id']=$post['id'];
        $post['others']='';
        for($i = 1;$i<3;$i++){
            $post['others'.$i]&& $post['others'] .= $post['others'.$i].',';
        }
        $post['others']&&$post['others'] = substr( $post['others'],0,-1);
        $post['skill'] = $post['skill1'].','.$post['skill2'].','.$post['skill3'].','.$post['skill4'].','.$post['skill5'].','.$post['skill6'].','.$post['skill7'];
        $id_card = $post['id_card'];
        //生肖
        $animals = array(
            '鼠', '牛', '虎', '兔', '龙', '蛇',
            '马', '羊', '猴', '鸡', '狗', '猪'
        );
        $key = (substr($id_card,6,4) - 1900) % 12;
        $post['zodiac'] = $animals[$key];
        //属相
        $signs = array(
            array('20'=>'水瓶座'), array('19'=>'双鱼座'),
            array('21'=>'白羊座'), array('20'=>'金牛座'),
            array('21'=>'双子座'), array('22'=>'巨蟹座'),
            array('23'=>'狮子座'), array('23'=>'处女座'),
            array('23'=>'天秤座'), array('24'=>'天蝎座'),
            array('22'=>'射手座'), array('22'=>'摩羯座')
        );
        $month = substr($id_card,10,2);
        $day= substr($id_card,12,2);
        $key = (int)$month - 1;
        list($startSign, $signName) = each($signs[$key]);
        if( $day < $startSign ){
            $key = $month - 2 < 0 ? $month = 11 : $month -= 2;
            list($startSign, $signName) = each($signs[$key]);
        }
        $post['constellation']=$signName;
        //年龄----不适用。。不适合保存
        $post['age'] = date('Y',time())-substr($id_card,6,4);

        $post['type']==1&&$number_1 = 'YS';//月嫂
        $post['type']==2&&$number_1 = 'YES';//育儿嫂
        $post['type']==3&&$number_1 = 'BM';//保姆
        $ayi_info = M('nurse')->field('number')->where('id='.$post['id'].'')->find();
        $date =substr($ayi_info['number'],-14);
        if(strlen($date)==14&&$date[0]==2){
            $post['number'] =$number_1.'-'.$date;
        }else{
            $post['number'] =$number_1.'-'.date('Ymd',time()).'-'.str_pad($post['id'],5,"0",STR_PAD_LEFT);
        }
        $save_mod = $Nurse->save_mod($where,$post);
        if($save_mod!==false){
            if($imgPath){
                unlink('Uploads/'.$post['title_img_old']);
            }
            echo "<script>alert('修改成功');window.location.href='" . __MODULE__ . "/Ayi/info/id/" . $post['id'] . "';</script>";

        }else{
            echo "<script>alert('修改失败');window.location.href='" . __MODULE__ . "/Ayi/editNurse_1/id/" . $post['id'] . "';</script>";
        }



    }

    //添加阿姨基本资料
    public function addInfo(){
        $have = M('nurse')->where('phone="'.I("post.phone").'" ')->find();
        if($have){
            echo "<script>alert('手机号已存在！');history.go(-1);</script>";
            exit;
        }
        $post = I('post.');
        $Nurse = D('Nurse');
        $post['add_time']=time();
        $post['others']='';
        for($i = 1;$i<3;$i++){
            $post['others'.$i]&& $post['others'] .= $post['others'.$i].',';
        }
        $post['others']&&$post['others'] = substr( $post['others'],0,-1);
        $post['skill'] = $post['skill1'].','.$post['skill2'].','.$post['skill3'].','.$post['skill4'].','.$post['skill5'].','.$post['skill6'].','.$post['skill7'];
        $id_card = $post['id_card'];
        //生肖
        $animals = array(
            '鼠', '牛', '虎', '兔', '龙', '蛇',
            '马', '羊', '猴', '鸡', '狗', '猪'
        );
        $key = (substr($id_card,6,4) - 1900) % 12;
        $post['zodiac'] = $animals[$key];
        //属相
        $signs = array(
            array('20'=>'水瓶座'), array('19'=>'双鱼座'),
            array('21'=>'白羊座'), array('20'=>'金牛座'),
            array('21'=>'双子座'), array('22'=>'巨蟹座'),
            array('23'=>'狮子座'), array('23'=>'处女座'),
            array('23'=>'天秤座'), array('24'=>'天蝎座'),
            array('22'=>'射手座'), array('22'=>'摩羯座')
        );
        $month = substr($id_card,10,2);
        $day= substr($id_card,12,2);
        $key = (int)$month - 1;
        list($startSign, $signName) = each($signs[$key]);
        if( $day < $startSign ){
            $key = $month - 2 < 0 ? $month = 11 : $month -= 2;
            list($startSign, $signName) = each($signs[$key]);
        }
        $post['constellation']=$signName;
        //年龄----不适用。。不适合保存
        $post['age'] = date('Y',time())-substr($id_card,6,4);

        $post['employee_name'] = $_SESSION[C('AYI_AUTH_KEY')]['username'];
        $id = $Nurse->add_mod($post);
        if($id) {
            if($post['is_training']) {
                $save_training['to_nurse'] = 2;
                M('tra_training')->where('id=' . $post['is_training'] . '')->save($save_training);
            }

            $name = $this->pinyin_long($post['name']);
            $post['type']==1&&$number_1 = 'YS';//月嫂
            $post['type']==2&&$number_1 = 'YES';//育儿嫂
            $post['type']==3&&$number_1 = 'BM';//保姆
            $map['number'] =$number_1.'-'.date('Ymd',time()).'-'.str_pad($id,5,"0",STR_PAD_LEFT);
            if($post['is_training']){
                $map['title_img'] = $post['title_img_training'];
                if($_FILES['title_img']['tmp_name']) {
                    $upload = new \Think\Upload();// 实例化上传类
                    $upload->maxSize = 1048576;// 设置附件上传大小
                    $upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
                    $upload->savePath = 'nurse/'.$id .'_'. $name . '/head/'; // 设置附件上传目录
                    $upload->subName = ''; // 设置附件上传目录
                    $info = $upload->uploadOne($_FILES['title_img']);
                    if (!$info) {// 上传错误提示错误信息
                        echo "<script>alert('图片上传失败');window.location.href='" . __MODULE__ . "/Ayi/editNurse_1/id/" . $id . "';</script>";
                    } else {// 上传成功
                        $imgPath = $info['savepath'] . $info['savename'];
                        $map['title_img'] = $imgPath;
                    }
                }

                $where['id'] = $id;
                $save = $Nurse->save_mod($where,$map);
                if ($save!==false) {
                    echo "<script>alert('添加成功');window.location.href='" . __MODULE__ . "/Ayi/addTest/id/" . $id . "/smp/1';</script>";
                } else {
                    echo "<script>alert('图片上传失败');window.location.href='" . __MODULE__ . "/Ayi/editNurse_1/id/" . $id . "';</script>";
                }
            }else{
                if ($_FILES['title_img']) {
                    $upload = new \Think\Upload();// 实例化上传类
                    $upload->maxSize = 1048576;// 设置附件上传大小
                    $upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
                    $upload->savePath = 'nurse/'.$id .'_'. $name . '/head/'; // 设置附件上传目录
                    $upload->subName = ''; // 设置附件上传目录
                    $info = $upload->uploadOne($_FILES['title_img']);
                    if (!$info) {// 上传错误提示错误信息
                        echo "<script>alert('图片上传失败');window.location.href='" . __MODULE__ . "/Ayi/editNurse_1/id/" . $id . "';</script>";
                    } else {// 上传成功
                        $imgPath = $info['savepath'] . $info['savename'];
                        $map['title_img'] = $imgPath;
                        $where['id'] = $id;
                        $save = $Nurse->save_mod($where,$map);
                        if ($save!==false) {
                            echo "<script>alert('添加成功');window.location.href='" . __MODULE__ . "/Ayi/addTest/id/" . $id . "/smp/1';</script>";
                        } else {
                            echo "<script>alert('图片上传失败');window.location.href='" . __MODULE__ . "/Ayi/editNurse_1/id/" . $id . "';</script>";
                        }
                    }
                } else {
                    echo "<script>alert('图片异常');window.location.href='" . __MODULE__ . "/Ayi/editNurse_1/id/" . $id . "';</script>";
                }
            }
        }else{
            echo "<script>alert('上传失败');history.back();</script>";
        }
    }

    // 月嫂头像上传/以及导师推荐视频
    public function addTest(){
        if(!($_SESSION[C('AYI_AUTH_KEY')]['permission']==1||$_SESSION[C('AYI_AUTH_KEY')]['add_per']==1||$_SESSION[C('AYI_AUTH_KEY')]['edit_per']==1)){
            echo "<script>alert('你没有权限！');window.location.href='" . __MODULE__ . "/Admin/info.html';</script>";
        }
        $id = I("get.id");
        $nurse = M('nurse')->field('id,name,test_img,recommend_video')->where('id='.$id.'')->find();
        if(!$nurse){
            echo "<script>alert('地址异常');history.back();</script>";
            exit;
        }
        $test_img = explode(',',$nurse['test_img']);

        $this->assign('test_img',$test_img);
        $this->assign('nurse',$nurse);
        $this->display();
    }

    //添加修改 导师推荐视频
    public function addTest_p(){
        $post = I('post.');
        $name = $this->pinyin_long($post['name']);
        $test_img0 = $post['test_img0'];
        $test_img1 =  $post['test_img1'];
        $test_img2 =  $post['test_img2'];
        $test_img3 =  $post['test_img3'];
        if($_FILES['test_img0']['tmp_name']) {
            $upload = new \Think\Upload();// 实例化上传类
            $upload->maxSize = 1048576;// 设置附件上传大小
            $upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
            $upload->savePath = 'nurse/' . $post['id'] . '_' . $name . '/test/'; // 设置附件上传目录
            $upload->subName = ''; // 设置附件上传目录
            $info = $upload->uploadOne($_FILES['test_img0']);
            if (!$info) {
                $error = $upload->getError();
                echo "<script>alert('第一张图".$error."');</script>";
            } else {
                $imgPath = $info['savepath'] . $info['savename'];
                $test_img0  = $imgPath;
            }
        }
        if($_FILES['test_img1']['tmp_name']) {
            $upload = new \Think\Upload();// 实例化上传类
            $upload->maxSize = 1048576;// 设置附件上传大小
            $upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
            $upload->savePath = 'nurse/' . $post['id'] . '_' . $name . '/test/'; // 设置附件上传目录
            $upload->subName = ''; // 设置附件上传目录
            $info = $upload->uploadOne($_FILES['test_img1']);
            if (!$info) {
                $error = $upload->getError();
                echo "<script>alert('第二张图".$error."');</script>";
            } else {
                $imgPath = $info['savepath'] . $info['savename'];
                $test_img1 = $imgPath;
            }
        }
        if($_FILES['test_img2']['tmp_name']) {
            $upload = new \Think\Upload();// 实例化上传类
            $upload->maxSize = 1048576;// 设置附件上传大小
            $upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
            $upload->savePath = 'nurse/' . $post['id'] . '_' . $name . '/test/'; // 设置附件上传目录
            $upload->subName = ''; // 设置附件上传目录
            $info = $upload->uploadOne($_FILES['test_img2']);
            if (!$info) {
                $error = $upload->getError();
                echo "<script>alert('第三张图".$error."');</script>";
            } else {
                $imgPath = $info['savepath'] . $info['savename'];
                $test_img2 = $imgPath;
            }
        }

        if($_FILES['test_img3']['tmp_name']) {
            $upload = new \Think\Upload();// 实例化上传类
            $upload->maxSize = 1048576;// 设置附件上传大小
            $upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
            $upload->savePath = 'nurse/' . $post['id'] . '_' . $name . '/test/'; // 设置附件上传目录
            $upload->subName = ''; // 设置附件上传目录
            $info = $upload->uploadOne($_FILES['test_img3']);
            if (!$info) {
                $error = $upload->getError();
                echo "<script>alert('第四张图".$error."');</script>";
            } else {
                $imgPath = $info['savepath'] . $info['savename'];
                $test_img3 = $imgPath;
            }
        }


        if($_FILES['recommend_video']['tmp_name']) {
            $upload = new \Think\Upload();// 实例化上传类
            $upload->maxSize = 55145728;// 设置附件上传大小
            $upload->exts = array('mp4', 'avi');// 设置附件上传类型
            $upload->savePath = 'nurse/' . $post['id'] . '_' . $name . '/video/'; // 设置附件上传目录
            $upload->subName = ''; // 设置附件上传目录
            $info = $upload->uploadOne($_FILES['recommend_video']);
            if (!$info) {
                $error = $upload->getError();
                echo "<script>alert('视频".$error."');</script>";
            } else {
                $imgPath = $info['savepath'] . $info['savename'];
                $map['recommend_video']= $imgPath;
            }
        }
        $map['test_img'] = $test_img0.','.$test_img1.','.$test_img2.','.$test_img3;
        $where['id'] = $post['id'];
        $save_mod = D('Nurse')->save_mod($where,$map);
        if($save_mod!==false){
            if($test_img0!=$post['test_img0']){
                unlink('Uploads/'.$post['test_img0']);
            }
            if($test_img1!=$post['test_img1']){
                unlink('Uploads/'.$post['test_img1']);
            }
            if($test_img2!=$post['test_img2']){
                unlink('Uploads/'.$post['test_img2']);
            }
            if($test_img3!=$post['test_img3']){
                unlink('Uploads/'.$post['test_img3']);
            }
            if( $map['recommend_video']){
                unlink('Uploads/'.$post['recommend_video']);
            }
            $type =  M('nurse')->field('type')->where('id='.$post['id'].'')->find();
            $post['type'] = $type['type'];
            if($_GET['smp']==1){
                echo "<script>alert('编辑成功');window.location.href='" . __MODULE__ . "/Ayi/addVideo/id/" . $post['id'] . "';</script>";
            }else{
                echo "<script>alert('编辑成功');window.location.href='" . __MODULE__ . "/Ayi/info/id/" . $post['id'] . "';</script>";
            }
        }else{
            echo "<script>alert('上传失败');history.back();</script>";
        }
    }

    // 月嫂头像上传/以及导师推荐视频
    public function addVideo(){
        if(!($_SESSION[C('AYI_AUTH_KEY')]['permission']==1||$_SESSION[C('AYI_AUTH_KEY')]['add_per']==1||$_SESSION[C('AYI_AUTH_KEY')]['edit_per']==1)){
            echo "<script>alert('你没有权限！');window.location.href='" . __MODULE__ . "/Admin/info.html';</script>";
        }

        $id = I("get.id");
        $nurse = M('nurse')->field('id,name,do_word,do_video')->where('id='.$id.'')->find();
        if(!$nurse){
            echo "<script>alert('地址异常');history.back();</script>";
            exit;
        }
        $do_word = $nurse['do_word']?explode(',',$nurse['do_word']):'';
        $do_video = $nurse['do_video']?explode(',',$nurse['do_video']):'';
        foreach($do_word as $k=>$v){
            $video_info[$k]['do_word'] = $v;
            $video_info[$k]['do_video'] = $do_video[$k];
        }
        $this->assign('video_info',$video_info);
        $this->assign('nurse',$nurse);
        $this->display();
    }


    //添加视频 ajax
    public function addVideo_ajax()
    {
        $post = I('post.');
        $name = $this->pinyin_long($post['name']);
        $error=0;
        $Nurse_mod = D("Nurse");
        for($i=0;$i<count($post)/2-1;$i++){
            if($post['do_word'.$i]){
                if($_FILES['do_video'.$i]['tmp_name']) {
                    $upload = new \Think\Upload();// 实例化上传类
                    $upload->maxSize = 55145728;// 设置附件上传大小
                    $upload->exts = array('mp4', 'avi');// 设置附件上传类型
                    $upload->savePath = 'nurse/' . $post['id'] . '_' . $name . '/video/'; // 设置附件上传目录
                    $upload->subName = ''; // 设置附件上传目录
                    $info = $upload->uploadOne($_FILES['do_video' . $i]);
                    if (!$info){
                        if ($post['old_src' . $i]) {
                            $do_video[] = $post['old_src' . $i];
                            $do_word[] = $post['do_word' . $i];
                        }
                        $error++;
                    } else {
                        $do_video[]= $info['savepath'] . $info['savename'];
                        $do_video_ok[] = $info['savepath'] . $info['savename'];
                        $do_word[] = $post['do_word' . $i];
                    }
                }else{
                    if ($post['old_src' . $i]) {
                        $do_video[] = $post['old_src' . $i];
                        $do_word[] = $post['do_word' . $i];
                    }
                }
            }
        }
        $nurse_info = M('nurse')->field('do_video')->where('id='.$post['id'].'')->find();
        $do_video_old = explode(',',$nurse_info['do_video']);
        $map['do_video'] = implode(',',$do_video);
        $map['do_word'] = implode(',',$do_word);
        $where['id']=$post['id'];


        $type =  M('nurse')->field('type')->where('id='.$post['id'].'')->find();
        $post['type'] = $type['type'];

        $save_mode = $Nurse_mod->save_mod($where,$map);
        if($save_mode!==false){
            foreach($do_video_old as $v){
                if(!in_array($v,$do_video)){
                    unlink('Uploads/'.$v);
                }
            }
            if($error==0){
                echo "<script>alert('编辑成功');window.location.href='" . __MODULE__ . "/Ayi/info/id/" . $post['id'] . "';</script>";
            }else{
                echo "<script>alert('编辑成功,".$error."个视频未成功上传');window.location.href='" . __MODULE__ . "/Ayi/info/id/" . $post['id'] . "';</script>";
            }
        }else{
            foreach($do_video_ok as $v){
                unlink('Uploads/'.$v);
            }
            echo "<script>alert('编辑失败,不做任何修改');window.location.href='" . __MODULE__ . "/Ayi/addVideo/id/" . $post['id'] . "';</script>";
        }
    }

    //阿姨详细页面数据
    public  function info(){

        $nurse_info = M("nurse")->where('id='.I('get.id').'')->find();

        $educational = array('','小学', '初中', '高中', '本科', '本科以上');
        $type = array('','月嫂', '育儿嫂', '保姆');
        $others = explode(',',$nurse_info['others']);

        $nurse_info['others'] = $type[$others[0]]? $type[$others[0]].($type[$others[1]]?'、'.$type[$others[1]]:''):'无';


        $nurse_info['educational'] = $educational[$nurse_info['educational']];
        $nurse_info['ayi_type'] = $nurse_info['type'];
        $nurse_info['type'] = $type[$nurse_info['type']];

        $nurse_info['age'] = $nurse_info['id_card']?date('Y',time())-substr($nurse_info['id_card'],6,4):'';

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
        $nurse_use = M("nurse_use")->where('nurse_id='.I('get.id').'')->order('b_time desc')->select();

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

    public function go_training(){
        $id = I('get.id');
        $_SESSION[C('TRAINING_AUTH_KEY')]['id'] = 99;
        $_SESSION[C('TRAINING_AUTH_KEY')]['username'] = '查看基本信息';
        $_SESSION[C('TRAINING_AUTH_KEY')]['permission'] = 2;
        $_SESSION[C('TRAINING_AUTH_KEY')]['status'] = 1;
        $_SESSION[C('TRAINING_AUTH_KEY')]['real_name'] = '查看基本信息';
        $_SESSION[C('TRAINING_AUTH_KEY')]['edit_per'] = 0;
        $_SESSION[C('TRAINING_AUTH_KEY')]['add_per'] = 0;
        $_SESSION[C('TRAINING_AUTH_KEY')]['del_per'] = 0;
        $_SESSION[C('TRAINING_AUTH_KEY')]['nurse'] = 1;
        $_SESSION[C('TRAINING_AUTH_KEY')]['training'] = 1;
        $_SESSION[C('TRAINING_AUTH_KEY')]['order'] = 0;

        echo "<script>window.location.href='".__ROOT__."/training.php/Training/info/id/".$id.".html';</script>";


    }

    //联系记录
    public function record(){
        if(I("post.")){
            $post = I("post.");
            $post['add_time'] = time();
            $record_id = M('nurse_record')->add($post);
            if($record_id){
                echo "<script>alert('添加成功');window.location.href='".__MODULE__."/Ayi/record/id/".$post['nurse_id'].".html';</script>";
            }else{
                echo "<script>alert(' 添加失败');history.back();</script>";
            }
        }else{
            $nurse_info = M("nurse")->field('id,status,number,name')->where('id='.I('get.id').'')->find();

            $record = M("nurse_record")->where('nurse_id='.I('get.id').'')->order('add_time desc')->select();


            $this->assign('record',$record);
            $this->assign('info',$nurse_info);
            $this->display();
        }

    }

    //添加或修改上单记录
    public function addUse(){
        if(!($_SESSION[C('AYI_AUTH_KEY')]['permission']==1||$_SESSION[C('AYI_AUTH_KEY')]['add_per']==1||$_SESSION[C('AYI_AUTH_KEY')]['edit_per']==1)){
            $back['code'] = 1005;//无权限
            echo json_encode($back);
            die;
        }
        $post = I("post.");
        $repeat = M('nurse_use')->where('nurse_id='.$post['nurse_id'].' and id!='.$post['use_id'].' and ("'.$post['s_time'].'" BETWEEN b_time and s_time      OR   "'.$post['b_time'].'" BETWEEN b_time and s_time)')->find();
        if($repeat){
            $back['code'] = 1002;
            echo json_encode($back);
            die;
        }
        if($post['use_id']&&$post['use_id']!=0){
            $add = M("nurse_use")->where('id='.$post['use_id'].'')->save($post);
        }else{
            $post['add_time'] = time();
            $add = M("nurse_use")->add($post);
        }
        if($add!==false){
            $back['code'] = 1000;
            echo json_encode($back);
            die;
        }else{
            $back['code'] = 1001;
            echo json_encode($back);
            die;
        }
    }

    // 删除上单记录
    public function delUse(){
        $post = I("post.");
        $delete = M("nurse_use")->where('id='.$post['id'].'')->delete();
        if($delete!==false){
            $back['code'] = 1000;
        }else{
            $back['code'] = 1001;
        }
        echo json_encode($back);
    }

    public function getAyilist()
    {
        $post = I("post.");
        $currentpage = I("post.currentpage");
        $pagenum = I("post.pagenum");
        $start = ($currentpage - 1) * $pagenum;
        $post['type'] || $post['type'] = 1;
        if ($post['type'] == 30) {
            if ($post['name'] && $post['name'] != '') {
               $where = ' and  name LIKE "%'.$post['name'].'%"';
            }

            $list = M('tra_training')->where('to_nurse=1'.$where)->order('to_nurse_time desc')->select();
            $count = M('tra_training')->where('to_nurse=1'.$where)->count();
            foreach($list as $k=>$v){
                $list[$k]['to_nurse_time'] = date('Y-m-d H:i:s',$v['to_nurse_time']);
            }

            $back['code'] = 1000;
            $back['data']['list'] = $list;
            $back['data']['type'] = $post['type'];
            $back['data']['num'] = $count;
            echo json_encode($back);
        } else {
                if ($post['type'] > 9) {//黑名单或者被删除的
                    $where = 'status='.$post['type'];//普通查询条件。。。可以被sql注入。不推荐使用
//                    $where['status'] = ':status';
//                    $bind[':status'] = array($post['type'], \PDO::PARAM_INT);
                    if ($post['name'] && $post['name'] != '') {
                        if($post['name'] == '学员'){
                            $where .= ' and is_training >0 ';
                        }else{
                            $where .= ' and name LIKE "%'.$post['name'].'%"';
                        }
    //                $where .= ' and name LIKE "%'.$post['name'].'%" ';//普通查询条件。。。可以被sql注入。不推荐使用
                    }

                    $field = 'id,number,is_training,title_img,name,id_card,l_price,h_price,zodiac,work_date,type,others,remark,del_time,status';
                    $order = 'del_time desc';

                    $nurse = M("nurse")->field($field)->where($where)->order($order)->limit($start, $pagenum)->select();
                    $count = M("nurse")->where($where)->count();
                } elseif ($post['type'] == 5) {
                    $where = 'status<10 ';
                    $post['age1'] && $where .= ' and right(left(id_card,10),4) <= ' . (date('Y') - $post['age1']);
    //            $post['age1']&& $where['right(left(id_card,10),4)'] = array('elt', ':id_card');
    //            $post['age1']&& $bind[':id_card'] = array((date('Y')-$post['age1']), \PDO::PARAM_INT);


                    $post['age2'] && $where .= ' and right(left(id_card,10),4) >= ' . (date('Y') - $post['age2']);

    //            $post['age2']&& $where['right(left(id_card,10),4)'] = array('egt', ':id_card');
    //            $post['age2']&& $bind[':id_card'] = array((date('Y')-$post['age2']), \PDO::PARAM_INT);

                    if ($post['l_price'] && $post['h_price']) {
                        $where .= ' and (' . $post['l_price'] . ' <= h_price  OR   ' . $post['h_price'] . ' >= l_price )';
                    } else if ($post['l_price']) {
                        $where .= ' and (' . $post['l_price'] . ' <= h_price )';
                    } elseif ($post['h_price']) {
                        $where .= ' and ( ' . $post['h_price'] . ' >= l_price  )';
                    }

                    if ($post['zodiac'] && $post['zodiac'] != '0') {
                        $where .= ' and zodiac="' . $post['zodiac'] . '"';
                    }
                    if ($post['b_time'] && $post['s_time']) {
                        $where .= ' and  not EXISTS (SELECT nurse_id from nurse_use where (("' . $post['b_time'] . '" > b_time and "' . $post['b_time'] . '" < s_time OR "' . $post['s_time'] . '" > b_time and "' . $post['s_time'] . '"< s_time) OR ("' . $post['b_time'] . '" <= b_time  and  "' . $post['s_time'] . '" >= s_time)) and nurse_use.nurse_id=nurse.id  )';
                    }
                    if ($post['employee_name'] != '0') {
                        $where .= ' and nurse_record.employee_name LIKE "%' . $post['employee_name'] . '%" ';
                    }

                    if ($post['add_time_s']) {
                        $where .= '  and nurse.add_time <' . strtotime($post['add_time_s']);
                    }
                    if ($post['add_time_b']) {
                        $where .= '  and nurse.add_time >' . strtotime($post['add_time_b']);
                    }

                    if ($post['name'] && $post['name'] != '') {
                        if($post['name'] == '学员'){
                            $where = '  is_training >0 ';
                        }else{
                            $where = '(name LIKE "%' . $post['name'] . '%" OR phone LIKE "%' . $post['name'] . '%" OR urgent_phone LIKE "%' . $post['name'] . '%" )';
                        }
                    }

                    $field = 'nurse.id,nurse.is_training,nurse.add_time as add_time_nurse,number,title_img,name,id_card,l_price,h_price,zodiac,work_date,type,others,nurse_record.remark,del_time,status,nurse_record.add_time,nurse_record.employee_name';
                    $nurse = M("nurse")->field($field)->where($where)->join('right join nurse_record ON nurse_record.nurse_id = nurse.id')->group('nurse_record.nurse_id')->order('nurse_record.add_time desc')->limit($start, $pagenum)->select();
    //        echo M("nurse")->getLastSql();die;
                    $count = M("nurse")->where($where)->join('right join nurse_record ON nurse_record.nurse_id = nurse.id')->count('DISTINCT(nurse.id)');
    //                    echo M("nurse")->getLastSql();die;
                } elseif ($post['type'] == 8) {//\进3个月生日
                    $three_mounth = date('md', strtotime('3 MONTH'));
                    $today = date('md');
                    $where = 'right(left(id_card,14),4) <= ' . $three_mounth . ' and right(left(id_card,14),4) >= ' . $today . '';
                    $post['age1'] && $where .= ' and right(left(id_card,10),4) <= ' . (date('Y') - $post['age1']);
    //            $post['age1']&& $where['right(left(id_card,10),4)'] = array('elt', ':id_card');
    //            $post['age1']&& $bind[':id_card'] = array((date('Y')-$post['age1']), \PDO::PARAM_INT);


                    $post['age2'] && $where .= ' and right(left(id_card,10),4) >= ' . (date('Y') - $post['age2']);

    //            $post['age2']&& $where['right(left(id_card,10),4)'] = array('egt', ':id_card');
    //            $post['age2']&& $bind[':id_card'] = array((date('Y')-$post['age2']), \PDO::PARAM_INT);

                    if ($post['l_price'] && $post['h_price']) {
                        $where .= ' and (' . $post['l_price'] . ' <= h_price  OR   ' . $post['h_price'] . ' >= l_price )';
                    } else if ($post['l_price']) {
                        $where .= ' and (' . $post['l_price'] . ' <= h_price )';
                    } elseif ($post['h_price']) {
                        $where .= ' and ( ' . $post['h_price'] . ' >= l_price  )';
                    }

                    if ($post['zodiac'] && $post['zodiac'] != '0') {
                        $where .= ' and zodiac="' . $post['zodiac'] . '"';
                    }
                    if ($post['b_time'] && $post['s_time']) {
                        $where .= ' and  not EXISTS (SELECT nurse_id from nurse_use where (("' . $post['b_time'] . '" > b_time and "' . $post['b_time'] . '" < s_time OR "' . $post['s_time'] . '" > b_time and "' . $post['s_time'] . '"< s_time) OR ("' . $post['b_time'] . '" <= b_time  and  "' . $post['s_time'] . '" >= s_time)) and nurse_use.nurse_id=nurse.id  )';
                    }
                    if ($post['add_time_s']) {
                        $where .= '  and nurse.add_time <' . strtotime($post['add_time_s']);
                    }
                    if ($post['add_time_b']) {
                        $where .= '  and nurse.add_time >' . strtotime($post['add_time_b']);
                    }

                    if ($post['name'] && $post['name'] != '') {
                        if($post['name'] == '学员'){
                            $where = '  is_training >0 ';
                        }else{
                            $where = '(type=' . $post['type'] . ' OR others LIKE "%' . $post['type'] . '%") and  (name LIKE "%' . $post['name'] . '%" OR phone LIKE "%' . $post['name'] . '%" OR urgent_phone LIKE "%' . $post['name'] . '%" )';
                        }


                    }


                    $where .= ' and status < 10';
    //            $where['status'] = array('lt',':status');
    //            $bind[':status'] = array(10,\PDO::PARAM_INT);

                    $field = 'id,number,is_training,is_training,title_img,name,id_card,l_price,h_price,zodiac,work_date,type,others,status,specialty,add_time as add_time_nurse';
                    $order = 'right(left(id_card,14),4) asc';
                    $nurse = M("nurse")->field($field)->where($where)->order($order)->limit($start, $pagenum)->select();
    //            echo M("nurse")->getLastSql();die;

                    $count = M("nurse")->where($where)->count();
                } else {
                    if ($post['jianzhi'] != 1) {
                        $where = 'type=' . $post['type'];
    //                $where['type'] = ':type';
    //                $bind[':type'] = array($post['type'], \PDO::PARAM_INT);

                    } else {
                        $post['jianzhi'] == 1 && $where = 'others LIKE "%' . $post['type'] . '%"';
    //                $where['others'] = array('LIKE', ':others');
    //                $bind[':others'] = array('%' . $post['type'] . '%', \PDO::PARAM_STR);
                    }

                    $post['age1'] && $where .= ' and right(left(id_card,10),4) <= ' . (date('Y') - $post['age1']);
    //            $post['age1']&& $where['right(left(id_card,10),4)'] = array('elt', ':id_card');
    //            $post['age1']&& $bind[':id_card'] = array((date('Y')-$post['age1']), \PDO::PARAM_INT);


                    $post['age2'] && $where .= ' and right(left(id_card,10),4) >= ' . (date('Y') - $post['age2']);

    //            $post['age2']&& $where['right(left(id_card,10),4)'] = array('egt', ':id_card');
    //            $post['age2']&& $bind[':id_card'] = array((date('Y')-$post['age2']), \PDO::PARAM_INT);

                    if ($post['l_price'] && $post['h_price']) {
                        $where .= ' and (' . $post['l_price'] . ' <= h_price  OR   ' . $post['h_price'] . ' >= l_price )';
                    } else if ($post['l_price']) {
                        $where .= ' and (' . $post['l_price'] . ' <= h_price )';
                    } elseif ($post['h_price']) {
                        $where .= ' and ( ' . $post['h_price'] . ' >= l_price  )';
                    }

                    if ($post['zodiac'] && $post['zodiac'] != '0') {
                        $where .= ' and zodiac="' . $post['zodiac'] . '"';
                    }
                    if ($post['b_time'] && $post['s_time']) {
                        $where .= ' and  not EXISTS (SELECT nurse_id from nurse_use where (("' . $post['b_time'] . '" > b_time and "' . $post['b_time'] . '" < s_time OR "' . $post['s_time'] . '" > b_time and "' . $post['s_time'] . '"< s_time) OR ("' . $post['b_time'] . '" <= b_time  and  "' . $post['s_time'] . '" >= s_time)) and nurse_use.nurse_id=nurse.id  )';
                    }
                    if ($post['add_time_s']) {
                        $where .= '  and nurse.add_time <' . strtotime($post['add_time_s']);
                    }
                    if ($post['add_time_b']) {
                        $where .= '  and nurse.add_time >' . strtotime($post['add_time_b']);
                    }

                    if ($post['name'] && $post['name'] != '') {
                        if($post['name'] == '学员'){
                            $where = '  is_training >0 ';
                        }else{
                            $where = '(type=' . $post['type'] . ' OR others LIKE "%' . $post['type'] . '%") and (name LIKE "%' . $post['name'] . '%" OR phone LIKE "%' . $post['name'] . '%" OR urgent_phone LIKE "%' . $post['name'] . '%" ) ';
                        }


                    }


                    $where .= ' and status < 10';
    //            $where['status'] = array('lt',':status');
    //            $bind[':status'] = array(10,\PDO::PARAM_INT);

                    $field = 'id,number,is_training,title_img,name,id_card,l_price,h_price,zodiac,work_date,type,others,status,specialty,add_time as add_time_nurse';
                    $order = 'add_time desc';


                    $nurse = M("nurse")->field($field)->where($where)->order($order)->limit($start, $pagenum)->select();
    //        echo M("nurse")->getLastSql();die;
                    $count = M("nurse")->where($where)->count();
                }


            $belong = array(
                '', '月嫂', '育儿嫂', '保姆'
            );
            foreach ($nurse as $k => $v) {
                if ($post['type'] == 5) {
                    $nurse[$k]['add_time'] = date('Y-m-d', $v['add_time']);
                    $nurse[$k]['add_time_nurse'] = date('Y-m-d', $v['add_time_nurse']);
                }
                $nurse[$k]['age'] = date('Y', time()) - substr($v['id_card'], 6, 4);
                $nurse_use = M("nurse_use")->field('b_time,s_time,remark')->where('nurse_id=' . $v['id'] . '')->order('b_time desc')->select();
                $nurse[$k]['nurse_use'] = $nurse_use;
                if ($v['status'] > 9) {
                    $nurse[$k]['del_time'] = date('Y-m-d', $v['del_time']);
                } else {
                    $nurse[$k]['del_time'] = '';
                    $nurse[$k]['add_time_nurse'] = date('Y-m-d', $v['add_time_nurse']);
                }

                if ($post['type'] == 8) {
                    $nurse[$k]['birthday'] = substr($v['id_card'], 6, 4) . '-' . substr($v['id_card'], 10, 2) . '-' . substr($v['id_card'], 12, 2);
                }
                $type_name = $belong[$v['type']];
                $others = explode(',', $v['others']);
                $others_name1 = $belong[$others[0]];
                $others_name2 = $belong[$others[1]];

                $educational = ['', '小学', '初中', '高中', '大专', '本科'];

                $nurse[$k]['educational'] = $educational[$v['educational']];
                $nurse[$k]['type_name'] = $type_name;
                $nurse[$k]['others_name1'] = $others_name1 ? $others_name1 : '';
                $others_name2 && $others_name2 = '、' . $others_name2;
                $nurse[$k]['others_name2'] = $others_name2 ? $others_name2 : '';
            }

            $back['code'] = 1000;
            $back['data']['list'] = $nurse;
            $back['data']['type'] = $post['type'];
            $back['data']['num'] = $count;
            echo json_encode($back);
        }
    }





    // 删除月嫂
    public function deleteNurse(){
        $admin_user = M('permission')->where('employee_id='. $_SESSION[C('AYI_AUTH_KEY')]['id'].'')->find();
        if($admin_user['del_per']!=1){
            $back['code'] = 1003;//无权限
            echo json_encode($back);
        }else{
            $info = M('nurse')->field('id,type,title_img')->where('id='.I('post.id').'')->find();
            if($info['status']==10||$info['status']==20){
                $back['code'] = 1002;//已经被删除或已经被加入黑名单
                echo json_encode($back);
            }

            $save['status']=I("post.status");
            $save['remark']=I("post.remark_del");
            $save['del_time']=time();
            $delete = M('nurse')->where('id='.I('post.id').'')->save($save);
            if($delete!==false){
//                $img = explode('/',$info['title_img']);
//                $dir = 'Uploads/'.'nurse/'.$img[1];
//                $this->deldir($dir);
                $back['code'] = 1000;
                echo json_encode($back);
            }else{
                $back['code'] = 1001;
                echo json_encode($back);
            }
        }
    }

    public function recommend(){
        $admin_user = M('permission')->where('employee_id='. $_SESSION[C('AYI_AUTH_KEY')]['id'].'')->find();
        if($admin_user['del_per']!=1){
            echo "<script>alert('你没有权限这么做');history.back();</script>";
        }else {
            $map['is_recommend'] = I("get.is_recommend");
            if(I("get.is_recommend")==1){
                $map['recommend_time'] = time();
            }else{
                $map['recommend_time'] = '';
            }
            $where ['id'] =I("get.id");
            $nurse = D("Nurse");
            $save_mod = $nurse->save_mod($where,$map);
            if($save_mod!==false){
                if(I('get.p')){
                    echo "<script>alert(' 设置成功');window.location.href='".__MODULE__."/Ayi/index/p/".I('get.p').".html';</script>";
                }else{
                    echo "<script>alert('设置成功');window.location.href='".__MODULE__."/Ayi/index/p/1.html';</script>";
                }
            }else{
                echo "<script>alert('设置失败');window.location.href='".__MODULE__."/Ayi/index/p/1.html';</script>";
            }
        }
    }

//阿姨管理系统统计信息excel表。
    public function to_excel(){
        $str = "日期\t护理人员总量\t学员总量\t月嫂总量\t育儿嫂总量\t保姆总量\t钟点工总量\t新增量\t新学员量\t联系量\t新增月嫂\t新增育儿嫂\t新增保姆\t新增钟点工\t淘汰量\t\n";
        if(I('post.time_b_1')&&I('post.time_s_1')){
            I('post.time_s_1')>date('Y-m-d')?($time_s = date('Y-m-d')):($time_s = I('post.time_s_1'));
            $add_time = M('nurse')->field('add_time')->where('add_time!=""')->order('add_time asc')->find();
            I('post.time_b_1')<date('Y-m-d',$add_time['add_time'])?($time_b = date('Y-m-d',$add_time['add_time'])):($time_b = I('post.time_b_1'));
            for($day=$time_b;$day<=$time_s;$day=date('Y-m-d',(strtotime($day)+86400))){
                $day_num = strtotime($day);
                $excel_1 = M('nurse')->field('count(id) as number')->where('add_time<="'.($day_num+86400).'" and status<10 and type!=""')->find();
                $excel_2 = M('nurse')->field('count(id) as number')->where('add_time<="'.($day_num+86400).'" and status<10 and is_training>0 and type!=""')->find();
                $excel_3 = M('nurse')->field('count(id) as number')->where('add_time<="'.($day_num+86400).'" and status<10 and type=1')->find();
                $excel_4 = M('nurse')->field('count(id) as number')->where('add_time<="'.($day_num+86400).'" and status<10 and type=2')->find();
                $excel_5 = M('nurse')->field('count(id) as number')->where('add_time<="'.($day_num+86400).'" and status<10 and type=3')->find();
                $excel_6 = M('nurse')->field('count(id) as number')->where('add_time<="'.($day_num+86400).'" and status<10 and type=4')->find();
                $excel_7 = M('nurse')->field('count(id) as number')->where('add_time >="'.$day_num.'" and add_time<="'.($day_num+86400).'" and status<10 and type!=""')->find();
                $excel_8 = M('nurse')->field('count(id) as number')->where('add_time >="'.$day_num.'" and add_time<="'.($day_num+86400).'" and status<10 and is_training>0 and type!=""')->find();

                $excel_9 = M('nurse_record')->field('count(id) as number')->where('add_time >="'.$day_num.'" and add_time<="'.($day_num+86400).'"')->find();

                $excel_10 = M('nurse')->field('count(id) as number')->where('add_time >="'.$day_num.'" and add_time<="'.($day_num+86400).'" and status<10  and type=1')->find();
                $excel_11 = M('nurse')->field('count(id) as number')->where('add_time >="'.$day_num.'" and add_time<="'.($day_num+86400).'" and status<10  and type=2')->find();
                $excel_12 = M('nurse')->field('count(id) as number')->where('add_time >="'.$day_num.'" and add_time<="'.($day_num+86400).'" and status<10  and type=3')->find();
                $excel_13 = M('nurse')->field('count(id) as number')->where('add_time >="'.$day_num.'" and add_time<="'.($day_num+86400).'" and status<10  and type=4')->find();

                $excel_14 = M('nurse')->field('count(id) as number')->where('del_time >="'.$day_num.'" and del_time<="'.($day_num+86400).'" and status>=10  and type!=""')->find();

                $str .= $day."\t".$excel_1['number']."\t".$excel_2['number']."\t".$excel_3['number']."\t".$excel_4['number']."\t".$excel_5['number']."\t".$excel_6['number']."\t".$excel_7['number']."\t".$excel_8['number']."\t".$excel_9['number']."\t".$excel_10['number']."\t".$excel_11['number']."\t".$excel_12['number']."\t".$excel_13['number']."\t".$excel_14['number']."\t\n";
            }
        }else{
            $day = date("Y-m-d");
            $day_num = strtotime($day);
            $excel_1 = M('nurse')->field('count(id) as number')->where('add_time<="'.($day_num+86400).'" and status<10 and type!=""')->find();
            $excel_2 = M('nurse')->field('count(id) as number')->where('add_time<="'.($day_num+86400).'" and status<10 and is_training>0 and type!=""')->find();
            $excel_3 = M('nurse')->field('count(id) as number')->where('add_time<="'.($day_num+86400).'" and status<10 and type=1')->find();
            $excel_4 = M('nurse')->field('count(id) as number')->where('add_time<="'.($day_num+86400).'" and status<10 and type=2')->find();
            $excel_5 = M('nurse')->field('count(id) as number')->where('add_time<="'.($day_num+86400).'" and status<10 and type=3')->find();
            $excel_6 = M('nurse')->field('count(id) as number')->where('add_time<="'.($day_num+86400).'" and status<10 and type=4')->find();
            $excel_7 = M('nurse')->field('count(id) as number')->where('add_time >="'.$day_num.'" and add_time<="'.($day_num+86400).'" and status<10 and type!=""')->find();
            $excel_8 = M('nurse')->field('count(id) as number')->where('add_time >="'.$day_num.'" and add_time<="'.($day_num+86400).'" and status<10 and is_training>0 and type!=""')->find();

            $excel_9 = M('nurse_record')->field('count(id) as number')->where('add_time >="'.$day_num.'" and add_time<="'.($day_num+86400).'"')->find();

            $excel_10 = M('nurse')->field('count(id) as number')->where('add_time >="'.$day_num.'" and add_time<="'.($day_num+86400).'" and status<10  and type=1')->find();
            $excel_11 = M('nurse')->field('count(id) as number')->where('add_time >="'.$day_num.'" and add_time<="'.($day_num+86400).'" and status<10  and type=2')->find();
            $excel_12 = M('nurse')->field('count(id) as number')->where('add_time >="'.$day_num.'" and add_time<="'.($day_num+86400).'" and status<10  and type=3')->find();
            $excel_13 = M('nurse')->field('count(id) as number')->where('add_time >="'.$day_num.'" and add_time<="'.($day_num+86400).'" and status<10  and type=4')->find();

            $excel_14 = M('nurse')->field('count(id) as number')->where('del_time >="'.$day_num.'" and del_time<="'.($day_num+86400).'" and status>=10  and type!=""')->find();

            $str .= $day."\t".$excel_1['number']."\t".$excel_2['number']."\t".$excel_3['number']."\t".$excel_4['number']."\t".$excel_5['number']."\t".$excel_6['number']."\t".$excel_7['number']."\t".$excel_8['number']."\t".$excel_9['number']."\t".$excel_10['number']."\t".$excel_11['number']."\t".$excel_12['number']."\t".$excel_13['number']."\t".$excel_14['number']."\t\n";

        }

        $str = iconv('utf-8','gb2312',$str);
        $filename = '阿姨管理系统'.I('post.time_b_1').'----'.I('post.time_s_1').'统计报表.xls';

        $this->exportExcel($filename,$str);
    }



    public function excel_info(){
        $ayi = M('nurse')->select();
        $belong = array(
            '', '月嫂', '育儿嫂', '保姆'
        );

        $educational = ['', '小学', '初中', '高中', '大专', '本科'];
        $str ="ID\t姓名\t类型\t备注\t放弃(删除)时间\t身份证\t价格\t学历\t年龄\t属相\t星座\t籍贯\t身高\t体重\t婚姻\t生育\t带bb数量\t技能\t家庭情况\t证书\t领证日期\t工作年限\t培训机构\t电话\t紧急电话\t微信\t添加员工\t添加时间\n";
        foreach($ayi as $k=>$v){
            $str .=  $v['id']."\t". $v['name']."\t". $belong[$v['type']]."\t". $v['remark']."\t".date('Y-m-d H:i:s',$v['del_time'])."\t".$v['id_card']."\t".($v['l_price']."--".$v['h_price'])."\t". $educational[$v['educational']]."\t".$v['age']."\t". $v['zodiac']."\t". $v['constellation']."\t".$v['native_place']."\t".$v['hight']."\t".$v['weight']."\t". $v['marrage']."\t".$v['birth']."\t".$v['baby_num']."\t". $v['skill']."\t".$v['family']."\t".$v['zhengshu']."\t".$v['zhengshu_date']."\t". $v['work_date']."\t".$v['train']."\t".$v['phone']."\t".$v['urgent_phone']."\t".$v['wechat']."\t".$v['employee_name']."\t".date('Y-m-d H:i:s',$v['add_time'])."\t\n";
        }

        $str = iconv('utf-8','GBK',$str);
        $filename = '阿姨管理系统信息报表.xls';
        $this->exportExcel($filename,$str);

    }
}