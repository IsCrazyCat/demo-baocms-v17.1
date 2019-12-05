var tulock = 1;
var tunum = 1;
var myScroll = null;



function dingwei(page, lat, lng) {
    page = page.replace('llaatt', lat);
    page = page.replace('llnngg', lng);
    $.get(page, function (data) {
    }, 'html');
}

/* 公用 */
