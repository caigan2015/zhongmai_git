<?php
$arr=array(
	    //  系统配置
		'SYSTEM_NAME'           => '中迈融资租赁管理后台',
		'SYSTEM_VERSION'        => 'v1.0.0',
		
		//'配置项'=>'配置值'
		'URL_MODEL' =>'1',
		'URL_PATHINFO_MODEL' => '2',
		'DATA_CACHE_TYPE'=>'file',
		'APP_DEBUG' => true,
		//	模板左右标签
		'TMPL_L_DELIM'=>'{',
		'TOKEN_ON'     =>   false, //是否开启令牌验证
		'TMPL_R_DELIM'=>'}',
		
		/* 模板引擎设置 */
		'TMPL_ACTION_ERROR'     => MODULE_PATH.'View'.DS.'Public'.DS.'dispatch_jump.html',   // 默认错误跳转对应的模板文件
		'TMPL_ACTION_SUCCESS'   => MODULE_PATH.'View'.DS.'Public'.DS.'dispatch_jump.html',   // 默认成功跳转对应的模板文件
		'TMPL_EXCEPTION_FILE'   => MODULE_PATH.'View'.DS.'Public'.DS.'error.html',       // 异常页面的模板文件
		//'TMPL_EXCEPTION_FILE'   => MODULE_PATH.'View'.DS.'Public'.DS.'exception.html',       // 异常页面的模板文件
		
		/* 运行时间设置 */
		'SHOW_RUN_TIME'=>false,          // 运行时间显示
		'SHOW_ADV_TIME'=>false,          // 显示详细的运行时间
		'SHOW_DB_TIMES'=>false,          // 显示数据库查询和写入次数
		'SHOW_CACHE_TIMES'=>false,       // 显示缓存操作次数
		'SHOW_USE_MEM'=>false,           // 显示内存开销
		'SHOW_PAGE_TRACE'=>false,        // 显示页面Trace信息 由Trace文件定义和Action操作赋值
		'APP_FILE_CASE'  =>   true, // 是否检查文件的大小写 对Windows平台有效
		
		/* URL配置 */
		'URL_CASE_INSENSITIVE'  => true,        // 默认false 表示URL区分大小写 true则表示不区分大小写
		'URL_MODEL'             => 0,           // URL模式
		'URL_PATHINFO_DEPR'     => '/',         // PATHINFO URL分割符
		'URL_ROUTER_ON'         => false,       // 是否开启URL路由
		'URL_ROUTE_RULES'       => array(),     // 默认路由规则 针对模块
//-----------------------RBAC相关的配置----------------------------------		
		'USER_AUTH_ON'=>true,       //开启用户验证
		'USER_AUTH_TYPE'			=>1,		// 默认认证类型 1 登录认证 2 实时认证
		'USER_AUTH_KEY'			=>'authId',	// 用户认证SESSION标记
		'ADMIN_AUTH_KEY'			=>'administrator',
		'USER_AUTH_MODEL'		=>'Admin',	// 默认验证数据表模型（找admin表）
		'AUTH_PWD_ENCODER'		=>'md5',	// 用户认证密码加密方式
		'USER_AUTH_GATEWAY'	=>'?m=Admin&c=Public&a=login',	// 默认认证网关
		'NOT_AUTH_MODULE'		=>'Public',		// 默认无需认证模块
		'REQUIRE_AUTH_MODULE'=>'',		// 默认需要认证模块
		'NOT_AUTH_ACTION'		=>'',		// 默认无需认证操作
		'REQUIRE_AUTH_ACTION'=>'',		// 默认需要认证操作
		'GUEST_AUTH_ON'          => false,    // 是否开启游客授权访问
		'GUEST_AUTH_ID'           =>    0,     // 游客的用户ID
		
		'DB_LIKE_FIELDS'=>'title|remark',
		'RBAC_ROLE_TABLE'=>'app_role',
		'RBAC_USER_TABLE'	=>	'app_role_user',
		'RBAC_ACCESS_TABLE' =>	'app_access',
		'RBAC_NODE_TABLE'	=> 'app_node',
		
		/* 模板解析设置 */
		'TMPL_PARSE_STRING'     => array(
				'__UPLOAD__' => SCRIPT_DIR . '/Public/upload',
				'__ADMIN__'      => SCRIPT_DIR . '/Public/Admin',
		),

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

$arr1=include 'config.inc.php';

return array_merge($arr1,$arr);
?>