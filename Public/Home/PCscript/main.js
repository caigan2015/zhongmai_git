$("body").ready(function(){
	all();
	news();
	$(".ctc").on("click","span",function(){
		var n = $(this).index();
	    $(".ctc span").eq(n).addClass("classify_type_active").siblings().removeClass("classify_type_active");
    })
    $(".pro_info_pro_num_add_reduce").on("click",function(){
    	if($(".pro_info_pro_num_add_num").val()>1){
    		$(".pro_info_pro_num_add_num").val(parseInt($(".pro_info_pro_num_add_num").val())-1);
    	}
    	else{
    		$(".pro_info_pro_num_add_num").val(1);
    	}
    })
    $(".pro_info_pro_num_add_add").on("click",function(){
    		$(".pro_info_pro_num_add_num").val(parseInt($(".pro_info_pro_num_add_num").val())+1);
    })
   /* $(".pro_info_msg_code_get_code").on("click",function(){
    	$(this).css({"background":"url(images/all_logo.png) no-repeat 1px -505px"})
    })*/
    if($('.getForm_url').size()){
		$('.getForm_url').on('click',function(){
			var that = this;
			$.post($(this).attr('data-url'),{ajax:1},function(data){
				str = $.parseJSON(data);
				if(str.status!='100'){
					if($(that).attr('data-val')=='1'){
						$('#getForm').attr('action',$(that).attr('data-url'));
						$('#getForm').submit();
					}else{
						$.post($(that).attr('data-url'),$("#getForm").serialize(),function(datax){
							arr = $.parseJSON(datax);
							createWarn(arr.msg,'');
							return false;
						});
					}
				}else{
					createWarn(str.msg,str.url);
					return false;
				}
			});
			//$('#getForm').attr('action',$(this).attr('data-url'));
		})	
	}
})
	