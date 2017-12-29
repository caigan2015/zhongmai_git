<?php
namespace Admin\Controller;

use Org\Net\UploadFile;

use Think\Upload;

use Org\Util\Rbac;

use Think\Image;

use Think\Controller;

class PublicController extends Controller{
	/**
	 * 检查用户是否登录
	 */
	protected function checkUser() {
		if(!isset($_SESSION[C('USER_AUTH_KEY')])) {
			$this->assign('jumpUrl',U('Public/login'));
			$this->error('没有登录');
		}
	}		
	
	/**
	 * 用户登录页面
	 */
	public function login() {
		if(!isset($_SESSION[C('USER_AUTH_KEY')])) {
			$this->display('login');
		}else{
			$this->redirect(U('Index/index'));
		}
	}
	
	/**
	 * 退出登录
	 */
	public function logout() {
		if(isset($_SESSION[C('USER_AUTH_KEY')])) {
			unset($_SESSION[C('USER_AUTH_KEY')]);
			unset($_SESSION);
			cookie('username', null);
			session_destroy();
			$this->success('安全退出！', U('Index/login'));
		}else {
			$this->error('已经登出！');
		}
	}
	
	/**
	 * 跳转到后台首页
	 */
	public function index()
	{
		//如果通过认证跳转到首页
		redirect(U('Index/index'));
	}
	
	/**
	 * 欢迎页
	 */
	public function welcome()
	{
		$user = M("Admin");
		$info = array(
				'操作系统'=>PHP_OS,
				'运行环境'=>$_SERVER["SERVER_SOFTWARE"],
				'主机名'=>$_SERVER['SERVER_NAME'],
				'WEB服务端口'=>$_SERVER['SERVER_PORT'],
				'网站文档目录'=>$_SERVER["DOCUMENT_ROOT"],
				'浏览器信息'=>substr($_SERVER['HTTP_USER_AGENT'], 0, 40),
				'通信协议'=>$_SERVER['SERVER_PROTOCOL'],
				'请求方法'=>$_SERVER['REQUEST_METHOD'],
				'ThinkPHP版本'=>THINK_VERSION,
				'上传附件限制'=>ini_get('upload_max_filesize'),
				'执行时间限制'=>ini_get('max_execution_time').'秒',
				'服务器时间'=>date("Y年n月j日 H:i:s"),
				'北京时间'=>gmdate("Y年n月j日 H:i:s",time()+8*3600),
				'服务器域名'=>$_SERVER['SERVER_NAME'].' [ '.gethostbyname($_SERVER['SERVER_NAME']).' ]',
				'服务器端口'=>$_SERVER['SERVER_PORT'],
				'用户的IP地址'=>$_SERVER['REMOTE_ADDR'],
				'剩余空间'=>round((disk_free_space(".")/(1024*1024)),2).'M',
		);
		$this->assign('info',$info);
		$id = $_SESSION[authId];
		$arr = $user -> field("id,username,nickname,login_count,register_time,last_login_time,last_login_ip") -> where("id=".$id) -> select();
		$this->assign('userInfo', $arr[0]);
		$sysinfo = \Admin\Common\Sysinfo::getinfo();
		$os = explode(' ', php_uname());
		//网络使用状况
		$net_state = null;
		if ($sysinfo['sysReShow'] == 'show' && false !== ($strs = @file("/proc/net/dev"))){
			for ($i = 2; $i < count($strs); $i++ ){
				preg_match_all( "/([^\s]+):[\s]{0,}(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)/", $strs[$i], $info );
				$net_state.="{$info[1][0]} : 已接收 : <font color=\"#CC0000\"><span id=\"NetInput{$i}\">".$sysinfo['NetInput'.$i]."</span></font> GB &nbsp;&nbsp;&nbsp;&nbsp;已发送 : <font color=\"#CC0000\"><span id=\"NetOut{$i}\">".$sysinfo['NetOut'.$i]."</span></font> GB <br />";
			}
		}
		$this->assign('sysinfo',$sysinfo);
		$this->assign('os',$os);
		$this->assign('net_state',$net_state);
		$this->display('welcome');
	}
	
