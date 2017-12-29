<?php
namespace Admin\Controller;
use Admin\Controller\CommonController;
use Think\Controller;
class ContentController extends CommonController {
	//资讯管理
	public function index(){
		$contentM = M('Content');
		$file = M('File');
		$column = M('Column');
		
		if(I('get.dosubmit')){
			if($_POST['c_id'] != 0){
				$where['c_id']=$_POST['c_id'];
				$this->assign('c_id',$_POST['c_id']);
			}
			
			if($_POST['status'] != ''){
				$where['status']=$_POST['status'];
				$this->assign('status',$_POST['status']);
			}
			
			if($_POST['stime'] != ''){
				$where['date_time']=array('egt',strtotime($_POST['stime']));
				$this->assign('stime',$_POST['stime']);
			}
			
			if($_POST['etime'] != ''){
				$where['date_time']=array('elt',strtotime($_POST['etime']));
				$this->assign('etime',$_POST['etime']);
			}
			
			if($_POST['title'] != ''){
				$where['title']=array('like',"%{$_POST['title']}%");
				$this->assign('title',$_POST['title']);
			}
		}
		
		$list = $contentM -> where($where) -> order('date_time desc') -> select();
		foreach($list as $k => $v){
			switch ($v['status']){
				case 0:$list[$k]['t']='<span class="label label-default radius">待审核</span>';break;
				case 1:$list[$k]['t']='<span class="label label-success radius">已发布</span>';break;
				case 2:$list[$k]['t']='<span class="label label-danger radius">未通过</span>';break;
				case -1:$list[$k]['t']='<span class="label label-defaunt radius">已下架</span>';break;
			}
			$cname=$column -> where('id='.$v[c_id]) ->  getField('name');
			$list[$k][cname] = $cname?$cname:'已删除';
			$list[$k][img] = $file -> where('type=1 and n_id='.$v[id]) ->  select();
			$list[$k][attach] = $file -> where('type=2 and n_id='.$v[id]) ->  select();
		}
		$num = $contentM -> where($where) -> count();
		$this->assign('list',$list);
		$this->assign('num',$num);
		
		//获取栏目
		$clist = $column->where(['level'=>1,'type'=>1])->order('sort asc')->select();
		foreach ($clist as $k=>$v){
			$zi = $column->where(['level'=>2,'type'=>1,'pid'=>$v['id']])->order('sort asc')->select();
			$clist[$k]['zi']=$zi;
		}
		$this->assign('clist',$clist);
		
		$this->display();
	}
	
	//资讯添加
	public function addinfo(){
		if(I('get.dosubmit')){
			$base = D('Content');
			$file = M('File');
		
			$flag = 1;
			//不开启表单令牌验证，但是用这个方法可以进行数据的自动获取和自动验证
			if(!$base -> create()){
				$flag = 0;
				//$this -> error($base -> getError());
				$re = array("error"=>0,"msg"=>$base -> getError());
				echo json_encode($re);
				exit();
			}
			
			$columnM = M('Column');
			$pid = $columnM->where(['id'=>$_POST['c_id']])->getField('pid');
			$base -> b_id = $pid?$pid:0;
			$base -> date_time = strtotime($_POST[date_time]);
			$base -> content = htmlspecialchars_decode(html_entity_decode($_POST['content']));
			if(!$id = $base -> add()){
				$flag = 0;
			}
				
			if($_POST[img_name]){
				for($i=0;$i<count($_POST[img_name]);$i++){
					if($_POST[img_name][$i] != 'undefined'){
						$data[type] = 1;
						$data[n_id] = $id;
						$data[size] = $_POST[img_size][$i];
						$data[title] = $_POST[img_title][$i];
						$data[name] = $_POST[img_name][$i];
						if(!$file -> add($data)){
							$flag = 0;
						}
					}
				}
			}
				
			if($_POST[attach_name]){
				for($i=0;$i<count($_POST[attach_name]);$i++){
					if($_POST[attach_name][$i] != 'undefined'){
						$data[type] = 2;
						$data[n_id] = $id;
						$data[size] = $_POST[attach_size][$i];
						$data[title] = $_POST[attach_title][$i];
						$data[name] = $_POST[attach_name][$i];
						if(!$file -> add($data)){
							$flag = 0;
						}
					}
				}
			}
				
			if($flag == 1){
				$re = array("error"=>0,"msg"=>"保存成功");
			}else{
				$re = array("error"=>1,"msg"=>"保存失败");
			}
			echo json_encode($re);
			exit;
		}
		
		$columnM = M('Column');
		$CK =  editor('content','','Editor','99%','400');
		$this -> assign('fck',$CK);
		$clist = $columnM->where(['level'=>1,'type'=>1])->order('sort asc')->select();
		foreach ($clist as $k=>$v){
			$zi = $columnM->where(['level'=>2,'type'=>1,'pid'=>$v['id']])->order('sort asc')->select();
			$clist[$k]['zi']=$zi;
		}
		$this->assign('clist',$clist);
		$this->assign('now',date('Y-m-d H:i:s'));
		$user = M("Admin");
		$id = $_SESSION[authId];
		$author = $user -> where("id=".$id) -> getField('nickname');
		$this->assign('author',$author);
		$this->display();
	}
	
