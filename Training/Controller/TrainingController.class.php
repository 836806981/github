<?php
namespace Training\Controller;
use Think\Controller;
class TrainingController extends CommonController {
    public function  _initialize(){
        if(!$_SESSION[C('TRAINING_AUTH_KEY')]['id']){
            echo "<script>alert('请登录！');window.location.href='" . __MODULE__ . "/Admin/login.html';</script>";
            exit;
        }
    }

    public function to_zodiac(){
        $training = M('tra_training')->select();
        $animals = array(
            '鼠', '牛', '虎', '兔', '龙', '蛇',
            '马', '羊', '猴', '鸡', '狗', '猪'
        );
        foreach($training as $k=>$v){
            $key = (substr($v['id_card'],6,4) - 1900) % 12;
            $save['zodiac'] =  $animals[$key];
            M('tra_training')->where('id='.$v['id'])->save($save);
        }


//       echo '成功';
    }

    public function info(){

        $training = M('tra_training')->where('id='.I('get.id').'')->find();
        if(!$training){
            echo "<script>alert('地址异常！');history.back();</script>";
            exit;
        }

        if($training['price_time']){
            $training['skills'] = explode(',',$training['skill']);
        }

        $priority_arr = ['','小学','初中','高中','专科','本科'];
        $training['educational_name'] = $priority_arr[$training['educational']];


        $this->assign('training',$training);
        $this->display();
    }

    public function stay(){

        if(I('post.')){
            $post = I('post.');
            if($post['stay_id']&&$post['stay_id']!=0){
                $where['id'] = $post['stay_id'];
                $add = M('tra_stay')->where($where)->save($post);
            }else{
                $post['mac'] = $this->GetMacAddr(PHP_OS);
                $post['add_time'] = time();

                $add = M('tra_stay')->add($post);
            }
            if($add!==false){
                echo "<script>alert('保存成功！');window.location.href='" . __MODULE__ . "/Training/stay/id/".$post['training_id'].".html';</script>";
            }else{
                echo "<script>alert('保存失败！');history.back();</script>";
            }
        }else{
            $stay  = M('tra_stay')->where('training_id='.I('get.id').'')->order('add_time desc')->select();
            $training = M('tra_training')->where('id='.I('get.id').'')->find();
            if(!$training){
                echo "<script>alert('地址异常！');history.back();</script>";
                exit;
            }
            if($training['price_time']){
                $training['skills'] = explode(',',$training['skill']);
            }

            $priority_arr = ['','小学','初中','高中','专科','本科'];
            $training['educational_name'] = $priority_arr[$training['educational']];
            $this->assign('training',$training);
            $this->assign('stay',$stay);
            $this->display();

        }
    }


    public function delStay(){
       $delete =  M('tra_stay')->where('id='.I('post.id').'')->delete();
        if($delete){
            $back['code'] = 1000;
            echo json_encode($back);
        }else{
            $back['code'] = 1001;
            echo json_encode($back);
        }

    }
    public function hygiene(){
         if(I('post.')){
            $post = I('post.');
             if($post['hygiene_id']&&$post['hygiene_id']!=0){
                 $post['mac'] = $this->GetMacAddr(PHP_OS);
                 $where['id'] = $post['hygiene_id'];
                 $add = M('tra_hygiene')->where($where)->save($post);
             }else{
                $post['mac'] = $this->GetMacAddr(PHP_OS);
                $post['add_time'] = time();
                $add = M('tra_hygiene')->add($post);
             }
             if($add!==false){
                echo "<script>alert('保存成功！');window.location.href='" . __MODULE__ . "/Training/hygiene/id/".$post['training_id'].".html';</script>";
            }else{
                echo "<script>alert('保存失败！');history.back();</script>";
            }
        }else {
             $hygiene  = M('tra_hygiene')->where('training_id='.I('get.id').'')->order('check_time desc')->select();
             $training = M('tra_training')->where('id=' . I('get.id') . '')->find();
             if (!$training) {
                 echo "<script>alert('地址异常！');history.back();</script>";
                 exit;
             }

             if ($training['price_time']) {
                 $training['skills'] = explode(',', $training['skill']);
             }

             $priority_arr = ['', '小学', '初中', '高中', '专科', '本科'];
             $training['educational_name'] = $priority_arr[$training['educational']];
             $this->assign('training', $training);
             $this->assign('hygiene', $hygiene);
             $this->display();
         }
    }

    public function delHygiene(){
        $delete =  M('tra_hygiene')->where('id='.I('post.id').'')->delete();
        if($delete){
            $back['code'] = 1000;
            echo json_encode($back);
        }else{
            $back['code'] = 1001;
            echo json_encode($back);
        }

    }
    public function class_show(){
        if(I('post.')){
            $post = I('post.');
            foreach ($post['detail'] as $k => $v) {
                $post['total_score'] += $v;
                $post['score_detail'] .= $v . ',';
            }
            if($post['class_show_id']&&$post['class_show_id']!=0){
                $where['id'] = $post['class_show_id'];
                $add = M('tra_class_show')->where($where)->save($post);

            }else {
                $post['mac'] = $this->GetMacAddr(PHP_OS);
                $post['add_time'] = time();

                $add = M('tra_class_show')->add($post);
            }

            if($add!==false){
                echo "<script>alert('保存成功！');window.location.href='" . __MODULE__ . "/Training/class_show/id/".$post['training_id'].".html';</script>";
            }else{
                echo "<script>alert('保存失败！');history.back();</script>";
            }
        }else {
            $class_show = M('tra_class_show')->where('training_id=' . I('get.id') . '')->order('check_time desc,add_time desc')->select();
            foreach($class_show as $k=>$v){
                if($v['is_come']==1){
                    $class_show[$k]['come'] = '未出勤('.$v['uncome_reason'].')';
                }else{
                    $class_show[$k]['come'] = '已出勤';
                }
                $class_show[$k]['detail'] = explode(',',$v['score_detail']);
                $class_show[$k]['detail_json'] = json_encode($class_show[$k]['detail']);
            }

            $training = M('tra_training')->where('id=' . I('get.id') . '')->find();
            if (!$training) {
                echo "<script>alert('地址异常！');history.back();</script>";
                exit;
            }

            if ($training['price_time']) {
                $training['skills'] = explode(',', $training['skill']);
            }

            $priority_arr = ['', '小学', '初中', '高中', '专科', '本科'];
            $training['educational_name'] = $priority_arr[$training['educational']];
            //平均得分；
            $score_average = M('tra_class_show')->field('sum(total_score)/count(training_id) as score_average')->where('training_id=' . I('get.id') . '')->find();
            $training['score_average'] = round($score_average['score_average'],2);//number_format($score_average['score_average'], 2, '.', '');;


            $this->assign('training', $training);
            $this->assign('class_show', $class_show);
            $this->assign('today', date('Y-m-d'));
            $this->display();
        }
    }
    public function delClass_show(){
        $delete =  M('tra_class_show')->where('id='.I('post.id').'')->delete();
        if($delete){
            $back['code'] = 1000;
            echo json_encode($back);
        }else{
            $back['code'] = 1001;
            echo json_encode($back);
        }

    }
    public function class_score(){


        if(I('post.')){
            $post = I('post.');
            if($post['class_score_id']&&$post['class_score_id']!=0){
                $where['id'] = $post['class_score_id'];
                $add = M('tra_class_score')->where($where)->save($post);
            }else{
                $post['mac'] = $this->GetMacAddr(PHP_OS);
                $post['add_time'] = time();

                $add = M('tra_class_score')->add($post);
            }
            if($add!==false){
                echo "<script>alert('保存成功！');window.location.href='" . __MODULE__ . "/Training/class_score/id/".$post['training_id'].".html';</script>";
            }else{
                echo "<script>alert('保存失败！');history.back();</script>";
            }
        }else {

            $class_score = M('tra_class_score')->where('training_id=' . I('get.id') . '')->order('check_time desc')->select();
            $class_average = M('tra_class_score')->field('sum(score)/count(training_id) as average')->where('training_id=' . I('get.id') . '')->order('check_time desc')->find();


            $training = M('tra_training')->where('id=' . I('get.id') . '')->find();
            if (!$training) {
                echo "<script>alert('地址异常！');history.back();</script>";
                exit;
            }

            if ($training['price_time']) {
                $training['skills'] = explode(',', $training['skill']);
            }


            $priority_arr = ['', '小学', '初中', '高中', '专科', '本科'];
            $training['average']  = round($class_average['average'], 2);

            $training['educational_name'] = $priority_arr[$training['educational']];
            $this->assign('training', $training);
            $this->assign('class_score', $class_score);
            $this->display();
        }
    }

