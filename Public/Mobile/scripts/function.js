var nowB=0;
var time=8000;
var banN;
function indexBanner(){
	var isDarg=false;
	var posX,tempX;
	var startX,endX;
	bannerSize();
	addWindowResize(bannerSize);
	onDiv=document.getElementById("indexBanner");
	onDiv.style.left="0px";
	if($('.indexBanner_aside').size()){
		for(i=0;i<=onDiv.getElementsByTagName('li').length-1;i++){
			$('.indexBanner_aside').append('<em></em>')
		}
		$('.indexBanner_aside').find('em').eq(0).addClass('ac');
	}
	onDiv.addEventListener('touchstart',touchstart);
	onDiv.addEventListener('touchmove',touchmove);
	onDiv.addEventListener('touchend',touchend);
	function touchstart(e){
		isDarg=true;
		startX=e.touches[0].pageX;
		tempX=startX-parseInt(onDiv.style.left);
		clearTimeout(banN);
	}
	function touchmove(e){
		if(isDarg){
			e.preventDefault();
			posX=e.touches[0].pageX-tempX+"px";
			onDiv.style.left=posX;
			endX=e.touches[0].pageX;
		}
	}
	function touchend(e){
		if(startX<endX-100){
			nowB--;
		}
		if(startX>endX+100){
			nowB++;
			autoPlayB();
		}
		autoPlayB();
        isdrag = false;
    }
}
function autoPlayB(){
	maxB=onDiv.getElementsByTagName("li").length;
	nowB=(nowB<0)?maxB-1:nowB;
	nowB=(nowB>=maxB)?0:nowB;
	$("#indexBanner").stop().animate({"left":-nowB*setWindowWidth()})
	$("#indexBanner").parent().find('.indexBanner_aside .ac').removeClass('ac');
	$("#indexBanner").parent().find('.indexBanner_aside').find('em').eq(nowB).addClass('ac');
	clearTimeout(banN);
	banN=setTimeout("autoPlayB()",time);
}
function setWindowWidth(){
	w=$("body").width();
	return w;
}
function setWindowHeight(){
	h=$("body").height();
	return h;
}
function bannerSize(){
	BanNum=$("#indexBanner").find("li").size();
	BanWidth=setWindowWidth()*BanNum;
	$("#indexBanner").find("li").css({"width":setWindowWidth()+"px","display":"block"});
	$("#indexBanner").css("width",BanWidth+"px");
}

function addWindowResize(f){
	if (document.all){ 
	window.attachEvent('onresize',f)//IE中 
	} else{ 
	window.addEventListener('resize',f,false);//firefox 
	} 
}
function addWindowLoad(o){
	oldload=window.onload;
	if(typeof oldload != 'function'){
		window.onload=o();
	}else{
		window.onload=function(){
			oldload();
			o();
		}
	}
}
function store_up(url,obj){
	$.ajax({url:url,async:false,success:function(data){
		switch (parseInt(data)){
			case 1:
				$(obj).addClass('ac');
				$(obj).find('span').html('已收藏');
			break;
			case 2:
				$(obj).removeClass('ac');
				$(obj).find('span').html('收藏');
			break;
			default:
				alert('数据错误');
			break;
		}
	}});	
}
function upMenu(url,obj){
	$('#class_0').html('');
	$.ajax({url:url,async:true,success:function(data){
		$('#class_0').html(data);
	}});	
}

// 添加ajax在更多加载
function getList(){
	var load_more_div_a = $(".load_list a");
	var list_url = load_more_div_a.attr("data-url");
    var list_classifiy = load_more_div_a.attr("data-class");
	var list_sort = load_more_div_a.attr("data-sort");
    var list_key = load_more_div_a.attr("data-key");
    var list_page = parseInt(load_more_div_a.attr("data-page"))+1;
	load_more_div_a.attr("data-page",list_page);
	//console.log(list_page)
	xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange = function(){
		if(xmlhttp.readyState==4&&xmlhttp.status>=200){
			// 判断是否还有数据
			//alert(xmlhttp.responseText)
			if(xmlhttp.responseText==0){
				$(".tips").html('--END--');
				$(".tips").css({'display':'block'});
				$(".load_list").hide();				
				return;
			}
			else{
				$(".pro_ul").append(xmlhttp.responseText);
			}
		}
	}

	xmlhttp.open("POST",list_url,true);
	xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	xmlhttp.send("sort_id="+list_sort+"&classify="+list_classifiy+"&keyword="+list_key+"&page="+list_page);
}

function getAjaxList(){
	var load_more_div_a = $(".load_ajax_list a");
	var list_url = load_more_div_a.attr("data-url");
	var list_page = parseInt(load_more_div_a.attr("data-page"))+1;
	load_more_div_a.attr("data-page",list_page);
	xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange = function(){
		if(xmlhttp.readyState==4&&xmlhttp.status>=200){
			// 判断是否还有数据
			if(xmlhttp.responseText==0){
				$(".tips").html('--END--');
				$(".tips").css({'display':'block'});
				$(".load_ajax_list").hide();
				return;
			}
			else{
				$(".list_show").append(xmlhttp.responseText);
			}
		}
	}

	xmlhttp.open("POST",list_url,true);
	xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	xmlhttp.send("page="+list_page);
}