	//资讯编辑
	public function editinfo(){
		if(I('get.dosubmit')){
			$base = D('Content');
			$file = M('File');
			if($base -> create()){
				$id = $_POST[id];
				$file = M('File');
				$file -> where('n_id='.$id) -> delete(); //先删掉再重新添加，这样做逻辑简单
				$flag = 1;
				//不开启表单令牌验证，但是用这个方法可以进行数据的自动获取和自动验证
				if(!$base -> create()){
					$flag = 0;
					$this -> error($base -> getError());
					exit();
				}
				if(!isset($_POST['tui'])){
					$base ->tui = 0;
				}
				if(!isset($_POST['new'])){
					$base ->new = 0;
				}
				if(!isset($_POST['advert'])){
					$base ->advert = 0;
				}
				if(!isset($_POST['activity'])){
					$base -> activity = 0;
				}
				$columnM = M('Column');
				$pid = $columnM->where(['id'=>$_POST['c_id']])->getField('pid');
				$base -> b_id = $pid?$pid:0;
				$base -> date_time = strtotime($_POST[date_time]);				
				$base -> content = htmlspecialchars_decode(html_entity_decode($_POST['content']));			
				if(!$base -> save()){
		
					$flag = 0;
				}
		
				if($_POST[img_name]){
					$flag = 1;
					for($i=0;$i<count($_POST[img_name]);$i++){
						if($_POST[img_name][$i] != 'undefined'){
							$data[type] = 1;
							$data[n_id] = $id;
							$data[title] = $_POST[img_title][$i];
							$data[name] = $_POST[img_name][$i];
							if(!$file -> add($data)){
								$flag = 0;
							}
						}
					}
				}
		
				if($_POST[attach_name]){
					$flag = 1;
					for($i=0;$i<count($_POST[attach_name]);$i++){
						if($_POST[attach_name][$i] != 'undefined'){
							$data[type] = 2;
							$data[n_id] = $id;
							$data[title] = $_POST[attach_title][$i];
							$data[name] = $_POST[attach_name][$i];
							if(!$file -> add($data)){
								$flag = 0;
							}
						}
					}
				}
				if($flag == 1){
					$re = array("error"=>0,"msg"=>"保存成功");
				}else{
					$re = array("error"=>1,"msg"=>"保存失败");
				}
			}else{
				$re = array("error"=>1,"msg"=>$base -> getError());
			}
			echo json_encode($re);
			exit;
		}
		
		$content=M('Content');
		$file = M('File');
		$con = $content -> where('id='.$_GET[id]) -> find();
		$imgs = $file -> where('type=1 and n_id='.$_GET[id]) -> select();
		$attachs = $file -> where('type=2 and n_id='.$_GET[id]) -> select();
		$columnM = M('Column');
		$CK =  editor('content',$con['content'],'Editor','99%','400');
		$this -> assign('fck',$CK);
		$this -> assign('con',$con);
		$this -> assign('attachs',$attachs);
		$this -> assign('imgs',$imgs);
		$clist = $columnM->where(['level'=>1,'type'=>1])->order('sort asc')->select();
		foreach ($clist as $k=>$v){
			$zi = $columnM->where(['level'=>2,'type'=>1,'pid'=>$v['id']])->order('sort asc')->select();
			$clist[$k]['zi']=$zi;
		}
		$this->assign('clist',$clist);
		$this->display();
	}
	
