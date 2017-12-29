<?php
namespace Mobile\Controller;
use Think\Controller;
class CartController extends CommonController {
	//我的购物车
	public function cart()
	{	
		$user_id=session("user")['id'];
		
		$amount=0;
		$shoppingcar_list=M("Cars")->where("user_id='{$user_id}'")->order("id desc")->select();
		if(!empty($shoppingcar_list[0]['id'])){
			foreach ($shoppingcar_list as $key=>$val){
				$goods_info=M("Products")->where("id='{$val['goods_id']}'")->select();
				$shoppingcar_list[$key]['title']=$goods_info[0]['title'];
				$shoppingcar_list[$key]['price']=$goods_info[0]['price'];
				$shoppingcar_list[$key]['smallpic']=M('ProImg')->where(['n_id'=>$goods_info[0]['id']])->limit(1)->getField('name');
				//计算总价
				$amount+=$shoppingcar_list[$key]['price']*$shoppingcar_list[$key]['quantity'];
			}
			$this->assign("shoppingcar_list",$shoppingcar_list);
			$this->assign("amount",$amount);
			$this->assign("sign","block");
		}else {
			$this->assign("sign","none");
			$this->assign("tips","购物车空空的，赶紧去逛一逛吧！");
		}
				
		$this->display();
	}
	
	//加入购物车
	public function add_shoppingcar()
	{
		$user_id=session("user")['id'];
	
		$goods_id=$_POST['goods_id'];
		$quantity=$_POST['quantity'];
		$mid = M('Products')->where(['id'=>$goods_id])->getfield("uid");
		//获取用户购物车，如果是相同产品则数量相加
		$q=M("Cars")->where("user_id='{$user_id}' and goods_id='{$goods_id}'")->getfield("quantity");
		if(!empty($q)){
			$data['quantity']=$q+$quantity;
			$inde=M("Cars")->where("user_id='{$user_id}' and goods_id='{$goods_id}'")->save($data);
		}else {
			$data['mid']=$mid;
			$data['goods_id']=$goods_id;
			$data['quantity']=$quantity;
			$data['user_id']=$user_id;
			$inde=M("Cars")->add($data);
		}
		if($inde!==false){
			$this->redirect("Cart/cart");
		}
	}
	
	//删除购物车
	public function delete_shoppingcar()
	{
		$user_id=session("user")['id'];
	
		$shop_id=$_GET['shop_id'];
		$inde=M("Cars")->where("id='{$shop_id}'")->delete();
		$this->redirect("Cart/cart");	
	}
	
	//购买，或立即购买
	public function pro_buy()
	{
		$user_id=session("user")['id'];
		$goods_id=$_POST['goods_id'];
		$quantity=$_POST['quantity'];
		if(!empty($goods_id)){
			//立即购买
			$amount=0;
			$shoppingcar_list=M("Products")->where("id='{$goods_id}'")->select();
			$shoppingcar_list[0]['quantity']=$quantity;
			$shoppingcar_list[0]['smallpic']=M('ProImg')->where(['n_id'=>$shoppingcar_list[0]['id']])->limit(1)->getField('name');
			$amount=$shoppingcar_list[0]['price']*$quantity;
			$this->assign("shoppingcar_list",$shoppingcar_list);
			$this->assign("amount",$amount);
			$this->assign("express",$shoppingcar_list[0]['express']?$shoppingcar_list[0]['express']:0);
			$this->assign("allpay",$amount+$shoppingcar_list[0]['express']);
			//标志赋值
			$this->assign("inde",2);
			$this->assign("goods_id",$shoppingcar_list[0]['id']);
			$this->assign("quantity",$quantity);
		}else {
			//查询购物车购买
			$amount=0;
			$express=0;
			$shoppingcar_list=M("Cars")->where("user_id='{$user_id}'")->order("id desc")->select();
			foreach ($shoppingcar_list as $key=>$val){
				$goods_info=M("Products")->where("id='{$val['goods_id']}'")->select();
				$shoppingcar_list[$key]['goods_name']=$goods_info[0]['title'];
				$shoppingcar_list[$key]['price']=$goods_info[0]['price'];
				$shoppingcar_list[$key]['express']=$goods_info[0]['express'];
				$shoppingcar_list[$key]['smallpic']=M('ProImg')->where(['n_id'=>$goods_info[0]['id']])->limit(1)->getField('name');
				//计算总价
				$amount+=$shoppingcar_list[$key]['price']*$shoppingcar_list[$key]['quantity'];
				//运费
				$express+=$shoppingcar_list[$key]['express'];
			}
			$this->assign("shoppingcar_list",$shoppingcar_list);
			$this->assign("amount",$amount);
			$this->assign("express",$express?$express:0);
			$this->assign("allpay",$amount+$express);
			//标志赋值
			$this->assign("inde",1);
		}
	
		$info=M("User")->where("id='{$user_id}'")->select();
		$this->assign("info",$info[0]);
		$this->display();
	}
	
