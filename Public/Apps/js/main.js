$(document).ready(function () {
    initialize();
    if ($('.openParent').size()) {
        $('.openParent').on('click', function () {
            $(this).parent().toggleClass('ac');
        })
    }
    if ($('#setClass').size()) {
        $('#setClass').on('click', '.setSel', function () {
            var box = $('#' + $(this).attr('data-box'));
            var obj = $(this).parent().parent().children('.ac');
            if (obj.size()&&obj.index()!=$(this).parent().index()) {
                obj.removeClass('ac');
            }
            $(this).parent().toggleClass('ac');
            if (!$(this).parent().parent().children('.ac').size()) {
                box.removeClass('open');
            } else {
                box.addClass('open');
            }
        })
        $('#setClass').on('click', '.bg', function () {
            $(this).parent().toggleClass('open');
            $(this).parent().find('ul').children('.ac').removeClass('ac');
        })
        winEvent().onscroll(function () {
            100 <= (window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0) ? $('#setClass').addClass('fixed') : $('#setClass').removeClass('fixed');
        });
    }
    if ($('#mBanner').size()) {
        mBanner();
    }
    if ($('.jumpScroll').size()) {
        jumpScroll();
    }
    if ($('.swipImg').size()) {
        $('.swipImg').on('click', function () {
            $(this).attr('src', $(this).attr('data-action'));
        })
    }
    if ($('.goBack').size()) {
        $('.goBack').on('click', function () {
            window.history.go(-1);
        })
    }
});