	//资讯删除
	public function deleteinfo(){
		$content=M('Content');
		$where['id']=$_POST['id'];
		$con=$content->where($where)->select();
		$file=M('File');
		$where_f['n_id']=$con[0]['id'];
		$f=$file->where($where_f)->select();
		$flag = 1;
		if(!empty($f)){
			foreach ($f as $val){
				if($val['type']=='1'){
					$o_name=$val['name'];
					$val['name']='./Public/upload/img/'.$val['name'];
					$s_name='./Public/upload/img/s_'.$o_name;
				}else{
					$val['name']='./Public/upload/file/'.$val['name'];
				}
				$del['f_id']=$val['f_id'];
				if($file -> where($del) -> delete()){					
					if(file_exists($val['name'])){
						unlink($val['name']);
						if($val['type']=='1'){
							unlink($s_name);
						}
					}else{
						$flag = 0;
					}
				}else{
					$re = array("error"=>1,"msg"=>"删除失败");
					echo json_encode($re);
					exit;
				}
			}
			if($flag != 0){
				if($content->where($where)->delete()){
					$re = array("error"=>0,"msg"=>"删除成功");
				}else{
					$re = array("error"=>1,"msg"=>"删除失败");
				}
			}else{
				$re = array("error"=>1,"msg"=>"删除失败");
			}
			echo json_encode($re);
			exit;
		}else{
			if($content->where($where)->delete()){
				$re = array("error"=>0,"msg"=>"删除成功");
			}else{
				$re = array("error"=>1,"msg"=>"删除失败");
			}
			echo json_encode($re);
			exit;
		}
	}
	
	//资讯审核
	public function auditinfo(){
		$contentM = M('Content');
		$where['id']=$_POST['id'];
		$data['status']=$_POST['status'];
		$data['audit_time']=time();
		$contentM->where($where)->save($data);
		echo "yes";
	}
	
	//栏目管理
	public function column(){
		$columnM = M('Column');
		if(I('get.dosubmit')&&!empty($_POST['key'])){
			$condition['id'] = $_POST['key'];
			$condition['name'] = array('like',"%{$_POST['key']}%");
			$condition['_logic'] = 'OR';
			$map['_complex'] = $condition;
			$map['type']  = 1;
			$list = $columnM->where($map)->order('sort asc')->select();
			$num = $columnM->where($map)->count();
			$this->assign('num',$num);
			$this->assign('list',$list);
			$this->assign('key',$_POST['key']);
		}else{
			$list = $columnM->where(['level'=>1,'type'=>1])->order('sort asc')->select();
			foreach ($list as $k=>$v){
				$zi = $columnM->where(['level'=>2,'type'=>1,'pid'=>$v['id']])->order('sort asc')->select();
				$list[$k]['zi']=$zi;
			}
			$this->assign('list',$list);
			$num = $columnM->where(['type'=>1])->count();
			$this->assign('num',$num);
		}
		$this->display();
	}
	
