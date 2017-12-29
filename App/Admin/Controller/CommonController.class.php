<?php
namespace Admin\Controller;
use Org\Util\Rbac;

use Think\Controller;
class CommonController extends Controller {
	function _initialize() {
		// 用户权限检查		
		if (C ( 'USER_AUTH_ON' ) && !in_array(MODULE_NAME,explode(',',C('NOT_AUTH_MODULE')))) {			
			if (! Rbac::AccessDecision ()) {
				//检查认证识别号
				if (! $_SESSION [C ( 'USER_AUTH_KEY' )]) {
					//跳转到认证网关
					redirect ( PHP_FILE . C ( 'USER_AUTH_GATEWAY' ) );
				}
				// 没有权限 抛出错误
				if (C ( 'RBAC_ERROR_PAGE' )) {
					// 定义权限错误页面
					redirect ( C ( 'RBAC_ERROR_PAGE' ) );
				} else {
					if (C ( 'GUEST_AUTH_ON' )) {
						$this->assign ( 'jumpUrl', PHP_FILE . C ( 'USER_AUTH_GATEWAY' ) );
					}
					// 提示错误信息
					$this->error ( L ( '_VALID_ACCESS_' ) ,U('Public/logout'));
				}
			}
		}
		
		//获取管理员信息
		$adminInfo = self::_userInfo();	
		$this->assign('adminInfo', $adminInfo);
		
		$system_setting=M('Setting');
		$setList = $system_setting->select();
		foreach ($setList as $k=>$v){
			$Sset[$v['key']]=$v['value'];
		}
		
		//检测是否登录超时
		if(isset($_SESSION['logintime'])&&$Sset['ADMIN_LOGIN_TIME']=='on'){
			self::checkAdminSession();
		}
		
		//记录操作日志
		if($Sset['ADMIN_LOG_OPEN']=='on'){
			self::manage_log();
		}
		
		//导航显示
		self::sidebar();
		
		//获取导航标签名称
		$nodeM = M('Node');
		$controllername = $nodeM->where(['name'=>CONTROLLER_NAME,'level'=>2])->getField('title');
		$controllerid= $nodeM->where(['name'=>CONTROLLER_NAME,'level'=>2])->getField('id');
		$actionname = $nodeM->where(['name'=>ACTION_NAME,'pid'=>$controllerid])->getField('title');
		$this->assign('controllername',$controllername);
		$this->assign('actionname',$actionname);
	}
	
	/**
	 * 设置登录超时
	 */
	public function checkAdminSession() {
		//设置超时为10分
		$nowtime = time();
		$s_time = $_SESSION['logintime'];
		if (($nowtime - $s_time) > 600) {
			unset($_SESSION[C('USER_AUTH_KEY')]);
			unset($_SESSION);
			session_destroy();
			$this->error('当前用户未登录或登录超时，请重新登录', U('Index/login'));
		} else {
			$_SESSION['logintime'] = $nowtime;
		}
	}

	/**
	 * 左边导航
	 */
	public function sidebar()
	{
		if(isset($_SESSION[C('USER_AUTH_KEY')])) {
			//显示菜单项
			$menu  = array();
			if(isset($_SESSION['menu'.$_SESSION[C('USER_AUTH_KEY')]])) {					
				//如果已经缓存，直接读取缓存
				$menu   =   $_SESSION['menu'.$_SESSION[C('USER_AUTH_KEY')]];
			}else {
				//读取数据库模块列表生成菜单项
				$node    =   M("Node");				
				$id	=	$node->getField("id");
				$where['level']=2;
				$where['status']=1;
				$where['pid']=$id;
				$where['display'] = 1;
				$list	=	$node->where($where)->field('id,name,title,sign')->order('sort asc')->select();
				$accessList = $_SESSION['_ACCESS_LIST'];	//在RBAC类里面获取的该用户的所有权限列表
				foreach($list as $key=>$module) {
					if(isset($accessList[strtoupper('ADMIN')][strtoupper($module['name'])]) || $_SESSION['administrator']) {
						//设置模块访问权限
						$module['access'] =   1;
						$menu[$key]  = $module;
						$selModel = explode('/', __ACTION__);
						$len = count($selModel);				
						if(ucfirst($selModel[$len-2]) == $module['name']){
							$menu[$key]['check'] = 1;
						}else{
							$menu[$key]['check'] = 0;
						}
						if($_SESSION['administrator']){
							$wherep['pid']=$module['id'];
							$wherep['display']=1;
							$str=$node->field('id,name,title,sign')->where($wherep)->order('sort asc')->select();
							foreach ($str as $k=>$val){
								if(ACTION_NAME == strtolower($val['name'])&&$menu[$key]['check']==1){
									$str[$k]['check']=1;
								}else{
									$str[$k]['check']=0;
								}									
							}
							$menu[$key][son] = $str;
						}else{
							//自己加的的部分level3
							$i = 0;
							foreach($accessList[strtoupper('ADMIN')][strtoupper($module['name'])] as $k => $val){
								$w['id']=$val;
								$w['status']=1;
								$w['display'] = 1;
								$listzi	=	$node->where($w)->field('id,name,title,sign')->order('sort asc')->limit(1)->select();
								if(ACTION_NAME == $listzi[0]['name']&&$menu[$key]['check']==1){
									$listzi[0]['check']=1;
								}else{
									$listzi[0]['check']=0;
								}
								if($listzi[0]['name']){
									$menu[$key][son][$i++] = $listzi[0];
								}
							}
						}
					}
				}
			}		
			$this->assign('menu',$menu);
		}
	}
			
