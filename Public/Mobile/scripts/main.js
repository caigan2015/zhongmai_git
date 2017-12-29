$(document).ready(function(){
	if($('.shopping_cart_aside').size()){
		$('.skin_app_footer').css({'display':'none'});	
	}
	//新增ajax中判断是否含有这个类
	if($(".load_list").size()){
		$(".load_list").on("click",function(){
		   getList();
		});
	}

	if($(".load_ajax_list").size()){
		$(".load_ajax_list").on("click",function(){
			getAjaxList();
		});
	}

	$('.showMenu').on('click',function(){
		$('.'+$(this).attr('data-obj')).slideToggle('normal');
	})
	if($('#indexBanner').size()){
		indexBanner();
	}
	if($('.pro_function_ul').find('li').has('div').size()){
		$('.pro_function_ul').find('li').has('div').on('click',function(){
			$(this).find('div').slideToggle('normal');
		})
	}
	if($('.submit').size()){
		$('.submit').on('click',function(){
			form=$(this).attr('data-form');
			$('#'+form).submit();
		})	
	}
	if($('.footer_aside').size()){
		$('body').after('<div class="footer_gekai"></div>')	
	}
	if($('.buy_num').size()){
		var obj=$('.buy_num');
		obj.on('click','.add_num',function(){
			temp=$(this).parent().find('.input_num').val();
			temp++;
			$(this).parent().find('.input_num').val(temp);
		})
		obj.on('click','.minus_num',function(){
			temp=$(this).parent().find('.input_num').val();
			temp=(temp<=1)?1:temp-1;
			$(this).parent().find('.input_num').val(temp);
		})
		obj.on('change','.input_num',function(){
			if($(this).val().length<=0&&$(this).val()<=0){
				$(this).val(1)	
			}
		})
	}
	if($('.data-id-button').size()){
		obj=$('.data-id-button');
		obj.find('li').eq(0).addClass('ac');
		$('.data-id').find('#class_0').addClass('show');
		upMenu(obj.find('li').eq(0).attr('data-url'));
		obj.on('click','li',function(){
			$(this).parent().find('.ac').removeClass('ac');
			$(this).addClass('ac');
			temp=$(this).attr('data-id');
			upMenu($(this).attr('data-url'));
			/*$('.data-id').find('.show').removeClass('show');
			$('.data-id').find('#class_'+temp).addClass('show');*/
		})	
	}
	if($('.getForm_url').size()){
		$('.getForm_url').on('click',function(){
			$('#getForm').attr('action',$(this).attr('data-url'));
		})	
	}
	if($('.my_label').size()){
		$('.my_label').each(function(){
			if ($(this).parent().find('[type="text"]').val().length <= 0) {
				$(this).css('display', 'block');
			} else {
				$(this).css('display', 'none');
			}
		})
		$('.my_label').parent().on('keyup', '[type="text"]', function() {
			if ($(this).val().length <= 0) {
				$(this).parent().find('label').css('display', 'block');
			} else {
				$(this).parent().find('label').css('display', 'none');
			}
		})
	}
})
