<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="content-type" content="text/html;charset=utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta content="always" name="referrer">
    <title>首页 - 中迈融资租赁（广州）有限公司</title>
    <link rel="icon" type="image/x-icon" href="/Public/favicon.ico">
    <link rel="stylesheet" href="/Public/Home/PCcss/base.css">
    <link rel="stylesheet" href="/Public/Home/PCcss/index.css">
    <link rel="stylesheet" href="/Public/Home/PCcss/common.css">
    <script type="text/javascript" src="/Public/Home/PCscript/area.js"></script>
    <link rel="stylesheet" href="/Public/Home/confirm/jquery.confirm.css">
    <link rel="stylesheet" href="/Public/Home/confirm/index.confirm.css">
    <!--[if IE]><script src="/Public/Home/PCscript/html5.js"></script><![endif]-->
<style>
.endtime{font-size:13px;text-align:left;padding:0 6px; font-family:"Microsoft Yahei"; color:#000;}
.showtime{position:absolute; height:24px; line-height:24px; opacity:.6; display:block;border:1px solid #ddd;width:174px;}
.endtime i.show-lock{width:24px;height:24px;float:left;background:url(/Public/Home/PCimages/052.png) no-repeat;background-size:24px;}
.endtime i.stop-lock{width:24px;height:24px;float:left;background:url(/Public/Home/PCimages/051.png) no-repeat;background-size:24px;}
</style>
</head>
<body>
<header>
    <div class="block clearfix">
        <div class="header_top">
            <div class="header_nav clearfix">
                <div class="header_nav_l clearfix">
                    <em>欢迎来到提车吧，</em>
                    <?php if($userInfo["username"] != ''): ?><a href="<?php echo U('User/index');?>" class="login_a"><?php echo ($userInfo["show_phone"]); ?></a>
                        <a href="<?php echo U('User/logout');?>" class="resigter_a">退出</a>
                        <?php else: ?>
                        <a href="<?php echo U('User/login');?>" class="login_a">请登录</a>
                        <a href="<?php echo U('User/register');?>" class="resigter_a">免费注册</a><?php endif; ?>
                </div>
                <ul class="header_nav_r clearfix">
                    <li><a href="javascript:void(0);" class="personal_center_a">提车热线: <?php echo ($sys["phone"]); ?> | <?php echo ($sys["time"]); ?></a></li>
                    <li class="separator"></li>
                    <li class="phone_code_a phone_code_wei"><i></i><a href="javascript:void(0)">官方服务号<div class="phone_code"><img src="/Public/Home/PCimages/phone_code.png" alt="" /><span>扫描二维码</span></div></a></li>
                    <!--<li class="separator"></li>
                    <li class="phone_code_a phone_code_app"><i></i><a href="javascript:void(0)">官方订阅号<div class="phone_code"><img src="/Public/Home/PCimages/phone_code_dyh.png" alt="" /><span>官方订阅号</span></div></a></li>-->
                    <li class="separator"></li>
                    <li><a href="<?php echo U('Index/contact');?>" class="personal_center_a">联系我们</a></li>
                </ul>
            </div>
        </div>
        <div class="header_mid clearfix">
            <div class="header_logo">
                <a href="<?php echo U('Index/index');?>"><img src="/Public/Home/PCimages/logo.png" alt=""></a>
            </div>
            <nav id="HeaderNav">
                <ul>
                    <li <?php if(CONTROLLER_NAME== 'Index' && ACTION_NAME== 'index'): ?>class="current"<?php endif; ?> ><a href="<?php echo U('Index/index');?>">首页</a></li>
                    <li <?php if(CONTROLLER_NAME== 'Index' && ACTION_NAME== 'lists'): ?>class="current"<?php endif; ?> ><a href="<?php echo U('Index/lists');?>" class="header_nav_list_a">我要买车</a></li>
                    <li <?php if(CONTROLLER_NAME== 'Index' && ACTION_NAME== 'about'): ?>class="current"<?php endif; ?> ><a href="<?php echo U('Index/about');?>" class="header_nav_list_a">提车吧</a></li>
                    <li <?php if(CONTROLLER_NAME== 'Index' && ACTION_NAME== 'help'): ?>class="current"<?php endif; ?> ><a href="<?php echo U('Index/help');?>" class="header_nav_list_a">使用帮助</a></li>
                </ul>
            </nav>
            <div class="search fl clrfix" style="width:300px;margin-top:34px;margin-left:50px;">
            	<div class="search-box fl clrfix" style="width: 220px;height: 28px;background: #fff;border: 2px solid #e95a47;position: relative;">
            		<input type="text" name="keys" value="<?php echo ($key_name); ?>" style="width:215px;height:28px;border:0;padding-left:5px;"/>
            	</div>
	        	<a class="search-btn fl" style="display: block;width: 76px;height: 32px;line-height: 32px;background: #e95a47;color: #fff;font-size: 14px;text-align: center;cursor:pointer;"><i></i>搜索</a>
	        </div>
            <div class="login-status">
                <a href="<?php echo U('User/index');?>">我的申请</a><i></i>
            </div>
        </div>
    </div>
</header>

<div class="ui-sidebar">
    <!--<a class="ui-sidebar-block app" href="javascript:void(0)"><i class="qrCodeWeixin"><img src="/Public/Home/PCimages/phone_code_dyh.png"><span>关注官方订阅号</span></i></a>-->
    <a class="ui-sidebar-block weixin" href="javascript:void(0)"><i class="qrCodeWeixin"><img src="/Public/Home/PCimages/phone_code.png"><span>扫描二维码<span>关注公众号</span></span></i></a>
    <a class="ui-sidebar-block onlineService" href="<?php echo U('Index/feedback');?>"></a>
    <a class="ui-sidebar-block feedback" href="javascript:void(0)"><span class="feedbackBox"><?php echo ($sys["phone"]); ?></span></a>
    <div id="backTop"><a class="ui-sidebar-block backtop" href="javascript:void(0)"></a>
    </div>
</div>


<div class="indexKuang_2">
    <section class="indexAdPic indexBanner">
        <aside class="indexAdPic_aside  indexBanner_aside">
        </aside>
        <ul class="indexAdPic_ul" id="mybanner">
            <?php if(is_array($banner)): $i = 0; $__LIST__ = $banner;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$str): $mod = ($i % 2 );++$i;?><li><a href="<?php echo ($str['url']); ?>" target="_blank"><img src="/Public/upload/banner/<?php echo ($str['img']); ?>" alt=""/></a></li><?php endforeach; endif; else: echo "" ;endif; ?>
        </ul>
    </section>
</div>

<section class="main-list recommond-more">
    <div class="container-center">
        <h3 class="title">限时特惠</h3>
        <ul class="ki-list" id="ki-list">
            <?php if(is_array($hui)): $i = 0; $__LIST__ = $hui;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$as): $mod = ($i % 2 );++$i;?><li>
                <a href="<?php echo U('Index/info',array('id'=>$as['id']));?>" target="_blank">
                    <div class="price" style="width:188px;padding-bottom:10px;">
                        <dl class="downpayment">
                            <dt>首付</dt>
                            <dd><em><?php echo ($as["cost"]); ?>万</em></dd>
                        </dl>
                        <dl class="monthly-payment">
                            <dt>月供</dt>
                            <dd><em><?php echo ($as["price"]); ?>元</em></dd>
                        </dl>
                        <div class="endtime showtime" value="<?php echo ($as["check_time"]); ?>"></div>
                    </div>
                    <div class="image">
                        <img src="/Public/upload/product/<?php echo ($as["img"]); ?>" alt="<?php echo ($as["title"]); ?>">
                    </div>
                    <div class="text">
                        <div class="tit" title="<?php echo ($as["title"]); ?>"><?php echo ($as["title"]); ?></div>
                        <div class="car-price">厂商指导价：<?php echo ($as["money"]); ?>万</div>
                    </div>
                </a>
            </li><?php endforeach; endif; else: echo "" ;endif; ?>
        </ul>
    </div>

