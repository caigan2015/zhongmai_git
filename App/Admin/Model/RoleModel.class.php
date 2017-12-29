<?php
namespace Admin\Model;

class RoleModel extends CommonModel{
	public $_validate	=	array(
		array('name','/^[a-z\d]\w{3,}$/i','角色名必须是字母或数字，且3位以上！'),
		array('name','','角色已经存在',0,'unique',1), // 在新增的时候验证username字段是否唯一
		);

	public $_link = array(
		'RoleUser' => array(
			'mapping_type' => HAS_MANY,
			'foreign_key' => 'user_id',
			
		),
	);
}


?>