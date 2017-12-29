<?php
$arr1 = array(
		//'配置项'=>'配置值'
		'URL_MODEL' =>'1',
		'URL_PATHINFO_MODEL' => '2',
		'DATA_CACHE_TYPE'=>'file',
		'APP_DEBUG' => true,
		//	模板左右标签
		'TMPL_L_DELIM'=>'{',
		'TOKEN_ON'     =>   false, //是否开启令牌验证
		'TMPL_R_DELIM'=>'}',

		/* URL配置 */
		'URL_CASE_INSENSITIVE'  => true,        // 默认false 表示URL区分大小写 true则表示不区分大小写
		'URL_MODEL'             => 2,           // URL模式
		'URL_PATHINFO_DEPR'     => '/',         // PATHINFO URL分割符
		'URL_ROUTER_ON'         => false,       // 是否开启URL路由
		'URL_ROUTE_RULES'       => array(),     // 默认路由规则 针对模块

		"WORK"=>array(
			"1"=>"公务员/事业单位",
			"2"=>"企业主",
			"3"=>"个体户",
			"4"=>"上班族",
			"5"=>"自由职业者",
		),

		"PAY"=>array(
			"1"=>"3000元以下",
			"2"=>"3000-5000元",
			"3"=>"5000-8000元",
			"4"=>"8000-12000元",
			"5"=>"12000-20000元",
			"6"=>"20000元以上",
		),

		"BAO"=>array(
	 	    "1"=>"有社保",
			"2"=>"无社保",
		),

		"JIN"=>array(
			"1"=>"有公积金",
			"2"=>"无公积金",
		),

		"XIN"=>array(
			"1"=>"信用良好",
			"2"=>"少数逾期",
			"3"=>"多次逾期",
			"4"=>"无信用记录",
		),

		"ZHU"=>array(
			"1"=>"自有全款",
			"2"=>"自有贷款",
			"3"=>"租赁",
			"4"=>"自建房",
			"5"=>"其他",
		)
);

$arr2 = include 'config.inc.php';

return array_merge($arr1,$arr2);
?>