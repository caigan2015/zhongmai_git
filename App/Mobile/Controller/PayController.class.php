<?php
namespace Mobile\Controller;

use Think\Model;

use Think\Controller;
header("Content-type: text/html; charset=utf-8");
class PayController extends Controller{
	public function _initialize() {
		vendor('Alipay.Corefunction');
		vendor('Alipay.Md5function');
		vendor('Alipay.Notify');
		vendor('Alipay.Submit');
	}
	
	public function doalipay(){
		$alipay_config=C('alipay_config');
	
		/**************************请求参数**************************/
		$payment_type = "1"; //支付类型 //必填，不能修改
		$notify_url = C('alipay.notify_url'); //服务器异步通知页面路径
		$return_url = C('alipay.return_url'); //页面跳转同步通知页面路径
		$seller_email = C('alipay.seller_email');//卖家支付宝帐户必填
		$out_trade_no = $_POST['trade_no'];//商户订单号 通过支付页面的表单进行传递，注意要唯一！
		$subject = $_POST['ordsubject'];  //订单名称 //必填 通过支付页面的表单进行传递
		$total_fee = $_POST['ordtotal_fee'];   //付款金额  //必填 通过支付页面的表单进行传递
		$body = "订单支付{$_POST['trade_no']}"; //订单描述 通过支付页面的表单进行传递
		$show_url = $_POST['ordshow_url'];  //商品展示地址 通过支付页面的表单进行传递
		$anti_phishing_key = "";//防钓鱼时间戳 //若要使用请调用类文件submit中的query_timestamp函数
		$exter_invoke_ip = get_client_ip(); //客户端的IP地址
		/************************************************************/
	
		//构造要请求的参数数组，无需改动
		$parameter = array(
				"service" => "create_direct_pay_by_user",
				"partner" => trim($alipay_config['partner']),
				"payment_type"    => $payment_type,
				"notify_url"    => $notify_url,
				"return_url"    => $return_url,
				"seller_email"    => $seller_email,
				"out_trade_no"    => $out_trade_no,
				"subject"    => $subject,
				"total_fee"    => $total_fee,
				"body"            => $body,
				"show_url"    => $show_url,
				"anti_phishing_key"    => $anti_phishing_key,
				"exter_invoke_ip"    => $exter_invoke_ip,
				"_input_charset"    => trim(strtolower($alipay_config['input_charset']))
		);
	
		//建立请求
		$alipaySubmit = new \AlipaySubmit($alipay_config);
		$html_text = $alipaySubmit->buildRequestForm($parameter,"post", "确认");
		echo $html_text;
	}
	
	/******************************
	 服务器异步通知页面方法
	*******************************/
	function notifyurl(){
	
		$alipay_config=C('alipay_config');
	
		//计算得出通知验证结果
		$alipayNotify = new \AlipayNotify($alipay_config);
		$verify_result = $alipayNotify->verifyNotify();
	
		if($verify_result) {
			//验证成功
			//获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表
			$out_trade_no   = $_POST['out_trade_no'];      //商户订单号
			$trade_no       = $_POST['trade_no'];          //支付宝交易号
			$trade_status   = $_POST['trade_status'];      //交易状态
			$total_fee      = $_POST['total_fee'];         //交易金额
			$notify_id      = $_POST['notify_id'];         //通知校验ID。
			$notify_time    = $_POST['notify_time'];       //通知的发送时间。格式为yyyy-MM-dd HH:mm:ss。
			$buyer_email    = $_POST['buyer_email'];       //买家支付宝帐号；
			$parameter = array(
					"out_trade_no"     => $out_trade_no, //商户订单编号；
					"trade_no"     => $trade_no,     //支付宝交易号；
					"total_fee"     => $total_fee,    //交易金额；
					"trade_status"     => $trade_status, //交易状态
					"notify_id"     => $notify_id,    //通知校验ID。
					"notify_time"   => $notify_time,  //通知的发送时间。
					"buyer_email"   => $buyer_email,  //买家支付宝帐号；
			);
			if($_GET['trade_status'] == 'TRADE_FINISHED' || $_GET['trade_status'] == 'TRADE_SUCCESS') {
				//更新订单
				$order_info = M('Order');
				$order_progress = M('OrderProgress');
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
			}
			echo "success";        //请不要修改或删除
		}else {
			//验证失败
			echo "fail";
		}
	}
	