</section>

<section class="main-list recommond-more">
    <div class="container-center">
        <h3 class="title">活动专区</h3>
        <ul class="ki-list" id="ki-list">
            <?php if(is_array($act)): $i = 0; $__LIST__ = $act;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$a): $mod = ($i % 2 );++$i;?><li>
                <a href="<?php echo U('Index/info',array('id'=>$a['id']));?>" target="_blank">
                    <div class="price">
                        <dl class="downpayment">
                            <dt>首付</dt>
                            <dd><em><?php echo ($a["cost"]); ?>万</em></dd>
                        </dl>
                        <dl class="monthly-payment">
                            <dt>月供</dt>
                            <dd><em><?php echo ($a["price"]); ?>元</em></dd>
                        </dl>
                        <div class="tax"><?php echo ($a["specs"]); ?></div>
                    </div>
                    <div class="image">
                        <img src="/Public/upload/product/<?php echo ($a["img"]); ?>" alt="<?php echo ($a["title"]); ?>">
                    </div>
                    <div class="text">
                        <div class="tit" title="<?php echo ($a["title"]); ?>"><?php echo ($a["title"]); ?></div>
                        <div class="car-price">厂商指导价：<?php echo ($a["money"]); ?>万</div>
                    </div>
                </a>
            </li><?php endforeach; endif; else: echo "" ;endif; ?>
        </ul>
    </div>

</section>

