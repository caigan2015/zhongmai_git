<?php
namespace Admin\Controller;
use Admin\Controller\CommonController;
use Think\Controller;
class AdminController extends CommonController {
	//管理员列表
	public function index(){
		$Admin	 =	 M("Admin");
		$role_user = M("RoleUser");
		$role = M("Role");
		
		if(I('get.dosubmit')){
			$stime = $_POST['stime']?strtotime($_POST['stime']):'';
			$etime = $_POST['etime']?strtotime($_POST['etime']):'';
			if($stime!=''){
				$where['register_time']=array('egt',$stime);
				$this->assign('stime',$_POST['stime']);
			}
			if($etime!=''){
				$where['register_time']=array('lt',strtotime ("+1 day", $etime));
				$this->assign('etime',$_POST['etime']);
			}
			if(!empty($_POST['nickname'])){
				$where['nickname']=array('like',"%{$_POST['nickname']}%");
				$this->assign('nickname',$_POST['nickname']);
			}
		}
		
		$list = $Admin ->where($where) ->order('id asc') -> select();
		$count = $Admin ->where($where)->count();
		foreach ($list as $k=>$v){
			if($v['username']=='rootadmin'){
				$list[$k]['power']=1;
				$list[$k]['role']='超级管理员';
			}else{
				$role_id = $role_user->where(['user_id'=>$v['id']])->getField('role_id');
				$list[$k]['power']=0;
				$list[$k]['role']=$role_id?$role->where(['id'=>$role_id])->getField('title'):'暂无权限';
			}
			$list[$k]['register_time']=date('Y-m-d H:i:s',$v['register_time']);
		}
		$this->assign('count',$count-1);
		$this->assign('list',$list);
		$this -> display();
	}
	
	//管理员添加
	public function addadmin(){
		if(I('get.dosubmit')){
			$Admin	 =	 D("Admin");
			if(!$Admin->create()) {
				$re = array("error"=>1,"msg"=>$Admin->getError());
			}else{
				// 写入帐号数据
				$result = $Admin->add();
				if($result) {
					if($_POST['adminrole']>0){
						$this->addAdminRole($result,$_POST['adminrole']);
					}
					$re = array("error"=>0,"msg"=>"添加成功！");
				}else{
					$re = array("error"=>1,"msg"=>"添加失败！");
				}
			}
			echo json_encode($re);
			exit;
		}
		
		$role = M('Role');
		$rList = $role -> where(['status'=>1])->select();
		$this -> assign('rList',$rList);
		$this -> display();
	}
	
	//管理员编辑
	public function editadmin(){
		$admin = M('Admin');
		$RoleUser = M("RoleUser");
		if($_GET[id]){
			$where[id] = $_GET[id];
		}else{
			$where[id] = $_SESSION[authId];
		}
		$ulist = $admin -> where($where) -> find();
		$role_id = $RoleUser->where(['user_id'=>$ulist['id']])->getField('role_id');
		
		if(I('get.dosubmit')){
			$where[id] = $_POST[id];
			if(!empty($_POST[password])){
				if(md5($_POST[password])==$ulist[password]){
					$re = array("error"=>1,"msg"=>"新密码不能跟原密码一样！");
					echo json_encode($re);
					exit;
				}
				$data[password] = md5($_POST[password]);
			}
			$data[nickname] = $_POST[nickname];
			$data[phone] = $_POST[phone];
			$data[email] = $_POST[email];
			$data[remark] = $_POST[remark];
			if(isset($_POST[status])) {
				$data[status] = $_POST[status];
			}
			$l = $admin -> where($where) -> save($data);
			if($l){
				if($_POST['adminrole']>0&&$_POST['adminrole']!=$role_id){
					$this->addAdminRole($_POST[id],$_POST['adminrole']);
				}
				$re = array("error"=>0,"msg"=>"修改成功！");
			}else{
				if($_POST['adminrole']>0&&$_POST['adminrole']!=$role_id){
					$this->addAdminRole($_POST[id],$_POST['adminrole']);
					$re = array("error"=>0,"msg"=>"修改成功！");
				}else{
					$re = array("error"=>1,"msg"=>"修改失败！");
				}
			}
			echo json_encode($re);
			exit;
		}
		$this -> assign('ulist',$ulist);
		$this -> assign('roleid',$role_id);
		
		$role = M('Role');
		$rList = $role -> where(['status'=>1])->select();
		$this -> assign('rList',$rList);
		
		if($_SESSION[authId]==$_GET[id]){
			$this->assign('us',1);
		}else{
			$this->assign('us',0);
		}
		
		$this -> display();
	}
	
