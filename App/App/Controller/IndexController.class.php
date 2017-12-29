<?php
namespace App\Controller;
use Common\Common\api\SMS;
use App\Controller\CommonController;
use Think\GoodPage;
use Think\Model;

class IndexController extends CommonController {
    //首页
    public function index(){
        //显示首页banner
        $banner_list=M("Banners")->where(['type'=>2,'display'=>1])->order('sort asc')->select();
        $this->assign("banner",$banner_list);

        //热卖车型
        $hot = M('Products')->order('hit desc,id desc')->limit(5)->select();
        foreach($hot as $kh=>$vh){
            $hot[$kh]['price']=intval($vh['price']);
            $cover_img = M('ProImg')->where(['n_id'=>$vh['id'],'type'=>2,'flag'=>1])->getField('name');
            $hot[$kh]['img']=$cover_img?$cover_img:M('ProImg')->where(['n_id'=>$vh['id']])->order('f_id asc')->getField('name');
        }
        $this->assign("hot",$hot);

        //活动推荐
        $act = M('Products')->where(['istui'=>'1'])->order('id desc')->limit(5)->select();
        foreach($act as $ka=>$va){
            $act[$ka]['price']=intval($va['price']);
            $cover_img = M('ProImg')->where(['n_id'=>$va['id'],'type'=>2,'flag'=>1])->getField('name');
            $act[$ka]['img']=$cover_img?$cover_img:M('ProImg')->where(['n_id'=>$va['id']])->order('f_id asc')->getField('name');
        }
        $this->assign("act",$act);

        $this->display();
    }

    //商品列表
    public function lists(){
        $sortM = M('ProductSort');
        $proDB = M('Products');

        if(isset($_GET['sort'])&&$_GET['sort']){
            $classly = $_GET['sort'];
            $arr = explode("_",$classly);
        }else{
            $arr = '';
        }
        $this->assign("arr",$arr);
        if($arr != '') {
            $i = 1;
            $sql = '';
            foreach ($arr as $ks => $vs) {
                if ($vs != '0') {
                    if ($i == 1) {
                        $sql = 'select pid as pid1 from app_pro_s where sid=' . $vs;
                    } else {
                        $sql = 'select pid as pid' . $i . ' from app_pro_s as s' . ($i - 1) . ' inner join (' . $sql . ') as t' . ($i - 1) . ' on s' . ($i - 1) . '.pid=t' . ($i - 1) . '.pid' . ($i - 1) . ' where s' . ($i - 1) . '.sid=' . $vs;
                    }
                    $i++;
                }
            }
        }

        $model = new Model();
        $arrList = $model->query($sql);
        if($arrList){
            $t = $i-1;
            foreach($arrList as $kw=>$vw){
                $f_ids[]=$vw['pid'.$t];
            }
            $where['id']=array('in',$f_ids);
        }else{
            if($arr!=''&&$_GET['sort']!='0_0_0'){
                $where['id']=0;
            }
        }

        if(isset($_GET['order'])){
            if($_GET['order']=='month'){
                $order = 'price asc';
            }
            if($_GET['order']=='hot'){
                $order = 'hit desc';
            }
        }else{
            $order = 'cost asc';
        }

        if(isset($_GET['keys'])&&!empty($_GET['keys'])){
            if(!is_utf8($_GET['keys'])){
                $keys = iconv("gb2312","utf-8",$_GET['keys']);
            }else{
                $keys = $_GET['keys'];
            }

            $where['title']=array('like',"%{$keys}%");
            $this->assign('keys',$keys);
        }

        $where['status']=1;
        $pageNum = 20;
        $count = $proDB -> where($where) -> count();
        $Page = new GoodPage($count,$pageNum);
        $pageshow = $Page->show();
        $list = $proDB -> where($where) ->  limit($Page->firstRow.','.$Page->listRows) -> order($order.', date_time desc') -> select();
        if($count>$pageNum){
            $this->assign('flag',1);
        }else{
            $this->assign('flag',0);
        }
        foreach($list as $ka=>$va){
            $list[$ka]['price']=intval($va['price']);
            $cover_img = M('ProImg')->where(['n_id'=>$va['id'],'type'=>1,'flag'=>1])->getField('name');
            $list[$ka]['img']=$cover_img?$cover_img:M('ProImg')->where(['n_id'=>$va['id'],'type'=>1])->order('f_id asc')->getField('name');
        }
        $this->assign('page',$pageshow );
        $this->assign('list',$list );

        //获取分类
        $clist = $sortM->where(['level'=>1])->order('sort asc')->select();
        foreach ($clist as $k=>$v){
            $zi = $sortM->where(['level'=>2,'pid'=>$v['id']])->order('sort asc')->select();
            foreach ($zi as $kl=>$vl){
                $zi_l = $sortM->where(['level'=>3,'pid'=>$vl['id']])->order('sort asc')->select();
                $zi[$kl]['zi']=$zi_l;
            }
            $clist[$k]['zi']=$zi;
        }
        $this->assign('clist',$clist);
        $this->display();
    }