	/**
     * 验证码
     */
    public function code(){
    	$verify = new \Think\Verify();
    	$verify->useCurve = false;
    	$verify->useNoise = false;
    	$verify->bg = array(255, 255, 255);
    
    	if (I('get.code_len')) $verify->length = intval(I('get.code_len'));
    	if ($verify->length > 8 || $verify->length < 2) $verify->length = 4;
    
    	if (I('get.font_size')) $verify->fontSize = intval(I('get.font_size'));
    
    	if (I('get.width')) $verify->imageW = intval(I('get.width'));
    	if ($verify->imageW <= 0) $verify->imageW = 130;
    
    	if (I('get.height')) $verify->imageH = intval(I('get.height'));
    	if ($verify->imageH <= 0) $verify->imageH = 50;
    
    	$verify->entry('admin');
    }
    
    /**
     * 登陆检测
     */
    function checkLogin(){ 
    	$username = I('post.username', '', 'trim') ? I('post.username', '', 'trim') : $this->error('用户名不能为空', HTTP_REFERER);
    	$password = I('post.password', '', 'trim') ? I('post.password', '', 'trim') : $this->error('密码不能为空', HTTP_REFERER);
    	//验证码判断
    	$code = I('post.verify', '', 'trim') ? I('post.verify', '', 'trim') : $this->error('请输入验证码', HTTP_REFERER);
    	//生成认证条件
    	$map            =   array();
    	// 支持使用绑定帐号登录
    	$map['username']	= $username;
    	$map['status']	=	array('eq',1);
    	if(!check_verify($code, 'admin')) $this->error('验证码错误！', HTTP_REFERER);
    	
    	$authInfo = Rbac::authenticate($map);
    	//使用用户名、密码和状态的方式进行认证
    	//修改 FALSE 改为 NULL
    	if( NULL === $authInfo) {
    		$this->error('帐号不存在或已禁用！');
    	}else {
    		if($authInfo['password'] != md5($password)) {
    			$this->error('密码错误！');
    		}
    		$_SESSION[C('USER_AUTH_KEY')]	=	$authInfo['id'];
    		$_SESSION['lastLoginTime']		=	$authInfo['last_login_time'];
    		$_SESSION['login_count']	=	$authInfo['login_count'];
    		cookie('username', $username);
    		if($authInfo['username']=='rootadmin') {
    			$_SESSION['administrator']		=	true;
    		}
    		//保存登录信息,可用于登陆的时候更新数据
    		$Admin	=	M('Admin');
    		$ip		=	get_client_ip();
    		$time	=	time();
    		$data = array();
    		$data['id']	=	$authInfo['id'];
    		$data['last_login_time']	=	$time;
    		$data['login_count']	=	array('exp','login_count+1');
    		$data['last_login_ip']	=	$ip;
    		$Admin->save($data);
    		//保存登录时间
    		$_SESSION['logintime']=$time;
    		// 缓存访问权限
    		RBAC::saveAccessList();
    		$this->success('登录成功', U('Index/index'));
    	}
    }

	/**
	 *
	 */
	public function saw_xin(){
		$userXinDB = M('UserXin');
		$where['uid']=$_GET['id'];
		$xin = $userXinDB->where($where)->find();
		$xin['widName'] = $xin['wid']?C('WORK')[$xin['wid']]:'暂无设置';
		$xin['pidName'] = $xin['pid']?C('PAY')[$xin['pid']]:'暂无设置';
		$xin['sidName'] = $xin['sid']?C('BAO')[$xin['sid']]:'暂无设置';
		$xin['gidName'] = $xin['gid']?C('JIN')[$xin['gid']]:'暂无设置';
		$xin['xidName'] = $xin['xid']?C('XIN')[$xin['xid']]:'暂无设置';
		$xin['zidName'] = $xin['zid']?C('ZHU')[$xin['zid']]:'暂无设置';
		//print_r($xin);
		$this->assign('xin',$xin);
		$this->display();
	}
    
    /**
     *	作用：ajax查找模块
     */
    function findmodel(){
    	$flag = 0;
		$str = '';
    	$str.='<div class="row cl">
					<label class="form-label col-xs-4 col-sm-3">下级模块：</label>
					<div class="formControls col-xs-8 col-sm-9"> 
						<span class="select-box" style="width:150px;">
						<select class="select" name="pid" size="1">';
    	if($_GET['id']=='2'){
    		$str.='<option value="1"> 项目运用   </option>';
    	}else{
    		$node=M('Node');
    		$where['level']=2;
    		$m=$node->field('id,name,title')->where($where)->select();
    		foreach ($m as $val){
    			$str.='<option value="'.$val['id'].'"> '.$val['title'].'('.$val['name'].') </option>';
    		}
    		if(empty($m)){
    			$flag = 1;
    		}
    	}
    		$str.='</select>
				</span> 
			</div>
		</div>';
    	if($flag==0){
    		echo $str;
    	}else{
    		echo "0";
    	}
    }
    
