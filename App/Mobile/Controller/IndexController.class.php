<?php
namespace Mobile\Controller;
use Think\Controller;
class IndexController extends Controller {
	public function index(){
		//显示首页banner
		$banner_list=M("Banners")->where(['type'=>2,'display'=>1])->order('sort asc')->select();
		$this->assign("banner",$banner_list);
		
		//获取产品分类
		$classify=M("ProductSort")->where("pid='0' and display='1'")->limit(7)->select();
		$this->assign("classify",$classify);
		
		//获取产品类别
		$sort=M("ProductClass")->where("fid='0' and state='1'")->limit(1)->select();
		$this->assign("sort",$sort);
		
		//推荐产品
		//大屏推荐
		$goods_b=M("Products")->where(['istui'=>1])->limit(1)->select();
		foreach ($goods_b as $k1=>$v1){
			$goods_b[$k1]['pic']=M('ProImg')->where(['n_id'=>$v1['id']])->limit(1)->getField('name');
		}
		$this->assign("goods_b",$goods_b);
		//普通推荐
		$goods=M("Products")->where(['istui'=>1])->limit(6)->select();
		foreach ($goods as $k2=>$v2){
			$goods[$k2]['pic']=M('ProImg')->where(['n_id'=>$v2['id']])->limit(1)->getField('name');
		}
		$this->assign("goods_s",$goods);
		$this->display();
	}
	
	//商品列表
	public function pro()
	{
		//获取分类
		$classify=M("ProductSort")->where("pid='0' and display='1'")->select();
		$this->assign("classify",$classify);
	
		$classify_id=$_GET['classify'];
		$sort_id=$_GET['sort_id'];
		$keyword=$_GET['keyword'];
		$page=$_GET['page']?$_GET['page']:1;
		$this->assign("sort_id",$sort_id);
		$this->assign("keyword",$keyword);
		$this->assign("page",$page);
		$limit=10;
	
		if(!empty($classify_id)){
			$where['c_id']=$classify_id;
		}
	
		if(!empty($sort_id)){
			$where['s_id']=$sort_id;
		}
	
		if(!empty($keyword)){
			$where['title']=array("like","%{$keyword}%");
		}
	
		$goods=M("Products")->where($where)->page($page)->limit($limit)->select();
		foreach($goods as $key=>$val){
			$goods[$key]['smallpic']=M('ProImg')->where(['n_id'=>$val['id']])->limit(1)->getField('name');
		}
		$this->assign("goods",$goods);
		$this->assign("classify_id",$classify_id);
		if(empty($goods)){
			$this->assign("tips","查无数据！");
			$this->assign("block","display:block;");
		}
	
		$this->display();
	}
	
	//商品详情
	public function pro_info()
	{
		$user_id=session("user")['id'];
		
		$goods_id=$_GET['id']?$_GET['id']:5;
	
		//月销量统计
		$mcount =M("OrderInfo")->where("id='{$goods_id}'")->sum("quantity");
		$this->assign("mcount",$mcount?$mcount:'0');
	
		$goods_info=M("Products")->where("id='{$goods_id}'")->select();
		
		if(empty($goods_info)){
			$this->redirect('Index/index');
		}
		
		$goods_img =M('ProImg')->where(['n_id'=>$goods_info[0]['id']])->select();
		$this->assign("goods_img",$goods_img);
		$this->assign("goods_info",$goods_info);
		$this->assign("goods_id",$goods_info[0]['id']);
		//判断该商品是否已经收藏
		$id=M("Mycollect")->where("user_id='{$user_id}' and goods_id='{$goods_id}'")->getfield("id");
		if(!empty($id)){
			$this->assign("ac","ac");
			$this->assign("text","已收藏");
		}else {
			$this->assign("text","收藏");
		}
		$this->display();
	}
	
	//产品分类
	public function pro_class()
	{
		$type=M("ProductSort")->where("pid='0' and display='1'")->select();
		foreach ($type as $key=>$val){
			$type[$key]['banner_pic']=$this->per_url.$val['banner_pic'];
		}
		$this->assign("type",$type);
		$this->display();
	}
	
