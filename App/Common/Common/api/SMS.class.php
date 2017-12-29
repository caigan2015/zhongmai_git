<?
namespace Common\Common\api;

include "xmlbase.inc.php";//����д�����xmlbase�ļ���·��
include "agentxmlclient.inc.php";//����д�����agentxmlclient.inc.php�ļ���·��

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
	//����SMS���Žӿ�
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
	//��ѯԶ���˻����
	function infoSMSAccount()
	{
		$xml_command="<action>SMS:infoSMSAccount</action>";
		$this->sendSCPData($this->serverURL, $xml_command);
		$this->toPlain();
		return $this->responseXML;
	}
	//����SMS���Žӿ�
	function readSMS()
	{
		$xml_command="<action>SMS:readSMS</action>";
		$this->sendSCPData($this->serverURL, $xml_command);
		$this->toPlain();
		return $this->responseXML;
	}
}

?>