<?php
namespace Admin\Model;

class ColumnModel extends CommonModel{
	public $_validate	=	array(
			array('name','require','栏目名必须填'),
			array('name','','栏目已经存在',0,'unique',1), // 在新增的时候验证name字段是否唯一			
	);
}
?>