<?php
namespace Mobile\Common;

class Basic {
	private static $appid="";
	private static $appsecret="";
	
	//获取微信授权链接
	function get_authorize_url($redirect_uri = '', $state = '')
	{
		$redirect_uri = urlencode($redirect_uri);
		return "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".self::$appid."&redirect_uri={$redirect_uri}&response_type=code&scope=snsapi_base&state={$state}#wechat_redirect";
	}
	
	//获取微信用户openid
	function getOpenId($code)
	{
		$url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".self::$appid."&secret=".self::$appsecret."&code={$code}&grant_type=authorization_code";
	
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$output = curl_exec($ch);
		curl_close($ch);
		$jsoninfo = json_decode($output, true);
		return $jsoninfo;
	
	}
	
	//获取微信用户基本信息
	function getWechatInfo($access_token,$openid){
		$info_url = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$access_token.'&openid='.$openid;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $info_url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$output = curl_exec($ch);
		curl_close($ch);
		$jsoninfo = json_decode($output, true);
		return $jsoninfo;
	}
	
	//获得JS API的ticket
	function getJsApiTicket()
	{
		$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".self::$appid."&secret=".self::$appsecret;
		$res = $this->http_request($url);
		$result = json_decode($res, true);
		$this->access_token = $result["access_token"];
		$this->expires_time = time();
		file_put_contents('./Public/mp_cache/access_token.json', '{"access_token": "'.$this->access_token.'", "expires_time": '.$this->expires_time.'}');
		if(file_exists('./Public/mp_cache/jsapi_ticket.json')){
			$res = file_get_contents('./Public/mp_cache/jsapi_ticket.json');
			$result = json_decode($res, true);
			$this->jsapi_ticket = $result["jsapi_ticket"];
			$this->jsapi_expire = $result["jsapi_expire"];
	
			if (time() > ($this->jsapi_expire + 3600)){
				$url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=".$this->access_token;
				$res = $this->http_request($url);
				$result = json_decode($res, true);
				$this->jsapi_ticket = $result["ticket"];
				$this->jsapi_expire = time();
				file_put_contents('./Public/mp_cache/jsapi_ticket.json', '{"jsapi_ticket": "'.$this->jsapi_ticket.'", "jsapi_expire": '.$this->jsapi_expire.'}');
			}
			return $this->jsapi_ticket;
		}else{
			$result = json_decode($res, true);
			$this->jsapi_ticket = $result["jsapi_ticket"];
			$this->jsapi_expire = $result["jsapi_expire"];
	
			if (time() > ($this->jsapi_expire + 3600)){
				$url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=".$this->access_token;
				$res = $this->http_request($url);
				$result = json_decode($res, true);
				$this->jsapi_ticket = $result["ticket"];
				$this->jsapi_expire = time();
				file_put_contents('./Public/mp_cache/jsapi_ticket.json', '{"jsapi_ticket": "'.$this->jsapi_ticket.'", "jsapi_expire": '.$this->jsapi_expire.'}');
			}
			return $this->jsapi_ticket;
		}
	}
	
	//HTTP请求（支持HTTP/HTTPS，支持GET/POST）
	protected function http_request($url, $data = null)
	{
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		if (!empty($data)){
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		}
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
		$output = curl_exec($curl);
		curl_close($curl);
		return $output;
	}
}