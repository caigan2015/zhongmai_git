<?php
namespace Admin\Controller;
use Think\Model;

use Admin\Controller\CommonController;
use Think\Controller;
class OrderController extends CommonController {
	//订单列表
	public function index(){
		$order=M('Order');
		if(I('get.dosubmit')){
			if($_POST['status'] != ''){
				$where['state']=$_POST['status'];
				$this->assign('status',$_POST['status']);
			}
		
			if($_POST['stime'] != ''){
				$where['postdate']=array('egt',strtotime($_POST['stime']));
				$this->assign('stime',$_POST['stime']);
			}
		
			if($_POST['etime'] != ''){
				$where['postdate']=array('elt',strtotime($_POST['etime']));
				$this->assign('etime',$_POST['etime']);
			}
		
			if($_POST['number'] != ''){
				$where['order_num']=array('like',"%{$_POST['number']}%");
				$this->assign('number',$_POST['number']);
			}
		}
		$where['state']=array('egt',0);
		$count  = $order ->where($where) -> count();
		$list = $order ->where($where) ->order('postdate desc')->select();
		foreach ($list as $k=>$v){		
			switch ($v['payment']){
				case 1:$list[$k]['payment']='支付宝';break;
				case 2:$list[$k]['payment']='微信';break;
				case 3:$list[$k]['payment']='银联';break;
			}
			switch ($v['state']){
				case 0:$list[$k]['status']='未支付';break;
				case 1:$list[$k]['status']='已支付';break;
				case 2:$list[$k]['status']='已发货';break;
				case 3:$list[$k]['status']='已完成';break;
				case 4:$list[$k]['status']='退款中';break;
				case 5:$list[$k]['status']='已退款';break;
				case 6:$list[$k]['status']='退货中';break;
				case 7:$list[$k]['status']='已退货';break;
				case 8:$list[$k]['status']='已取消';break;
			}
			if($v['mid']=='0'){
				$list[$k]['m']='平台';
			}
		}
		$this->assign('num',$count);
		$this->assign('list',$list);
		
		//统计
		$allprice = $order -> where($where) -> sum('amount');
		$where['state']=array('between','1','7');
		$paynum = $order -> where($where) -> count();
		$this->assign('allprice',$allprice?number_format($allprice,2):'0.00');
		$this->assign('paynum',$paynum);
		
		$this->display();
	}
	
	//订单修改
	public function audit(){
		$orderM = M('Order');
		$progress=M('OrderProgress');
		$where['id']=$_POST['id'];
		$data['state']=$_POST['status'];
		$is = $orderM->where($where)->save($data);
		$text = $this->getOrderState($_POST['status']);
		if($is){
			$datax['order_id']=$_POST['id'];
			$datax['status']=$_POST['status'];
			$datax['info']=$text;
			$datax['time']=time();
			$datax['title']=$text;
			$progress->add($datax);
		}
		echo "yes";
	}
	
	//订单彻底删除
	public function delete(){
		$orderM = M('Order');
		$where['id']=$_POST['id'];
		if($orderM->where($where)->delete()){
			$re = array("error"=>0,"msg"=>"删除成功");
		}else{
			$re = array("error"=>1,"msg"=>"删除失败");
		}
		echo json_encode($re);
		exit;
	}
	