	//关联到Adminr的index
	public function index() {
		//列表过滤器，生成查询Map对象
		$map = $this->_search ($map);
		if (method_exists ( $this, '_filter' )) {
			$this->_filter ( $map );	//用于查询
		}
		$name=$this->getControllerName();
		$model = D ($name);
		if (! empty ( $model )) {
			$this->_list ($model,$map);	//方法在下面
		}
		$this->display ();		
		return;
	}
	
	/**
     +----------------------------------------------------------
	 * 根据表单生成查询条件
	 * 进行列表过滤
     +----------------------------------------------------------
	 * @access protected
     +----------------------------------------------------------
	 * @param string $name 数据对象名称
     +----------------------------------------------------------
	 * @return HashMap
     +----------------------------------------------------------
	 * @throws ThinkExecption
     +----------------------------------------------------------
	 */
	protected function _search($name = '') {
		//生成查询条件
		if (empty ( $name )) {
			$name = $this->getControllerName();		//getActionName() --> Action方法里面的函数
		}
		$name=$this->getControllerName();
		$model = D ($name);
		$map = array ();
		foreach ( $model->getDbFields () as $key => $val ) {
			if (isset ( $_REQUEST [$val] ) && $_REQUEST [$val] != '') {
				$map [$val] = $_REQUEST [$val];
			}
		}
		return $map;

	}
	
	protected function getControllerName(){
		$group   =  defined('GROUP_NAME') && C('APP_GROUP_MODE')==0 ? GROUP_NAME.'/' : '';
		return $group.MODULE_NAME;
	}
		
	
	/**
	 * 
	 +--------
	 *查询出用户的基本资料然后存入session
	 +--------
	 * 
	 */
	function _userInfo(){
		$id = $_SESSION[authId];
		$user = M("Admin");
		$arr = $user -> field("id,username,nickname,login_count,register_time,last_login_time,last_login_ip") -> where("id=".$id) -> select();
		
		return $arr[0];
	}

	/**
	 * 记录日志
	 */
	final private function manage_log(){
		//判断是否记录
		$action = ACTION_NAME;
		if($action == '' || strchr($action,'public') || (CONTROLLER_NAME =='Index' && in_array($action, array('login','code'))) ||  CONTROLLER_NAME =='Upload') {
			return false;
		}else {
			$ip = get_client_ip();
			$username = cookie('username');
			$userid = session(C('USER_AUTH_KEY'));
			$time = time();
			$data = array('GET'=>$_GET);
			if(IS_POST) $data['POST'] = $_POST;
			$data_json = json_encode($data);
			$log_db = M('log');
			$node_db = M('node');
			$findc['name']=CONTROLLER_NAME;
			$findc['level']=2;
			$c = $node_db->field('id,title')->where($findc)->find();
			$controller_name=$c['title'];
			$finda['name']=ACTION_NAME;
			$finda['pid']==$c['id'];
			$finda['level']=3;
			$a = $node_db->field('title')->where($finda)->find();
			$action_name=$a['title'];
			//$log_db->add(array('username'=>$username,'userid'=>$userid,'controller'=>CONTROLLER_NAME,'action'=>ACTION_NAME, 'querystring'=>$data_json,'time'=>$time,'ip'=>$ip));
			$log_db->add(array('type'=>2,'username'=>$username,'user_id'=>$userid,'controller'=>$controller_name,'action'=>$action_name, 'querystring'=>$data_json,'time'=>$time,'ip'=>$ip));
		}
	}
	
	/**
	 * 空操作，用于输出404页面
	 */
	public function _empty(){
		//针对后台ajax请求特殊处理
		if(!IS_AJAX) send_http_status(404);
		if (IS_AJAX && IS_POST){
			$data = array('info'=>'请求地址不存在或已经删除', 'status'=>0, 'total'=>0, 'rows'=>array());
			$this->ajaxReturn($data);
		}else{
			$this->display('Public:404');
		}
	}
	
}
?>