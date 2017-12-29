<?php
namespace Admin\Controller;
use Org\Net\UploadFile;

use Admin\Controller\CommonController;
use Think\Controller;
class SystemController extends CommonController {
	//公司介绍
	public function about(){
		$aboutM = M('About');
		if(I('get.dosubmit')){
			$aboutM->where(['ks'=>'about'])->delete();
			$data['ks']='about';
			$data['val']= htmlspecialchars_decode(html_entity_decode($_POST['content']));
			$is = $aboutM->add($data);
			if($is){
				$re = array("error"=>0,"msg"=>"保存成功");
			}else{
				$re = array("error"=>1,"msg"=>"保存失败");
			}
			echo json_encode($re);
			exit;
		}
		$old = $aboutM->where(['ks'=>'about'])->getField('val');
		$CK =  editor('content',$old,'Editor','99%','600');
		$this -> assign('fck',$CK);
		$this->display();
	}

	//联系我们
	public function contact(){
		$aboutM = M('About');
		if(I('get.dosubmit')){
			$aboutM->where(['ks'=>'lon'])->delete();
			$aboutM->where(['ks'=>'lat'])->delete();
			$aboutM->where(['ks'=>'address'])->delete();
			$aboutM->where(['ks'=>'phone'])->delete();
			$aboutM->where(['ks'=>'time'])->delete();
			foreach ($_POST as $k=>$v){
				$dataList[]=array("ks"=>$k,"val"=>$v);
			}
			$count = $aboutM->addAll($dataList);
			if($count>0){
				$re = array("error"=>0,"msg"=>"保存成功");
			}else{
				$re = array("error"=>1,"msg"=>"保存失败");
			}
			echo json_encode($re);
			exit;
		}
		$arr = $aboutM->select();
		foreach($arr as $k=>$v){
			$info[$v['ks']]=$v['val'];
		}
		$this->assign('info',$info);
		$this->display();
	}

	//使用帮助
	public function help(){
		$aboutM = M('About');
		if(I('get.dosubmit')){
			$aboutM->where(['ks'=>'help'])->delete();
			$data['ks']='help';
			$data['val']= htmlspecialchars_decode(html_entity_decode($_POST['content']));
			$is = $aboutM->add($data);
			if($is){
				$re = array("error"=>0,"msg"=>"保存成功");
			}else{
				$re = array("error"=>1,"msg"=>"保存失败");
			}
			echo json_encode($re);
			exit;
		}
		$old = $aboutM->where(['ks'=>'help'])->getField('val');
		$CK =  editor('content',$old,'Editor','99%','600');
		$this -> assign('fck',$CK);
		$this->display();
	}

	//网络服务条款
	public function terms(){
		$aboutM = M('About');
		if(I('get.dosubmit')){
			$aboutM->where(['ks'=>'terms'])->delete();
			$data['ks']='terms';
			$data['val']= htmlspecialchars_decode(html_entity_decode($_POST['content']));
			$is = $aboutM->add($data);
			if($is){
				$re = array("error"=>0,"msg"=>"保存成功");
			}else{
				$re = array("error"=>1,"msg"=>"保存失败");
			}
			echo json_encode($re);
			exit;
		}
		$old = $aboutM->where(['ks'=>'terms'])->getField('val');
		$CK =  editor('content',$old,'Editor','99%','600');
		$this -> assign('fck',$CK);
		$this->display();
	}

	//常见问题
	public function question(){
		$questionM = M('Question');
		$list = $questionM->order('ctime desc')->select();
		$num = $questionM->count();
		foreach($list as $k=>$v){
			$list[$k]['content']=msubstr($v['content'],0,50);
			$list[$k]['ctime']=date('Y-m-d H:i:s',$v['ctime']);
		}
		$this->assign('list',$list);
		$this->assign('num',$num);
		$this->display();
	}

	//添加问题
	public function add(){
		$questionM = M('Question');
		if(I('get.dosubmit')){
			$data['title']=$_POST['title'];
			$data['content']= htmlspecialchars_decode(html_entity_decode($_POST['content']));
			$data['ctime']=strtotime($_POST['ctime']);
			$is = $questionM->add($data);
			if($is){
				$re = array("error"=>0,"msg"=>"保存成功");
			}else{
				$re = array("error"=>1,"msg"=>"保存失败");
			}
			echo json_encode($re);
			exit;
		}
		$CK =  editor('content','','Editor','99%','600');
		$this -> assign('fck',$CK);
		$this->assign('now',date('Y-m-d H:i:s'));
		$this->display();
	}

	//编辑问题
	public function edit(){
		$questionM = M('Question');
		if(I('get.dosubmit')){
			$data['title']=$_POST['title'];
			$data['content']= htmlspecialchars_decode(html_entity_decode($_POST['content']));
			$data['ctime']=strtotime($_POST['ctime']);
			$is = $questionM->where(['id'=>$_POST['id']])->save($data);
			if($is){
				$re = array("error"=>0,"msg"=>"保存成功");
			}else{
				$re = array("error"=>1,"msg"=>"保存失败");
			}
			echo json_encode($re);
			exit;
		}
		$info = $questionM ->where(['id'=>$_GET['id']])->find();
		$info['ctime']=date('Y-m-d H:s:i',$info['ctime']);
		$CK =  editor('content',$info['content'],'Editor','99%','600');
		$this -> assign('fck',$CK);
		$this -> assign('info',$info);
		$this->display();
	}

