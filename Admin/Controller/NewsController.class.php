<?php
namespace Admin\Controller;
use Think\Controller;
class NewsController extends Controller {
    public function _initialize(){
        if(!$_SESSION[C('ADMIN_AUTH_KEY')]['id']){
            echo "<script>alert('请登录！');window.location.href='" . __MODULE__ . "/Admin/login.html';</script>";
            exit;
        }else{
            $admin_user = M('permission')->where('employee_id='. $_SESSION[C('ADMIN_AUTH_KEY')]['id'].'')->find();
            if($admin_user['news']!=1){
                echo "<script>alert('你没有权限！');window.location.href='" . __MODULE__ . "/Admin/info.html';</script>";
                exit;
            }
        }
    }
    //新闻列表页
    public function index(){
        $news = D('News');

        $where['belong'] = I("get.type");
        $count=$news->where($where)->count();
        $Page       = new \Think\Page($count,20);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show       = $Page->showFront();// 分页显示输出// 进行分页数据查询 注意limit方法的参数要使用Page类的属性

        $news_list = $news->where($where)->order('belong asc,sort desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        foreach($news_list as $k=>$v){
            $news_list[$k]['add_time']=date('Y-m-d H:i:s',$v['add_time']);
            switch($v['belong']){
                case 1:
                    $news_list[$k]['belong']='新闻资讯';
                    break;
                case 2:
                    $news_list[$k]['belong']='月嫂新闻';
                    break;
                case 3:
                    $news_list[$k]['belong']='育儿嫂新闻';
                    break;
                case 4:
                    $news_list[$k]['belong']='保姆新闻';
                    break;
//                case 5:
//                    $news_list[$k]['belong']='月嫂新闻4';
//                    break;
            }

        }

        $this->assign('news_list',$news_list);
        $this->assign('page',$show);
        $this->display('News/list');
    }



    //添加新闻页面
    public function addNews(){
        if(!($_SESSION[C('ADMIN_AUTH_KEY')]['add_per']==1||$_SESSION[C('ADMIN_AUTH_KEY')]['permission']==1)) {
            echo "<script>alert('你没有权限！');window.location.href='" . __MODULE__ . "/Admin/info.html';</script>";
        }
        $sort = M('news')->field('sort')->where('belong=1')->order('sort desc')->limit(1)->find();
        $sort_num = $sort['sort']?$sort['sort']+1:100;

        $this->assign('sort_num',$sort_num);
        $this->display();
    }

    //修改新闻页面
    public function editNews(){
        if(!($_SESSION[C('ADMIN_AUTH_KEY')]['edit_per']==1||$_SESSION[C('ADMIN_AUTH_KEY')]['permission']==1)) {
            echo "<script>alert('你没有权限！');window.location.href='" . __MODULE__ . "/Admin/info.html';</script>";
        }
        if(is_numeric(I('get.id'))){
            $news = M('news')->where('id='.I('get.id').'')->find();
            if($news&&$news!=''){
                $this->assign('news',$news);
                $this->display();
            }else{
                echo "<script>alert('地址异常');history.back();</script>";
            }

        }else{
            echo "<script>alert('地址异常');history.back();</script>";
        }
    }

    // 删除新闻
    public function deleteNews(){
        $admin_user = M('permission')->where('employee_id='. $_SESSION[C('ADMIN_AUTH_KEY')]['id'].'')->find();
        if($admin_user['del_per']!=1){
            echo "<script>alert('你没有权限这么做');history.back();</script>";
        }else{
            $info = M('news')->where('id='.I('get.id').'')->find();
            $delete = M('news')->where('id='.I('get.id').'')->delete();
            if($delete!==false){
                unlink('Uploads/'.$info['title_img']);
                if(I('get.p')){
                    echo "<script>alert('删除成功');window.location.href='".__MODULE__."/News/index/p/".I('get.p').".html';</script>";
                }else{
                    echo "<script>alert('删除成功');window.location.href='".__MODULE__."/News/index/p/1.html';</script>";
                }
            }else{
                echo "<script>alert('删除失败');history.back();</script>";
            }
        }


    }
    //变更默认排序
    public function change_order(){
        $order = M('news')->field('sort')->where('belong='.I('post.belong').'')->order('sort desc')->limit(1)->find();
        $order_num = $order['sort']?$order['sort']+1:100;
        $back['code']=1000;
        $back['data']=$order_num;
        echo json_encode($back);
    }

