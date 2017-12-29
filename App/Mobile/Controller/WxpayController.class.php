<?php
namespace Mobile\Controller;

use Think\Model;

use Think\Controller;

class WxpayController extends Controller{
	//获取access_token过程中的跳转url，通过跳转将code传入jsapi支付页面
	public function js_api_call() {
		$order_sn = $_GET['order_sn'];
		if (empty($order_sn)) {
			$this->redirect("Mobile/Index/index");
		}
		vendor('Weixinpay.WxPayPubHelper');
		//使用jsapi接口
		$jsApi = new \JsApi_pub();
		//=========步骤1：网页授权获取用户openid============
		//通过code获得openid
		if (!isset($_GET['code'])){
			//触发微信返回code码
			$url = $jsApi->createOauthUrlForCode('http://'.SITE_URL.'/index.php/Api/Wxpay/js_api_call?order_sn='.$order_sn);
			Header("Location: $url");
		}else{
			//获取code码，以获取openid
			$code = $_GET['code'];
			$jsApi->setCode($code);
			$openid = $jsApi->getOpenId();
		}
		$order_info = M('Order');
		$amount=$order_info->where(["oid"=>$_GET['order_sn']])->getfield("total");
		$res = array(
				'order_sn' => $_GET['order_sn'],
				'order_amount' => $amount
		);
		//=========步骤2：使用统一支付接口，获取prepay_id============
		//使用统一支付接口
		$unifiedOrder = new \UnifiedOrder_pub();
		//设置统一支付接口参数
		//设置必填参数
	
		$total_fee = $res['order_amount']*100;
		//$total_fee = 1;
		$body = "订单支付{$res['order_sn']}";
		$unifiedOrder->setParameter("openid", "$openid");//用户标识
		$unifiedOrder->setParameter("body", $body);//商品描述
		//自定义订单号，此处仅作举例
		$out_trade_no = $res['order_sn'];
		$unifiedOrder->setParameter("out_trade_no", $out_trade_no);//商户订单号
		$unifiedOrder->setParameter("total_fee", $total_fee);//总金额
	
		$unifiedOrder->setParameter("notify_url", \WxPayConf_pub::NOTIFY_URL);//通知地址
		$unifiedOrder->setParameter("trade_type", "JSAPI");//交易类型
	
		$prepay_id = $unifiedOrder->getPrepayId();
		//=========步骤3：使用jsapi调起支付============
		$jsApi->setPrepayId($prepay_id);
		$jsApiParameters = $jsApi->getParameters();
		$wxconf = json_decode($jsApiParameters, true);
		if ($wxconf['package'] == 'prepay_id=') {
			$this->error('当前订单存在异常，不能使用支付');
		}
		$this->assign('res', $res);
		$this->assign('jsApiParameters', $jsApiParameters);
		$this->display('jsapi');
	}
	//扫描支付
	public function code_pay()
	{
		vendor('Weixinpay.WxPayPubHelper');
		//使用统一支付接口
		$unifiedOrder = new \UnifiedOrder_pub();
		//设置统一支付接口参数
		$order_info = M('OrderInfo','shop_');
		$amount=$order_info->where(["oid"=>$_GET['order_sn']])->getfield("total");
		$res = array(
				'order_sn' => $_GET['order_sn'],
				'order_amount' => $amount
		);
		
		$total_fee = $res['order_amount']*100;
		//$total_fee = 1;
		$body = "订单支付{$res['order_sn']}";
		$unifiedOrder->setParameter("body", $body);//商品描述
		//自定义订单号，此处仅作举例
		$out_trade_no = $res['order_sn'];
		$unifiedOrder->setParameter("out_trade_no", $out_trade_no);//商户订单号
		$unifiedOrder->setParameter("total_fee", $total_fee);//总金额
	
		$unifiedOrder->setParameter("notify_url", \WxPayConf_pub::NOTIFY_URL);//通知地址
		$unifiedOrder->setParameter("trade_type","NATIVE");//交易类型
		//获取统一支付接口结果
		$unifiedOrderResult = $unifiedOrder->getResult();
		//         var_dump($unifiedOrder);
		//商户根据实际情况设置相应的处理流程
		if ($unifiedOrderResult["return_code"] == "FAIL")
		{
			//商户自行增加处理流程
			echo "通信出错：".$unifiedOrderResult['return_msg']."<br>";
		}
		elseif($unifiedOrderResult["result_code"] == "FAIL")
		{
			//商户自行增加处理流程
			echo "错误代码：".$unifiedOrderResult['err_code']."<br>";
			echo "错误代码描述：".$unifiedOrderResult['err_code_des']."<br>";
		}
		elseif($unifiedOrderResult["code_url"] != NULL)
		{
			//从统一支付接口获取到code_url
			$code_url = $unifiedOrderResult["code_url"];
			//商户自行增加处理流程
			//......
		}
		$this->assign('out_trade_no',$out_trade_no);
		$this->assign('code_url',$code_url);
		$this->assign('unifiedOrderResult',$unifiedOrderResult);
	
		//页脚信息
		$category = M('Category');
		$where['type']=0;
		$where['level']=1;
		$var = $category->where($where)->limit(7)->select();
		foreach ($var as $k=>$v){
			$find['type']=0;
			$find['level']=2;
			$find['parentid']=$v['catid'];
			$zi = $category->where($find)->select();
			$var[$k]['zi']=$zi;
		}
		$this->assign('foot',$var);
		$this->display();
	}
	//查询订单
	public function orderQuery()
	{
		vendor('Weixinpay.WxPayPubHelper');
		//退款的订单号
		if (!isset($_POST["out_trade_no"]))
		{
			$out_trade_no = " ";
		}else{
			$out_trade_no = $_POST["out_trade_no"];
			//使用订单查询接口
			$orderQuery = new \OrderQuery_pub();
			//设置必填参数
			//appid已填,商户无需重复填写
			//mch_id已填,商户无需重复填写
			//noncestr已填,商户无需重复填写
			//sign已填,商户无需重复填写
			$orderQuery->setParameter("out_trade_no","$out_trade_no");//商户订单号
	
			//获取订单查询结果
			$orderQueryResult = $orderQuery->getResult();
	
			//商户根据实际情况设置相应的处理流程,此处仅作举例
			if ($orderQueryResult["return_code"] == "FAIL") {
				$this->error($out_trade_no);
			}
			elseif($orderQueryResult["result_code"] == "FAIL"){
				//     			$this->ajaxReturn('','支付失败！',0);
				$this->error($out_trade_no);
			}
			else{
				$i=$_SESSION['i'];
				$i--;
				$_SESSION['i'] = $i;
				//判断交易状态
				switch ($orderQueryResult["trade_state"])
				{
					case SUCCESS:
						$this->success("支付成功！");
						break;
					case REFUND:
						$this->error("超时关闭订单：".$i);
						break;
					case NOTPAY:
						$this->error("超时关闭订单：".$i);
						//     		              $this->ajaxReturn($orderQueryResult["trade_state"], "支付成功", 1);
						break;
					case CLOSED:
						$this->error("超时关闭订单：".$i);
						break;
					case PAYERROR:
						$this->error("支付失败".$orderQueryResult["trade_state"]);
						break;
					default:
						$this->error("未知失败".$orderQueryResult["trade_state"]);
						break;
				}
			}
		}
	}
	//异步通知url，商户根据实际开发过程设定
	public function notify_url() {
		vendor('Weixinpay.WxPayPubHelper');
		//使用通用通知接口
		$notify = new \Notify_pub();
		//存储微信的回调
		$xml = $GLOBALS['HTTP_RAW_POST_DATA'];
		$notify->saveData($xml);
		//验证签名，并回应微信。
		//对后台通知交互时，如果微信收到商户的应答不是成功或超时，微信认为通知失败，
		//微信会通过一定的策略（如30分钟共8次）定期重新发起通知，
		//尽可能提高通知的成功率，但微信不保证通知最终能成功。
		if($notify->checkSign() == FALSE){
			$notify->setReturnParameter("return_code", "FAIL");//返回状态码
			$notify->setReturnParameter("return_msg", "签名失败");//返回信息
		}else{
			$notify->setReturnParameter("return_code", "SUCCESS");//设置返回码
		}
		$returnXml = $notify->returnXml();
		//==商户根据实际情况设置相应的处理流程，此处仅作举例=======
		error_log("\r\n [".date("Y-m-d H:i:s",time())."-微信支付]:【接收到的notify通知】:\n".$xml."\n",3,"./Public/pay_log/".date("Ymd",time()).".log");
		$parameter = $notify->xmlToArray($xml);
		error_log("\r\n [".date("Y-m-d H:i:s",time())."-微信支付]:【接收到的notify通知】:\n".json_encode($parameter)."\n",3,"./Public/pay_log/".date("Ymd",time()).".log");
		if($notify->checkSign() == TRUE){
			if ($notify->data["return_code"] == "FAIL") {
				error_log("\r\n [".date("Y-m-d H:i:s",time())."-微信支付]:【通信出错】:\n".$xml."\n",3,"./Public/pay_log/".date("Ymd",time()).".log");
				echo 'error';
			}
			else if($notify->data["result_code"] == "FAIL"){
				error_log("\r\n [".date("Y-m-d H:i:s",time())."-微信支付]:【业务出错】:\n".$xml."\n",3,"./Public/pay_log/".date("Ymd",time()).".log");
				echo 'error';
			}
			else{
				error_log("\r\n [".date("Y-m-d H:i:s",time())."-微信支付]:【支付成功】:\n".$xml."\n",3,"./Public/pay_log/".date("Ymd",time()).".log");
				if ($this->process($parameter)) {
					echo 'success';
				}else {
					echo 'error';
				}
			}
		}
	}
	//订单处理
	private function process($parameter) {
		//更新订单
		$order_info = M('Order');
		$order_progress = M('OrderProgress');
		$out_trade_no=$parameter['out_trade_no'];
		$status = $order_info->where("order_num='{$out_trade_no}'")->getfield("status");
		if($status!=1){
			$datax['status'] ='2';
			$datax['payment'] = 1;
			$order_info->where("order_num='{$out_trade_no}'")->save($datax);
			$datar['order_id']=$out_trade_no;
			$datar['status']=2;
			$datar['info']='已支付';
			$datar['time']=strtotime($parameter['notify_time']);
			$datar['title']='已支付';
			$order_progress->add($datar);
		}
		return true;
	}
}