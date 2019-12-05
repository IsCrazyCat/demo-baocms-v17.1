/**
 * Created by Administrator on 2017/3/21 0021.
 */
$(function(){

    var h = $(window).height();
    $('.bigData').height(h);
    
    $('.bigData .inner .threeBox > ul > li').height(h-340);
    $('.bigData .inner .threeBox > ul > li .out').height(h-360);
    $('.bigData .inner .threeBox > ul > li .out .in').height(h-400);
    $('.bigData .inner .threeBox > ul > li .out .in .switchPond').height(h-440);
    $('.bigData .inner .threeBox > ul > li .out .in .switchPond').height(h-440);
    $('.bigData .inner .threeBox > ul > li .out .in .switchPond .item').height(h-440);

    $('#fadeSwitch').carouselPlugin({
        'autoChange':true,
        'autoTime':5000,
        'changeSpend':0
    });
    $('#scroll').myScroll({
        speed: 40, //数值越大，速度越慢
        rowHeight: 30 //li的高度
    });

    

});