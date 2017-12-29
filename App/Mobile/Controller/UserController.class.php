<?php
namespace Mobile\Controller;
use Mobile\Common\Basic;

use Org\Net\UploadFile;

use Think\Image;

use Think\Model;

use Mobile\Controller\CommonController;

class UserController extends CommonController {
	//用户登录
	public function login()
	{
		if(session("user")){
			$this->redirect('User/my');exit;
		}
		
		$inde=$_POST['inde']?$_POST['inde']:2;
		if($inde==1){
			$phone=$_POST['phone'];
			$password=$_POST['password'];
			$info=M("User")->where("phone='{$phone}'")->find();
			if(empty($info)){
				$re = array('code'=>'001','msg'=>'账号不存在！');
				echo json_encode($re);
				exit;
			}
			if(md5($password)==$info['password']){
				session("user", $info);
				if(isset($_SESSION['back_url'])){
					$back_url = session("back_url");
					$re = array('code'=>'000','msg'=>'登录成功','url'=>$back_url);
				}else{
					$re = array('code'=>'000','msg'=>'登录成功','url'=>U('User/my'));
				}
				echo json_encode($re);
				exit;
			}else {
				$re = array('code'=>'001','msg'=>'密码错误！');
				echo json_encode($re);
				exit;
			}
		}
		$this->display();
	}
	
	//退出登录
	public function logout()
	{
		unset($_SESSION['user']);
		unset($_SESSION['openid']);
		$this->redirect("Index/index");
	}
	
	//注册与登录
	public function reg()
	{
		if(session("user")){
			$this->redirect('User/my');exit;
		}
		
		$inde=$_POST['inde']?$_POST['inde']:2;
		if($inde==1){
			$data['phone']=$_POST['phone'];
			$data['username']=$_POST['phone'];
			$phonecode=$_POST['phonecode'];
			$yz_code=session("phonecode");
			$pass = I('post.password','tirm','');
			$data['password']=md5($pass);
			$data['postdate']=time();
			if($yz_code==$phonecode){
				$id=M("User")->where("phone='{$data['phone']}'")->getfield("id");
				if(empty($id)){
					$id=M("User")->add($data);
					if($id){
						$sign = M("User")->where("id='{$id}'")->find();
						session("user", $sign);
						$re = array('code'=>'000','msg'=>'注册成功！');
					}else{
						$re = array('code'=>'001','msg'=>'注册失败！');
					}
					echo json_encode($re);
					exit;
				}else {
					$re = array('code'=>'001','msg'=>'该账户已注册！');
					echo json_encode($re);
					exit;
				}
			}else {
				$re = array('code'=>'001','msg'=>'验证码输入错误，请重新输入！');
				echo json_encode($re);
				exit;
			}
		}
		$this->display();
	}
	
	//个人中心
	public function my()
	{
		$user_id=session("user")['id'];		
	
		//我的电话
		$phone=M("User")->where("id='{$user_id}'")->getfield("phone");
		$username=M("User")->where("id='{$user_id}'")->getfield("username");
		//收藏数
		$collect_num=M("Mycollect")->where("user_id='{$user_id}'")->count();
		$orders_num=M("Order")->where("user_id='{$user_id}'")->count();
		$this->assign("phone",$phone);     //电话除了用于显示用户外还用于自住服务的地址
		$this->assign("username",$username);
		$this->assign("collect_num",$collect_num?$collect_num:0);
		$this->assign("orders_num",$orders_num?$orders_num:0);
	
		//大屏推荐
		$goods_b=M("Products")->where(['istui'=>1])->limit(1)->select();
		foreach ($goods_b as $k1=>$v1){
			$goods_b[$k1]['pic']=M('ProImg')->where(['n_id'=>$v1['id']])->limit(1)->getField('name');
		}
		$this->assign("goods_b",$goods_b);
		
		$this->display();
	}
	