    public function delClass_score(){
        $delete =  M('tra_class_score')->where('id='.I('post.id').'')->delete();
        if($delete){
            $back['code'] = 1000;
            echo json_encode($back);
        }else{
            $back['code'] = 1001;
            echo json_encode($back);
        }

    }

    public function body_test(){

        if(I('post.')){
            $post = I('post.');
            if($post['body_test_id']&&$post['body_test_id']!=0){
                $where['id'] = $post['body_test_id'];
                $post['test_img']='';
                if ($_FILES['test_img1']['tmp_name']) {
                    $name = $this->pinyin_long($post['training_name']);
                    $upload = new \Think\Upload();// 实例化上传类
                    $upload->maxSize = 2048576;// 设置附件上传大小
                    $upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
                    $upload->savePath = 'training/'.$post['training_id'] .'_'. $name . '/'; // 设置附件上传目录
                    $upload->subName = ''; // 设置附件上传目录
                    $info = $upload->uploadOne($_FILES['test_img1']);
                    if (!$info) {// 上传错误提示错误信息
                        echo "<script>alert('图片上传失败');history.back();</script>";
                    } else {// 上传成功
                        $imgPath = $info['savepath'] . $info['savename'];
                        $post['test_img'] .= $imgPath.',';
                    }
                }

                if ($_FILES['test_img2']['tmp_name']) {
                    $name = $this->pinyin_long($post['training_name']);
                    $upload = new \Think\Upload();// 实例化上传类
                    $upload->maxSize = 2048576;// 设置附件上传大小
                    $upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
                    $upload->savePath = 'training/'.$post['training_id'] .'_'. $name . '/'; // 设置附件上传目录
                    $upload->subName = ''; // 设置附件上传目录
                    $info = $upload->uploadOne($_FILES['test_img2']);
                    if (!$info) {// 上传错误提示错误信息
                        echo "<script>alert('图片上传失败');history.back();</script>";
                    } else {// 上传成功
                        $imgPath = $info['savepath'] . $info['savename'];
                        $post['test_img'] .= $imgPath.',';
                    }
                }

                if ($_FILES['test_img3']['tmp_name']) {
                    $name = $this->pinyin_long($post['training_name']);
                    $upload = new \Think\Upload();// 实例化上传类
                    $upload->maxSize = 2048576;// 设置附件上传大小
                    $upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
                    $upload->savePath = 'training/'.$post['training_id'] .'_'. $name . '/'; // 设置附件上传目录
                    $upload->subName = ''; // 设置附件上传目录
                    $info = $upload->uploadOne($_FILES['test_img3']);
                    if (!$info) {// 上传错误提示错误信息
                        echo "<script>alert('图片上传失败');history.back();</script>";
                    } else {// 上传成功
                        $imgPath = $info['savepath'] . $info['savename'];
                        $post['test_img'] .= $imgPath.',';
                    }
                }
                if($post['test_time']){
                    $save['body_test_time'] = '';
                    $save_table = M('tra_training')->where('id='.$post['training_id'].'')->save($save);
                    if($save_table===false){
                        M('tra_training')->where('id='.$post['training_id'].'')->save($save);
                    }
                }
                $add = M('tra_body_test')->where($where)->save($post);
            }else{
                $is_null = M('tra_body_test')->where('training_id='.$post['training_id'].' and (test_time="" OR  isnull(test_time))')->find();
                if($is_null){
                    echo "<script>alert('有未处理的体检计划！');history.back();</script>";
                    exit;
                }
                $post['test_img']='';
                if ($_FILES['test_img1']['tmp_name']) {
                    $name = $this->pinyin_long($post['training_name']);
                    $upload = new \Think\Upload();// 实例化上传类
                    $upload->maxSize = 2048576;// 设置附件上传大小
                    $upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
                    $upload->savePath = 'training/'.$post['training_id'] .'_'. $name . '/'; // 设置附件上传目录
                    $upload->subName = ''; // 设置附件上传目录
                    $info = $upload->uploadOne($_FILES['test_img1']);
                    if (!$info) {// 上传错误提示错误信息
                        echo "<script>alert('图片上传失败');history.back();</script>";
                    } else {// 上传成功
                        $imgPath = $info['savepath'] . $info['savename'];
                        $post['test_img'] .= $imgPath.',';
                    }
                }

                if ($_FILES['test_img2']['tmp_name']) {
                    $name = $this->pinyin_long($post['training_name']);
                    $upload = new \Think\Upload();// 实例化上传类
                    $upload->maxSize = 2048576;// 设置附件上传大小
                    $upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
                    $upload->savePath = 'training/'.$post['training_id'] .'_'. $name . '/'; // 设置附件上传目录
                    $upload->subName = ''; // 设置附件上传目录
                    $info = $upload->uploadOne($_FILES['test_img2']);
                    if (!$info) {// 上传错误提示错误信息
                        echo "<script>alert('图片上传失败');history.back();</script>";
                    } else {// 上传成功
                        $imgPath = $info['savepath'] . $info['savename'];
                        $post['test_img'] .= $imgPath.',';
                    }
                }

                if ($_FILES['test_img3']['tmp_name']) {
                    $name = $this->pinyin_long($post['training_name']);
                    $upload = new \Think\Upload();// 实例化上传类
                    $upload->maxSize = 2048576;// 设置附件上传大小
                    $upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
                    $upload->savePath = 'training/'.$post['training_id'] .'_'. $name . '/'; // 设置附件上传目录
                    $upload->subName = ''; // 设置附件上传目录
                    $info = $upload->uploadOne($_FILES['test_img3']);
                    if (!$info) {// 上传错误提示错误信息
                        echo "<script>alert('图片上传失败');history.back();</script>";
                    } else {// 上传成功
                        $imgPath = $info['savepath'] . $info['savename'];
                        $post['test_img'] .= $imgPath.',';
                    }
                }
                $post['mac'] = $this->GetMacAddr(PHP_OS);
                $post['add_time'] = time();
                $add = M('tra_body_test')->add($post);
                if(!$post['test_time']){
                    $save['body_test_time'] = $post['estimated_time'];
                    $save_table = M('tra_training')->where('id='.$post['training_id'].'')->save($save);
                    if($save_table===false){
                        M('tra_training')->where('id='.$post['training_id'].'')->save($save);
                    }
                }
            }
            if($add!==false){
                echo "<script>alert('保存成功！');window.location.href='" . __MODULE__ . "/Training/body_test/id/".$post['training_id'].".html';</script>";
            }else{
                echo "<script>alert('保存失败！');history.back();</script>";
            }
        }else {

            $body_test = M('tra_body_test')->where('training_id=' . I('get.id') . '')->order('add_time desc')->select();
            $training = M('tra_training')->where('id=' . I('get.id') . '')->find();
            if (!$training) {
                echo "<script>alert('地址异常！');history.back();</script>";
                exit;
            }

            if ($training['price_time']) {
                $training['skills'] = explode(',', $training['skill']);
            }

            foreach($body_test as $k=>$v){
                $body_test[$k]['test_imgs'] = explode(',',$v['test_img'],-1);

            }
            $priority_arr = ['', '小学', '初中', '高中', '专科', '本科'];
            $training['educational_name'] = $priority_arr[$training['educational']];
            $this->assign('training',$training);
            $this->assign('body_test', $body_test);
            $this->display();
        }
    }


