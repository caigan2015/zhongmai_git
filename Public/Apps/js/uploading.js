  var myScroll,
  pullUpEl, pullUpOffset,
  generatedCount = 0;

/**
 * 滚动翻页 （自定义实现此方法）
 * myScroll.refresh();    // 数据加载完成后，调用界面更新方法
 */
function pullUpAction () {
  setTimeout(function () {  // <-- Simulate network congestion, remove setTimeout from production!
    var url = $('.ajax-url').val();
    var next = $('.next-page').val();
    var flag  = $('.flag-page').val();
    if(flag==1){
        $.ajax({
			url:url,
			type:"POST",
			data:{next:next},
			async: false,
			success:function(data){
				str = $.parseJSON(data);
	            $('#loading-list').append(str.data); 
	            $('.next-page ').val(str.next);
	            $('.flag-page ').val(str.flag);
	            if(str.flag==0){
	                $('#pullUp').remove();
	            }
			},
			error:function(){
				$.confirm({
					'title'		: '提示',
					'message'	: '网络有问题！！请检查网络连接！',
					'buttons'	: {
						'确定'	: {
							'class'	: 'all',
							'action': function(){

							}
						}
					}
				});
				return false;
			}
		});
    }else{
       $('#pullUp').remove();
    }
    myScroll.refresh();   // 数据加载完成后，调用界面更新方法 Remember to refresh when contents are loaded (ie: on ajax completion)
  }, 1000); // <-- Simulate network congestion, remove setTimeout from production!
}

/**
 * 初始化iScroll控件
 */
function loaded() {
  pullUpEl = document.getElementById('pullUp'); 
  pullUpOffset = pullUpEl.offsetHeight;
  
  myScroll = new iScroll('wrapper', {
    scrollbarClass: 'myScrollbar', /* 重要样式 */
    useTransition: false, /* 此属性不知用意，本人从true改为false */
    onRefresh: function () {
      if (pullUpEl.className.match('loading')) {
        pullUpEl.className = '';
        pullUpEl.querySelector('.pullUpLabel').innerHTML = '上拉加载更多...';
      }
    },
    onScrollMove: function () {
      if (this.y < (this.maxScrollY - 5) && !pullUpEl.className.match('flip')) {
        pullUpEl.className = 'flip';
        pullUpEl.querySelector('.pullUpLabel').innerHTML = '松手开始更新...';
        this.maxScrollY = this.maxScrollY;
      } else if (this.y > (this.maxScrollY + 5) && pullUpEl.className.match('flip')) {
        pullUpEl.className = '';
        pullUpEl.querySelector('.pullUpLabel').innerHTML = '上拉加载更多...';
        this.maxScrollY = pullUpOffset;
      }
    },
    onScrollEnd: function () {
      if (pullUpEl.className.match('flip')) {
        pullUpEl.className = 'loading';
        pullUpEl.querySelector('.pullUpLabel').innerHTML = '加载中...';        
        pullUpAction(); // Execute custom function (ajax call?)
      }
    }
  });
  
  setTimeout(function () { document.getElementById('wrapper').style.left = '0'; }, 800);
}

//初始化绑定iScroll控件 
//document.addEventListener('touchmove', function (e) { e.preventDefault(); }, false);
document.getElementById('wrapper').addEventListener('touchmove', function (e) { e.preventDefault(); }, false);
document.addEventListener('DOMContentLoaded', loaded, false); 