	protected function addAdminRole($userId,$roleId) {
		//新增用户自动加入相应权限组
		$RoleUser = M("RoleUser");
		$RoleUser->user_id	=	$userId;
		// 默认加入网站编辑组
		$RoleUser->role_id	=	$roleId;
		$RoleUser->where(['user_id'=>$userId])->delete();
		$RoleUser->add();
	}
	
	//管理员删除
	public function deleteadmin(){
		if($_POST['id']){
			$admin = M('Admin');
			$r_u = M('Role_user');
			$where_u['user_id'] = $_POST['id'];
			$is = $r_u -> where($where_u) -> select();
			if(empty($is)){
				$where[id] = $_POST['id'];
				if($admin -> where($where) -> delete()) {
					$re = array("error"=>0,"msg"=>"删除成功！");
				}else {
					$re = array("error"=>1,"msg"=>"删除失败！");
				}
			}else{
				if($r_u -> where($where_u) -> delete()){
					$where[id] = $_POST['id'];
					if($admin -> where($where) -> delete()) {
						$re = array("error"=>0,"msg"=>"删除成功！");
					}else {
						$re = array("error"=>1,"msg"=>"删除失败！");
					}
				}else{
					$re = array("error"=>1,"msg"=>"删除失败！");
				}
			}
			echo json_encode($re);
			exit;
		}
	}
	
	//管理员禁用
	public function adminforbid(){	
		$admin = M('Admin');
		$where[id] = $_POST['id'];
		$data[status] = 0;
		$l = $admin -> where($where) -> save($data);
		if($l){
			$re = array("error"=>0,"msg"=>"禁用成功！");
		}else{
			$re = array("error"=>1,"msg"=>"禁用失败！");
		}
		echo json_encode($re);
		exit;
	}
	
	//管理员启用
	public function adminstart(){	
		$admin = M('Admin');
		$where[id] = $_POST['id'];
		$data[status] = 1;
		$l = $admin -> where($where) -> save($data);
		if($l){
			$re = array("error"=>0,"msg"=>"启用成功！");
		}else{
			$re = array("error"=>1,"msg"=>"启用失败！");
		}
		echo json_encode($re);
		exit;
	}
	
	//角色列表
	public function rolelist(){
		$role = M('Role');
		$num = $role ->count();
		$list = $role -> select();
		$this->assign('num',$num);
		$this -> assign('list',$list);
		$this -> display();
	}
	
	//角色添加
	public function addrole(){		
		$this -> _roleInfo($_REQUEST[roleId]);
		$role_level1 = $this -> _roleLevelList($_REQUEST[roleId], 1);
		$role_level2 = $this -> _roleLevelList($_REQUEST[roleId], 2);
		$role_level3 = $this -> _roleLevelList($_REQUEST[roleId], 3);
		$this -> _levelList($role_level1, $role_level2, $role_level3);
		if(I('get.dosubmit')){	
			$Role	 =	 D("Role");
			if(!$Role->create()) {
				$this->error($Role -> getError());
			}else{
				$result	 =	 $Role->add();
				if($result) {
					if($_POST[level1]){
						if($_POST[level2]){
							if($this -> _isPid($_POST[level1], $_POST[level2])){
								if($_POST[level3] ){
									if($this -> _isPid($_POST[level2], $_POST[level3])){															
										$this -> _insert_del_level ($_POST[level1], $role_level1, 1, $result);
										$this -> _insert_del_level ($_POST[level2], $role_level2, 2, $result);
										$this -> _insert_del_level ($_POST[level3], $role_level3, 3, $result);

										$re = array("error"=>0,"msg"=>"添加成功！");
									}else{
										$re = array("error"=>1,"msg"=>"您勾选了第三级权限,但是与第二级权限不对应!");
									}
					
								}else{
									$re = array("error"=>1,"msg"=>"您在有些项目中只勾选了第一、二级权限,但是还没有勾选第三级权限!");
								}
							}else{
								$re = array("error"=>1,"msg"=>"您勾选了第二级权限,但是与第一级权限不对应!");
							}
					
						}else{
							$re = array("error"=>1,"msg"=>"您勾选了第一级权限,但是您还没有勾选第二级权限!");
						}
						echo json_encode($re);
						exit;
					}
					$re = array("error"=>0,"msg"=>"添加成功！");
				}else{
					$re = array("error"=>1,"msg"=>"添加失败！");
				}
			}
			echo json_encode($re);
			exit;
		}
		$this -> display();
	}
	