    //ajax加载更多
    public function ajax_more(){
        $proDB = M('Products');

        if(isset($_GET['sort'])&&$_GET['sort']){
            $classly = $_GET['sort'];
            $arr = explode("_",$classly);
        }else{
            $arr = '';
        }

        if($arr != '') {
            $i = 1;
            $sql = '';
            foreach ($arr as $ks => $vs) {
                if ($vs != '0') {
                    if ($i == 1) {
                        $sql = 'select pid as pid1 from app_pro_s where sid=' . $vs;
                    } else {
                        $sql = 'select pid as pid' . $i . ' from app_pro_s as s' . ($i - 1) . ' inner join (' . $sql . ') as t' . ($i - 1) . ' on s' . ($i - 1) . '.pid=t' . ($i - 1) . '.pid' . ($i - 1) . ' where s' . ($i - 1) . '.sid=' . $vs;
                    }
                    $i++;
                }
            }
        }

        $model = new Model();
        $arrList = $model->query($sql);
        if($arrList){
            $t = $i-1;
            foreach($arrList as $kw=>$vw){
                $f_ids[]=$vw['pid'.$t];
            }
            $where['id']=array('in',$f_ids);
        }else{
            if($arr!=''&&$_GET['sort']!='0_0_0'){
                $where['id']=0;
            }
        }

        if(isset($_GET['order'])){
            if($_GET['order']=='month'){
                $order = 'price asc';
            }
            if($_GET['order']=='hot'){
                $order = 'hit desc';
            }
        }else{
            $order = 'cost asc';
        }

        if(isset($_GET['keys'])&&!empty($_GET['keys'])){
            if(!is_utf8($_GET['keys'])){
                $keys = iconv("gb2312","utf-8",$_GET['keys']);
            }else{
                $keys = $_GET['keys'];
            }
            $where['title']=array('like',"%{$keys}%");
        }

        $where['status']=1;
        $pagenum = 20;
        $count = $proDB -> where($where) -> count();
        $page = $_POST['next'];
        $pages = ceil(max(1,$count/$pagenum));
        $list = array();
        if($pages<$page){
            $next_page = 0;
            $page_flag = 0;
        }else {
            if ($pages < $page + 1) {
                $pageset1 = $count;
                $page_flag = 0;
                $next_page = 0;
            } else {
                $pageset1 = $page * $pagenum;
                $page_flag = 1;
                $next_page = $page + 1;
            }
            //分页参数
            $pageset = ($page - 1) * $pagenum;
            $list = $proDB->where($where)->limit($pageset, $pagenum)->order($order . ', date_time desc')->select();
            foreach($list as $ka=>$va){
                $list[$ka]['price']=intval($va['price']);
                $cover_img = M('ProImg')->where(['n_id'=>$va['id'],'type'=>1,'flag'=>1])->getField('name');
                $list[$ka]['img']=$cover_img?$cover_img:M('ProImg')->where(['n_id'=>$va['id'],'type'=>1])->order('f_id asc')->getField('name');
            }
        }
        $str = '';
        foreach ($list as $key=>$val){
            $str.='<li>
                        <div class="left">
                            <a href="'.U('Index/info',array('id'=>$val['id'])).'"><img src="http://'.SITE_URL.'/Public/upload/product/'.$val['img'].'" class="img" /></a>
                        </div>
                        <div class="right">
                            <p class="title">'.$val['title'].'</p>
                            <p class="info">'.$val['specs'].'</p>
                            <p class="des"><span class="key">厂商指导价：</span><span class="name">'.$val['money'].'万</span></p>
                            <p class="des"><span class="key">首付：</span><span class="name hot">'.$val['cost'].'万</span></p>
                            <p class="des"><span class="key">月供：</span><span class="name hot">'.$val['price'].'元</span></p>
                        </div>
                    </li>';
        }
        $re = array("pages"=>$pages,"count"=>$count,"pageset"=>$pageset1,"next"=>$next_page,"flag"=>$page_flag,"data"=>$str);
        echo json_encode($re);
    }

    //商品详情
    public function info(){

        if(session('user')&&session('pro_id')){
            session('pro_id',null);
        }

        $proDB = M('Products');
        $imgDB = M('ProImg');
        $info = $proDB->where(['id'=>$_GET['id']])->find();
        if(!$info){
            $this->redirect('Index/index');
        }
        $cover_img = M('ProImg')->where(['n_id'=>$info['id'],'type'=>1,'flag'=>1])->getField('name');
        $info['img']=$cover_img?$cover_img:$imgDB->where(['n_id'=>$info['id'],'type'=>1])->order('f_id asc')->getField('name');
        $info['price']=intval($info['price']);
        $imgs = $imgDB->where(['n_id'=>$info['id'],'type'=>1])->order('f_id asc')->limit(4)->select();
        $this->assign("info",$info);
        $this->assign("imgs",$imgs);

        //常见问题
        $que = M('Question')->order('ctime desc')->select();
        $this->assign("que",$que);

        //最近申请
        $apply = M('Apply')->where(['status'=>1])->order('otime desc,ctime desc')->limit(10)->select();
        foreach($apply as $kp=>$vp){
            $aid = M('Stores')->where(['id'=>$vp['sid']])->getField('aid');
            $city = M('Area')->where(['id'=>$aid])->getField('name');
            $apply[$kp]['phone'] = substr_replace($vp['phone'], '****', 3, 4);
            $apply[$kp]['city'] = $city;
            $apply[$kp]['title'] = M('Products')->where(['id'=>$vp['pid']])->getField('title');
            $apply[$kp]['otime'] = date('Y-m-d',$vp['otime']);
        }
        $this->assign("apply",$apply);
        $this->display();
    }