    //异步图片上传
    function ajaxImgUp(){
    	$file = M('File');
    	//过滤检测
    	if(I('get.dologo')){
    		$info = $this -> uplogo();
    	}else{
    		$info = $this -> up();
    	}
    	if($info[0][flag] != false){
    		$info[0]['mes'] = '上传成功！';
    		echo json_encode($info[0]);
    	}else{
    		$info[0]['mes'] = $info[0]['error'];
    		echo json_encode($info[0]);
    	}
    }
    
    //异步附件上传
    function ajaxAttachUp(){
    	$file = M('File');
    	//过滤检测
    	$info = $this -> upAttach();
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
    	$upload->savePath='./Public/upload/img/';       //上传保存到什么地方？路径建议大家已主文件平级目录或者平级目录的子目录来保存
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
    
    		//生成等比例的缩略图
    		$oldName='./Public/upload/img/'.$info[0][savename];
    		$img = new Image();
    		$img->open($oldName);
    		$width = $img->width(); // 返回图片的宽度
    		$height = $img->height(); // 返回图片的高度
    		$img_type = $img->type();
    		$thumb_min_name= UPLOAD_PATH .'img/s_'.$info[0][savename];
    		$s_w=$width*0.3;
    		$s_h=$height*0.3;
    		$img->thumb($s_w, $s_h)->save($thumb_min_name);
    		return $info;
    	}else{
    		//是专门来获取上传的错误信息的
    		$info[0][flag] = false;
    		$info[0][error] = $upload->getErrorMsg();
    		return $info;
    	}
    }
    
    private function uplogo(){
    	$upload=new UploadFile();
    	$upload->maxSize= '10485760';  //10M //是指上传文件的大小，默认为-1,不限制上传文件大小bytes
    	$upload->savePath='./Public/upload/img/';       //上传保存到什么地方？路径建议大家已主文件平级目录或者平级目录的子目录来保存
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
    
    		//生成等比例的缩略图
    		$oldName='./Public/upload/img/'.$info[0][savename];
    		$img = new Image();
    		$img->open($oldName);
    		$width = $img->width(); // 返回图片的宽度
    		$height = $img->height(); // 返回图片的高度
    		$img_type = $img->type();
    		$thumb_min_name= UPLOAD_PATH .'img/s_'.$info[0][savename];
    		$s_w=$width*0.3;
    		$s_h=$height*0.3;
    		$img->thumb($s_w, $s_h)->save($thumb_min_name);
    		return $info;
    	}else{
    		//是专门来获取上传的错误信息的
    		$info[0][flag] = false;
    		$info[0][error] = $upload->getErrorMsg();
    		return $info;
    	}
    }
    
    //过滤检测函数
    private function upAttach(){
    	$upload=new UploadFile();
    	$upload->maxSize= '10485760';//10485760';  //10M //是指上传文件的大小，默认为-1,不限制上传文件大小bytes
    	$upload->savePath='./Public/upload/file/';       //上传保存到什么地方？路径建议大家已主文件平级目录或者平级目录的子目录来保存
    	$upload->saveRule=time;    //上传文件的文件名保存规则  time uniqid  com_create_guid  uniqid
    	$upload->uploadReplace=true;     //如果存在同名文件是否进行覆盖
    	$upload->allowExts=array('txt','ppt','xlsx','docx','pptx','png','gif','jpeg','jpg','doc','rar','xls','zip','gz');     //准许上传的文件后缀
    	$upload->allowTypes=array('text/plain','application/vnd.openxmlformats-officedocument.wordprocessingml.document','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','application/vnd.openxmlformats-officedocument.presentationml.presentation','application/vnd.ms-powerpoint','image/png','image/pjpeg','application/msword','image/x-png','image/x-png','image/gif','image/jpeg','application/vnd.ms-excel','application/octet-stream','application/zip','application/x-gzip','application/x-zip-compressed');  //检测mime类型
    
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