	//删除问题
	public function delete(){
		$questionM = M('Question');
		$is = $questionM->where(['id'=>$_POST['id']])->delete();
		if($is){
			$re = array("error"=>0,"msg"=>"删除成功");
		}else{
			$re = array("error"=>1,"msg"=>"删除失败");
		}
		echo json_encode($re);
		exit;
	}

	//意见反馈
	public function feedback(){
		$feedsM = M('Feeds');
		$list = $feedsM->order('ctime desc')->select();
		$num = $feedsM->count();
		foreach($list as $k=>$v){
			$list[$k]['ctime']=date('Y-m-d H:i:s',$v['ctime']);
		}
		$this->assign('list',$list);
		$this->assign('num',$num);
		$this->display();
	}

	//banner管理
	public function banner(){
		$bannerM = M('Banners');
		$list = $bannerM->select();
		foreach ($list as $k=>$v){
			if($v['type']=='1'){
				$list[$k]['type']='PC端';
			}else{
				$list[$k]['type']='移动端';
			}
			if($v['display']=='1'){
				$list[$k]['display']='显示';
			}else{
				$list[$k]['display']='不显示';
			}
		}
		$num = $bannerM -> count();
		$this->assign('list',$list);
		$this->assign('num',$num);
		$this->display();
	}
	
	//banner添加
	public function addbanner(){
		$bannerM = M('Banners');
		if(I('get.dosubmit')){
			$data['type']=$_POST['type'];
			$data['img']=$_POST['img_name'];
			$data['sort']=$_POST['sort'];
			$data['url']=$_POST['url'];
			$data['display']=$_POST['display'];
			if(!isset($_POST['img_name'])||empty($_POST['img_name'])){
				$re = array("error"=>1,"msg"=>"请先上传图片");
				echo json_encode($re);
				exit();
			}
			$is = $bannerM->add($data);
			if($is){
				$re = array("error"=>0,"msg"=>"保存成功");
			}else{
				$re = array("error"=>1,"msg"=>"保存失败");
			}
			echo json_encode($re);
			exit;
		}
		$this->display();
	}
	
	//banner编辑
	public function editbanner(){
		$bannerM = M('Banners');
		if(I('get.dosubmit')){
			$data['type']=$_POST['type'];
			$data['img']=$_POST['img_name'];
			$data['sort']=$_POST['sort'];
			$data['url']=$_POST['url'];
			$data['display']=$_POST['display'];
			if(!isset($_POST['img_name'])||empty($_POST['img_name'])){
				$re = array("error"=>1,"msg"=>"请先上传图片");
				echo json_encode($re);
				exit();
			}
			$is = $bannerM->where(['id'=>$_POST['id']])->save($data);
			if($is){
				$re = array("error"=>0,"msg"=>"保存成功");
			}else{
				$re = array("error"=>1,"msg"=>"保存失败");
			}
			echo json_encode($re);
			exit;
		}
		$info = $bannerM->where(['id'=>$_GET['id']])->find();
		$this->assign('info',$info);
		$this->display();
	}
	
	//banner删除
	public function deletebanner(){
		$bannerM = M('Banners');
		$is = $bannerM->where(['id'=>$_POST['id']])->delete();
		if($is){
			$re = array("error"=>0,"msg"=>"删除成功");
		}else{
			$re = array("error"=>1,"msg"=>"删除失败");
		}
		echo json_encode($re);
		exit;
	}
	
	public function messages(){
		$this->display();
	}
	
	//异步图片上传
	public function ajaxImgUp(){
		//过滤检测
		$info = $this -> up();
		if($info[0][flag] != false){
			$info[0]['mes'] = '上传成功！';
			echo json_encode($info[0]);
		}else{
			$info[0]['mes'] = $info[0]['error'];
			echo json_encode($info[0]);
		}
	}
	
	//过滤检测函数
	private function up(){
		$upload=new UploadFile();
		$upload->maxSize= '10485760';  //10M //是指上传文件的大小，默认为-1,不限制上传文件大小bytes
		$upload->savePath='./Public/upload/banner/';       //上传保存到什么地方？路径建议大家已主文件平级目录或者平级目录的子目录来保存
		$upload->saveRule=time;    //上传文件的文件名保存规则  time uniqid  com_create_guid  uniqid
		$upload->saveExt = 'png';
		$upload->uploadReplace=true;     //如果存在同名文件是否进行覆盖
		$upload->allowExts=array('jpg','jpeg','png','gif');     //准许上传的文件后缀
		$upload->allowTypes=array('image/png','image/jpg','image/pjpeg','image/gif','image/jpeg');  //检测mime类型
	
		//upload()  如果上传成功，返回ture,失败返回false
	
		if($upload->upload()){
			//局部变量，你可以在此处，保存到一个超全局变量当中去进行返回
			$info=$upload->getUploadFileInfo();
			$info[0][flag] = true;
			return $info;
		}else{
			//是专门来获取上传的错误信息的
			$info[0][flag] = false;
			$info[0][error] = $upload->getErrorMsg();
			return $info;
		}
	}
}