<?php
use Org\Util\CKFinder;
use Org\Util\CKEditor;
/**
 * 字符串截取，支持中文和其他编码
 * @param string $str 需要转换的字符串
 * @param string $start 开始位置
 * @param string $length 截取长度
 * @param string $charset 编码格式
 * @param string $suffix 截断显示字符
 * @return string
 */
function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=true) {
    $charset = strtolower($charset);
    if(function_exists("mb_substr"))
        $slice = mb_substr($str, $start, $length, $charset);
    elseif(function_exists('iconv_substr')) {
        $slice = iconv_substr($str,$start,$length,$charset);
        if(false === $slice) {
            $slice = '';
        }
    }else{
        $re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
        $re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
        $re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
        preg_match_all($re[$charset], $str, $match);
        $slice = join("",array_slice($match[0], $start, $length));
    }
    return $suffix ? $slice.'...' : $slice;
}

/**
 * 检测输入的验证码是否正确
 * @param string $code 为用户输入的验证码字符串
 * @return boolen
 */
function check_verify($code, $id = ''){
    $verify = new \Think\Verify();
    return $verify->check($code, $id);
}

/**
 * 对用户的密码进行加密
 * @param string $password
 * @param string $encrypt //传入加密串，在修改密码时做认证
 * @return array/password
 */
function password($password, $encrypt='') {
	$pwd = array();
	$pwd['encrypt'] =  $encrypt ? $encrypt : Org\Util\String::randString(6);
	$pwd['password'] = md5(md5(trim($password)).$pwd['encrypt']);
	return $encrypt ? $pwd['password'] : $pwd;
}

/**
 * 解析多行sql语句转换成数组
 * @param string $sql
 * @return array
 */
function sql_split($sql) {
	$sql = str_replace("\r", "\n", $sql);
	$ret = array();
	$num = 0;
	$queriesarray = explode(";\n", trim($sql));
	unset($sql);
	foreach($queriesarray as $query) {
		$ret[$num] = '';
		$queries = explode("\n", trim($query));
		$queries = array_filter($queries);
		foreach($queries as $query) {
			$str1 = substr($query, 0, 1);
			if($str1 != '#' && $str1 != '-') $ret[$num] .= $query;
		}
		$num++;
	}
	return($ret);
}

/**
 * 格式化字节大小
 * @param  number $size      字节数
 * @param  string $delimiter 数字和单位分隔符
 * @return string            格式化后的带单位的大小
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function format_bytes($size, $delimiter = '') {
    $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
    for ($i = 0; $size >= 1024 && $i < 5; $i++) $size /= 1024;
    return round($size, 2) . $delimiter . $units[$i];
}

/**
 * 取得文件扩展
 * @param $filename 文件名
 * @return 扩展名
 */
function file_ext($filename) {
	return strtolower(trim(substr(strrchr($filename, '.'), 1, 10)));
}

/**
 * 文件是否存在
 * @param string $filename  文件名
 * @return boolean  
 */
function file_exist($filename ,$type=''){
	switch (STORAGE_TYPE){
		case 'Sae':
			$arr = explode('/', ltrim($filename, './'));
	        $domain = array_shift($arr);
	        $filePath = implode('/', $arr);
	        $s = new SaeStorage();
			return $s->fileExists($domain, $filePath);
			break;
		default:
			return \Think\Storage::has($filename ,$type);
	}
}

/**
 * 文件内容读取
 * @param string $filename  文件名
 * @return boolean         
 */
function file_read($filename, $type=''){
	switch (STORAGE_TYPE){
		case 'Sae':
			$arr = explode('/', ltrim($filename, './'));
	        $domain = array_shift($arr);
			$filePath = implode('/', $arr);
			$s=new SaeStorage();
			return $s->read($domain, $filePath);
			break;
		default:
			return \Think\Storage::read($filename, $type);
	}
}

/**
 * 文件写入
 * @param string $filename  文件名
 * @param string $content  文件内容
 * @return boolean         
 */
function file_write($filename, $content, $type=''){
	switch (STORAGE_TYPE){
		case 'Sae':
			$s=new SaeStorage();
			$arr = explode('/',ltrim($filename,'./'));
			$domain = array_shift($arr);
			$save_path = implode('/',$arr);
			return $s->write($domain, $save_path, $content);
			break;
		default:
			return \Think\Storage::put($filename, $content, $type);
	}
}

/**
 * 文件删除
 * @param string $filename  文件名
 * @return boolean     
 */
function file_delete($filename ,$type=''){
	switch (STORAGE_TYPE){
		case 'Sae':
			$arr = explode('/', ltrim($filename, './'));
	        $domain = array_shift($arr);
	        $filePath = implode('/', $arr);
	        $s = new SaeStorage();
			return $s->delete($domain, $filePath);
			break;
		default:
			return \Think\Storage::unlink($filename ,$type);
	}
}

