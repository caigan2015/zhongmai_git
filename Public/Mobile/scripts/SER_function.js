$(document).ready(function(){
	if($('.fourIcon').size()){
	$('.fourIcon').find('dl').on('click',function(){
		$(this).parent().find('dl').removeClass('ac');
		$(this).find('input').attr('checked','checked');
		$(this).addClass('ac');
	})
	}
	if($('.openInstruction').size()){
	$('.openInstruction').on('click',function(){
		$('.myInstruction').css('display','block');
	})
	}
	if($('.closeMyInstruction').size()){
	$('.closeMyInstruction').on('click',function(){$(this).parent().parent().css('display','none');});
	}
	if($('.closeWxWindow').size()){
		$('.closeWxWindow').on('click',function(){
			wx.closeWindow();	
		})	
	}
})
function getNowDate(){
	var d=new Date();
	var n=d.getFullYear()+'-'+d.getMonth()+'-'+d.getDate();
	return n;
}
function request(strParame) { 
var args = new Object( ); 
var query = location.search.substring(1); 

var pairs = query.split("&"); // Break at ampersand 
for(var i = 0; i < pairs.length; i++) { 
var pos = pairs[i].indexOf('='); 
if (pos == -1) continue; 
var argname = pairs[i].substring(0,pos); 
var value = pairs[i].substring(pos+1); 
value = decodeURIComponent(value); 
args[argname] = value; 
} 
return args[strParame]; 
} 
function openGetSubmit(v){
if(document.getElementById('JSconfirm')) {
	
	}else{
		var div=document.getElementById('form_in');
		var obj=document.createElement('div');
		var objCon=document.createElement('div');
		var objBg=document.createElement('div');
		obj.setAttribute('id','JSconfirm');
		objCon.setAttribute('class','JSconfirm_con');
		objBg.setAttribute('class','JSconfirm_bg');
		objCon.innerHTML='<strong style="color:#FFF">温馨提示：</strong><br/><p>'+v+'</p>'+'<br/><input type="submit" value="确认预约" class="inputSubmit"><input type="button" value="返 回" class="inputback" id="closeGetSubmit">'
		div.appendChild(obj);
		obj.appendChild(objCon);
		obj.appendChild(objBg);
	}
	document.getElementById('JSconfirm').style.display='block';
	document.getElementById('closeGetSubmit').onclick=function(){
		this.parentNode.parentNode.style.display='none'
	}
}
function openGetErr(o){
	if(document.getElementById('JSalert')) {
		document.getElementById('alertCon').innerHTML=o
	}else{
		var div=document.body;
		var obj=document.createElement('div');
		var objCon=document.createElement('div');
		var objBg=document.createElement('div');
		obj.setAttribute('id','JSalert');
		objCon.setAttribute('class','JSalert_con');
		objBg.setAttribute('class','JSalert_bg');
		objCon.innerHTML='<strong style="color:#FFF">温馨提示：</strong><br/><p id="alertCon">'+o+'</p>'+'<br/><input type="button" value="返 回" class="inputSubmit" id="closeGetAlert">'
		div.appendChild(obj);
		obj.appendChild(objCon);
		obj.appendChild(objBg);
	}
	document.getElementById('JSalert').style.display='block';
	document.getElementById('closeGetAlert').onclick=function(){
		this.parentNode.parentNode.style.display='none'
	}
}
function cherkForm(){
	err=0;
	errmsg='';
	if($('form').find('.select_required').size()&&err==0){
		for(i=0;i<$('form').find('.select_required').size();i++){
			if($('form').find(".select_required ").eq(i).find('option:selected').index()==0){
				
				err=1;
				errmsg=$('form').find('.select_required').eq(i).attr('errmsg');
				break;
			}
		}
	}
	if($('form').find('.input_required').size()&&err==0){
		for(i=0;i<$('form').find('.input_required').size();i++){
			
			if($('form').find('.input_required').eq(i).val()==''){
				err=1;
				errmsg=$('form').find('.input_required').eq(i).attr('errmsg');
				break;
			}
		}
	}
	if(err==1){
		//alert(errmsg);
		openGetErr(errmsg);
		return false;
	}else{
		return true;	
	}
}