	//角色修改
	public function editrole(){
		$role = M('role');
		$this -> _roleInfo($_REQUEST['id']);
		$role_level1 = $this -> _roleLevelList($_REQUEST['id'], 1);
		$role_level2 = $this -> _roleLevelList($_REQUEST['id'], 2);
		$role_level3 = $this -> _roleLevelList($_REQUEST['id'], 3);
		$this -> _levelList($role_level1, $role_level2, $role_level3);
		if(I('get.dosubmit')){
			$where['id'] = $_POST['id'];
			$data['title'] = $_POST['title'];
			$data['status'] = $_POST['status'];
			$data['remark'] = $_POST['remark'];
			$l = $role -> where($where) -> save($data);
			if($l){
				if($_POST[level1]){
					if($_POST[level2]){
						if($this -> _isPid($_POST[level1], $_POST[level2])){
							if($_POST[level3] ){
								if($this -> _isPid($_POST[level2], $_POST[level3])){
									$this -> _insert_del_level ($_POST[level1], $role_level1, 1, $_REQUEST['id']);
									$this -> _insert_del_level ($_POST[level2], $role_level2, 2, $_REQUEST['id']);
									$this -> _insert_del_level ($_POST[level3], $role_level3, 3, $_REQUEST['id']);
				
									$re = array("error"=>0,"msg"=>"保存成功！");
								}else{
									$re = array("error"=>1,"msg"=>"您勾选了第三级权限,但是与第二级权限不对应!");
								}
									
							}else{
								$re = array("error"=>1,"msg"=>"您在有些项目中只勾选了第一、二级权限,但是还没有勾选第三级权限!");
							}
						}else{
							$re = array("error"=>1,"msg"=>"您勾选了第二级权限,但是与第一级权限不对应!");
						}
							
					}else{
						$re = array("error"=>1,"msg"=>"您勾选了第一级权限,但是您还没有勾选第二级权限!");
					}
					echo json_encode($re);
					exit;
				}
				$re = array("error"=>0,"msg"=>"修改成功！");
			}else{
				if($_POST[level1]){
					if($_POST[level2]){
						if($this -> _isPid($_POST[level1], $_POST[level2])){
							if($_POST[level3] ){
								if($this -> _isPid($_POST[level2], $_POST[level3])){
									$this -> _insert_del_level ($_POST[level1], $role_level1, 1, $_REQUEST['id']);
									$this -> _insert_del_level ($_POST[level2], $role_level2, 2, $_REQUEST['id']);
									$this -> _insert_del_level ($_POST[level3], $role_level3, 3, $_REQUEST['id']);
				
									$re = array("error"=>0,"msg"=>"保存成功！");
								}else{
									$re = array("error"=>1,"msg"=>"您勾选了第三级权限,但是与第二级权限不对应!");
								}
									
							}else{
								$re = array("error"=>1,"msg"=>"您在有些项目中只勾选了第一、二级权限,但是还没有勾选第三级权限!");
							}
						}else{
							$re = array("error"=>1,"msg"=>"您勾选了第二级权限,但是与第一级权限不对应!");
						}
							
					}else{
						$re = array("error"=>1,"msg"=>"您勾选了第一级权限,但是您还没有勾选第二级权限!");
					}
					echo json_encode($re);
					exit;
				}else{
					$re = array("error"=>1,"msg"=>"修改失败！");
				}
			}
			echo json_encode($re);
			exit;
		}
		$this -> display();
	}
	