    public function delBody_test(){
        $find =  M('tra_body_test')->where('id='.I('post.id').'')->find();
        $delete =  M('tra_body_test')->where('id='.I('post.id').'')->delete();
        if($delete){
            $save['body_test_time'] = '';
            $save_table = M('tra_training')->where('id='.$find['training_id'].'')->save($save);
            if($save_table===false){
                M('tra_training')->where('id='.$find['training_id'].'')->save($save);
            }
            $test_img = explode(',',$find['test_img'],-1);
            unlink('Uploads/'.$test_img[0]);
            unlink('Uploads/'.$test_img[1]);
            unlink('Uploads/'.$test_img[2]);
            $back['code'] = 1000;
            echo json_encode($back);
        }else{
            $back['code'] = 1001;
            echo json_encode($back);
        }
    }



    public function view(){
        if(I('post.')){
            $post = I('post.');
            $post['test_img']='';
            if ($_FILES['test_img1']['tmp_name']) {
                $name = $this->pinyin_long($post['training_name']);
                $upload = new \Think\Upload();// 实例化上传类
                $upload->maxSize = 2048576;// 设置附件上传大小
                $upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
                $upload->savePath = 'training/'.$post['training_id'] .'_'. $name . '/'; // 设置附件上传目录
                $upload->subName = ''; // 设置附件上传目录
                $info = $upload->uploadOne($_FILES['test_img1']);
                if (!$info) {// 上传错误提示错误信息
                    echo "<script>alert('图片上传失败');history.back();</script>";
                } else {// 上传成功
                    $imgPath = $info['savepath'] . $info['savename'];
                    $post['test_img'] .= $imgPath.',';
                }
            }

            if ($_FILES['test_img2']['tmp_name']) {
                $name = $this->pinyin_long($post['training_name']);
                $upload = new \Think\Upload();// 实例化上传类
                $upload->maxSize = 2048576;// 设置附件上传大小
                $upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
                $upload->savePath = 'training/'.$post['training_id'] .'_'. $name . '/'; // 设置附件上传目录
                $upload->subName = ''; // 设置附件上传目录
                $info = $upload->uploadOne($_FILES['test_img2']);
                if (!$info) {// 上传错误提示错误信息
                    echo "<script>alert('图片上传失败');history.back();</script>";
                } else {// 上传成功
                    $imgPath = $info['savepath'] . $info['savename'];
                    $post['test_img'] .= $imgPath.',';
                }
            }

            if ($_FILES['test_img3']['tmp_name']) {
                $name = $this->pinyin_long($post['training_name']);
                $upload = new \Think\Upload();// 实例化上传类
                $upload->maxSize = 2048576;// 设置附件上传大小
                $upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
                $upload->savePath = 'training/'.$post['training_id'] .'_'. $name . '/'; // 设置附件上传目录
                $upload->subName = ''; // 设置附件上传目录
                $info = $upload->uploadOne($_FILES['test_img3']);
                if (!$info) {// 上传错误提示错误信息
                    echo "<script>alert('图片上传失败');history.back();</script>";
                } else {// 上传成功
                    $imgPath = $info['savepath'] . $info['savename'];
                    $post['test_img'] .= $imgPath.',';
                }
            }
            $post['mac'] = $this->GetMacAddr(PHP_OS);
            $post['add_time'] = time();
            $add = M('tra_view')->add($post);

            if($add!==false){
                echo "<script>alert('保存成功！');window.location.href='" . __MODULE__ . "/Training/view/id/".$post['training_id'].".html';</script>";
            }else{
                echo "<script>alert('保存失败！');history.back();</script>";
            }
        }else {

            $body_test = M('tra_view')->where('training_id=' . I('get.id') . '')->order('add_time desc')->select();
            $training = M('tra_training')->where('id=' . I('get.id') . '')->find();
            if (!$training) {
                echo "<script>alert('地址异常！');history.back();</script>";
                exit;
            }

            if ($training['price_time']) {
                $training['skills'] = explode(',', $training['skill']);
            }

            foreach($body_test as $k=>$v){
                $body_test[$k]['test_imgs'] = explode(',',$v['test_img'],-1);

            }
            $priority_arr = ['', '小学', '初中', '高中', '专科', '本科'];
            $training['educational_name'] = $priority_arr[$training['educational']];
            $this->assign('training',$training);
            $this->assign('body_test', $body_test);
            $this->display();
        }
    }




    public function delView(){
        $find =  M('tra_view')->where('id='.I('post.id').'')->find();
        $delete =  M('tra_view')->where('id='.I('post.id').'')->delete();
        if($delete){
            $test_img = explode(',',$find['test_img'],-1);
            unlink('Uploads/'.$test_img[0]);
            unlink('Uploads/'.$test_img[1]);
            unlink('Uploads/'.$test_img[2]);
            $back['code'] = 1000;
            echo json_encode($back);
        }else{
            $back['code'] = 1001;
            echo json_encode($back);
        }
    }