<section class="main-list activity-first">
    <div class="container-center">
        <h3 class="title">热卖车型</h3>
        <ul class="product-list">
            <?php if(is_array($hot)): $i = 0; $__LIST__ = $hot;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$h): $mod = ($i % 2 );++$i;?><li>
                <a href="<?php echo U('Index/info',array('id'=>$h['id']));?>" target="_blank">
                    <div class="image">
                        <img src="/Public/upload/product/<?php echo ($h["img"]); ?>" alt="<?php echo ($h["title"]); ?>">
                    </div>
                    <div class="text">
                        <div class="tit" title="<?php echo ($h["title"]); ?>"><?php echo ($h["title"]); ?></div>
                        <div class="tax">厂商指导价：<?php echo ($h["money"]); ?>万</div>
                    </div>
                    <div class="price">
                        <dl class="downpayment">
                            <dt>首付</dt>
                            <dd><em><?php echo ($h["cost"]); ?>万</em></dd>
                        </dl>
                        <dl class="monthly-payment">
                            <dt>月供</dt>
                            <dd><em><?php echo ($h["price"]); ?>元</em></dd>
                        </dl>
                    </div>
                    <div class="go">立即预约</div>
                </a>
            </li><?php endforeach; endif; else: echo "" ;endif; ?>
        </ul>
    </div>
</section>

<section class="main-list ki-all" style="margin-top:20px;">
    <div class="container-center">
        <h3 class="title"><a href="<?php echo U('Index/lists');?>">全部车源 &gt;</a></h3>
    </div>
</section>


<section class="apply-foot ki-process">
    <div class="container-center">
        <h3 class="title">申请流程</h3>
        <div class="process"></div>
    </div>
</section>
<footer>
    <div class="footerWarp">
        <div class="footer_line"></div>
        <div class="footer_authentication">
            <div class="footer-center">
                <div>免费咨询、建议、投诉</div>
                <font><?php echo ($sys["phone"]); ?></font>
                <span>工作时间 <?php echo ($sys["time"]); ?></span>
            </div>
            <span style="display: inline-block;width: 110px;vertical-align: top;"><img src="/Public/Home/PCimages/phone_code.png" height="100" alt=""/>官方服务号</span>
            <!--<span style="display: inline-block;width: 110px;vertical-align: top;"><img src="/Public/Home/PCimages/phone_code_dyh.png" height="100" alt=""/>官方订阅号</span>--> </div>
        <div class="footer_icp">
            <p>Copyright © 2015-<?php echo (date('Y',(isset($data["time"]) && ($data["time"] !== ""))?($data["time"]):time())); ?> 版权所有 中迈融资租赁（广州）有限公司. <a href="http://www.miibeian.gov.cn/" target="_blank" style="color: #737476;font-size: 12px;">粤ICP备17115139号</a> <a href="http://www.beian.gov.cn/portal/registerSystemInfo?recordcode=44030002000001" target="_blank" style="color: #737476;font-size: 12px;"><img src="/Public/Home/PCimages/icon_yuewangga1.png" style="margin: -2px 0 0 5px;"> 粤公网安备 44030002000001号</a></p>
        </div>
    </div>
</footer>
<script src="/Public/Home/PCscript/jquery-1.3.2.min.js"></script>
<script src="/Public/Home/PCscript/function.js"></script>
<script src="/Public/Home/PCscript/main.js"></script>
<script src="/Public/Home/confirm/jquery.confirm.js"></script>
<script type="text/javascript">
    $(function(){
        $(function () {
            $(window).scroll(function(){
                if ($(window).scrollTop()>100){
                    $(".backtop").css('display','block');
                }
                else
                {
                    $(".backtop").css('display','none');
                }
            });

            $(".backtop").click(function(){
                $('body,html').animate({scrollTop:0},1000);
                return false;
            });

            $('.search-btn').on('click',function(){
            	var key = $('input[name=keys]').val();
            	if(key!=''&&key!=null){
            		window.location.href="<?php echo U('Index/lists',array('order'=>$_GET['order'],'sort'=>$_GET['sort']));?>"+"?key="+key;
            	}else{
            		window.location.href="<?php echo U('Index/lists',array('order'=>$_GET['order'],'sort'=>$_GET['sort']));?>";
            	}
            });

        });
    });
</script>
<script>
var serverTime = "<?php echo ($now_time); ?>"; //服务器时间，毫秒数
$(function(){
  var dateTime = new Date();
  var difference = dateTime.getTime() - serverTime; //客户端与服务器时间偏移量

  setInterval(function(){
   $(".endtime").each(function(){
    var obj = $(this);
    var endTime = new Date(parseInt(obj.attr('value')) * 1000);
    var nowTime = new Date();
    var nMS=endTime.getTime() - nowTime.getTime() + difference;
    var myD=Math.floor(nMS/(1000 * 60 * 60 * 24)); //天
    var myH=Math.floor(nMS/(1000*60*60)) % 24; //小时
    var myM=Math.floor(nMS/(1000*60)) % 60; //分钟
    var myS=Math.floor(nMS/1000) % 60; //秒
    var myMS=Math.floor(nMS/100) % 10; //拆分秒
    if(myD>= 0){
      var str = "<i class='show-lock'></i>"+myD+"天"+myH+"小时"+myM+"分"+myS+"."+myMS+"秒";
    }else{
      var str = "<i class='stop-lock'></i>已结束！";
    }
    obj.html(str);
   });
  }, 100); //每个0.1秒执行一次
});
</script>
</body>
</html>