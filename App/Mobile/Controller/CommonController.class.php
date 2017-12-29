<?php
namespace Mobile\Controller;
use Think\Controller;
use Mobile\Common\Basic;

class CommonController extends Controller {
	public $user;
	function _initialize(){
		if(IS_AJAX && IS_GET) C('DEFAULT_AJAX_RETURN', 'html');
		self::check_login();
		//检测是否登录超时
		if(isset($_SESSION['logintime'])){
			self::checkUserSession();
		}
		$inde = self::isMobile();
		if(!$inde){
			$this->redirect('Home/Index/index');
			exit;
		}else{
			$cbasic = new Basic();
			if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger' ) !== false ) {
				$openid = session("openid");
				if(!$openid){
					if (!isset($_GET['code'])) {
						$url = 'http://'.SITE_URL.'/index.php?m=Mobile&c=Index&a=index';
						$return_url = $cbasic->get_authorize_url($url, "123");
						header("Location: {$return_url}");
					} else {
						$arr = $cbasic->getOpenId($_GET['code']);
						$openid = $arr['openid'];
						$access_token = $arr['access_token'];
						session("openid", $openid);
						session("access_token", $access_token);
					}
				}
			}
			
			if(session("openid")&&session('user')){
				$user = M('User');
				$data['openId']=session("openid");
				$uInfo = session('user');
				$user->where(['id'=>$uInfo['id']])->save($data);
			}
		}
	}

	/**
	 * 判断用户是否已经登陆
	 */
	final public function check_login() {
		if($this->_check_login(CONTROLLER_NAME,ACTION_NAME)) {
			if(!session('user')){
				$this->redirect('Mobile/User/login');
			}else{
				//获取用户基本信息
				$userInfo = session('user');
				$this->user = $userInfo;
				return true;
			}
		}else{
			return true;
		}
	}

	/**
	 * 设置登录超时
	 */
	public function checkUserSession() {
		//设置超时为10分
		$nowtime = time();
		$s_time = $_SESSION['logintime'];
		if (($nowtime - $s_time) > 1800) {
			session('userid', null);
			cookie('username', null);
			cookie('userid', null);
			session('logintime', null);
			$this->redirect('Mobile/User/login');
		} else {
			$_SESSION['logintime'] = $nowtime;
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
			$this->display('Common:404');
		}
	}
	
	//检查是否需要登录
	private function _check_login($c,$a){
		$nc_controller = array("Index");
		$nc_action = array('login','reg','logout');
		if(!in_array(CONTROLLER_NAME,$nc_controller)){
			if(!in_array(ACTION_NAME,$nc_action)){
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
	
	//curl
	private function _postRfsim($url,$parameter,$method='post'){
		$ch = curl_init() ;
		curl_setopt($ch,CURLOPT_URL,$url) ;
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);    //设置为字符串方式
		curl_setopt($ch, CURLOPT_HEADER, false);        //禁止头部信息
		curl_setopt($ch, CURLOPT_NOBODY,true);          //显示页面内容
		$method=='post'?curl_setopt($ch, CURLOPT_POST, true):curl_setopt($ch, CURLOPT_GET, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $parameter);    //POST数据
		curl_setopt($ch, CURLOPT_REFERFER, '1');       //伪装REFERER
		$result = curl_exec($ch) ;
		curl_close($ch) ;
		return $result;
	}

	//判断是否为手机端
	public function isMobile()
	{
		// 如果有HTTP_X_WAP_PROFILE则一定是移动设备
		if (isset ($_SERVER['HTTP_X_WAP_PROFILE']))
		{
			return true;
		}
		// 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
		if (isset ($_SERVER['HTTP_VIA']))
		{
			// 找不到为flase,否则为true
			return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
		}
		// 脑残法，判断手机发送的客户端标志,兼容性有待提高
		if (isset ($_SERVER['HTTP_USER_AGENT']))
		{
			$clientkeywords = array ('nokia',
					'sony',
					'ericsson',
					'mot',
					'samsung',
					'htc',
					'sgh',
					'lg',
					'sharp',
					'sie-',
					'philips',
					'panasonic',
					'alcatel',
					'lenovo',
					'iphone',
					'ipod',
					'blackberry',
					'meizu',
					'android',
					'netfront',
					'symbian',
					'ucweb',
					'windowsce',
					'palm',
					'operamini',
					'operamobi',
					'openwave',
					'nexusone',
					'cldc',
					'midp',
					'wap',
					'mobile'
			);
			// 从HTTP_USER_AGENT中查找手机浏览器的关键字
			if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT'])))
			{
				return true;
			}
		}
		// 协议法，因为有可能不准确，放到最后判断
		if (isset ($_SERVER['HTTP_ACCEPT']))
		{
			// 如果只支持wml并且不支持html那一定是移动设备
			// 如果支持wml和html但是wml在html之前则是移动设备
			if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html'))))
			{
				return true;
			}
		}
		return false;
	}
}
?>