	//我的订单
	public function order()
	{
		$user_id=session("user")['id'];

		$page=$_GET['page']?$_GET['page']:1;
		$this->assign("page",$page);
		$limit=10;
		$order_list=M("Order")->where("user_id='{$user_id}' and state!='-1'")->page($page)->limit($limit)->order("postdate desc")->select();
		foreach ($order_list as $key=>$val){
			$goods_list=M("OrderInfo")->where("order_id='{$val['id']}'")->limit(1)->select();
			$goods=M("Products")->where("id='{$goods_list[0]['goods_id']}'")->select();
			$order_list[$key]['goods_name']=$goods[0]['title'];
			$order_list[$key]['price_now']=$goods[0]['price'];
			$order_list[$key]['smallpic']=M('ProImg')->where(['n_id'=>$goods[0]['id']])->limit(1)->getField('name');
			//删除多余元素
			unset($order_list[$key]['user_id']);
			unset($order_list[$key]['express']);
			unset($order_list[$key]['recipient']);
			unset($order_list[$key]['address']);
			unset($order_list[$key]['phone']);
			unset($order_list[$key]['postdate']);
			//判断订单状态
			switch($order_list[$key]['state']){
				case 0:$order_list[$key]['h_state']="<font color='red'>未付款</font>";break;
				case 1:$order_list[$key]['h_state']="<font color='orange'>已付款</font>";break;
				case 2:$order_list[$key]['h_state']="<font color='orange'>物流配送中</font>";break;
				case 3:$order_list[$key]['h_state']="<font color='green'>已完成</font>";break;
				case 4:$order_list[$key]['h_state']="<font color='#ffd700'>退款中</font>";break;
				case 5:$order_list[$key]['h_state']="<font color='#daa520'>已退款</font>";break;
				case 6:$order_list[$key]['h_state']="<font color='#8a2be2'>退货中</font>";break;
				case 7:$order_list[$key]['h_state']="<font color='#daa520'>已退货</font>";break;
				case 8:$order_list[$key]['h_state']="<font color='red'>已取消</font>";break;
			}
			
		}
		$this->assign("order_list",$order_list);
		$count = M("Order")->where("user_id='{$user_id}'")->count();
		$allpage=ceil($count/$limit);
		if(empty($order_list)||$allpage<=1){
			$this->assign("tips","查无数据！");
			$this->assign("block","display:block;");
		}
		$this->display();
	}
	
	//Ajax获取我的订单
	public function order_temp()
	{
		$user_id=session("user")['id'];
	
		$page=$_POST['page'];
		$limit=3;
		$order_list=M("Order")->where("user_id='{$user_id}'")->page($page)->limit($limit)->order("postdate desc")->select();
		foreach ($order_list as $key=>$val){
			$goods_list=M("OrderInfo")->where("order_id='{$val['id']}'")->limit(1)->select();
			$goods=M("Products")->where("id='{$goods_list[0]['goods_id']}'")->select();
			$order_list[$key]['goods_name']=$goods[0]['title'];
			$order_list[$key]['price_now']=$goods[0]['price'];
			$order_list[$key]['smallpic']=M('ProImg')->where(['n_id'=>$goods[0]['id']])->limit(1)->getField('name');
	
			//删除多余元素
			unset($order_list[$key]['user_id']);
			unset($order_list[$key]['express']);
			unset($order_list[$key]['recipient']);
			unset($order_list[$key]['address']);
			unset($order_list[$key]['phone']);
			unset($order_list[$key]['postdate']);
			//判断订单状态
			switch($order_list[$key]['state']){
				case 0:$order_list[$key]['h_state']="<font color='red'>未付款</font>";break;
				case 1:$order_list[$key]['h_state']="<font color='orange'>已付款</font>";break;
				case 2:$order_list[$key]['h_state']="<font color='orange'>物流配送中</font>";break;
				case 3:$order_list[$key]['h_state']="<font color='green'>已完成</font>";break;
				case 4:$order_list[$key]['h_state']="<font color='#ffd700'>退款中</font>";break;
				case 5:$order_list[$key]['h_state']="<font color='#daa520'>已退款</font>";break;
				case 6:$order_list[$key]['h_state']="<font color='#8a2be2'>退货中</font>";break;
				case 7:$order_list[$key]['h_state']="<font color='#daa520'>已退货</font>";break;
				case 8:$order_list[$key]['h_state']="<font color='red'>已取消</font>";break;
			}
			
		}
		$str = '';
		if(!empty($order_list)){
			foreach ($order_list as $k=>$v){
				$str.='<ul class="order_ul '.$v['h_end'].'">
						<li>
						<p class="order_key hui">订单号：</p>
							<a href="'.U('User/order_state',array('order_id'=>$v['id'])).'"><p class="order_con">'.$v['order_num'].'</p></a></li>
						<li class="order_pro_li"><a href="'.U('User/order_state',array('order_id'=>$v['id'])).'" class="block_a"></a><div class="left_f"><img src="__PUBLIC__/upload/product/'.$v['smallpic'].'"/></div><div class="right_f"><p class="pro_title">'.$v['goods_name'].'</p><p class="pro_price">￥'.$v['price_now'].'</p></div>
						<div class="order_state"></div>
						</li>
						<li><p class="order_play_price">实付款：￥'.$v['amount'].'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$v['h_state'].'</p><p class="order_del"><a href="'.U('User/delete_order',array('order_id'=>$v['id'])).'" class="order_del_icon">删除订单</a></p></li></ul>';
			}
		}else {
			$str=0;
		}
		echo $str;
	
	}
	