    public function tra_use(){

        if(I('post.')){
            $post = I('post.');
            if($post['signing_id']&&$post['signing_id']!=0){
                $where['id'] = $post['signing_id'];
                $add = M('tra_signing')->where($where)->save($post);
            }else{
                $post['mac'] = $this->GetMacAddr(PHP_OS);
                $post['add_time'] = time();
                $add = M('tra_signing')->add($post);
            }
            if($add!==false){
                $training = M('tra_training')->field('status')->where('id=' . $post['training_id'] . '')->find();
                if($training['status']!==3){
                    $save_training['status']=3;
                    $save = M('tra_training')->where('id=' . $post['training_id'] . '')->save($save_training);
                    if($save===false){
                        M('tra_training')->where('id=' . $post['training_id'] . '')->save($save_training);
                    }
                }
                echo "<script>alert('保存成功！');window.location.href='" . __MODULE__ . "/Training/tra_use/id/".$post['training_id'].".html';</script>";
            }else{
                echo "<script>alert('保存失败！');history.back();</script>";
            }

        }else {

            $signing = M('tra_signing')->where('training_id=' . I('get.id') . '')->order('add_time desc')->select();

            $training = M('tra_training')->where('id=' . I('get.id') . '')->find();
            if (!$training) {
                echo "<script>alert('地址异常！');history.back();</script>";
                exit;
            }

            if ($training['price_time']) {
                $training['skills'] = explode(',', $training['skill']);
            }


            foreach($signing as $k=>$v){
                $signing[$k]['t_s'] = M('tra_teacher_situation')->where('signing_id = '.$v['id'].'')->order('date desc')->select();
            }

            $priority_arr = ['', '小学', '初中', '高中', '专科', '本科'];
            $training['educational_name'] = $priority_arr[$training['educational']];
            $training['now_date'] = date('Y-m-d');
            $this->assign('training', $training);
            $this->assign('signing', $signing);
            $this->display('use');
        }
    }
    public function tra_teacher(){
        $post = I('post.');
        $post['mac'] = $this->GetMacAddr(PHP_OS);
        $add = M('tra_teacher_situation')->add($post);
        if($add!==false){
            echo "<script>alert('添加成功！');window.location.href='" . __MODULE__ . "/Training/tra_use/id/".$post['training_id'].".html';</script>";
        }else{
            echo "<script>alert('添加失败！');history.back();</script>";
        }

    }



    public function delSigning(){
        $tra_signing =  M('tra_signing')->where('id='.I('post.id').'')->find();
        $delete =  M('tra_signing')->where('id='.I('post.id').'')->delete();
        if($delete){
            $tra_signing_find = M('tra_signing')->where('training_id='.$tra_signing['training_id'].'')->find();
            if(!$tra_signing_find){
                $save_training['status']=2;
                $save = M('tra_training')->where('id=' . $tra_signing['training_id'] . '')->save($save_training);
                if($save===false){
                    M('tra_training')->where('id=' . $tra_signing['training_id'] . '')->save($save_training);
                }
                $back['status'] = 1;
            }
            $back['code'] = 1000;
            echo json_encode($back);
        }else{
            $back['code'] = 1001;
            echo json_encode($back);
        }

    }


    public function zhengshu(){
        if(I('post.')){
            $post = I('post.');
            if ($_FILES['src']['tmp_name']) {
                $name = $this->pinyin_long($post['training_name']);
                $upload = new \Think\Upload();// 实例化上传类
                $upload->maxSize = 2048576;// 设置附件上传大小
                $upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
                $upload->savePath = 'training/'.$post['training_id'] .'_'. $name . '/'; // 设置附件上传目录
                $upload->subName = ''; // 设置附件上传目录
                $info = $upload->uploadOne($_FILES['src']);
                if (!$info) {// 上传错误提示错误信息
                    echo "<script>alert('图片上传失败');history.back();</script>";
                } else {// 上传成功
                    $imgPath = $info['savepath'] . $info['savename'];
                    $post['src'] = $imgPath;
                }
            }


            if($post['zhengshu_id']&&$post['zhengshu_id']!=0){

                $where['id'] = $post['zhengshu_id'];
                $add = M('tra_get_zhengshu')->where($where)->save($post);
            }else{
                $post['mac'] = $this->GetMacAddr(PHP_OS);
                $post['add_time'] = time();

                $add = M('tra_get_zhengshu')->add($post);
            }
            if($add!==false){
                echo "<script>alert('保存成功！');window.location.href='" . __MODULE__ . "/Training/zhengshu/id/".$post['training_id'].".html';</script>";
            }else{
                echo "<script>alert('保存失败！');history.back();</script>";
            }

        }else {

            $tra_get_zhengshu= M('tra_get_zhengshu')->where('training_id=' . I('get.id') . '')->order('add_time desc')->select();

            $training = M('tra_training')->where('id=' . I('get.id') . '')->find();
            if (!$training) {
                echo "<script>alert('地址异常！');history.back();</script>";
                exit;
            }

            if ($training['price_time']) {
                $training['skills'] = explode(',', $training['skill']);
            }

            $priority_arr = ['', '小学', '初中', '高中', '专科', '本科'];
            $training['educational_name'] = $priority_arr[$training['educational']];
            $this->assign('training', $training);
            $this->assign('tra_get_zhengshu', $tra_get_zhengshu);
            $this->display();
        }
    }

    public function delZhengshu(){
        $delete =  M('tra_get_zhengshu')->where('id='.I('post.id').'')->delete();
        if($delete){
            $back['code'] = 1000;
            echo json_encode($back);
        }else{
            $back['code'] = 1001;
            echo json_encode($back);
        }

    }


    //学员面试情况页面及添加
    public function face(){
        if(I('post.')){
            $post = I('post.');

            $post['mac'] = $this->GetMacAddr(PHP_OS);
            $add = M('tra_face')->add($post);
            if($add){
                echo "<script>alert('添加成功！');window.location.href='" . __MODULE__ . "/Training/face/id/".$post['training_id'].".html';</script>";
                exit;
            }else{
                echo "<script>alert('添加失败！');history.back();</script>";
                exit;
            }
        }else{
            if(I('get.id')){
                $tra_face = M('tra_face')->where('training_id=' . I('get.id') . '')->order('face_time asc')->select();
                $training = M('tra_training')->where('id=' . I('get.id') . '')->find();
                if (!$training) {
                    echo "<script>alert('地址异常！');history.back();</script>";
                    exit;
                }
                if ($training['price_time']) {
                    $training['skills'] = explode(',', $training['skill']);
                }
                $all_count = M('tra_face')->where('training_id=' . I('get.id') . '')->count();
                $success_count = M('tra_face')->where('training_id=' . I('get.id') . ' and face_result="成功"')->count();
                $training['all_count'] = $all_count;
                $training['success_count'] = $success_count;
                $priority_arr = ['', '小学', '初中', '高中', '专科', '本科'];
                $training['educational_name'] = $priority_arr[$training['educational']];
                $training['now_date'] = date('Y-m-d');
                $this->assign('training', $training);
                $this->assign('tra_face', $tra_face);
                $this->display();
            }else{
                echo "<script>alert('地址异常！');history.back();</script>";
                exit;
            }
        }
    }

    //修改评定薪资
    public function edit_price_time(){
        $post = I('post.');
        if($post){
            $where['id'] = $post['price_id'];
            $data['price_time'] = $post['price_time'];
            $data['price_standard'] = $post['price_standard'];
            $data['price'] = $post['price'];
            $data['average_score'] = ($post['skill1']+$post['skill2']+$post['skill3']+$post['skill4']+$post['skill5']+$post['skill6']+$post['skill7'])/7;
            $data['skill'] = $post['skill1'].','.$post['skill2'].','.$post['skill3'].','.$post['skill4'].','.$post['skill5'].','.$post['skill6'].','.$post['skill7'];

             $training_id = M('tra_training')->where($where)->save($data);
            if($training_id!==false){
                echo "<script>alert('修改成功！');window.location.href='" . __MODULE__ . "/Training/info/id/".$post['price_id'].".html';</script>";
            }else{
                echo "<script>alert('修改失败！');history.back();</script>";
            }
        }else{
            echo "<script>alert('地址异常！');history.back();</script>";
            die;
        }
    }