	//栏目添加
	public function addcolumn(){
		$columnM = D('Column');		
		if(I('get.dosubmit')){
			if(!$columnM->create()) {
				$re = array("error"=>1,"msg"=>$columnM -> getError());
			}else{
				if($_POST['pid']!='0'){
					$columnM->level=2;
				}
				$result	 =	 $columnM->add();
				if($result) {
					$re = array("error"=>0,"msg"=>"添加成功");
				}else{
					$re = array("error"=>1,"msg"=>"添加失败");
				}
			}
			echo json_encode($re);
			exit;
		}		
		$column = $columnM->where(['level'=>1,'type'=>1,'display'=>1])->select();
		$this->assign('column',$column);
		$this->display();
	}
	
	//栏目编辑
	public function editcolumn(){
		$columnM = D('Column');
		
		if(I('get.dosubmit')){
			if(!$columnM->create()) {
				$re = array("error"=>1,"msg"=>$columnM -> getError());
			}else{
				$xiu['id']=$_POST['id'];
				$result	 =	 $columnM ->where($xiu) -> save();
				if($result) {
					$re = array("error"=>0,"msg"=>"修改成功");
				}else{
					$re = array("error"=>1,"msg"=>"修改失败");
				}
			}
			echo json_encode($re);
			exit;
		}
		
		$info = $columnM->where(['id'=>$_GET['id']])->find();
		$this->assign('info',$info);
		$column = $columnM->where(['level'=>1,'type'=>1,'display'=>1])->select();
		$this->assign('column',$column);
		$this->display();
	}
	
	//栏目删除
	public function deletecolumn(){
		$columnM = M('Column');
		$where['id'] = $_POST['id'];
		$pid=$columnM->field('id,pid')->where($where)->find();
		$count = $columnM->where(['pid'=>$_POST['id']])->count();
		if($pid['pid']=='0'&&$count>0){
			$p['pid']=$pid['id'];
			if($columnM -> where($p) -> delete()) {
				$w['id']=$pid['id'];
				if($columnM -> where($w) -> delete()) {
					$re = array("error"=>0,"msg"=>"删除成功");
				}else{
					$re = array("error"=>1,"msg"=>"删除失败");
				}
			}else {
				$re = array("error"=>1,"msg"=>"删除失败");
			}
		}else{
			if($columnM -> where($where) -> delete()) {
				$re = array("error"=>0,"msg"=>"删除成功");
			}else {
				$re = array("error"=>1,"msg"=>"删除失败");
			}
		}
		echo json_encode($re);
		exit;
	}
	
	//导航管理
	public function link(){
		$columnM = M('Column');
		$find['level']=1;
		$find['display']=1;
		$find['url']=array('neq','');
		$list = $columnM->where($find)->order('sort asc')->select();
		foreach ($list as $k=>$v){
			$zi = $columnM->where(['level'=>2,'pid'=>$v['id'],'url'=>array('neq','')])->order('sort asc')->select();
			$list[$k]['zi']=$zi;
		}
		$this->assign('list',$list);
		$num = $columnM->where(['display'=>1,'url'=>array('neq','')])->count();
		$this->assign('num',$num);
		$this->display();
	}
	