	//订单详情
	public function order_state()
	{
		$user_id=session("user")['id'];
	
		$order_id=$_GET['order_id'];
		$order=M("Order")->where("id='{$order_id}'")->select();
		$order_info=M("OrderInfo")->where("order_id='{$order_id}'")->select();
		foreach ($order_info as $key=>$val){
			$goods=M("Products")->where("id='{$val['goods_id']}'")->find();
			$order_info[$key]['goods_name']=$goods['title'];
			$order_info[$key]['smallpic']=M('ProImg')->where(['n_id'=>$goods['id']])->limit(1)->getField('name');
		}
		//订单信息
		$this->assign("order_num",$order[0]['order_num']);
		$this->assign("order_amount",$order[0]['amount']);
		$this->assign("goods_amount",$order[0]['amount']-$order[0]['express']);
		$this->assign("order_postdate",date("Y-m-d H:i:s",$order[0]['postdate']));
		$this->assign("recipient",$order[0]['recipient']);
		$this->assign("phone",$order[0]['phone']);
		$this->assign("address",$order[0]['address']);
		$this->assign("order_express",$order[0]['express']);
		$openid=session("openid");
		switch($order[0]['state']){
			case 0:
				if($openid){
					$form='<form id="getForm" action="'.U('Wxpay/js_api_call',array('order_sn'=>$order[0]['order_num'])).'" method="post">
  					<aside class="shopping_cart_aside footer_aside">
  					<input type="hidden" name="amount"  value="'.$order[0]['amount'].'"/>
  					<input type="hidden" name="order_num"  value="'.$order[0]['order_num'].'"/>
    				<ul class="buy_function_ul">
        			<li class="buy_function_con"><div class="buy_function_text">实付款：<span class="big_font">￥'.$order[0]['amount'].'</span></div></li>
        			<li class="buy_function_submit"><input type="submit" class="shopping_input_submit" value="马上付款"></li>
    				</ul></aside></form>';
				}else{
					$form='<form id="getForm" action="'.U('Pay/doalipay').'" method="post">
  					<aside class="shopping_cart_aside footer_aside">
  					<input type="hidden" name="ordtotal_fee"  value="'.$order[0]['amount'].'"/>
  					<input type="hidden" name="trade_no"  value="'.$order[0]['order_num'].'"/>
  					<input type="hidden" name="ordsubject"  value="商品支付"/>
  					<input type="hidden" name="ordshow_url"  value="'.U('CShop/order_state',array('order_id'=>$order_id)).'"/>
    				<ul class="buy_function_ul">
        			<li class="buy_function_con"><div class="buy_function_text">实付款：<span class="big_font">￥'.$order[0]['amount'].'</span></div></li>
        			<li class="buy_function_submit"><input type="submit" class="shopping_input_submit" value="马上付款"></li>
    				</ul></aside></form>';
				}
				$this->assign("form",$form);
				break;
			case 1:
				$this->assign("paydate",'<span class="small_font hui">支付时间：'.date("Y-m-d H:i:s",$order[0]['paydate']).'</span>');
				$form='
  						<aside class="shopping_cart_aside footer_aside">
						<input type="hidden" name="proid" value="1">
						<ul class="shopping_cart_function_ul">
							<li class="shopping_add_cart" style="width: 50%"><input class="shopping_cart_add check_order" type="button" data-val="2" value="确认收货"></li>
							<li class="shopping_buy" style="width: 50%"><input class="shopping_input_submit check_order" type="button" data-val="3" value="申请退款"></li>
						</ul>
						</aside>';
				$this->assign("form",$form);
				break;
			case 2:
				$this->assign("end","end");
				$form='
  					<aside class="shopping_cart_aside footer_aside">
    				<ul class="buy_function_ul">
        			<li class="buy_function_con"><div class="buy_function_text">实付款：<span class="big_font">￥'.$order[0]['amount'].'</span></div></li>
        			<li class="buy_function_submit"><input type="button" class="shopping_input_submit check_order" data-val="5" value="申请退货"></li>
    				</ul></aside>';
				$this->assign("form",$form);
				break;
			case 3:
				$form='
  					<aside class="shopping_cart_aside footer_aside">
    				<ul class="buy_function_ul">
        			<li class="buy_function_con"><div class="buy_function_text">实付款：<span class="big_font">￥'.$order[0]['amount'].'</span></div></li>
        			<li class="buy_function_submit"><input type="submit" class="shopping_input_submit" value="已完成" style="background:#eee;color:#999;"></li>
    				</ul></aside>';
				$this->assign("form",$form);
				break;
			case 4:
				$form='
  					<aside class="shopping_cart_aside footer_aside">
    				<ul class="buy_function_ul">
        			<li class="buy_function_con"><div class="buy_function_text">实付款：<span class="big_font">￥'.$order[0]['amount'].'</span></div></li>
        			<li class="buy_function_submit"><input type="submit" class="shopping_input_submit" value="退款中" style="background:#eee;color:#999;"></li>
    				</ul></aside>';
				$this->assign("form",$form);
				break;
			case 5:
				$form='
  					<aside class="shopping_cart_aside footer_aside">
    				<ul class="buy_function_ul">
        			<li class="buy_function_con"><div class="buy_function_text">实付款：<span class="big_font">￥'.$order[0]['amount'].'</span></div></li>
        			<li class="buy_function_submit"><input type="submit" class="shopping_input_submit" value="已退款" style="background:#fff;color:red;"></li>
    				</ul></aside>';
				$this->assign("form",$form);
				break;
			case 6:
				$form='
  					<aside class="shopping_cart_aside footer_aside">
    				<ul class="buy_function_ul">
        			<li class="buy_function_con"><div class="buy_function_text">实付款：<span class="big_font">￥'.$order[0]['amount'].'</span></div></li>
        			<li class="buy_function_submit"><input type="submit" class="shopping_input_submit" value="退货中" style="background:#eee;color:#999;"></li>
    				</ul></aside>';
				$this->assign("form",$form);
				break;
			case 7:
				$form='
  					<aside class="shopping_cart_aside footer_aside">
    				<ul class="buy_function_ul">
        			<li class="buy_function_con"><div class="buy_function_text">实付款：<span class="big_font">￥'.$order[0]['amount'].'</span></div></li>
        			<li class="buy_function_submit"><input type="submit" class="shopping_input_submit" value="已退货" style="background:#eee;color:#999;"></li>
    				</ul></aside>';
				$this->assign("form",$form);
				break;
			case 8:
				$form='
  					<aside class="shopping_cart_aside footer_aside">
    				<ul class="buy_function_ul">
        			<li class="buy_function_con"><div class="buy_function_text">实付款：<span class="big_font">￥'.$order[0]['amount'].'</span></div></li>
        			<li class="buy_function_submit"><input type="button" class="shopping_input_submit check_order" data-val="4" value="关闭订单" style="background:#fff;color:#999;"></li>
    				</ul></aside>';
				$this->assign("form",$form);
				break;
		}
		
		$this->assign('order_id',$order_id);
		//商品信息
		$this->assign("order_info",$order_info);
		$this->display();
	}
	