    //修改结业信息
    public function edit_stop_time(){
        $post = I('post.');
        if($post){
            $where['id'] = $post['stop_id'];
//            $data['stop_zhengshu_time'] = $post['stop_zhengshu_time'];
            $data['stop_time'] = $post['stop_time'];
            $data['teacher_comments'] = $post['teacher_comments'];
            $data['status'] = 2;

            $training_id = M('tra_training')->where($where)->save($data);
            if($training_id!==false){
                echo "<script>alert('修改成功！');window.location.href='" . __MODULE__ . "/Training/info/id/".$post['stop_id'].".html';</script>";
            }else{
                echo "<script>alert('修改失败！');history.back();</script>";
            }
        }else{
            echo "<script>alert('地址异常！');history.back();</script>";
            die;
        }
    }

    //修改淘汰信息
    public function edit_die_time(){
        $post = I('post.');
        if($post){
            $where['id'] = $post['die_id'];
            $data['die_reason'] = $post['die_reason'];
            $data['die_mac'] = $this->GetMacAddr(PHP_OS);
            $data['die_employee'] = $_SESSION[C('TRAINING_AUTH_KEY')]['username'];
            $data['die_time'] = date('Y-m-d');
            $data['status'] = 4;

            $training_id = M('tra_training')->where($where)->save($data);
            if($training_id!==false){
                echo "<script>alert('修改成功！');window.location.href='" . __MODULE__ . "/Training/info/id/".$post['die_id'].".html';</script>";
            }else{
                echo "<script>alert('修改失败！');history.back();</script>";
            }
        }else{
            echo "<script>alert('地址异常！');history.back();</script>";
            die;
        }
    }


