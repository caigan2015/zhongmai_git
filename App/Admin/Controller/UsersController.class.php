<?php
namespace Admin\Controller;
use Admin\Controller\CommonController;
use Think\Controller;
class UsersController extends CommonController {
	//会员列表
	public function index(){
		$userdb	 =	 M("User");
		if(I('get.dosubmit')){
			$stime = $_POST['stime']?strtotime($_POST['stime']):'';
			$etime = $_POST['etime']?strtotime($_POST['etime']):'';
			if($stime!=''){
				$where['postdate']=array('egt',$stime);
				$this->assign('stime',$_POST['stime']);
			}
			if($etime!=''){
				$where['postdate']=array('lt',strtotime ("+1 day", $etime));
				$this->assign('etime',$_POST['etime']);
			}
			if(!empty($_POST['phone'])){
				$where['phone']=array('like',"%{$_POST['phone']}%");
				$this->assign('phone',$_POST['phone']);
			}
		}
		$list = $userdb ->where($where) ->order('postdate desc') -> select();
		$count = $userdb ->where($where)->count();
		foreach ($list as $k=>$v){
			$my = M('UserInfo')->where(['uid'=>$v['id']])->find();
			if($my) {
				if (!empty($my['card'])) {
					$list[$k]['state'] = 1;
					$list[$k]['card']=substr_replace($my['card'], '****', 8, 4);
				}else{
					$list[$k]['state'] = 0;
				}
				$list[$k]['sex'] = $my['sex'] == 1 ? '男' : '女';
				$list[$k]['sname'] = $my['sname'];
			}else{
				$list[$k]['state'] = 0;
				$list[$k]['sex'] = '未设置';
			}
			$list[$k]['ctime']=date('Y-m-d H:i:s',$v['postdate']);
		}
		$this->assign('count',$count);
		$this->assign('list',$list);
		$this->display();
	}
	
	//会员添加
	public function add(){
		if(I('get.dosubmit')){
			$User	 =	 D("User");
			if(!$User->create()) {
				$re = array("error"=>1,"msg"=>$User->getError());
			}else{
				// 写入帐号数据
				$result = $User->add();
				if($result) {					
					$re = array("error"=>0,"msg"=>"添加成功！");
				}else{
					$re = array("error"=>1,"msg"=>"添加失败！");
				}
			}
			echo json_encode($re);
			exit;
		}

		$this->display();
	}
	
	//会员编辑
	public function edit(){
		$userdb	 =	 M("User");		
		$where[id] = $_GET[id];
		$ulist = $userdb -> where($where) -> find();
		$this -> assign('ulist',$ulist);
		
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
			$data[phone] = $_POST[phone];
			$data[email] = $_POST[email];
			$l = $userdb -> where($where) -> save($data);
			if($l){				
				$re = array("error"=>0,"msg"=>"修改成功！");
			}else{
				$re = array("error"=>1,"msg"=>"修改失败！");
			}
			echo json_encode($re);
			exit;
		}
		$this->display();
	}
	
	//会员删除
	public function delete(){
		if($_POST['id']){
			$userdb	 =	 M("User");
			$where[id] = $_POST['id'];
			if($userdb -> where($where) -> delete()) {
				$re = array("error"=>0,"msg"=>"删除成功！");
			}else {
				$re = array("error"=>1,"msg"=>"删除失败！");
			}
			echo json_encode($re);
			exit;
		}
	}
	
	//会员禁用
	public function forbid(){
		$userdb	 =	 M("User");
		$where[id] = $_POST['id'];
		$data['flag'] = '0';
		$l = $userdb -> where($where) -> save($data);
		if($l){
			$re = array("error"=>0,"msg"=>"禁用成功！");
		}else{
			$re = array("error"=>1,"msg"=>"禁用失败！");
		}
		echo json_encode($re);
		exit;
	}
	
	//会员启用
	public function start(){
		$userdb	 =	 M("User");
		$where[id] = $_POST['id'];
		$data['flag'] = '1';
		$l = $userdb -> where($where) -> save($data);
		if($l){
			$re = array("error"=>0,"msg"=>"启用成功！");
		}else{
			$re = array("error"=>1,"msg"=>"启用失败！");
		}
		echo json_encode($re);
		exit;
	}

	//用户评价
	public function comments(){
		$commentsDB = M('Comments');
		$list = $commentsDB ->order('ctime desc') -> select();
		$count = $commentsDB->count();
		$this->assign('count',$count);
		$this->assign('list',$list);
		$this->display();
	}

}