/**
 * 获取文件URL
 * @param string $filename  文件名
 * @return string
 */
function file_path2url($filename){
	$search = array_keys(C('TMPL_PARSE_STRING'));
	$replace = array_values(C('TMPL_PARSE_STRING'));
	return str_ireplace($search, $replace, $filename);
}
/**
 * 获取文件路径
 * @param string $fileurl  文件URL
 * @return string
 */
function file_url2path($fileurl){
	$search = array_values(C('TMPL_PARSE_STRING'));
	$replace = array_keys(C('TMPL_PARSE_STRING'));
	return str_ireplace($search, $replace, $fileurl);
}

/**
 * curl请求
 */
function postRfsim($url,$parameter,$method='post'){
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

/**
 * 指定位置插入字符串
 * @param $str  原字符串
 * @param $i    插入位置
 * @param $substr 插入字符串
 * @return string 处理后的字符串
 */
function insertToStr($str, $i, $substr){
	//指定插入位置前的字符串
	$startstr="";
	for($j=0; $j<$i; $j++){
		$startstr .= $str[$j];
	}

	//指定插入位置后的字符串
	$laststr="";
	for ($j=$i; $j<strlen($str); $j++){
		$laststr .= $str[$j];
	}

	//将插入位置前，要插入的，插入位置后三个字符串拼接起来
	$str = $startstr . $substr . $laststr;

	//返回结果
	return $str;
}

/**
 * fck
 */
function editor($name='content',$value='',$editorName='Editor',$width='1000',$height='400') {
	//实例化CKeditor方法,传入参数为编辑器所在位置public/ckeditor
	$ckeditor = new CKEditor(__ROOT__.'/Public/common/ckeditor/');
	//设置模式为输出,否则下面的editor方法没有返回值,而是直接输出,无法显示在我们想要显示编辑器的位置
	$ckeditor->returnOutput=true;
	$configArray=array(
			'height'=>$height,  //编辑器高度
			'width'=>$width     //编辑器宽度
	);
	$_SESSION['CKFINDER']['baseurl'] = __ROOT__.'/Public/upload/news/';
	//CKfinder与CKEditor整合,参数1为上面实例化的CKEditor对象,参数2为CKfinder的位置public.ckfinder
	CKFinder::SetupCKEditor($ckeditor, __ROOT__.'/Public/common/ckfinder/') ; //无需上传功能则跳过
	//创建编辑器并返回代码,用于分配到页面
	$CK = $ckeditor->editor($name,$value,$configArray);
	//分配编辑器到页面,但未显示
	return $CK;
}

/**********
 * 发送邮件 *
**********/
function SendMail($address,$title,$message)
{
	vendor('PHPMailer.class#phpmailer');

	$mail=new PHPMailer();
	// 设置PHPMailer使用SMTP服务器发送Email
	$mail->IsSMTP();

	// 设置邮件的字符编码，若不指定，则为'UTF-8'
	$mail->CharSet='UTF-8';

	// 添加收件人地址，可以多次使用来添加多个收件人
	$mail->AddAddress($address);
	
	$mail->IsHTML(true); //支持html格式内容

	// 设置邮件正文
	$mail->Body=$message;

	// 设置邮件头的From字段。
	$mail->From=C('MAIL_ADDRESS');

	// 设置发件人名字
	$mail->FromName=C('MAIL_SENDER');
	// 设置邮件标题
	$mail->Subject=$title;

	// 设置SMTP服务器。
	$mail->Host=C('MAIL_SMTP');

	// 设置为“需要验证”
	$mail->SMTPAuth=true;

	// 设置用户名和密码。
	$mail->Username=C('MAIL_LOGINNAME');
	$mail->Password=C('MAIL_PASSWORD');

	// 发送邮件。
	return($mail->Send());
}

/**
 * 导出数据为excel表格
 *@param $data    一个二维数组,结构如同从数据库查出来的数组
 *@param $title   excel的第一行标题,一个数组,如果为空则没有标题
 *@param $filename 下载的文件名
 *@examlpe
 */
function exportexcel($data=array(),$title=array(),$filename='report'){
	header("Content-type:application/octet-stream");
	header("Accept-Ranges:bytes");
	header("Content-type:application/vnd.ms-excel");
	header("Content-Disposition:attachment;filename=".$filename.".xls");
	header("Pragma: no-cache");
	header("Expires: 0");
	//导出xls 开始
	if (!empty($title)){
		foreach ($title as $k => $v) {
			$title[$k]=iconv("utf-8", "gb2312",$v);
		}
		$title= implode("\t", $title);
		echo "$title\n";
	}
	if (!empty($data)){
		foreach($data as $key=>$val){
			foreach ($val as $ck => $cv) {
				$data[$key][$ck]=iconv("utf-8", "gb2312",$cv);
			}
			$data[$key]=implode("\t", $data[$key]);

		}
		echo implode("\n",$data);
	}
}