	//订单查看
	public function saw(){
		$orderM = M('Order');
		$order_id=$_GET['id'];
		//订单概况
		$value=$orderM->where("id='{$order_id}'")->select();
		$value[0]['postdate']=date("Y-m-d H:i:s",$value[0]['postdate']);
		$state = $value[0]['state'];
		switch ($value[0]['state']){
			case -1:$value[0]['state']="已关闭";$value[0]['color']='';
				break;
			case 0:$value[0]['state']="未支付";$value[0]['color']='color:#F1AA3C;';
				break;
			case 1:$value[0]['state']="已支付";$value[0]['color']='color:#5FB75F;';
				break;
			case 2:$value[0]['state']="物流配送中";$value[0]['color']='color:#F14B51;';
				break;
			case 3:$value[0]['state']="已签收";$value[0]['color']='color:#F14B51;';
				break;
			case 4:$value[0]['state']="退款中";$value[0]['color']='color:#F1AA3C;';
				break;
			case 5:$value[0]['state']="已退款";$value[0]['color']='color:#F14B51;';
				break;
			case 6:$value[0]['state']="退货中";$value[0]['color']='color:#F1AA3C;';
				break;
			case 7:$value[0]['state']="已退货";$value[0]['color']='color:#F14B51;';
				break;
			case 8:$value[0]['state']="已取消";$value[0]['color']='color:red;';
				break;
			//default:$list[$key]['state']="交易关闭";$list[$key]['color']='color:#F14B51;';
		}
		if($value[0]['use_coupon']==-1){
			$value[0]['use_coupon']="否";
		}else {
			$value[0]['use_coupon']="是";
		}
		$this->assign("state",$state);
		$this->assign("order_id",$value[0]['id']);
		$this->assign("order",$value);
		//订单详情
		$info=M('OrderInfo')->where("order_id='{$order_id}'")->select();
		foreach ($info as $key=>$val){
			$goods=M('Products')->where("id='{$val['goods_id']}'")->select();
			$goods[0]['smallpic']=M('ProImg')->where(['n_id'=>$goods[0]['id']])->limit(1)->getField('name');
			$info[$key]['goods_name']=$goods[0]['title'];
			$info[$key]['smallpic']=$goods[0]['smallpic'];
			$info[$key]['specs']=$goods[0]['specs'];			
		}
		$this->assign("order_info",$info);
		$this->display();
	}
	
	//订单编辑
	public function edit(){
		$orderM = M('Order');
		$order_id=$_GET['id'];
		//订单概况
		$value=$orderM->where("id='{$order_id}'")->select();
		$value[0]['postdate']=date("Y-m-d H:i:s",$value[0]['postdate']);
		$state = $value[0]['state'];
		switch ($value[0]['state']){
			case -1:$value[0]['state']="已关闭";$value[0]['color']='';
			break;
			case 0:$value[0]['state']="未支付";$value[0]['color']='color:#F1AA3C;';
			break;
			case 1:$value[0]['state']="已支付";$value[0]['color']='color:#5FB75F;';
			break;
			case 2:$value[0]['state']="物流配送中";$value[0]['color']='color:#F14B51;';
			break;
			case 3:$value[0]['state']="已签收";$value[0]['color']='color:#F14B51;';
			break;
			case 4:$value[0]['state']="退款中";$value[0]['color']='color:#F1AA3C;';
			break;
			case 5:$value[0]['state']="已退款";$value[0]['color']='color:#F14B51;';
			break;
			case 6:$value[0]['state']="退货中";$value[0]['color']='color:#F1AA3C;';
			break;
			case 7:$value[0]['state']="已退货";$value[0]['color']='color:#F14B51;';
			break;
			case 8:$value[0]['state']="已取消";$value[0]['color']='color:red;';
			break;
			//default:$list[$key]['state']="交易关闭";$list[$key]['color']='color:#F14B51;';
		}
		if($value[0]['use_coupon']==-1){
			$value[0]['use_coupon']="否";
		}else {
			$value[0]['use_coupon']="是";
		}
		$this->assign("state",$state);
		$this->assign("order_id",$value[0]['id']);
		$this->assign("order",$value);
		//订单详情
		$info=M('OrderInfo')->where("order_id='{$order_id}'")->select();
		foreach ($info as $key=>$val){
			$goods=M('Products')->where("id='{$val['goods_id']}'")->select();
			$goods[0]['smallpic']=M('ProImg')->where(['n_id'=>$goods[0]['id']])->limit(1)->getField('name');
			$info[$key]['goods_name']=$goods[0]['title'];
			$info[$key]['smallpic']=$goods[0]['smallpic'];
			$info[$key]['specs']=$goods[0]['specs'];
		}
		$this->assign("order_info",$info);
		$this->display();
	}
	
	//订单处理
	public function check_order()
	{
		if(I('get.dosubmit')){
			if(empty($_POST['state'])){
				$re = array('error'=>1,'msg'=>'对不起，请选择处理状态！');
				echo json_encode($re);
				exit;
			}
		
			$where['id']=$_POST['order_id'];
			$id=M("Order")->where($where)->getfield("id");
			if($id>0){
				switch($_POST['state']){
					case 1:$find['state']=array('lt',1);$date['state']=$_POST['state'];break;
					case 2:$find['state']=array('lt',2);$date['state']=$_POST['state'];break;
					case 3:$find['state']=array('lt',3);$date['state']=$_POST['state'];break;
					case 5:$find['state']=array('lt',5);$date['state']=$_POST['state'];break;
					case 7:$find['state']=array('lt',7);$date['state']=$_POST['state'];break;
					case 8:$find['state']=array('lt',8);$date['state']=$_POST['state'];break;
				}
				$find['id']=$id;
				$is = M("Order")->where($find)->save($date);
				if($is){			
					$re = array('error'=>0,'msg'=>'处理订单成功！');
				}else{
					$re = array('error'=>1,'msg'=>'处理订单失败！');
				}
				echo json_encode($re);
				exit;
			}else{
				$re = array('error'=>1,'msg'=>'对不起，暂无该处理订单！');
				echo json_encode($re);
				exit;
			}
		}
	}
	
