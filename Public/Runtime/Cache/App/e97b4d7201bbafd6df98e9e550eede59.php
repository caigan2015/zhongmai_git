<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <title>首页</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="icon" type="image/x-icon" href="/Public/favicon.ico">
    <link rel="stylesheet" href="/Public/Apps/css/style.css">
    <link rel="stylesheet" href="/Public/Apps/confirm/jquery.confirm.css">
    <script src="/Public/Apps/js/jquery-1.3.2.min.js"></script>
    <script src="/Public/Apps/js/function.js"></script>
    <script src="/Public/Apps/js/main.js"></script>
    <script src="/Public/Apps/confirm/jquery.confirm.js"></script>
</head>
    <body>
        <header class="top_header_box">
    <div class="top_header">
        <div class="left"><a href="index.html" class="link_a top_logo"><img src="/Public/Apps/images/logo.png" class="logo" alt="" /></a></div>
        <div class="center">
            <div style="text-align:left;width: 60%;border: 1px solid #eee;border-radius: .06667rem;float: right;height: 25px;line-height: 25px;margin-top: 13px;margin-right: 10px;">
                <span style="float: left;position: absolute;width: 25px;height:  25px;background: url('/Public/Apps/images/search-icon.png') no-repeat center;background-size:  15px  15px;"></span>
                <form action="<?php echo U('Index/lists');?>">
                    <input type="text" name="keys" value="<?php echo ($keys); ?>" placeholder="请输入关键字" style="width: 70%;height: 25px;line-height: 25px;padding-left: 25px;"/>
                </form>
            </div>
        </div>
        <div class="right">
            <div class="top_more_box">
                <a href="javascript:void(0);" class="link_a top_more_a openParent"></a>
                <div class="top_more">
                    <a href="<?php echo U('Index/index');?>" class="button_a"><i class="icon home"></i><span>首页</span></a>
                    <a href="<?php echo U('User/user');?>" class="button_a"><i class="icon user"></i><span>个人中心</span></a>
                    <a href="<?php echo U('Index/about');?>" class="button_a"><i class="icon about"></i><span>中迈简介</span></a>
                    <a href="<?php echo U('Index/contact');?>" class="button_a"><i class="icon contact"></i><span>联系我们</span></a>
                    <a href="<?php echo U('Index/help');?>" class="button_a"><i class="icon shace"></i><span>使用帮助</span></a>
                </div>
            </div>
        </div>
    </div>