	//处理订单状态
	public function setting_order()
	{
		$user_id=session("user")['id'];
		if(empty($user_id)){
			$url = U("User/login");
			$re = array("status"=>200,"msg"=>"请先登录","url"=>$url);
			echo json_encode($re);exit;
		}
		switch($_GET['type'])
		{
			case 1:$where['state']=0;$data['state']=2;$tip="取消订单";break;
			case 2:$where['state']=1;$data['state']=3;$tip="确认收货";break;
			case 3:$where['state']=1;$data['state']=4;$tip="申请退款";break;
			case 4:$where['state']=8;$data['state']=-1;$tip="关闭订单";break;
			case 5:$where['state']=3;$data['state']=6;$tip="申请退款";break;
		}
		$where['user_id']=$user_id;
		$where['id']=$_GET['order_id'];
		$order = M("Order")->where($where)->limit(1)->select();
		if($order){
			$is = M("Order")->where($where)->limit(1)->save($data);
			if($is){
				$url = U("User/order");
				$re = array("status"=>200,"msg"=>"{$tip}成功","url"=>$url);
				echo json_encode($re);exit;
			}else{
				$re = array("status"=>100,"msg"=>"{$tip}失败");
				echo json_encode($re);exit;
			}
		}else{
			$re = array("status"=>100,"msg"=>"您暂无该订单！");
			echo json_encode($re);exit;
		}
	}
	