	//导航添加
	public function addlink(){
		$columnM = M('Column');
		
		if(I('get.dosubmit')){
			if($_POST['type']=='1'){
				$data['url']=$_POST['url'];
				$data['jump']=$_POST['jump'];
				$where['id']=$_POST['cid'];
				$ourl = $columnM->where($where)->getField('url');
				if($_POST['cid']=='0'){
					$re = array("error"=>1,"msg"=>"请选择栏目");
					echo json_encode($re);
					exit;
				}
				if(!empty($ourl)){
					$re = array("error"=>1,"msg"=>"已存在该导航");
					echo json_encode($re);
					exit;
				}
				$is = $columnM->where($where)->save($data);
				if($is){
					$columnM->where(['pid'=>$_POST['cid']])->save($data);
					$re = array("error"=>0,"msg"=>"添加成功");
				}else{
					$re = array("error"=>1,"msg"=>"添加失败");
				}
			}else{
				if(!empty($_POST['ocid'])){
					$data['pid']=$_POST['ocid'];
					$data['level']=2;
				}
				$data['type']=$_POST['type'];
				$data['name']=$_POST['name'];
				$data['url']=$_POST['url'];
				$data['jump']=$_POST['jump'];
				$data['display']=1;
				$is=$columnM->add($data);
				if($is){
					$re = array("error"=>0,"msg"=>"添加成功");
				}else{
					$re = array("error"=>1,"msg"=>"添加失败");
				}
			}
			echo json_encode($re);
			exit;
		}
		
		//站内栏目
		$find['level']=1;
		$find['type']=1;
		$find['display']=1;
		//$find['url']=array('exp','is NULL');
		$column = $columnM->where($find)->select();
		foreach ($column as $k1=>$v1){
			$findz['level']=2;
			$findz['pid']=$v1['id'];
			$findz['display']=1;
			$zi = $columnM->where($findz)->select();
			$column[$k1]['zi']=$zi;
		}
		$this->assign('column',$column);
		
		//站外栏目
		$ofind['level']=1;
		$ofind['type']=2;
		$ofind['display']=1;
		$ocolumn = $columnM->where($ofind)->select();
		$this->assign('ocolumn',$ocolumn);
		
		$this->display();
	}
	
	//导航编辑
	public function editlink(){
		$columnM = M('Column');
		
		if(I('get.dosubmit')){
			if($_POST['type']!='1'){
				$data['name']=$_POST['name'];
			}
			$data['url']=$_POST['url'];
			$data['jump']=$_POST['jump'];
			$where['id']=$_POST['id'];
			$is = $columnM->where($where)->save($data);
			if($is){
				$re = array("error"=>0,"msg"=>"修改成功");
			}else{
				$re = array("error"=>1,"msg"=>"修改失败");
			}
			echo json_encode($re);
			exit;
		}
		
		$info = $columnM->where(['id'=>$_GET['id']])->find();
		$this->assign('info',$info);
		$this->display();
	}
	
	//导航删除
	public function deletelink(){
		$columnM = M('Column');
		$type = $columnM->where(['id'=>$_POST['id']])->getField('type');
		$level = $columnM->where(['id'=>$_POST['id']])->getField('level');
		if($type=='1'){
			$data['url']='';
			$where['id']=$_POST['id'];
			$is = $columnM->where($where)->save($data);
			if($is&&$level>1){
				$dataz['url']='';
				$columnM->where(['pid'=>$_POST['id']])->save($dataz);
			}
		}else{
			$where['id']=$_POST['id'];
			$is = $columnM->where($where)->delete();
		}
		if($is){
			$re = array("error"=>0,"msg"=>"删除成功");
		}else{
			$re = array("error"=>1,"msg"=>"删除失败");
		}
		echo json_encode($re);
		exit;
	}
	
	//ajax设置地址
	public function seturl(){
		$columnM = M('Column');
		$name = $columnM->where(['id'=>$_POST['id']])->getField('name');
		$url = 'News/info';
		$re = array("name"=>$name,"url"=>$url);
		echo json_encode($re);
	}
	
	//导航设置
	public function setlink(){
		$columnM = M('Column');
		
		if(I('get.dosubmit')){
			$ids = $_POST['ids'];
			$list = explode(',', $ids);
			$i = 1;
			foreach ($list as $k=>$v){
				$where['id']=$v;
				$data['sort']=$i++;
				$columnM->where($where)->save($data);
			}
			echo "yes";exit;
		}
		
		$find['level']=1;
		$find['display']=1;
		$find['url']=array('neq','');
		$column = $columnM->where($find)->order('sort asc')->select();
		$this->assign('column',$column);
		$this->display();
	}
}