</header>
        <div class="co_index_banner" id="mBanner">
            <ul class="lo_index_banner">
                <?php if(is_array($banner)): $i = 0; $__LIST__ = $banner;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$str): $mod = ($i % 2 );++$i;?><li>
                    <a href="<?php echo ($str['url']); ?>" target="_blank" class=""><img src="/Public/upload/banner/<?php echo ($str['img']); ?>"></a>
                </li><?php endforeach; endif; else: echo "" ;endif; ?>
            </ul>
        </div>
        <section class="co_index_intro">
            <ul>
                <li>车辆首付灵活掌控</li>
                <li>免购置税和保险</li>
                <li>享厂家售后保修保养</li>
                <li>提供牌照省心省力</li>
            </ul>
        </section>
        <div class="co_index_warp">
            <div class="co_index_warp_box">
                    <h3 class="co_title">
                        <p class="title">热卖车型</p>
                    </h3>
                </div>
            <ul class="lo_index_ad">
                <?php if(is_array($hot)): $i = 0; $__LIST__ = $hot;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$h): $mod = ($i % 2 );++$i;?><li>
                    <section class="co_pro_section" style="background-image:url(/Public/upload/product/<?php echo ($h["img"]); ?>)">
                        <a href="<?php echo U('Index/info',array('id'=>$h['id']));?>" class="fixed_a"></a>
                        <div class="title" style="font-size: 15px;font-family:'幼圆';"><?php echo ($h["title"]); ?></div>
                        <div class="text">
                            <p class="list">
                                <span class="key">首付</span>
                                <span class="name"><?php echo ($h["cost"]); ?>万</span>
                            </p>
                            <p class="list" style="margin-top: 10px;border-top: 1px dotted #d24747;">
                                <span class="key">月供</span>
                                <span class="name"><?php echo ($h["price"]); ?>元</span>
                            </p>
                            <?php if($h["specs"] != ''): ?><div class="tip"><?php echo ($h["specs"]); ?></div><?php endif; ?>
                        </div>
                        <!--<aside class="aside">
                                <p class="aside_title">热买</p>
                        </aside>-->
                    </section>
                </li><?php endforeach; endif; else: echo "" ;endif; ?>
            </ul>

            </div>
        <div class="co_index_warp">
            <div class="co_index_warp_box">
                    <h3 class="co_title">
                        <p class="title">活动推荐</p>
                    </h3>
                </div>
            <ul class="lo_index_ad">
                <?php if(is_array($act)): $i = 0; $__LIST__ = $act;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$a): $mod = ($i % 2 );++$i;?><li>
                    <section class="co_pro_section" style="background-image:url(/Public/upload/product/<?php echo ($a["img"]); ?>)">
                        <a href="<?php echo U('Index/info',array('id'=>$a['id']));?>" class="fixed_a"></a>
                        <div class="title" style="font-size: 15px;font-family:'幼圆';"><?php echo ($a["title"]); ?></div>
                        <div class="text">
                            <p class="list">
                                <span class="key">首付</span>
                                <span class="name"><?php echo ($a["cost"]); ?>万</span>
                            </p>
                            <p class="list" style="margin-top: 10px;border-top: 1px dotted #d24747;">
                                <span class="key">月供</span>
                                <span class="name"><?php echo ($a["price"]); ?>元</span>
                            </p>
                            <?php if($a["specs"] != ''): ?><div class="tip"><?php echo ($a["specs"]); ?></div><?php endif; ?>
                        </div>
                        <aside class="aside">
                                <p class="aside_title">促销</p>
                        </aside>
                    </section>
                </li><?php endforeach; endif; else: echo "" ;endif; ?>
            </ul>
             <div class="co_index_more">
                <a href="<?php echo U('Index/lists');?>" class="link_a">更多车源 ></a>
            </div>
        </div>
        <div class="co_index_warp">
    <div class="co_index_warp_box">
        <h3 class="co_title">
            <p class="title">申请流程</p>
            <i class="line"></i>
        </h3>
        <ul class="lo_index_li">
            <li><span>1</span>拨打提车吧热线或登录官网</li>
            <li><span>2</span>提出订车需求（我们提供一对一服务）</li>
            <li><span>3</span>定制灵活、个性的购车方案</li>
            <li><span>4</span>交订金（3000元）</li>
            <li><span>4</span>提车（15个工作日内便可把爱车带回家）</li>
        </ul>
    </div>
</div>
        <div class="co_index_warp">
    <div class="co_index_warp_box">
        <h3 class="co_title">
            <p class="title">常见问题</p>
            <i class="line"></i>
        </h3>
        <div class="co_index_ask_box">
            <?php if(is_array($que)): $i = 0; $__LIST__ = $que;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vq): $mod = ($i % 2 );++$i;?><dl class="lo_index_ask">
                <dt><?php echo ($vq["title"]); ?></dt>
                <dd><?php echo ($vq["content"]); ?></dd>
            </dl><?php endforeach; endif; else: echo "" ;endif; ?>
        </div>
    </div>
</div>

        <footer class="fo_function">
    <div class="fo_function_box">
        <ul class="lo_function">
            <li class="ac">
                <i class="img home"></i>
                <a href="<?php echo U('Index/index');?>" class="title">首页</a>
            </li>
            <li class="hot">
                <a href="<?php echo U('Index/lists');?>">
                <i class="img cc"></i>
                <p class="title">我要买车</p>
                </a>
            </li>
            <li>
                <i class="img user"></i>
                <a href="<?php echo U('User/user');?>" class="title">我的</a>
            </li>
        </ul>
    </div>
</footer>
    </body>
</html>