	//支付成功返回页面
	public function pay_back()
	{
		$this->assign("error",$_GET['error']);
		$this->display();
	}
	
	//温馨提示页面
	public function pay_tip()
	{
		$this->display();
	}
	
	//修改个人信息
	public function personal_detail()
	{
		$user_id=session("user")['id'];
		
		if($_POST['username']){
			$where['id']=$_POST['id'];
			$data['username']=$_POST['username'];
			$data['email']=$_POST['email'];
			$is = M("User")->where($where)->save($data);
			if(!$is){
				$re = array("status"=>100,"msg"=>"无任何修改");
			}else{
				$re = array("status"=>200,"msg"=>"修改成功");
			}
			echo json_encode($re);exit;
		}
		$info=M("User")->where("id='{$user_id}'")->select();
		$this->assign("info",$info[0]);
		$this->display();
	}
	
	//我的优惠卷
	public function my_coupon()
	{
		$this->display();
	}
	
	
	//收藏商品
	public function my_collection()
	{
		$user_id=session("user")['id'];
	
		$goods_id=$_GET['goods_id'];
		$id=M("Mycollect")->where("user_id='{$user_id}' and goods_id='{$goods_id}'")->getfield("id");
		if(empty($id)){
			$data['user_id']=$user_id;
			$data['goods_id']=$goods_id;
			$inde=M("Mycollect")->add($data);
			echo "1";
		}else {
			$inde=M("Mycollect")->where("user_id='{$user_id}' and goods_id='{$goods_id}'")->delete();
			echo "2";
		}
	}
	
	//我的收藏
	public function my_collection_goods()
	{
		$user_id=session("user")['id'];
		
		$list=M("Mycollect")->where("user_id='{$user_id}'")->select();
		
		$goods=array();
		foreach ($list as $key=>$val){
			$temp=M("Products")->where("id='{$val['goods_id']}'")->find();
			$temp['pic'] = M('ProImg')->where(['n_id'=>$temp['id']])->limit(1)->getField('name');
			$goods[]=$temp;
		}
		if(empty($goods)){
			$this->assign("tips",'暂无收藏信息');
		}
		$this->assign("goods",$goods);
		$this->display();
	}
}