	//角色删除
	public function deleterole(){
		if(I('get.dosubmit')){
			$role = M('role');
			$access = M('Access');
			$r_u = M('Role_user');
			$where[role_id] = $_POST[id];
			$where_i[id] = $_POST[id];
			$is = $access -> where($where) -> select();
			$iu = $r_u -> where($where) -> select();
			$flag = 1;
			if(!empty($is)){
				if($access -> where($where) -> delete()){
					$flag = 1;
				}else{
					$flag = 0;
				}
			}
			if(!empty($iu)){
				if($r_u -> where($where) -> delete()){
					$flag = 1;
				}else{
					$flag = 0;
				}
			}
			if($flag == 1){
				if($role -> where($where_i) -> delete()) {
					$re = array("error"=>1,"msg"=>"删除成功！");
				}else {
					$re = array("error"=>1,"msg"=>"删除失败！");
				}
			}else{
				$re = array("error"=>1,"msg"=>"删除失败！");
			}
			echo json_encode($re);
			exit;
		}
	}
	
	/**
	 * 显示出该角色的名称和描述
	 * $id	传过来的角色id
	 * $l level几
	 */
	protected function _roleInfo($id){
		$role = M('role');
		$data[id] = $id;
		$list = $role -> where($data) -> select();
		$this -> assign('roleInfo',$list);
		return;
	}
	protected function _levelList($role_level1, $role_level2, $role_level3){
	
		$node = M('node');
		$level1 = $node -> where('level=1') -> field('id,title')->order('sort asc') -> select();
	
		foreach($level1 as $key1 => $vo1 ){
	
			$check = in_array($vo1[id], $role_level1) ? 'checked' : null ;
			$level1[$key1][check] = $check;
	
			$data['level'] = 2;
			$data['pid'] = $vo1[id];
			$data['licensed'] = 1;
			$level2[] = $node -> where($data) -> field('id,title')->order('sort asc') -> select();
	
			foreach($level2[$key1] as $key2 => $vo2){
				$check = in_array($vo2[id], $role_level2) ? 'checked' : null ;
				$level2[$key1][$key2][check] = $check;
	
				$data['level'] = 3;
				$data['pid'] = $vo2[id];
				$level3[$key1][] = $node -> where($data) -> field('id,title')->order('id asc') -> select();
				foreach($level3[$key1][$key2] as $key3 => $vo3){
					$check = in_array($vo3[id], $role_level3) ? 'checked' : null ;
					$level3[$key1][$key2][$key3][check] = $check;
				}
			}
		}
		$this -> assign('level1',$level1);
		$this -> assign('level2',$level2);
		$this -> assign('level3',$level3);
		return;
	}
	/**
	 * 查出该角色原来拥有的节点ID并用于模板的勾选check
	 * $id	传过来的角色id
	 * $l level几
	 * 最终返回的是角色某个level的节节点id组成一维数组。
	 */
	protected function _roleLevelList($role_id, $l){
		$access = M('access');
		$data['role_id'] = array('eq', $role_id);
	
		$data['level'] = array('eq', $l);
		$level = $access -> where($data) -> field('node_id') ->  select();
	
		foreach($level as $vo){
			$list[] = $vo[node_id];
		}
		return $list;
	}
	
	/**
	 *  判断下一级权限是否对应着上一级的权限
	 *	$pidArr		上一级权限
	 * 	$idArr		这一级权限
	 *
	 */
	
	protected function _isPid ($pidArr, $idArr) {
		foreach($idArr as $id){
			$node = M('node');
			$data[id] = $id;
			$arr = $node -> where($data) -> field('pid') -> find();
			if(!in_array($arr[pid], $pidArr)){
				return false;
			}
		}
		return true;
	}
	
	
	/**
	 * 对原有授权的修改
	 *	$post_level		提交过来的节点id数组
	 *	$role_level		已经读出来的这个角色的原来的节点id数组
	 *	$level			什么级别的权限
	 *	$role_id		这个角色的id
	 */
	protected function _insert_del_level ($post_level, $role_level, $level, $role_id) {
		$access = M('access');
		foreach($post_level as $val){
			if(!in_array($val, $role_level)){
				$where[role_id] =  $role_id;
				$where[node_id] = $val;
				$where[level] = $level;
				$access ->  add($where);
	
			}
		}
		foreach($role_level as $val){
			if(!in_array($val, $post_level)){
				$data[role_id] =  $role_id;
				$data[node_id] = $val;
				$access -> where($data) -> delete();
			}
		}
	}
	
