<?php
namespace Admin\Model;
/* 管理员模型*/

class AdminModel extends CommonModel{

	public $_validate	=	array(
			array('username','/^[a-z\d]\w{3,}$/i','用户名必须是字母或数字，且3位以上！'),
			array('password','require','密码必须'),
			array('nickname','require','昵称必须'),
			array('repassword','require','确认密码必须'),
			array('repassword','password','确认密码不一致',0,'confirm',1), // 验证确认密码是否和密码一致
			array('username','','帐号已经存在！',0,'unique',1), // 在新增的时候验证username字段是否唯一
	);

	public $_auto		=	array(

			array('password','md5',self::MODEL_BOTH,'function'),
			array('register_time','time',self::MODEL_INSERT,'function'),	//调用php系统函数time()
			array('last_login_time','time',self::MODEL_BOTH,'function'),
			array('last_login_ip','get_client_ip',self::MODEL_BOTH,'function'),
	);

	public $_link = array(
				
			'RoleUser' => array(
					'mapping_type' => self::BELONGS_TO,
					'class_name'   => 'RoleUser',  //关联的表名
					'foreign_key'  => 'user_id',  //外键
					'as_fields' => 'role_id,user_id',
			),
	);



}
?>