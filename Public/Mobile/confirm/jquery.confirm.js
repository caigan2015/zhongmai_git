(function($){

	$.confirm = function(params){

		if($('#confirmOverlay').length){
			// A confirm is already shown on the page:
			$('#confirmOverlay').remove();
		}

		var buttonHTML = '';
		var buttonsCount = 0;
		$.each(params.buttons,function(name,obj){

			// Generating the markup for the buttons:

			buttonHTML += '<a href="javascript:void(0)" class="button '+obj['class']+'">'+name+'<span></span></a>';
			buttonsCount++;
			if(!obj.action){
				obj.action = function(){};
			}
		});

		var markup = [
			'<div id="confirmOverlay">',
			'<div id="confirmBox">',
			'<h1>',params.title,'<a href="javascript:void(0)" class="closea" onclick="$.confirm.hide();"></a></h1>',
			'<p>',params.message,'</p>',
			'<div id="confirmButtons">',
			buttonHTML,
			'</div></div></div>'
		].join('');

		$(markup).hide().appendTo('body').fadeIn();

		var buttons = $('#confirmBox .button'),
			i = 0;

		$.each(params.buttons,function(name,obj){
			buttons.eq(i++).click(function(){
				// Calling the action attribute when a
				// click occurs, and hiding the confirm.

				obj.action();
				if (buttonsCount == 1) {
					$.confirm.hide();
				}
				return false;
			});
		});
	}

	$.confirm.hide = function(buttonsCount){
		if (buttonsCount==undefined || 2 == buttonsCount) {
			$('#confirmOverlay').fadeOut(function(){
				$(this).remove();
			});
		}
	}


})(jQuery);
