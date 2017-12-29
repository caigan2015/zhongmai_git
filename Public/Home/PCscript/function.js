function all(){
	var num = $(".indexAdPic_ul li a img").size();
	var winW=$(window).width();
	var imgW = $(".indexAdPic_ul li a img").width();
	var index = 0;
	var timer = null;
	var autoplay = null;
	// 动画函数
	$(".indexAdPic_ul").css({'height':'100%'})
	$(".indexAdPic_ul li").css({'height':'100%','width':$(window).width(),'overflow':'hidden','position':'relative'});
	$(".indexAdPic_ul li a img").css({'position':'absolute','display':'block','width':'1920px','left':'50%','margin-left':'-960px','top':'0'});
	function setliw(){
		$(".indexAdPic_ul li").each(function(){
			$(this).css({'width':$(window).width()});
		})
	}
	setliw();
	window.onresize=function(){
		setliw();
	}
	
	
	function run(){
		doc();
		if(index>=num){
			index=0;
		}
		$(".indexAdPic_ul").animate({"left": -index*$(window).width() + "px"},200);
	}
	// 给页面动态生成小点
	function addSpot(){
		for(var i=0;i<num;i++){
			if(i==0){
				$('<em class="ac"></em>').appendTo(".indexAdPic_aside");
			}
			else{
				$('<em></em>').appendTo(".indexAdPic_aside");
			}
		}
	}
    // 给页面动态生成小点
	addSpot();
	//给页面下的每一个小圆点加点击事件
	$(".indexAdPic_aside").on("click","em",function(){
		index = $(this).index();
		run();
	})
	function doc() {
		$(".indexAdPic_aside em").eq(index).addClass("ac").siblings().removeClass("ac");
	}
	// 下一张
	function next(){
		index++;
		if(index>num-1){
			index=0;
		}	
	    run();
	}
	//移动上去自动走计时器清掉
	$(".indexAdPic").on("mouseover",function(){
		clearInterval(autoplay);
	})
	$(".indexAdPic").on("mouseout",function(){
		autoplay = setInterval(next,8000);
	})
    if($("#mybanner").size()){
		// 自动走
	    autoplay = setInterval(next,8000);
	}


}
function openId_div(o){
	document.getElementById(o).style.display='block';
}
function del(u){
	if(confirm('您确定要删除这个商品吗？执行删除之后则不能恢复咯！')){
		window.location.href=u;
	}
}

//首页最新产品模块 新闻轮播显示
function news(){
	var li_num = $(".new_list li").size();
	var timer2 = null;
	var new_top = 0;
	$(".new_list li:first").clone().appendTo(".new_list")
	function next_top(){
		if(new_top < li_num){
			new_top++;
		}
		else{
			new_top=1;
			$(".new_list").css({top:"0px"});
		}
		$(".new_list").animate({top:new_top*(-45)+"px"});
	}
	timer2 = setInterval(next_top,4000);
}

function createWarn(data,url){
	if(!$("#PromptDialogBox").size()){
		if(url!=''){
			$("body").append('<div id="PromptDialogBox" class="warn_bg"><div class="warnning"><a href="javascript:void(0);" class="close close_box"></a><div class="warn_title">温馨提示</div><div class="warn_content">'+data+'</div><a href="'+url+'" class="sure_close close_box">确定</a></div></div>');
		}else{
			$("body").append('<div id="PromptDialogBox" class="warn_bg"><div class="warnning"><a href="javascript:void(0);" class="close close_box"></a><div class="warn_title">温馨提示</div><div class="warn_content">'+data+'</div><a href="javascript:void(0);" class="sure_close close_box">返回</a></div></div>');
		}
	}
	$("#PromptDialogBox").show();
	$(".close_box").on("click",function(){
		$(".warn_bg").hide();
		if(url!=''){
			location.href=url;
		}
	});
}

function store_up(url,obj){
	$.ajax({url:url,async:false,success:function(data){
		switch (parseInt(data)){
			case 1:
				$(obj).addClass('ac');
				alert('已收藏成功')
				//$(obj).find('span').html('已收藏');
			break;
			case 2:
				$(obj).removeClass('ac');
				alert('已取消收藏')
				//$(obj).find('span').html('收藏');
			break;
			default:
				alert('请您登录账号！');
			break;
		}
	}});	
}