	//分类ajax
	public function pro_class_ajax()
	{
		$fid=$_GET['id']?$_GET['id']:1;
		$pic = M("ProductSort")->where("id='{$fid}' and display='1'")->getfield("pic");
		$banner_pic='./Public/upload/banner/'.$pic;
		$value=M("ProductSort")->where("pid='{$fid}' and display='1'")->select();
		if(!empty($pic)){
			$str='<a><img src="'.$banner_pic.'"/></a><ul class="pro_class_right_ul">';
		}else{
			$str='<ul class="pro_class_right_ul">';
		}
		foreach ($value as $key=>$val){
			$str.='<li><a href="'.U("Index/pro",array('classify'=>$val['id'])).'">'.$val['name'].'</a></li>';
		}
		$str.='</ul>';
		echo $str;
	
	}
	
	//忘记密码
	public function wj_password()
	{
		$phonecode=$_SESSION['phonecode'];
		$where['phone']=$_POST['phone'];
		$postcode=$_POST['postcode'];
		if(!empty($_POST['phone'])&&!empty($postcode)){
			$user = M("User")->where($where)->limit(1)->select();
			if(!$user){
				$re = array("status"=>100,"msg"=>"该账号不存在！");
				echo json_encode($re);exit;
			}
			if($phonecode==$postcode){
				session("set_phone", $_POST['phone']);
				$url = U('Index/fd_reset');
				$re = array("status"=>200,"msg"=>"确认跳转","url"=>$url);
				echo json_encode($re);exit;
			}else{
				$re = array("status"=>100,"msg"=>"验证码输入错误！");
				echo json_encode($re);exit;
			}
		}
		$this->display();
	}
	
	//重置密码
	public function fd_reset()
	{
		if($_POST['password']){
			$where['phone']=$_SESSION['set_phone'];
			$user = M("User")->where($where)->limit(1)->select();
			if(!$user){
				$re = array("status"=>100,"msg"=>"账号不存在");
				echo json_encode($re);exit;
			}
			if($_POST['password']!=$_POST['repassword']){
				$re = array("status"=>100,"msg"=>"密码与确认密码不一致");
				echo json_encode($re);exit;
			}
			$old['password']=0;
			M("User")->where($where)->limit(1)->save($old);
			$data['password']=$_POST['password'];
			$is=M("User")->where($where)->limit(1)->save($data);
			if($is){
				unset($_SESSION['set_phone']);
				$back_url = U('Index/fd_success');
				$re = array("status"=>200,"msg"=>"重置密码成功","url"=>$back_url);
				echo json_encode($re);exit;
			}else{
				$re = array("status"=>100,"msg"=>"重置密码失败");
				echo json_encode($re);exit;
			}
		}
		$this->display();
	}
	
	//重置密码成功跳转页面
	public function fd_success()
	{
		$this->display();
	}
	
	//JAXA获取更多商品
	public function pro_temp()
	{
	
		$classify_id=$_POST['classify'];
		$sort_id=$_POST['sort_id'];
		$keyword=$_POST['keyword'];
		$page=$_POST['page'];
	
		$limit=10;
	
		if(!empty($classify_id)){
			$where['c_id']=$classify_id;
		}
	
		if(!empty($sort_id)){
			$where['s_id']=$sort_id;
		}
	
		if(!empty($keyword)){
			$where['title']=array("like","%{$keyword}%");
		}
	
		$goods=M("Products")->where($where)->page($page)->limit($limit)->select();
		//ajax输出
		//$str=$page;
		$str = '';
		if(!empty($goods)){
			foreach ($goods as $key=>$val){
				$val['smallpic']=M('ProImg')->where(['n_id'=>$val['id']])->limit(1)->getField('name');
				$str.='<li><div class="left_f"><a href="'.U('Index/pro_info',array('id'=>$val['id'])).'"><img src="./Public/upload/product/'.$val['smallpic'].'" alt="'.$val['title'].'"/></a></div>
      						<div class="right_f"><a href="'.U('Index/pro_info',array('id'=>$val['id'])).'"><p class="pro_title">'.$val['title'].'</p></a>
        					<p class="pro_old_price hui">市场价：'.$val['price'].'元</p>
        					<p class="pro_now_price reb">现价:￥'.$val['cost'].'元</p>
      						</div>
    			  	  </li>';
			}
		}else {
			$str=0;
		}
		echo $str;
	
	}
	