    //关于我们
    public function about(){
        $this->display();
    }

    //联系我们
    public function contact(){
        $this->display();
    }

    //使用帮助
    public function help(){
        $this->display();
    }

    //网络服务条款
    public function terms(){
        $this->display();
    }

    //意见反馈
    public function feedback(){
        $feedDB = M('Feeds');
        $u = session('user');
        if (I('get.submit')){
            $ip = $_SERVER["REMOTE_ADDR"];
            $now = time();
            $time = $feedDB->where(['ip'=>$ip])->getField('ctime');
            if($time && $time+60>$now){
                $re = array("code"=>"001","msg"=>"请勿频繁操作");
                echo json_encode($re);exit;
            }
            $data['uid']=$u?$u['id']:0;
            $data['name']=$_POST['name'];
            $data['phone']=$_POST['phone'];
            $data['content']=$_POST['con'];
            $data['ip']=$ip;
            $data['ctime']=time();
            $is= $feedDB->add($data);
            if($is){
                $re = array('code'=>'000','msg'=>'提交成功');
            }else{
                $re = array('code'=>'001','msg'=>'提交失败');
            }
            echo json_encode($re);exit;
        }
        //常见问题
        $que = M('Question')->order('ctime desc')->limit(3)->select();
        $this->assign("que",$que);
        $this->display();
    }

    //检测是否登录
    public function checkLogin(){
        if(!session('user')){
            session('pro_id',$_POST['id']);
            $re = array('code'=>'001','msg'=>'请先登录');
        }else{
            $re = array('code'=>'000','msg'=>'验证成功');
        }
        echo json_encode($re);
    }

    //获取门店
    public function find_store(){
        $storeDB = M('Stores');
        $li = $storeDB->where(['aid'=>$_POST['id']])->select();
        if($li){
            $str = '<option value="0">选择门店</option>';
            foreach($li as $k=>$v){
                $str.='<option value="'.$v['id'].'">'.$v['title'].'</option>';
            }
            echo $str;
        }else{
            echo 0;
        }
    }

    /**
     * 验证码
     */
    public function code(){
        $verify = new \Think\Verify();
        $verify->useCurve = false;
        $verify->useNoise = false;
        $verify->bg = array(255, 255, 255);

        if (I('get.code_len')) $verify->length = intval(I('get.code_len'));
        if ($verify->length > 8 || $verify->length < 2) $verify->length = 4;

        if (I('get.font_size')) $verify->fontSize = intval(I('get.font_size'));

        if (I('get.width')) $verify->imageW = intval(I('get.width'));
        if ($verify->imageW <= 0) $verify->imageW = 130;

        if (I('get.height')) $verify->imageH = intval(I('get.height'));
        if ($verify->imageH <= 0) $verify->imageH = 50;

        $verify->entry('home');
    }

    /**
     * 获取手机验证码
     */
    public function getPhoneCode(){
        $user_vers = M('UserVers');
        $phone = $_POST['phone'];
        $time = $user_vers->where(['phone'=>$phone])->order("time desc")->getField("time");
        $now = time();
        if($time && $time+60>$now){
            $error = "手机验证码已经发到你手机，请不要重复发送";
            $re_arr = array("error"=>"1","msg"=>$error);
        }else{
            $num="";
            for($i=0;$i<6;$i++){
                $num.= rand(0,9);
            }
            if(!$phone){
                $error = "手机不能为空";
                $re_arr = array("error"=>"1","msg"=>$error);
            }else{
                $sms = new SMS();
                session('phone_code', $num);
                $content = "您的注册验证码是：{$num}。该验证码在半小时内均有效。如非本人操作，请勿理会！";
                $mobile = $phone;
                $message = iconv("UTF-8", "GB2312", $content);
                $sms->sendSMS($mobile, $message);
                $code=$sms->getCode();
                if($code=='2000') {
                    $data = array();
                    $data['time'] = $now;
                    $data['phone'] = $phone;
                    $data['vers'] = $num;
                    $user_vers->add($data);
                    $re_arr = array("error" => "0", "msg" => "手机验证码已经发到你手机,请查收");
                }else{
                    $re_arr = array("error" => "1", "msg" => "发送失败");
                }
            }
        }
        echo json_encode($re_arr);
    }
}