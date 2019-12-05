/**
 * Created by Administrator on 2016/4/27.
 */

$(function(){
    
    /*设置CSS*/
    $(".header .inner .searchPool .loginTxt a:nth-child(2)").css("color","#F85801");


    /* 后续添加筛选搜索 */
    $("#fileBox .default").click(function(){
		
        if( $(this).find("label").html() == "商品" ){
            $("#fileBox .itemU li").html("店铺");
        }else{
            $("#fileBox .itemU li").html("商品");
        }
        $(this).find("i").toggleClass('deg');
    	$("#fileBox .itemU").stop(true).slideToggle("fast");
    });
    $("#fileBox .itemU li").click(function(){
    	$("#fileBox .default label").html($(this).html());
    	$("#fileBox .itemU").slideUp("fast");
		if($(this).html() == "商品" ){
			$(this).attr("data-type","1");
		}else{
			$(this).attr("data-type","2");
			}
    });

    $('.topLine .inner ul.itemNav li.qrliB').click(function(){
        $(this).find('.hideQr').slideToggle('fast');
    });
    $(".orientation").hover(function(){
			$(this).find(".select_site").stop(false).slideDown(300);
		},function(){
			$(this).find(".select_site").stop(false).slideUp(300);
		
		})
});
