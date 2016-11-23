<?php
namespace NAdmin\Controller;
use Think\Controller;
class ConfinementController extends CommonController {

    public function _initialize(){
        if(!$_SESSION[C('ADMIN_AUTH_KEY')]['id']){
            echo "<script>alert('请登录！');window.location.href='" . __MODULE__ . "/Admin/login.html';</script>";
            exit;
        }else{
            $admin_user = M('permission')->where('employee_id='. $_SESSION[C('ADMIN_AUTH_KEY')]['id'].'')->find();
            if($admin_user['nurse']!=1){
                echo "<script>alert('你没有权限！');window.location.href='" . __MODULE__ . "/Admin/info.html';</script>";
                exit;
            }
        }


    }
    //月嫂列表页
    public function index(){
        $Nurse = D('Nurse');
        $count=$Nurse->count();
        $Page       = new \Think\Page($count,20);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show       = $Page->showFront();// 分页显示输出// 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $nurse_list = $Nurse->order('is_recommend desc,sort desc,recommend_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        foreach($nurse_list as $k=>$v){
            $nurse_list[$k]['age']=date('Y',time())-date('Y',strtotime($v['age']));
        }
        $this->assign('nurse_list',$nurse_list);
        $this->assign('page',$show);
        $this->display('Confinement/list');
    }



    //添加月嫂基本信息页面
    public function addConfinement_1(){

        $sort = M('nurse')->field('sort')->order('sort desc')->limit(1)->find();
        $sort_num = $sort['sort']?$sort['sort']+1:100;

        $this->assign('sort_num',$sort_num);
        $this->display();
    }

    //修改月嫂基本信息页面
    public function editConfinement_1(){

        $id = I("get.id");
        $nurse = M('nurse')->where('id='.$id.'')->find();

        $this->assign('nurse',$nurse);
        $this->display();
    }

    //修改月嫂基本信息
    public function editInfo(){
        $post = I('post.');
        $Nurse = D('Nurse');
        if ($_FILES['title_img']['tmp_name']) {
            $name = $this->pinyin_long($post['name']);
            $upload = new \Think\Upload();// 实例化上传类
            $upload->maxSize = 2145728;// 设置附件上传大小
            $upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
            $upload->savePath = 'nurse/'.$post['id'] .'_'. $name . '/head/'; // 设置附件上传目录
            $upload->subName = ''; // 设置附件上传目录
            $info = $upload->uploadOne($_FILES['title_img']);
            if (!$info) {// 上传错误提示错误信息
                echo "<script>alert('图片上传失败');window.location.href='" . __MODULE__ . "/Confinement/editConfinement_1/id/" . $post['id'] . "';</script>";
            } else {// 上传成功
                $imgPath = $info['savepath'] . $info['savename'];
                $post['title_img'] = $imgPath;
            }
        }
        $where['id']=$post['id'];

        $save_mod = $Nurse->save_mod($where,$post);
        if($save_mod!==false){

            echo "<script>alert('修改成功');window.location.href='" . __MODULE__ . "/Confinement/index';</script>";

        }else{
            echo "<script>alert('修改失败');window.location.href='" . __MODULE__ . "/Confinement/editConfinement_1/id/" . $post['id'] . "';</script>";
        }



    }

    public function addInfo(){
        $have = M('nurse')->where('name="'.I("post.name").'" and age="'.I("post.age").'"')->find();
        if($have){
            echo "<script>alert('添加成功');window.location.href='" . __MODULE__ . "/Confinement/addTest/id/" . $have['id'] . "/smp/1';</script>";
        }
        $post = I('post.');
        $Nurse = D('Nurse');
        $post['add_time']=time();
        $id = $Nurse->add_mod($post);
        if($id) {
            if ($_FILES['title_img']) {
                $name = $this->pinyin_long($post['name']);
                $upload = new \Think\Upload();// 实例化上传类
                $upload->maxSize = 2145728;// 设置附件上传大小
                $upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
                $upload->savePath = 'nurse/'.$id .'_'. $name . '/head/'; // 设置附件上传目录
                $upload->subName = ''; // 设置附件上传目录
                $info = $upload->uploadOne($_FILES['title_img']);
                if (!$info) {// 上传错误提示错误信息
                    echo "<script>alert('图片上传失败');window.location.href='" . __MODULE__ . "/Confinement/editConfinement_1/id/" . $id . "';</script>";
                } else {// 上传成功
                    $imgPath = $info['savepath'] . $info['savename'];
                    $map['title_img'] = $imgPath;
                    $where['id'] = $id;
                    $save = $Nurse->save_mod($where,$map);
                    if ($save!==false) {
                        echo "<script>alert('添加成功');window.location.href='" . __MODULE__ . "/Confinement/addTest/id/" . $id . "/smp/1';</script>";
                    } else {
                        echo "<script>alert('图片上传失败');window.location.href='" . __MODULE__ . "/Confinement/editConfinement_1/id/" . $id . "';</script>";
                    }
                }
            } else {
                echo "<script>alert('图片异常');window.location.href='" . __MODULE__ . "/Confinement/editConfinement_1/id/" . $id . "';</script>";
            }
        }else{
            echo "<script>alert('上传失败');history.back();</script>";
        }
    }

    // 月嫂头像上传/以及导师推荐视频
    public function addTest(){
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

    public function addTest_p(){
        $post = I('post.');
        $name = $this->pinyin_long($post['name']);
        $test_img0 = $post['test_img0'];
        $test_img1 =  $post['test_img1'];
        $test_img2 =  $post['test_img2'];
        if($_FILES['test_img0']['tmp_name']) {
            $upload = new \Think\Upload();// 实例化上传类
            $upload->maxSize = 2145728;// 设置附件上传大小
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
            $upload->maxSize = 2145728;// 设置附件上传大小
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
            $upload->maxSize = 2145728;// 设置附件上传大小
            $upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
            $upload->savePath = 'nurse/' . $post['id'] . '_' . $name . '/test/'; // 设置附件上传目录
            $upload->subName = ''; // 设置附件上传目录
            $info = $upload->uploadOne($_FILES['test_img2']);
            if (!$info) {
                $error = $upload->getError();
                echo "<script>alert('第三张图".$error."');</script>";
            } else {
                $imgPath = $info['savepath'] . $info['savename'];
                $new_map['title_img'] = $imgPath;
                $test_img2 = $imgPath;
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
        $map['test_img'] = $test_img0.','.$test_img1.','.$test_img2;
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
            if( $map['recommend_video']){
                unlink('Uploads/'.$post['recommend_video']);
            }

            if($_GET['smp']==1){
                echo "<script>alert('编辑成功');window.location.href='" . __MODULE__ . "/Confinement/addVideo/id/" . $post['id'] . "';</script>";
            }else{
                echo "<script>alert('编辑成功');window.location.href='" . __MODULE__ . "/Confinement/index';</script>";
            }
        }else{
            echo "<script>alert('上传失败');history.back();</script>";
        }
    }

    // 月嫂头像上传/以及导师推荐视频
    public function addVideo(){
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
        $save_mode = $Nurse_mod->save_mod($where,$map);
        if($save_mode!==false){
            foreach($do_video_old as $v){
                if(!in_array($v,$do_video)){
                    unlink('Uploads/'.$v);
                }
            }
            if($error==0){
                echo "<script>alert('编辑成功');window.location.href='" . __MODULE__ . "/Confinement/index';</script>";
            }else{
                echo "<script>alert('编辑成功,".$error."个视频未成功上传');window.location.href='" . __MODULE__ . "/Confinement/index';</script>";
            }
        }else{
            foreach($do_video_ok as $v){
                unlink('Uploads/'.$v);
            }
            echo "<script>alert('编辑失败,不做任何修改');window.location.href='" . __MODULE__ . "/Confinement/addVideo/id/" . $post['id'] . "';</script>";
        }
    }

    // 删除月嫂
    public function deleteNurse(){
        $admin_user = M('permission')->where('employee_id='. $_SESSION[C('ADMIN_AUTH_KEY')]['id'].'')->find();
        if($admin_user['del_per']!=1){
            echo "<script>alert('你没有权限这么做');history.back();</script>";
        }else{
            $delete = M('nurse')->where('id='.I('get.id').'')->delete();
            $info = M('nurse')->where('id='.I('get.id').'')->find();
            if($delete!==false){
                $img = explode('/',$info['title_img']);
                $dir = 'Uploads/'.'nurse/'.$img[1];
                $this->deldir($dir);
                if(I('get.p')){
                    echo "<script>alert('删除成功');window.location.href='".__MODULE__."/Confinement/index/p/".I('get.p').".html';</script>";
                }else{
                    echo "<script>alert('删除成功');window.location.href='".__MODULE__."/Confinement/index/p/1.html';</script>";
                }
            }else{
                echo "<script>alert('删除失败');history.back();</script>";
            }
        }
    }

    public function recommend(){
        $admin_user = M('permission')->where('employee_id='. $_SESSION[C('ADMIN_AUTH_KEY')]['id'].'')->find();
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
                    echo "<script>alert(' 设置成功');window.location.href='".__MODULE__."/Confinement/index/p/".I('get.p').".html';</script>";
                }else{
                    echo "<script>alert('设置成功');window.location.href='".__MODULE__."/Confinement/index/p/1.html';</script>";
                }
            }else{
                echo "<script>alert('设置失败');window.location.href='".__MODULE__."/Confinement/index/p/1.html';</script>";
            }


        }



    }





    public function nametest(){
        print_r($_FILES);
        print_r($_POST);die;
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize   =     33145728 ;// 设置附件上传大小
        $upload->exts = array('jpg', 'gif', 'png', 'jpeg','mp4');// 设置附件上传类型
        $upload->savePath = '12_libo/'; // 设置附件上传目录
        $info   =   $upload->upload();
        if(!$info) {// 上传错误提示错误信息
            echo $upload->getError();
        }else{// 上传成功
            echo '上传成功！';
        }

    }





}