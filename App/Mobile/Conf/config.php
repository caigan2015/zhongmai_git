<?php
$arr=array(
	    //  系统配置
		'SYSTEM_NAME'           => '商城-首页',
		'SYSTEM_VERSION'        => 'v1.0.0',
		
		'SHOW_PAGE_TRACE'       => false,
		
		'SAVE_SHOP_LOG_OPEN'   => true,
		
		//	模板左右标签
		'TMPL_L_DELIM'=>'{',
		'TOKEN_ON'     =>   false, //是否开启令牌验证
		'TMPL_R_DELIM'=>'}',
		
		/* URL配置 */
		'URL_CASE_INSENSITIVE'  => true,        // 默认false 表示URL区分大小写 true则表示不区分大小写
		'URL_MODEL'             => 1,           // URL模式
		'URL_PATHINFO_DEPR'     => '/',         // PATHINFO URL分割符
		'URL_ROUTER_ON'         => false,       // 是否开启URL路由
		'URL_ROUTE_RULES'       => array(),     // 默认路由规则 针对模块
		
		'alipay_config'   =>array(
				//这里是卖家的支付宝账号，也就是你申请接口时注册的支付宝账号
				'seller_email'=>'xxxxxx@qq.com',
		
				//这里是异步通知页面url，提交到项目的Pay控制器的notifyurl方法；
				'notify_url'=>'http://www.xxxx.com.cn/index.php/Pay/notifyurl',
		
				//这里是页面跳转通知url，提交到项目的Pay控制器的returnurl方法；
				'return_url'=>'http://www.xxxx.com.cn/index.php/Pay/returnurl',
		),
);

return $arr;
?>