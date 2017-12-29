<?
namespace Common\Common\api;

include "xmlbase.inc.php";//请填写你放置xmlbase文件的路径
include "agentxmlclient.inc.php";//请填写你放置agentxmlclient.inc.php文件的路径

class SMS extends \XMLClient
{
	var $ConfNull="1";
	function __construct()
	{
		$this->serverURL="sms.todaynic.com:20002";
		$this->XMLType="SMS";
		$this->VCP="ms103746";
		$this->VCPPassword="mtkznj";
	}
	//发送SMS短信接口
	function sendSMS($mobile, $msg, $time="", $apitype=0)
	{
		$xml_command="<action>SMS:sendSMS</action>
						<sms:mobile>$mobile</sms:mobile>
						<sms:message>".base64_encode($msg)."</sms:message>
						<sms:datetime>$time</sms:datetime>
						<sms:apitype>$apitype</sms:apitype>";
		$this->sendSCPData($this->serverURL, $xml_command);
		$this->toPlain();
		return $this->responseXML;
	}
	//查询远程账户余额
	function infoSMSAccount()
	{
		$xml_command="<action>SMS:infoSMSAccount</action>";
		$this->sendSCPData($this->serverURL, $xml_command);
		$this->toPlain();
		return $this->responseXML;
	}
	//接收SMS短信接口
	function readSMS()
	{
		$xml_command="<action>SMS:readSMS</action>";
		$this->sendSCPData($this->serverURL, $xml_command);
		$this->toPlain();
		return $this->responseXML;
	}
}

?>