    //添加新闻
    public function addNews_p(){
        $News_mod = D("News");
        if($_FILES['title_img']['tmp_name']) {
            $file = $_FILES['title_img'];
            $upload = new \Think\Upload();// 实例化上传类
            $upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
            $upload->savePath = 'news/'; // 设置附件上传目录
            $upload->saveName = time() . '_' . mt_rand();
            $upload->maxSize = 1048576;// 设置附件上传大小
            $upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
            $info = $upload->uploadOne($file);
            if (!$info) {
                $error = $upload->getError();
                echo "<script>alert('".$error."');history.back();</script>";
            } else {
                $imgPath = $info['savepath'] . $info['savename'];
                $new_map['title_img'] = $imgPath;
            }
        }else{
            echo "<script>alert('请上传头图');history.back();</script>";
            die;
        }
        $post = I("post.");
        $new_map['title'] = $post['title'];
        $new_map['description'] = $post['description'];
        $new_map['keyword'] = $post['keyword'];
        $new_map['content'] = $post['content'];
        $new_map['add_time'] = time();
        $new_map['edit_time'] = time();
        $new_map['sort'] =$post['sort'];

        $new_map['belong'] = $post['belong'];
        if($post['belong']!=1){
            $new_map['index_show'] = $post['index_show']?$post['index_show']:0;
            $new_map['detail_show'] = $post['detail_show']?$post['detail_show']:0;
        }else{
            $new_map['index_show'] = $post['index_show1']?$post['index_show1']:0;
            $new_map['detail_show'] = $post['detail_show1']?$post['detail_show1']:0;
        }
        $add_mod = $News_mod->add_mod($new_map);
        if($add_mod!==false){
            echo "<script>alert('添加成功！');window.location.href='".__MODULE__."/News/index/type/".$post['belong'].".html';</script>";
        }
    }

    //修改新闻
    public function editNews_p(){
        $News_mod = D("News");
        if($_FILES['title_img']['tmp_name']) {
            $file = $_FILES['title_img'];
            $upload = new \Think\Upload();// 实例化上传类
//            $upload->maxSize = 20000000;// 设置附件上传大小
            $upload->maxSize = 1048576;// 设置附件上传大小
            $upload->exts = array('jpg', 'gif', 'png', 'jpeg','exe');// 设置附件上传类型
            $upload->savePath = 'news/'; // 设置附件上传目录
            $upload->saveName = time() . '_' . mt_rand();
            $upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
            $info = $upload->uploadOne($file);
            if (!$info) {
                $error = $upload->getError();
                echo "<script>alert('".$error."');history.back();</script>";
                die;
            } else {
                $imgPath = $info['savepath'] . $info['savename'];
                $new_map['title_img'] = $imgPath;
            }
        }
        $post = I("post.");
        $new_map['title'] = $post['title'];
        $new_map['description'] = $post['description'];
        $new_map['keyword'] = $post['keyword'];
        $new_map['content'] = $post['content'];
        $new_map['edit_time'] = time();
        $new_map['sort'] =$post['sort'];

        $new_map['belong'] = $post['belong'];
        if($post['belong']!=1){
            $new_map['index_show'] = $post['index_show']?$post['index_show']:0;
            $new_map['detail_show'] = $post['detail_show']?$post['detail_show']:0;
        }else{
            $new_map['index_show'] = $post['index_show1']?$post['index_show1']:0;
            $new_map['detail_show'] = $post['detail_show1']?$post['detail_show1']:0;
        }
        $where['id']=$post['id'];
        $save_mod = $News_mod->save_mod($where,$new_map);
        if($save_mod!==false){
            if($info){
                unlink('Uploads/'.I("post.title_img_old"));
            }
            echo "<script>alert('修改成功！');window.location.href='".__MODULE__."/News/index/type/".$post['belong'].".html';</script>";
        }
    }








}