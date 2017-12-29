<?php
namespace Admin\Model;

class UserModel extends CommonModel{
	public $_validate	=	array(
			array('username','/^[a-z\d]\w{3,}$/i','用户名必须是字母或数字，且3位以上！'),
			array('password','require','密码必须'),
			array('repassword','require','确认密码必须'),
			array('repassword','password','确认密码不一致',0,'confirm',1), // 验证确认密码是否和密码一致
			array('username','','帐号已经存在！',0,'unique',1), // 在新增的时候验证username字段是否唯一
	);
	
	public $_auto		=	array(
			array('password','md5',self::MODEL_BOTH,'function'),
			array('postdate','time',self::MODEL_INSERT,'function'),	//调用php系统函数time()
	);
}