    //学员列表页
    public function tList(){
        $number[1] = M('tra_training')->where('status=1')->count();
        $number[2] = M('tra_training')->where('status=2')->count();
        $number[3] = M('tra_training')->where('status=3')->count();
        $number[4] = M('tra_training')->where('status=4')->count();
        $number[5] = M('tra_training')->where('status!=4 and  EXISTS (SELECT training_id from tra_get_zhengshu  where  tra_training.id=tra_get_zhengshu.training_id  )')->count();
        $number[6] = M('tra_training')->where('status!=4 and  body_test_time!=0')->count();
        $number[7] = M('tra_day_report')->count();
        $number[9] = M('tra_training')->where('to_nurse >0')->count();
        $three_mounth = date('Y-m-d',strtotime('3 MONTH'));
        $today = date('Y-m-d');
        $where = 'status <10 and DAYOFYEAR(birthday)<= DAYOFYEAR("'.$three_mounth.'") and DAYOFYEAR(birthday)>=DAYOFYEAR("'.$today.'")  ';
        $number[8] = M('tra_training')->where($where)->count();

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

    public function to_nurse(){
        if(!I('get.id')){
            echo "<script>alert('地址异常！');history.back();</script>";
            die;
        }
       $have =  M('tra_training')->where('id='.I('get.id').'')->find();
        if(!$have){
            echo "<script>alert('地址异常！');history.back();</script>";
            die;
        }

        $save['to_nurse'] = 1;
        $save['to_nurse_time'] = time();
        $save_mod = M('tra_training')->where('id='.I('get.id').'')->save($save);
        if($save_mod!==false){
            echo "<script>alert('成功！');history.back();</script>";
            die;
        }else{
            echo "<script>alert('失败！');history.back();</script>";
            die;
        }
    }

    //获取学员列表数据
    public function getTrainingList(){
        $post = I('post.');
        $currentpage = I("post.currentpage");
        $pagenum = I("post.pagenum");
        $start = ($currentpage - 1) * $pagenum;

        if($post['type']==10){
            $where =' and  not EXISTS (SELECT trainee_id from tra_training  where  tra_trainee.id=tra_training.trainee_id  )';
            if($post['keyword']&&$post['keyword']!=''){
                $where .= ' and  (name LIKE "%'.$post['keyword'].'%"  OR phone LIKE "%'.$post['keyword'].'%" )';
            }
            $field = 'id,name,cover,phone';
            $list = M('tra_trainee')->field($field)->where('status=4 '.$where)->limit($start,$pagenum)->order('add_time desc')->select();
            $count = M('tra_trainee')->where('status=4 '.$where)->count();
        }elseif($post['type']<5){
            $where  =  'status='.$post['type'];
            if($post['keyword']&&$post['keyword']!=''){
                $where .= ' and (name LIKE "%'.$post['keyword'].'%"  OR phone LIKE "%'.$post['keyword'].'%" OR id_card LIKE "%'.$post['keyword'].'%" )';
            }
            $list = M('tra_training')->where($where)->limit($start,$pagenum)->order('add_time desc')->select();
            $count = M('tra_training')->where($where)->count();
        }elseif($post['type']==5){
            $where = 'status!=4 and  EXISTS (SELECT training_id from tra_get_zhengshu  where  tra_training.id=tra_get_zhengshu.training_id  )';
            if($post['keyword']&&$post['keyword']!=''){
                $where .= ' and (name LIKE "%'.$post['keyword'].'%"  OR phone LIKE "%'.$post['keyword'].'%" OR id_card LIKE "%'.$post['keyword'].'%" )';
            }
            $list = M('tra_training')->where($where)->limit($start,$pagenum)->order('add_time desc')->select();
            $count = M('tra_training')->where($where)->count();
        }elseif($post['type']==6){
            $where = 'status!=4 and  body_test_time!=0 ';
            if($post['keyword']&&$post['keyword']!=''){
                $where .= ' and (name LIKE "%'.$post['keyword'].'%"  OR phone LIKE "%'.$post['keyword'].'%" OR id_card LIKE "%'.$post['keyword'].'%" )';
            }
            $list = M('tra_training')->where($where)->order('body_test_time asc')->limit($start,$pagenum)->order('add_time desc')->select();
            $count = M('tra_training')->where($where)->count();
        }elseif($post['type']==7){//日报
            $back['data']['sess'] = $_SESSION[C('TRAINING_AUTH_KEY')];
            $list = M('tra_day_report')->order('date desc')->limit($start,$pagenum)->select();
            $count = M('tra_day_report')->count();
        }elseif($post['type']==8){//生日
            $three_mounth = date('Y-m-d',strtotime('3 MONTH'));
            $today = date('Y-m-d');
            $where = 'status <10 and DAYOFYEAR(birthday)<= DAYOFYEAR("'.$three_mounth.'") and DAYOFYEAR(birthday)>=DAYOFYEAR("'.$today.'")  ';
            if($post['keyword']&&$post['keyword']!=''){
                $where .= ' and (name LIKE "%'.$post['keyword'].'%"  OR phone LIKE "%'.$post['keyword'].'%" OR id_card LIKE "%'.$post['keyword'].'%" )';
            }
            $list = M('tra_training')->where($where)->order('DAYOFYEAR(birthday) asc')->limit($start,$pagenum)->select();
            $count = M('tra_training')->where($where)->count();
        }elseif($post['type']==9){//已输出
            $where = 'to_nurse >0 ';
            if($post['keyword']&&$post['keyword']!=''){
                $where .= ' and (name LIKE "%'.$post['keyword'].'%"  OR phone LIKE "%'.$post['keyword'].'%" OR id_card LIKE "%'.$post['keyword'].'%" )';
            }
            $list = M('tra_training')->where($where)->order('DAYOFYEAR(birthday) asc')->limit($start,$pagenum)->select();
            $count = M('tra_training')->where($where)->count();
        }
        foreach($list as $k=>$v){
            if($v['stop_zhengshu_time']){
                $list[$k]['stop_zhengshu'] = 1;
            }
            if($post['type']==3){
                $list[$k]['use'] = M('tra_signing')->where('training_id='.$v['id'].'')->order('add_time desc')->find();
                $teacher = M('tra_teacher_situation')->field('tra_teacher_situation.teacher')->join('tra_signing ON tra_signing.id=tra_teacher_situation.signing_id')->where('tra_signing.training_id='.$v['id'].'')->find();
                $list[$k]['use'] ['teacher'] = $teacher['teacher']?$teacher['teacher']:'还未督导';
            }
            if($post['type']==5){
                $list[$k]['zhengshu'] = M('tra_get_zhengshu')->where('training_id='.$v['id'].'')->order('test_time desc')->find();
            }
        }
        $back['code'] = 1000;
        $back['data']['list'] =$list;
        $back['data']['num'] =$count;
        $back['data']['type'] =$post['type'];
        echo json_encode($back);
    }

    //导出列表数据excel
    public function excel(){
        if($_GET['type']==1){
            $str = "学员姓名\t年龄\t电话\t身份证\t\n";
            $str = iconv('utf-8','gb2312',$str);
            $where  =  'status=1';
            $list = M('tra_training')->where($where)->select();
            foreach($list as $k=>$v){
                $name = iconv('utf-8','gb2312',$v['name']);
                $age = iconv('utf-8','gb2312',$v['age']);
                $phone = iconv('utf-8','gb2312',$v['phone']);
                $id_card = iconv('utf-8','gb2312',$v['id_card']);
                $str .= $name."\t".$age."\t".$phone."\t".$id_card."\t\n";
            }
            $filename = '培训中'.date('Ymd').'.xls';
            $this->exportExcel($filename,$str);
        }elseif($_GET['type']==2){
            $str = "学员姓名\t年龄\t电话\t身份证\t结业日期\t证书发放\t\n";
                $str = iconv('utf-8','gb2312',$str);
                $where  =  'status=2';
                $list = M('tra_training')->where($where)->select();
                foreach($list as $k=>$v){
                    if($v['stop_zhengshu_time']){
                        $zhengshu = iconv('utf-8','gb2312','是');;
                    }else{
                        $zhengshu = iconv('utf-8','gb2312','否');;
                    }
                    $name = iconv('utf-8','gb2312',$v['name']);
                    $age = iconv('utf-8','gb2312',$v['age']);
                $phone = iconv('utf-8','gb2312',$v['phone']);
                $id_card = iconv('utf-8','gb2312',$v['id_card']);
                $stop_time = iconv('utf-8','gb2312',$v['stop_time']);
                $str .= $name."\t".$age."\t".$phone."\t".$id_card."\t".$stop_time."\t".$zhengshu."\t\n";
            }
            $filename = '已结业'.date('Ymd').'.xls';
            $this->exportExcel($filename,$str);
        }elseif($_GET['type']==3){
            $str = "学员姓名\t年龄\t电话\t身份证\t上单时间\t上单客户\t督导老师\t\n";
            $str = iconv('utf-8','gb2312',$str);
            $where  =  'status=3';
            $list = M('tra_training')->where($where)->select();
            foreach($list as $k=>$v){
                $use = M('tra_signing')->where('training_id='.$v['id'].'')->order('add_time desc')->find();
                $teacher = M('tra_teacher_situation')->field('tra_teacher_situation.teacher')->join('tra_signing ON tra_signing.id=tra_teacher_situation.signing_id')->where('tra_signing.training_id='.$v['id'].'')->find();
                $name = iconv('utf-8','gb2312',$v['name']);
                $age = iconv('utf-8','gb2312',$v['age']);
                $phone = iconv('utf-8','gb2312',$v['phone']);
                $id_card = iconv('utf-8','gb2312',$v['id_card']);
                $use_all = $use['b_time'].'-'.$use['s_time'];
                $use_customer= iconv('utf-8','gb2312',$use['use_customer']);
                $teacher_name = $teacher['teacher']?$teacher['teacher']:'还未督导';
                $teacher_name= iconv('utf-8','gb2312',$teacher_name);
                $str .= $name."\t".$age."\t".$phone."\t".$id_card."\t".$use_all."\t".$use_customer."\t".$teacher_name."\t\n";
            }
            $filename = '已上单'.date('Ymd').'.xls';
            $this->exportExcel($filename,$str);
        }else if($_GET['type']==4){
            $str = "学员姓名\t年龄\t电话\t身份证\t淘汰日期\t淘汰理由\t\n";
            $str = iconv('utf-8','gb2312',$str);
            $where  =  'status=4';
            $list = M('tra_training')->where($where)->select();
            foreach($list as $k=>$v){
                $name = iconv('utf-8','gb2312',$v['name']);
                $age = iconv('utf-8','gb2312',$v['age']);
                $phone = iconv('utf-8','gb2312',$v['phone']);
                $id_card = iconv('utf-8','gb2312',$v['id_card']);
                $die_time = iconv('utf-8','gb2312',$v['die_time']);
                $die_reason = iconv('utf-8','gb2312',$v['die_reason']);
                $str .= $name."\t".$age."\t".$phone."\t".$id_card."\t".$die_time."\t".$die_reason."\t\n";
            }
            $filename = '已淘汰'.date('Ymd').'.xls';
            $this->exportExcel($filename,$str);
        }else if($_GET['type']==5){
            $str = "学员姓名\t年龄\t电话\t身份证\t考证时间\t考证级别\t预计领证日期\t\n";
            $str = iconv('utf-8','gb2312',$str);
            $where = 'status!=4 and  EXISTS (SELECT training_id from tra_get_zhengshu  where  tra_training.id=tra_get_zhengshu.training_id  )';
            $list = M('tra_training')->where($where)->select();
            foreach($list as $k=>$v){
                $zhengshu = M('tra_get_zhengshu')->where('training_id='.$v['id'].'')->order('test_time desc')->find();
                $name = iconv('utf-8','gb2312',$v['name']);
                $age = iconv('utf-8','gb2312',$v['age']);
                $phone = iconv('utf-8','gb2312',$v['phone']);
                $id_card = iconv('utf-8','gb2312',$v['id_card']);
                $test_time = iconv('utf-8','gb2312',$zhengshu['test_time']);
                $test_level = iconv('utf-8','gb2312',$zhengshu['test_level']);
                $get_time = iconv('utf-8','gb2312',$zhengshu['get_time']);
                $str .= $name."\t".$age."\t".$phone."\t".$id_card."\t".$test_time."\t".$test_level."\t".$get_time."\t\n";
            }
            $filename = '已考证'.date('Ymd').'.xls';
            $this->exportExcel($filename,$str);
        }else if($_GET['type']==6){
            $str = "学员姓名\t年龄\t电话\t身份证\t预计体检日期\t\n";
            $str = iconv('utf-8','gb2312',$str);
            $where = 'status!=4 and  body_test_time!=0 ';
            $list = M('tra_training')->where($where)->order('body_test_time asc')->select();
            foreach($list as $k=>$v){
                $name = iconv('utf-8','gb2312',$v['name']);
                $age = iconv('utf-8','gb2312',$v['age']);
                $phone = iconv('utf-8','gb2312',$v['phone']);
                $id_card = iconv('utf-8','gb2312',$v['id_card']);
                $body_test_time = iconv('utf-8','gb2312',$v['body_test_time']);
                $str .= $name."\t".$age."\t".$phone."\t".$id_card."\t".$body_test_time."\t\n";
            }
            $filename = '体检计划'.date('Ymd').'.xls';
            $this->exportExcel($filename,$str);
        }elseif($_GET['type']=='all'){
            $str = "学员姓名\t年龄\t电话\t身份证\t\n";
            $str = iconv('utf-8','gb2312',$str);
            $where  =  '1';
            $list = M('tra_training')->where($where)->select();
            foreach($list as $k=>$v){
                $name = iconv('utf-8','gb2312',$v['name']);
                $age = iconv('utf-8','gb2312',$v['age']);
                $phone = iconv('utf-8','gb2312',$v['phone']);
                $id_card = iconv('utf-8','gb2312',$v['id_card']);
                $str .= $name."\t".$age."\t".$phone."\t".$id_card."\t\n";
            }
            $filename = '所有学员'.date('Ymd').'.xls';
            $this->exportExcel($filename,$str);
        }

//        $str = "用户名\t昵称\t权限\t\n";
//        $str = iconv('utf-8','gb2312',$str);
//        $result = M('order_user')->select();
//        foreach($result as $k=>$v){
//            $name = iconv('utf-8','gb2312',$v['username']);
//            $sex = iconv('utf-8','gb2312',$v['real_name']);
//            $str .= $name."\t".$sex."\t".$v['sex']."\t\n";
//        }
//        $filename = date('Ymd').'.xls';
//        $this->exportExcel($filename,$str);
    }

    //统计报表页面
    public function count_html(){

        $number[1] = M('tra_training')->where('status=1')->count();
        $number[2] = M('tra_training')->where('status=2')->count();
        $number[3] = M('tra_training')->where('status=3')->count();
        $number[4] = M('tra_training')->where('status=4')->count();
        $number[5] = M('tra_training')->where('status!=4 and  EXISTS (SELECT training_id from tra_get_zhengshu  where  tra_training.id=tra_get_zhengshu.training_id  )')->count();
        $number[6] = M('tra_training')->where('status!=4 and  body_test_time!=0')->count();
        $number[7] = M('tra_day_report')->count();
        $number[9] = M('tra_training')->where('to_nurse >0')->count();
        $three_mounth = date('Y-m-d',strtotime('3 MONTH'));
        $today = date('Y-m-d');
        $where = 'status <10 and DAYOFYEAR(birthday)<= DAYOFYEAR("'.$three_mounth.'") and DAYOFYEAR(birthday)>=DAYOFYEAR("'.$today.'")  ';
        $number[8] = M('tra_training')->where($where)->count();

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

        $this->display();
    }


    //统计报表
    public function count_excel(){
        $str = "时间\t培训中\t报道\t结业\t考证\t体检\t淘汰\t输出\t\n";
        if(I('post.time_b_1')&&I('post.time_s_1')){
           I('post.time_s_1')>date('Y-m-d')?($time_s = date('Y-m-d')):($time_s = I('post.time_s_1'));
            $add_time = M('tra_training')->field('add_time')->where('add_time!=""')->order('add_time asc')->find();
            I('post.time_b_1')<$add_time['add_time']?($time_b = $add_time['add_time']):($time_b = I('post.time_b_1'));
            for($day=$time_b;$day<=$time_s;$day=date('Y-m-d',(strtotime($day)+86400))){
                $status_1 = M('tra_training')->field('count(id) as number')->where('add_time <="'.$day.'" and (stop_time >="'.$day.'" OR stop_time=""  OR isnull(stop_time)  ) and (die_time >="'.$day.'" OR die_time=""  OR isnull(die_time))')->find();
                $status_2 = M('tra_training')->field('count(id) as number')->where('add_time ="'.$day.'"')->find();
                $status_3 = M('tra_training')->field('count(id) as number')->where('stop_time ="'.$day.'"')->find();
                $status_4 = M('tra_get_zhengshu')->field('count(id) as number')->where('test_time ="'.$day.'"')->find();
                $status_5 = M('tra_body_test')->field('count(id) as number')->where('test_time ="'.$day.'"')->find();
                $status_6 = M('tra_training')->field('count(id) as number')->where('die_time ="'.$day.'"')->find();
                $status_7 = M('tra_training')->field('count(id) as number')->where('to_nurse_time <"'.(strtotime($day)+86400).'" and to_nurse_time>"'.strtotime($day).'"')->find();
                $str .=  $day."\t".$status_1['number']."\t".$status_2['number']."\t".$status_3['number']."\t".$status_4['number']."\t".$status_5['number']."\t".$status_6['number']."\t".$status_7['number']."\t\n";
            }
            $filename = '培训系统'.I('post.time_b_1').'到'.I('post.time_s_1').'统计报表.xls';
        }else{
            $day = date("Y-m-d");
            $status_1 = M('tra_training')->field('count(id) as number')->where('add_time <="'.$day.'" and (stop_time >="'.$day.'" OR stop_time=""  OR isnull(stop_time)  ) and (die_time >="'.$day.'" OR die_time=""  OR isnull(die_time))')->find();

            $status_2 = M('tra_training')->field('count(id) as number')->where('add_time ="'.$day.'"')->find();
            $status_3 = M('tra_training')->field('count(id) as number')->where('stop_time ="'.$day.'"')->find();
            $status_4 = M('tra_get_zhengshu')->field('count(id) as number')->where('test_time ="'.$day.'"')->find();
            $status_5 = M('tra_body_test')->field('count(id) as number')->where('test_time ="'.$day.'"')->find();
            $status_6 = M('tra_training')->field('count(id) as number')->where('die_time ="'.$day.'"')->find();
            $status_7 = M('tra_training')->field('count(id) as number')->where('to_nurse_time <"'.(strtotime($day)+86400).'" and to_nurse_time>"'.strtotime($day).'"')->find();
            $str .=  $day."\t".$status_1['number']."\t".$status_2['number']."\t".$status_3['number']."\t".$status_4['number']."\t".$status_5['number']."\t".$status_6['number']."\t".$status_7['number']."\t\n";

            $filename = '培训系统'.$day.'统计报表.xls';
        }

        $str = iconv('utf-8','gb2312',$str);
        $this->exportExcel($filename,$str);
    }
    // 生成今天日报
    public function add_day(){
        $post = I('post.');
        if($post['id']){
            $post['number'] = M('tra_class_show')->where('check_time="' . $post['date'] . '"   and is_come = 0')->count('distinct(training_id)');
            $name = M('tra_class_show')->field('tra_training.name as name,total_score')->join('tra_training ON tra_training.id=tra_class_show.training_id')->where('tra_class_show.check_time="' . $post['date'] . '"   and tra_class_show.is_come = 0')->group('tra_training.name')->order('total_score desc')->select();
            foreach ($name as $k => $v) {
                $post['number_detail'] .= $v['name'] . ',';
                $post['score_detail'] .= $v['name'] . '(' . $v['total_score'] . '),';

            }
            $post['number_detail'] = substr($post['number_detail'], 0, -1);
            $post['score_detail'] = substr($post['score_detail'], 0, -1);
            $add = M('tra_day_report')->where('id='.$post['id'].'')->save($post);
        }else {
//            $post['date'] = date('Y-m-d');


            $have_date =  M('tra_day_report')->where('date="'.$post['date'].'"')->find();

            if($have_date){
                echo "<script>alert('已存在".$post['date']."的日报！');history.back();</script>";
                exit;
            }
            $post['number'] = M('tra_class_show')->where('check_time="' . $post['date'] . '"   and is_come = 0')->count('distinct(training_id)');
            $name = M('tra_class_show')->field('tra_training.name as name,total_score')->join('tra_training ON tra_training.id=tra_class_show.training_id')->where('tra_class_show.check_time="' . $post['date'] . '"   and tra_class_show.is_come = 0')->group('tra_training.name')->order('total_score desc')->select();
            foreach ($name as $k => $v) {
                $post['number_detail'] .= $v['name'] . ',';
                $post['score_detail'] .= $v['name'] . '(' . $v['total_score'] . '),';

            }
            $post['number_detail'] = substr($post['number_detail'], 0, -1);
            $post['score_detail'] = substr($post['score_detail'], 0, -1);
            $post['mac'] = $this->GetMacAddr(PHP_OS);

            $add = M('tra_day_report')->add($post);
        }
        if ($add!==false) {
            echo "<script>alert('成功！');window.location.href='" . __MODULE__ . "/Training/tlist/type/7.html';</script>";
        } else {
            echo "<script>alert('失败！');history.back();</script>";
        }
    }
    public function day_report(){
        if(!$_GET['id']){
            echo "<script>alert('地址异常！');history.back();</script>";
            exit;
        }
        $report = M('tra_day_report')->where('id='.$_GET['id'].'')->find();
        if($report){

            $teacher = M('tra_teacher_situation')->field('tra_training.name as training_name,tra_teacher_situation.teacher,tra_teacher_situation.situation')->join('tra_signing ON tra_signing.id=tra_teacher_situation.signing_id')->join('tra_training ON tra_training.id=tra_signing.training_id')->where('tra_teacher_situation.date="'.$report['date'].'"')->select();

            $this->assign('teacher',$teacher);
            $this->assign('report',$report);
            $this->display();
        }else{
            echo "<script>alert('地址异常！');history.back();</script>";
        }

    }

    //转化学员
    public function addTraining(){
        if(I("post.")){
            $post = I("post.");
            $post['status']=1;
            $trainee_name = M('tra_training')->where('name = "'.$post['name'].'" OR phone= "'.$post['phone'].'" ')->find();
            if($trainee_name){
                echo "<script>alert('已存在该姓名或电话号码的学员！');history.back();</script>";
                exit;
            }

            if ($_FILES['title_img']['tmp_name']) {
                $name = $this->pinyin_long($post['name']);
                $upload = new \Think\Upload();// 实例化上传类
                $upload->maxSize = 2097576;// 设置附件上传大小
                $upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
                $upload->savePath = 'training/'.$post['id'] .'_'. $name . '/'; // 设置附件上传目录
                $upload->subName = ''; // 设置附件上传目录
                $info = $upload->uploadOne($_FILES['title_img']);
                if (!$info) {// 上传错误提示错误信息
                    echo "<script>alert('图片上传失败');window.location.href='" . __MODULE__ . "/Ayi/addTraining/id/" . $post['trainee_id'] . "';</script>";
                } else {// 上传成功

                    $imgPath = $info['savepath'] . $info['savename'];
                    $post['title_img'] = $imgPath;
                }
            }

            $animals = array(
                '鼠', '牛', '虎', '兔', '龙', '蛇',
                '马', '羊', '猴', '鸡', '狗', '猪'
            );
            $key = (substr($post['id_card'],6,4) - 1900) % 12;
            $post['zodiac'] = $animals[$key];

            $training_id = M('tra_training')->add($post);
            if($training_id){
                echo "<script>alert('上传成功！');window.location.href='" . __MODULE__ . "/Training/tlist/type/1.html';</script>";
            }else{
                echo "<script>alert('上传失败！');window.location.href='" . __MODULE__ . "/Training/addTraining/id/".$post['trainee_id'].".html';</script>";
            }

        }else{

            if(I('get.id')) {
                $trainee = M('tra_trainee')->where('id=' . I('get.id') . '')->find();
                if (!$trainee) {
                    echo "<script>alert('地址异常！');history.back();</script>";
                    die;
                }
            }else{
                $trainee['name'] = '';
                $trainee['id'] = 0;
                $trainee['cover'] = date('Y-m-d');
            }
            $this->assign('trainee',$trainee);
            $this->display();
        }
    }

    //转化学员
    public function editTraining(){
        if(I("post.")){
            $post = I("post.");
            $where['id'] = $post['id'];

            $trainee_name = M('tra_training')->where('(name = "'.$post['name'].'" OR phone= "'.$post['phone'].'" ) and id!='.$post['id'].' ')->find();
            if($trainee_name){
                echo "<script>alert('已存在该姓名或电话号码的学员！');history.back();</script>";
                exit;
            }
            if ($_FILES['title_img']['tmp_name']) {
                $name = $this->pinyin_long($post['name']);
                $upload = new \Think\Upload();// 实例化上传类
                $upload->maxSize = 2097576;// 设置附件上传大小
                $upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
                $upload->savePath = 'training/'.$post['id'] .'_'. $name . '/'; // 设置附件上传目录
                $upload->subName = ''; // 设置附件上传目录
                $info = $upload->uploadOne($_FILES['title_img']);
                if (!$info) {// 上传错误提示错误信息
                    echo "<script>alert('图片上传失败');window.location.href='" . __MODULE__ . "/Ayi/editTraining/id/" . $post['id'] . "';</script>";
                } else {// 上传成功

                    $imgPath = $info['savepath'] . $info['savename'];
                    $post['title_img'] = $imgPath;
                }
            }
            $animals = array(
                '鼠', '牛', '虎', '兔', '龙', '蛇',
                '马', '羊', '猴', '鸡', '狗', '猪'
            );
            $key = (substr($post['id_card'],6,4) - 1900) % 12;
            $post['zodiac'] = $animals[$key];
            $training_id = M('tra_training')->where($where)->save($post);
            if($training_id!==false){
                echo "<script>alert('修改成功！');window.location.href='" . __MODULE__ . "/Training/info/id/".$post['id'].".html';</script>";
            }else{
                echo "<script>alert('修改失败！');window.location.href='" . __MODULE__ . "/Training/editTraining/id/".$post['id'].".html';</script>";
            }
        }else{
            $training = M('tra_training')->where('id='.I('get.id').'')->find();
            if(!$training){
                echo "<script>alert('地址异常！');history.back();</script>";
                die;
            }
            $this->assign('training',$training);
            $this->display();
        }
    }

}