<?php
namespace Admin\Model;

class NodeModel extends CommonModel{
	public $_validate	=	array(
			array('name','require','节点名必须填'),
			//array('name','','节点已经存在',0,'unique',1), // 在新增的时候验证username字段是否唯一
			array('title','require','节点简称必须填'),
			array('level','require','模块等级必须选择'),
	);
}
?>