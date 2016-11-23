<?php
namespace NAdmin\Controller;
use Think\Controller;
class BannerController extends Controller {
    public function _initialize(){
        if(!$_SESSION[C('N_ADMIN_AUTH_KEY')]['id']){
            echo "<script>alert('请登录！');window.location.href='" . __MODULE__ . "/Admin/login.html';</script>";
            exit;
        }
    }


    //banner 列表页
    public function bannerList(){
        $count = M('n_banner')->count();
        $Page = new \Think\Page($count,20);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show = $Page->showFront();// 分页显示输出// 进行分页数据查询 注意limit方法的参数要使用Page类的属性

        $banner_list = M('n_banner')->order('belong asc,status asc,sort desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        $status_name = ['','显示','不显示'];
        $belong_name = ['','月嫂','关于我们','联系我们'];
        $belong_name[21] = '新闻动态';
        $belong_name[22] = '公司公告';
        $belong_name[11] = '首页';
        foreach($banner_list as $k=>$v){
            $banner_list[$k]['status_name'] = $status_name[$v['status']];
            $banner_list[$k]['belong_name'] = $belong_name[$v['belong']];
        }
        $this->assign('banner_list',$banner_list);
        $this->assign('page',$show);
        $this->display();
    }



    //添加banner页面
    public function addBanner(){
        $sort = M('n_banner')->field('sort')->order('sort desc')->limit(1)->find();
        $sort_num = $sort['sort']?$sort['sort']+1:100;

        $this->assign('sort_num',$sort_num);
        $this->display();
    }

    //添加banner
    public function add_banner(){
        $post = I("post.");
        if($_FILES['src']['tmp_name']) {
            $file = $_FILES['src'];
            $upload = new \Think\Upload();// 实例化上传类
            $upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
            $upload->savePath = 'banner/'; // 设置附件上传目录
            $upload->saveName = time() . '_' . mt_rand();
            $upload->maxSize = 1048576;// 设置附件上传大小
            $info = $upload->uploadOne($file);
            if (!$info) {
                $error = $upload->getError();
                echo "<script>alert('".$error."');history.back();</script>";
            } else {
                $imgPath = $info['savepath'] . $info['savename'];
                $post['src'] = $imgPath;
            }
        }else{
            echo "<script>alert('请上传banner!');history.back();</script>";
            die;
        }
        $add_mod = M('n_banner')->add($post);
        if($add_mod!==false){
            echo "<script>alert('添加成功！');window.location.href='".__MODULE__."/Banner/bannerList.html';</script>";
            exit;
        }else{
            echo "<script>alert('添加失败！');history.back();</script>";
            exit;
        }
    }



    //修改banner页面
    public function editBanner(){
        if(is_numeric(I('get.id'))){
            $banner = M('n_banner')->where('id='.I('get.id').'')->find();
            if($banner&&$banner!=''){
                $this->assign('banner',$banner);
                $this->display();
            }else{
                echo "<script>alert('地址异常');history.back();</script>";
                exit;
            }

        }else{
            echo "<script>alert('地址异常');history.back();</script>";
            exit;
        }
    }


    //修改banner
    public function edit_banner(){
        $post = I("post.");
        if($_FILES['src']['tmp_name']) {
            $file = $_FILES['src'];
            $upload = new \Think\Upload();// 实例化上传类
            $upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
            $upload->savePath = 'banner/'; // 设置附件上传目录
            $upload->saveName = time() . '_' . mt_rand();
            $upload->maxSize = 1048576;// 设置附件上传大小
            $info = $upload->uploadOne($file);
            if (!$info) {
                $error = $upload->getError();
                echo "<script>alert('".$error."');history.back();</script>";
            } else {
                $imgPath = $info['savepath'] . $info['savename'];
                $post['src'] = $imgPath;
            }
        }
        $add_mod = M('n_banner')->where('id='.$post['id'])->save($post);

        if($add_mod!==false){
            if( $post['src']){
                unlink('Uploads/'.I("post.src_old"));
            }
            echo "<script>alert('修改成功！');window.location.href='".__MODULE__."/Banner/bannerList.html';</script>";
            exit;
        }else{
            echo "<script>alert('修改失败！');history.back();</script>";
            exit;
        }
    }

    // 删除banner
    public function deleteBanner(){
        if(!I('get.id')){
            echo "<script>alert('地址异常');history.back();</script>";
            exit;
        }
        $info = M('n_banner')->where('id='.I('get.id').'')->find();
        if(!$info){
            echo "<script>alert('地址异常');history.back();</script>";
            exit;
        }
        $delete = M('n_banner')->where('id='.I('get.id').'')->delete();
        if($delete!==false){
            unlink('Uploads/'.$info['src']);
            echo "<script>alert('删除成功');window.location.href='".__MODULE__."/Banner/bannerList.html';</script>";
        }else{
            echo "<script>alert('删除失败');history.back();</script>";
        }
    }





    //友链列表页
    public function friendList(){
        $count = M('n_friendship_link')->count();
        $Page = new \Think\Page($count,20);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show = $Page->showFront();// 分页显示输出// 进行分页数据查询 注意limit方法的参数要使用Page类的属性

        $friend_list = M('n_friendship_link')->order('status asc,sort desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        $status_name = ['','显示','不显示'];
        $target_name = ['','是','否'];
        foreach($friend_list as $k=>$v){
            $friend_list[$k]['status_name'] = $status_name[$v['status']];
            $friend_list[$k]['target_name'] = $target_name[$v['target']];
        }
        $this->assign('friend_list',$friend_list);
        $this->assign('page',$show);
        $this->display();
    }



    //添加友联页面
    public function addFriend(){
        $sort = M('n_friendship_link')->field('sort')->order('sort desc')->limit(1)->find();
        $sort_num = $sort['sort']?$sort['sort']+1:100;

        $this->assign('sort_num',$sort_num);
        $this->display();
    }

    //添加友链
    public function add_friend(){
        $post = I("post.");
        $add_mod = M('n_friendship_link')->add($post);
        if($add_mod!==false){
            echo "<script>alert('添加成功！');window.location.href='".__MODULE__."/Banner/friendList.html';</script>";
        }
    }



    //修改友链页面
    public function editFriend(){
        if(is_numeric(I('get.id'))){
            $friend = M('n_friendship_link')->where('id='.I('get.id').'')->find();
            if($friend&&$friend!=''){
                $this->assign('friend',$friend);
                $this->display();
            }else{
                echo "<script>alert('地址异常');history.back();</script>";
                exit;
            }

        }else{
            echo "<script>alert('地址异常');history.back();</script>";
            exit;
        }
    }


    //修改友链
    public function edit_friend(){
        $post = I("post.");
        $add_mod = M('n_friendship_link')->where('id='.$post['id'])->save($post);

        if($add_mod!==false){
            echo "<script>alert('修改成功！');window.location.href='".__MODULE__."/Banner/friendList.html';</script>";
            exit;
        }else{
            echo "<script>alert('修改失败！');history.back();</script>";
            exit;
        }
    }

    // 删除友链
    public function deleteFriend(){
        if(!I('get.id')){
            echo "<script>alert('地址异常');history.back();</script>";
            exit;
        }
        $info = M('n_friendship_link')->where('id='.I('get.id').'')->find();
        if(!$info){
            echo "<script>alert('地址异常');history.back();</script>";
            exit;
        }
        $delete = M('n_friendship_link')->where('id='.I('get.id').'')->delete();
        if($delete!==false){
            echo "<script>alert('删除成功');window.location.href='".__MODULE__."/Banner/friendList.html';</script>";
        }else{
            echo "<script>alert('删除失败');history.back();</script>";
        }
    }


}