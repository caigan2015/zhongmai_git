<?php
namespace App\Controller;
use Think\Controller;

class CommonController extends Controller {

    function _initialize(){
        $inde = self::isMobile();
        if(!$inde){
            $gets = '';
            foreach($_GET as $kg=>$vg){
                $gets[$kg]=$vg;
            }
            $url = CONTROLLER_NAME.'/'.ACTION_NAME;
            $this->redirect('/'.$url,$gets);
            exit;
        }
        self::check_login();

        if(session('user')){
            //获取用户基本信息
            $userInfo = session('user');
            $userInfo['show_phone'] = substr_replace($userInfo['phone'], '****', 3, 4);
            $this->assign('userInfo',$userInfo);
        }

        //获取基本配置
        $set =M('About')->select();
        foreach($set as $kt=>$vt){
            $sys[$vt['ks']]=$vt['val'];
        }
        $this->assign('sys',$sys);

        //常见问题
        $que = M('Question')->order('ctime desc')->select();
        $this->assign("que",$que);

    }

    /**
     * 判断用户是否已经登陆
     */
    final public function check_login() {
        if($this->_check_login(CONTROLLER_NAME,ACTION_NAME)) {
            if(!session('user')){
                $this->redirect('User/login');
            }else{
                return true;
            }
        }else{
            return true;
        }
    }

    //检查是否需要登录
    private function _check_login($c,$a){
        $nc_controller = array("Index");
        $nc_action = array('login','login_pass','register','logout','pass','pass_1','pass_2','pass_3','getPhoneCode');
        if(!in_array($c,$nc_controller)){
            if(!in_array($a,$nc_action)){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
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