	//节点列表
	public function nodelist(){		
		$node = M('node');
		$level1 = $node -> where('level=1') -> field('id,name,title,display,sort,licensed') ->order('sort asc,id asc') -> select();	
		$num1 = $node -> where('level=1')->count();
		$num2 = 0;
		$num3 = 0;
		foreach($level1 as $key1 => $vo1 ){
			$data1['level'] = 2;
			$data1['pid'] = $vo1[id];
			$level2 = $node -> where($data1) -> field('id,name,title,display,sort,licensed') ->order('sort asc,id asc') -> select();
			$num2 +=$node -> where($data1) ->count();
			foreach($level2 as $key2 => $vo2){
				$data2['level'] = 3;
				$data2['pid'] = $vo2[id];
				$level3 = $node -> where($data2) -> field('id,name,title,display,sort,licensed') ->order('sort asc,id asc') -> select();
				$num3 +=$node -> where($data2) ->count();
				$level2[$key2]['last']=$level3;
			}
			$level1[$key1]['zi']=$level2;
		}
		$this->assign('num',$num1+$num2+$num3);
		$this->assign('level',$level1);		
		$this -> display();
	}
	
	//节点添加
	public function addnode(){
		// 创建数据对象		
		if(I('get.dosubmit')){
			$Node	 =	 D("Node");
			if(!$Node->create()) {
				$re = array("error"=>1,"msg"=>$Node -> getError());
			}else{
				// 写入数据
				$Node->sign = $_POST['sign']?$_POST['sign']:NULL;
				$result	 =	 $Node->add();
				if($result) {
					$re = array("error"=>0,"msg"=>"节点添加成功");
				}else{
					$re = array("error"=>1,"msg"=>"节点添加失败");
				}
			}
			echo json_encode($re);
			exit;
		}
		$this->display();
	}
	
	//节点修改
	public function editnode(){
		$node = M('node');
		$where['id'] = $_GET['id'];
		$nlist = $node -> where($where) -> select();
		if(I('get.dosubmit')){
			$where['id'] = $_POST['id'];
			$data['name'] = $_POST['name'];
			$data['title'] = $_POST['title'];
			$data['sort'] = $_POST['sort']?$_POST['sort']:0;
			$data['remark'] = $_POST['remark'];
			$data['display'] = $_POST['display'];
			$data['sign'] = $_POST['sign']?$_POST['sign']:NULL;
			$data['licensed'] = $_POST['licensed'];
			$l = $node -> where($where) -> save($data);
			if($l){
				$re = array("error"=>0,"msg"=>"修改成功");
			}else{
				$re = array("error"=>1,"msg"=>"修改失败");
			}
			echo json_encode($re);
			exit;
		}
		$nlist[0]['sign']=htmlspecialchars($nlist[0]['sign']);
		$this -> assign('nlist',$nlist[0]);
		$this -> display();
	}
	
	//删除节点
	public function deletenode()
	{
		$node = M('node');
		$where['id'] = $_POST['id'];
		$info = $node -> where($where) -> find();
		if($info['level']<3){
			$child = $node->where(["pid"=>$info['id']])->find();
			if($child){
				$re = array("error"=>1,"msg"=>"请先删除其子级");
			}else{
				$is = $node -> where($where) ->delete();
				if($is){
					$re = array("error"=>0,"msg"=>"删除成功");
				}else{
					$re = array("error"=>1,"msg"=>"删除失败");
				}	
			}
		}else{
			$is = $node -> where($where) ->delete();
			if($is){
				$re = array("error"=>0,"msg"=>"删除成功");
			}else{
				$re = array("error"=>1,"msg"=>"删除失败");
			}
		}
		echo json_encode($re);
	}
	
	//ajax设置节点排序
	public function setnode()
	{
		$node = M('node');
		$where['id']=$_POST['id'];
		$data['sort']=$_POST['val']?$_POST['val']:NULL;
		$is = $node->where($where)->save($data);
		if($is){
			$re = array("error"=>0,"msg"=>"设置成功");
		}else{
			$re = array("error"=>1,"msg"=>"无任何修改");
		}
		echo json_encode($re);
	}
}