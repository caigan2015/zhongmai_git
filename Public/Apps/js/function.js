function initialize() {
    winEvent().onscroll(showGetTop());
}
function showGetTop() {
    if (!$('#showGetTop').size()) {
        $('body').append('<aside id="showGetTop" class="show_get_top"><a href="javascript:void(0);" class="link_a goTop"><i class="icon top"></i></a></aside>');
    }
    var obj = $('#showGetTop');
    $('#showGetTop').on('click', '.goTop', function () {
        $('html body').stop().animate({ 'scrollTop': 0 });
    })
    function showHide() {
        var sTop = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0;
        if (sTop > 400) {
            obj.addClass('ac');
        } else {
            obj.removeClass('ac');
        }
    }
    return showHide;
}
function mBanner() {
    window['mBanner'] = {}
    window['mBanner']['option'] = { 'obj': null, 'objUl': null, 'objAside': null, 'nowB': 0, 'maxB': 0, 'banN': null, time: 8000, 'isDarg': false, 'posX': 0, 'tempX': 0, 'startX': 0, 'startY': 0, 'endX': 0, 'padding': 0, 'temp': 0 }
    mBanner.option.obj = $('#mBanner');
    if (mBanner.option.obj.width() != $(window).width()) {
        mBanner.option.padding = $(window).width() - mBanner.option.obj.width();
    }
    mBanner.option.objUl = mBanner.option.obj.find('ul');
    mBanner.option.objAside = mBanner.option.obj.find('aside');
    mBanner.option.objUl.css('left', 0)
    mBanner.option.maxB = mBanner.option.objUl.find('li').length;
    bannerSize();
    addWindowResize(bannerSize);
    if (mBanner.option.objAside.size()) {
        for (i = 0; i < mBanner.option.objUl.find('li').length; i++) {
            mBanner.option.objAside.append('<em></em>')
        }
        mBanner.option.objAside.find('em').eq(0).addClass('ac');
    }
    mBanner.option.objUl.on('touchstart', function (e) { touchEvent(e); });
    mBanner.option.objUl.on('touchmove', function (e) { touchEvent(e); });
    mBanner.option.objUl.on('touchend', function (e) { touchEvent(e); });
    mBanner.option.banN = setTimeout(function () { mBanner.option.nowB++; autoPlayB(); }, mBanner.option.time);
    function touchEvent(event) {
        if (mBanner.option.obj.find('li').length <= 1) return;
        if (event.originalEvent.changedTouches.length == 1) {
            switch (event.type) {
                case 'touchstart':
                    mBanner.option.isDarg = true;
                    mBanner.option.temp = 1;
                    mBanner.option.startX = event.originalEvent.changedTouches[0].pageX;
                    mBanner.option.startY = event.originalEvent.changedTouches[0].pageY;
                    mBanner.option.tempX = mBanner.option.startX - parseInt(mBanner.option.objUl.css('left'));
                    //console.log(mBanner.option.isDarg);
                    break;
                case 'touchend':
                    if (mBanner.option.startX < mBanner.option.endX - 80) {
                        mBanner.option.nowB--;
                    }
                    if (mBanner.option.startX > mBanner.option.endX + 80) {
                        mBanner.option.nowB++;
                    }
                    autoPlayB();
                    mBanner.option.isDarg = false;
                    break;
                case 'touchmove':
                    //event.preventDefault();
                    if (mBanner.option.temp == 1) {
                        mBanner.option.temp = 0;
                        switch (true) {
                            case (event.originalEvent.changedTouches[0].pageY > mBanner.option.startY + 5 || event.originalEvent.changedTouches[0].pageY < mBanner.option.startY - 5): {
                                mBanner.option.isDarg = false;
                            }
                        }
                    }
                    if (mBanner.option.isDarg == true) {
                        event.preventDefault();
                        mBanner.option.posX = event.originalEvent.changedTouches[0].pageX - mBanner.option.tempX
                        mBanner.option.objUl.css('left', mBanner.option.posX);
                        mBanner.option.endX = event.originalEvent.changedTouches[0].pageX;
                    }
                    //console.log(mBanner.option.isDarg);
                    break;
            }
        }

    }
}
function autoPlayB() {
    mBanner.option.nowB = (mBanner.option.nowB < 0) ? 0 : mBanner.option.nowB;
    mBanner.option.nowB = (mBanner.option.nowB >= mBanner.option.maxB) ? mBanner.option.maxB - 1 : mBanner.option.nowB;
    ww = mBanner.option.obj.width();
    mBanner.option.objUl.stop().animate({ 'left': -(mBanner.option.nowB * ww) });
    mBanner.option.objAside.find('em.ac').removeClass('ac');
    mBanner.option.objAside.find('em').eq(mBanner.option.nowB).addClass('ac');
    clearTimeout(mBanner.option.banN);
    mBanner.option.banN = setTimeout(function () { mBanner.option.nowB++; autoPlayB(); }, mBanner.option.time);
}
function bannerSize() {
    BanNum = mBanner.option.objUl.find('li').size();
    BanWidth = setWindowWidth() * BanNum - mBanner.option.padding;
    mBanner.option.objUl.find('li').css({ 'width': setWindowWidth() - mBanner.option.padding + 'px', 'display': 'block' });
    mBanner.option.objUl.css({ 'width': BanWidth, 'height': 'auto' });
}
function setWindowWidth() {
    w = $('body').width();
    return w;
}
function setWindowHeight() {
    h = $('body').height();
    return h;
}
function addWindowResize(f) {
    if (document.all) {
        window.attachEvent('onresize', f)
    } else {
        window.addEventListener('resize', f, false);
    }
}
function addWindowScroll(f) {
    if (document.all) {
        window.attachEvent('onscroll', f)
    } else {
        window.addEventListener('scroll', f, false);
    }
}
function dlObj(o, l, c) {
    this.option = { obj: null, list: null, nowP: 0, maxP: 0, 'co': null, time: 8000, 'isDarg': false, 'posX': 0, 'tempX': 0, 'startX': 0, 'startY': 0, 'endX': 0, autoFun: null }
    this.option.obj = $(o);
    this.option.list = this.option.obj.find(l);
    this.option.maxP = this.option.obj.find(l).length;
    for (i = 0; i < this.option.maxP; i++) {
        this.option.list.eq(i).css({ 'width': setWindowWidth(), 'float': 'left' });
    }
    this.option.obj.css({ 'overflow': 'hidden' });
    this.option.list.parent().css({ 'width': this.option.maxP * setWindowWidth(), 'left': 0, 'position': 'relative' });
    this.option.list.parent().on('touchstart', '', this.option, function (e) { touchEvent(e); });
    this.option.list.parent().on('touchmove', '', this.option, function (e) { touchEvent(e); });
    this.option.list.parent().on('touchend', '', this.option, function (e) { touchEvent(e); });
    this.option.autoFun = function (o) {
        o.co.find('.nowP').html((o.nowP + 1) + '鐨�' + o.maxP);
        o.list.parent().stop().animate({ 'left': -o.nowP * setWindowWidth() });
    }
    if (c) {
        this.option.co = this.option.obj.find(c);
        this.option.co.find('.nowP').html((this.option.nowP + 1) + '鐨�' + this.option.maxP);
        this.option.co.on('click', '.prevP', this.option, function (e) {
            e.data.nowP--;
            touchEvent(e);
        })
        this.option.co.on('click', '.nextP', this.option, function (e) {
            e.data.nowP++;
            touchEvent(e);
        })
    }
    function touchEvent(event) {
        this.gogo = function () {
            if (event.data.nowP < 0) {
                event.data.nowP = event.data.maxP - 1;
            }
            if (event.data.nowP > event.data.maxP - 1) {
                event.data.nowP = 0;
            }
            event.data.autoFun(event.data);
        }
        switch (event.type) {
            case 'touchstart':
                {
                    event.data.isDarg = true;
                    event.data.temp = 1;
                    event.data.startX = event.originalEvent.changedTouches[0].pageX;
                    event.data.startY = event.originalEvent.changedTouches[0].pageY;
                    event.data.tempX = event.data.startX - parseInt(event.data.list.parent().css('left'));
                    break;
                }
            case 'touchmove':
                {
                    event.preventDefault();
                    event.data.posX = event.originalEvent.changedTouches[0].pageX - event.data.tempX;
                    // event.data.obj.css('left', event.data.posX);
                    event.data.list.parent().css('left', event.data.posX);
                    event.data.endX = event.originalEvent.changedTouches[0].pageX;
                    //console.log('move'+event.data.endX);
                    break;
                }
            case 'touchend':
                {
                    if (event.data.startX < event.data.endX - 100) {
                        event.data.nowP--;
                    }
                    if (event.data.startX > event.data.endX + 100) {
                        event.data.nowP++;
                    }
                    this.gogo();
                    break;
                }
            case 'click':
                {
                    this.gogo();
                    break;
                }
        }
    }
}
function jumpScroll() {
    var obj = $('.jumpScroll');
    var objA = $('.jumpScroll').find('.jumpA');
    var objY = [];
    var wH = $(window).height();
    objA.on('click', function () {
        var nowId = $('#' + $(this).attr('data-id'));
        if (!nowId.size()) return false;
            $('html body').stop().animate({ 'scrollTop': nowId.offset().top - 46 });
    })
    winEvent().onscroll(function () {
        var sTop = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0;
        var nowId = null;
        if (sTop > obj.get(0).offsetTop) {
            obj.addClass('fixed');
        } else {
            obj.removeClass('fixed');
        }
        objY = [];
        for (i = 0; i < objA.length; i++) {
            var temp = objA.eq(i).attr('data-id');
            if ($('#' + temp).size()) {
                objY.push({ 'name': temp, 'y': $('#' + temp).get(0).offsetTop - 60 });
            }
        }
        for (i = 0; i < objY.length; i++) {
            if (sTop > objY[i].y) nowId = objY[i];
        }
        if (nowId) {
            obj.find('[data-id]').removeClass('ac');
            obj.find('[data-id="' + nowId.name + '"]').addClass('ac');
        }
    })
    //window.onscroll = 
}


/*window事件防冲突*/
function winEvent() {
    var Event = {
        onload: function (func) {
            var oldonload = window.onload;
            if (typeof window.onload != 'function') {
                window.onload = func;
            }
            else {
                window.onload = function () {
                    oldonload();
                    func();
                }
            }
        },
        onresize: function (func) {
            var oldonresize = window.onresize;
            if (typeof window.onresize != 'function') {
                window.onresize = func;
            }
            else {
                window.onresize = function () {
                    oldonresize();
                    func();
                }
            }
        },
        onscroll: function (func) {
            var oldonscroll = window.onscroll;
            if (typeof window.onscroll != 'function') {
                window.onscroll = func;
            }
            else {
                window.onscroll = function () {
                    oldonscroll();
                    func();
                }
            }
        }
    }

    return Event;
}