	//获取订单状态配置
	private function getOrderState($state){
		switch ($state){
			case 0:$text = '未支付';break;
			case 1:$text = '已支付';break;
			case 2:$text = '已发货';break;
			case 3:$text = '已完成';break;
			case 4:$text = '退款中';break;
			case 5:$text = '已退款';break;
			case 6:$text = '退货中';break;
			case 7:$text = '已退货';break;
			case 8:$text = '已取消';break;
		}
	
		return $text;
	}

	//申请列表
	public function apply(){
		$applyDB = M('Apply');
		if(I('get.dosubmit')){
			if($_POST['status'] != ''){
				$where['status']=$_POST['status'];
				$this->assign('status',$_POST['status']);
			}

			if($_POST['stime'] != ''){
				$where['ctime']=array('egt',strtotime($_POST['stime']));
				$this->assign('stime',$_POST['stime']);
			}

			if($_POST['etime'] != ''){
				$where['ctime']=array('elt',strtotime($_POST['etime']));
				$this->assign('etime',$_POST['etime']);
			}

			if($_POST['number'] != ''){
				$where['phone']=array('like',"%{$_POST['number']}%");
				$this->assign('number',$_POST['number']);
			}
		}else{
			$where['status']=array('egt',0);
		}
		$count  = $applyDB ->where($where) -> count();
		$list = $applyDB->where($where)->order('ctime desc')->select();
		foreach($list as $ka=>$va){
			$p = M('Products')->where(['id'=>$va['pid']])->find();
			$list[$ka]['title']=$p['title'];
			$list[$ka]['price']=intval($p['price']);
			switch($va['status']){
				case 0:$list[$ka]['state']='<span style="color: #f60;">审核中</span>';break;
				case 1:$list[$ka]['state']='<span style="color: green;">申请成功</span>';break;
				case 2:$list[$ka]['state']='已取消';break;
			}
			$list[$ka]['store']=M('Stores')->where(['id'=>$va['sid']])->getField('title');
			$list[$ka]['img']=M('ProImg')->where(['n_id'=>$va['pid']])->order('f_id asc')->getField('name');
			$list[$ka]['ctime']=date('Y-m-d H:i:s',$va['ctime']);
		}
		$this->assign('num',$count);
		$this->assign('list',$list);
		$this->display();
	}

	//申请审核
	public function audit_a(){
		$applyDB = M('Apply');
		$where['id']=$_POST['id'];
		$data['status']=$_POST['status'];
		$data['otime']=time();
		$applyDB->where($where)->save($data);
		echo "yes";
	}

	//查看申请
	public function saw_a(){
		$applyDB = M('Apply');
		$info = $applyDB->where(['id'=>$_GET['id']])->find();
		$p = M('Products')->where(['id'=>$info['pid']])->find();
		$info['store']=M('Stores')->where(['id'=>$info['sid']])->getField('title');
		switch($info['status']){
			case 0:$info['state']='<span style="color: #f60;">审核中</span>';break;
			case 1:$info['state']='<span style="color: green;">申请成功</span>';break;
			case 2:$info['state']='已取消';break;
		}
		$aid = M('Stores')->where(['id'=>$info['sid']])->getField('aid');
		$info['city']=M('Area')->where(['id'=>$aid])->getField('name');
		$info['etime'] = date('Y-m-d',$info['etime']);
		$info['otime'] = $info['otime']?date('Y-m-d H:i:s',$info['otime']):'无';
		$p['price']=intval($p['price']);
		$p['img']=M('ProImg')->where(['n_id'=>$p['id']])->order('f_id asc')->getField('name');
		$this->assign('pro',$p);
		$this->assign('info',$info);
		$this->display();
	}

}