	//最新活动
	public function active()
	{
		$type_id=M("Column")->where("sign='activity'")->getfield("id");
		$page=$_GET['page']?$_GET['page']:1;
		$limit=999;
		$list=M("Content")->where("c_id='{$type_id}'")->page($page)->limit($limit)->order("date_time desc")->select();
		foreach ($list as $key=>$val){
			$list[$key]['title']=iconv_substr($val['title'],0,25,'utf-8');
			$list[$key]['postdate']=date("Y-m-d",$val['date_time']);
			if($val['tui']==1){
				$list[$key]['tuijian']="<em>推荐</em>";
			}else {
				$list[$key]['tuijian']="";
			}
			$list[$key]['smallpic']=M('File')->where(['n_id'=>$val['id'],'type'=>1])->limit(1)->getField('name');
		}
		$this->assign("list",$list);
		$this->display();
	}
	
	//其它活动
	public function sense()
	{
		$type_id=M("Column")->where("sign='sense'")->getfield("id");
		$page=$_GET['page']?$_GET['page']:1;
		$limit=999;
		$list=M("Content")->where("c_id='{$type_id}'")->page($page)->limit($limit)->order("date_time desc")->select();
		foreach ($list as $key=>$val){
			$list[$key]['title']=iconv_substr($val['title'],0,25,'utf-8');
			$list[$key]['postdate']=date("Y-m-d",$val['date_time']);
			if($val['tui']==1){
				$list[$key]['tuijian']="<em>推荐</em>";
			}else {
				$list[$key]['tuijian']="";
			}
			$list[$key]['smallpic']=M('File')->where(['n_id'=>$val['id'],'type'=>1])->limit(1)->getField('name');
		}
		$this->assign("list",$list);
		$this->display();
	}
	
	//商家详情
	public function store(){
		$this->display();
	}
	
	//活动详情
	public function active_info()
	{
		$news_id=$_GET['news_id'];
		$value=M("Content")->where("id='{$news_id}'")->select();
		$this->assign("title",$value[0]['title']);
		$this->assign("postdate",date("Y-m-d H:i",$value[0]['date_time']));
		$this->assign("hit",$value[0]['hit']);
		$this->assign("info",$value[0]['content']);
		//分享文章，去除html标签
		$summary = utf8_strcut($value[0]['content'],0,100);
		$summary = trim(strip_tags($summary));
		$summary = str_replace(array("\r\n", "\n\n", "\r\r", "\n", "\r"), '', $summary).'...';
		$this->assign("c_info",$summary);
		//增加浏览数
		$data['hit']=$value[0]['hit']+1;
		M("Content")->where("id='{$news_id}'")->save($data);
		$this->display();
	}
	
	//取得验证码
	public function getPhoneCode()
	{
		$phone=$_POST['phone'];
		//正则匹配
		$phone_yz='/^(13[0-9]|15[0|1|3|6|7|8|9]|18[0-9])\d{8}$/';
		$t=preg_match($phone_yz, $phone);
		$otime=M("UserVers")->where("phone='{$phone}'")->order("time desc")->getfield("time");
		$now = time();
		if($otime && $otime+60>$now){
			echo 3;   //重复操作
		}else {
			if ($t) {
				$num = rand(100000, 999999);
				$content = "您的注册验证码是:" . $num . "。请妥善保管您的验证码，该验证码在20分钟内有效。";
				$info = array("mobile" => $phone, "content" => $content);
				$url = "59.42.210.216/wollar/Sendsms.php";
				$this->postSMS($url, $info);
				session("phonecode", $num);
				$data_in = array();
				$data_in['time'] = $now;
				$data_in['phone'] = $phone;
				$data_in['vers'] = $num;
				M("UserVers")->add($data_in);
				echo 1;
			} else {
				echo 2;
			}
		}
	}
	
	//post请求
	private function postSMS($url,$parameter){
		$ch = curl_init() ;
		curl_setopt($ch,CURLOPT_URL,$url) ;
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);    //设置为字符串方式
		curl_setopt($ch, CURLOPT_HEADER, false);        //禁止头部信息
		curl_setopt($ch, CURLOPT_NOBODY,true);          //显示页面内容
		curl_setopt($ch, CURLOPT_POST, true);           //POST方式
		curl_setopt($ch, CURLOPT_POSTFIELDS, $parameter);    //POST数据
		//curl_setopt($ch, CURLOPT_REFERFER, $referer);       //伪装REFERER
		$result = curl_exec($ch) ;
		curl_close($ch) ;
		return $result;
	}
}