	//提交订单
	public function add_order()
	{
		$user_id=session("user")['id'];
	
		$inde=$_POST['inde'];
		//收件信息
		$data['user_id']=$user_id;
		$data['recipient']=$_POST['username'];
		$data['address']=$_POST['address'];
		$data['phone']=$_POST['phone'];
		if($inde==1){
			//通过购物车购买
			$amount=0;
			$express=0;
			$shoppingcar=M("Cars")->where("user_id='{$user_id}'")->select();
			foreach ($shoppingcar as $key=>$val){
				$goods_info=M("Products")->where("id='{$val['goods_id']}'")->select();
				$shoppingcar[$key]['price']=$goods_info[0]['price'];
				$shoppingcar[$key]['price_pro']=$goods_info[0]['cost'];
				//总金额（不含邮）
				$amount+=$shoppingcar[$key]['price']*$shoppingcar[$key]['quantity'];
				$express+=$goods_info[0]['express'];  //总邮资
			}
			//总金额（含邮）
			$data['amount']=$amount+$express;
			$data['express']=$express;
			//订单号与其他信息
			$data['order_num']="110".time().rand(1000, 9999);
			$data['state']=0;       //订单状态：0未支付，1已支付
			$data['postdate']=time();
			$order_id=M("Order")->add($data);    //提交至订单主表(个人)
			//删除多余元素,将购物车信息插入订单商品详情表
			foreach ($shoppingcar as $key=>$val){
				unset($shoppingcar[$key]['id']);
				unset($shoppingcar[$key]['user_id']);
				$shoppingcar[$key]['order_id']=$order_id;
			}
			M("OrderInfo")->addAll($shoppingcar);
				
			//成功添加订单，删除购物车
			M("Cars")->where("user_id='{$user_id}'")->delete();
	
			$this->redirect("User/order_state",array('order_id'=>$order_id));
		}else {
			//立即购买渠道
			$goods_id=$_POST['goods_id'];
			$quantity=$_POST['quantity'];
				
			$amount=0;
			$express=0;
			$goods_info=M("Products")->where("id='{$goods_id}'")->select();
			$amount=$goods_info[0]['price']*$quantity;   //不含邮
			$data['express']=$goods_info[0]['express'];
			$data['amount']=$amount+$data['express'];
			//订单号与其他信息
			$data['order_num']="110".time().rand(1000, 9999);
			$data['state']=0;       //订单状态：0未支付，1已支付
			$data['postdate']=time();
			$order_id=M("Order")->add($data);    //提交至订单主表
				
			//将商品添加至订单详情表
			$order_info['order_id']=$order_id;
			$order_info['goods_id']=$goods_id;
			$order_info['quantity']=$quantity;
			$order_info['price']=$goods_info[0]['price_now'];
			$order_info['price_pro']=$goods_info[0]['price'];
			M("OrderInfo")->add($order_info);
	
			$this->redirect("User/order_state",array('order_id'=>$order_id));
		}
	}
}