	/*
	 页面跳转处理方法
	*/
	function returnurl(){
		//头部的处理跟上面两个方法一样，这里不罗嗦了！
		$alipay_config=C('alipay_config');
		$alipayNotify = new \AlipayNotify($alipay_config);//计算得出通知验证结果
		$verify_result = $alipayNotify->verifyReturn();
		if($verify_result) {
			//验证成功
			//获取支付宝的通知返回参数，可参考技术文档中页面跳转同步通知参数列表
			$out_trade_no   = $_GET['out_trade_no'];      //商户订单号
			$trade_no       = $_GET['trade_no'];          //支付宝交易号
			$trade_status   = $_GET['trade_status'];      //交易状态
			$total_fee      = $_GET['total_fee'];         //交易金额
			$notify_id      = $_GET['notify_id'];         //通知校验ID。
			$notify_time    = $_GET['notify_time'];       //通知的发送时间。
			$buyer_email    = $_GET['buyer_email'];       //买家支付宝帐号；
			 
			$parameter = array(
					"out_trade_no"     => $out_trade_no,      //商户订单编号；
					"trade_no"     => $trade_no,          //支付宝交易号；
					"total_fee"      => $total_fee,         //交易金额；
					"trade_status"     => $trade_status,      //交易状态
					"notify_id"      => $notify_id,         //通知校验ID。
					"notify_time"    => $notify_time,       //通知的发送时间。
					"buyer_email"    => $buyer_email,       //买家支付宝帐号
			);
			error_log("\r\n [".date("Y-m-d H:i:s",time())."-支付宝支付]:【接收到的returnurl通知】:\n".json_encode($parameter)."\n",3,"./Public/pay_log/".date("Ymd",time()).".log");
			if($_GET['trade_status'] == 'TRADE_FINISHED' || $_GET['trade_status'] == 'TRADE_SUCCESS') {
				
				error_log("\r\n [".date("Y-m-d H:i:s",time())."-支付宝支付]:【支付成功】:\n".json_encode($parameter)."\n",3,"./Public/pay_log/".date("Ymd",time()).".log");
				//跳转到配置项中配置的支付成功页面；
				//判断是否为手机端
				$inde=self::isMobile();
				if($inde){
					$this->redirect(U("Mobile/Index/pay_back",array("error"=>0)));
					exit();
				}else{
					$this->redirect(U("Home/index/pay_back",array("error"=>0)));
					exit();
				}
			}else {
				error_log("\r\n [".date("Y-m-d H:i:s",time())."-支付宝支付]:【支付失败】:\n".json_encode($parameter)."\n",3,"./Public/pay_log/".date("Ymd",time()).".log");
				//跳转到配置项中配置的支付失败页面；
				$inde=self::isMobile();
				if($inde){
					$this->redirect(U("Mobile/Index/pay_back",array("error"=>1)));
					exit();
				}else{
					$this->redirect(U("Home/index/pay_back",array("error"=>1)));
					exit();
				}
			}
		}else {
			error_log("\r\n [".date("Y-m-d H:i:s",time())."-支付宝支付]:【支付失败】:\n".json_encode($parameter)."\n",3,"./Public/pay_log/".date("Ymd",time()).".log");
			//跳转到配置项中配置的支付失败页面；
			$inde=self::isMobile();
			if($inde){
				$this->redirect(U("Mobile/Index/pay_back",array("error"=>1)));
				exit();
			}else{
				$this->redirect(U("Home/index/pay_back",array("error"=>1)));
				exit();
			}
		}
	}
	
	/*
	 * **********************
	* //判断是否为手机端
	*/
	public function isMobile()
	{
		// 如果有HTTP_X_WAP_PROFILE则一定是移动设备
		if (isset ($_SERVER['HTTP_X_WAP_PROFILE']))
		{
			return true;
		}
		// 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
		if (isset ($_SERVER['HTTP_VIA']))
		{
			// 找不到为flase,否则为true
			return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
		}
		// 脑残法，判断手机发送的客户端标志,兼容性有待提高
		if (isset ($_SERVER['HTTP_USER_AGENT']))
		{
			$clientkeywords = array ('nokia',
					'sony',
					'ericsson',
					'mot',
					'samsung',
					'htc',
					'sgh',
					'lg',
					'sharp',
					'sie-',
					'philips',
					'panasonic',
					'alcatel',
					'lenovo',
					'iphone',
					'ipod',
					'blackberry',
					'meizu',
					'android',
					'netfront',
					'symbian',
					'ucweb',
					'windowsce',
					'palm',
					'operamini',
					'operamobi',
					'openwave',
					'nexusone',
					'cldc',
					'midp',
					'wap',
					'mobile'
			);
			// 从HTTP_USER_AGENT中查找手机浏览器的关键字
			if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT'])))
			{
				return true;
			}
		}
		// 协议法，因为有可能不准确，放到最后判断
		if (isset ($_SERVER['HTTP_ACCEPT']))
		{
			// 如果只支持wml并且不支持html那一定是移动设备
			// 如果支持wml和html但是wml在html之前则是移动设备
			if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html'))))
			{
				return true;
			}
		}
		return false;
	}
}