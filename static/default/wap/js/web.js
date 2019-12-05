
$.ajaxSetup({
    cache: false
});
var lock = 0;

function loading() {
    layer.msg('正在加载中...');
    //lock = 1;
}

function LoginSuccess() {
    $(".tudialog").remove();
    success('登录成功，正在为您跳转', 3000, "loginCallback()");
}

function loginCallback() {
    $.get(TU_ROOT + "/index.php?m=passport&a=check&mt=" + Math.random(), function (data) {
        $(".topOne").find('.left').html(data);
    }, 'html');
	location.reload();
    return true;
}

function ajaxLogin() {
    lock = 0;
    layer.closeAll();
    var boxHtml = '<div class="tudialog"></div>';
    if ($(".tudialog").length == 0) {
        $("body").append(boxHtml);
        $(".tudialog").css('height', document.body.scrollHeight + 'px');
    }
    var url = TU_ROOT + '/index.php?g=home&m=passport&a=ajaxloging&t=' + Math.random();
    var width = document.body.clientWidth
    $.get(url, function (data) {

        $(".tudialog").html('<div class="tudialog_bg"></div>' + data);
        var left = (width - 350) / 2;
        var top = $(window).scrollTop() + 100;
        $(".loginPop").css({
            'left': left + 'px',
            'top': top + 'px'
        });

        $(".tudialog").show();
    }, 'html');

}

function success(msg, timeout, callback) {
    layer.msg(msg);
    setTimeout(function () {
        lock = 0;
        $(".tumsgbox").hide();
        eval(callback);
    }, timeout ? timeout : 3000);
}


function error(msg, timeout, callback) {
    layer.msg(msg);
    setTimeout(function () {
        lock = 0;
        eval(callback);
    }, timeout ? timeout : 3000);
}


function jumpUrl(url) {
    if (url) {
        location.href = url;
    } else {
        history.back(-1);
    }
}

function yzmCode() { //更换验证码
    $(".yzm_code").click();
}



//layer begin
function bmsg(msg, url, timeout, callback) { //信息,跳转地址,时间
    layer.msg(msg);
    if (url) {
        setTimeout(function () {
            window.location.href = url;
        }, timeout ? timeout : 3000);
    } else if (url === 0) {
        setTimeout(function () {
            location.reload(true);
        }, timeout ? timeout : 3000);
    } else {
        eval(callback);
    }

}

function bopen(msg, close, style) {
    layer.open({
        type: 1,
        skin: style, //样式类名
        closeBtn: close, //不显示关闭按钮
        shift: 2,
        shadeClose: true, //开启遮罩关闭
        content: msg
    });

}


//layer end

function dialog(title, content, width, height) {
    var dialogHtml = '<div class="dialogBox" title="' + title + '"></div>';
    if ($(".dialogBox").length == 0) {
        $("body").append(dialogHtml);
    }

    $(".dialogBox").attr('title', title);
    $(".dialogBox").html(content);
    $(".dialogBox").dialog({
        zIndex: 1000,
        width: width ? width : 300,
        height: height ? height : 200,
        modal: true
    });

}

var input_array = Array();
$(document).ready(function (e) {
    
    $(".tips").click(function () {
        var tipnr = $(this).attr('rel');
        layer.tips(tipnr, $(this), {
            tips: [4, '#1ca290'],
            time: 4000
        });
    })

    $(document).on('click', '.yzm_code', function () {
        $("#" + $(this).attr('rel')).attr('src', TU_ROOT + '/index.php?g=app&m=verify&a=index&mt=' + Math.random());
    });

    $(document).on("click", "a[mini='act']", function (e) {
        e.preventDefault();
        if (!lock) {
            //loading();
            $("#x-frame").attr('src', $(this).attr('href'));
        }
    });

    $(document).on('click', "a[mini='confirm']", function (e) {
        e.preventDefault();
        var url = $(this).attr('href');
        if (!lock) {
            layer.confirm("您确定要" + $(this).html() + "吗？", {area: '150px', btn: ['是的', '不'], shade: false}, function () {
                $("#x-frame").attr('src', url);
            })
        }
    });

    $(document).on("click", "a[mini='buy']", function (e) { //购买的算法
        e.preventDefault();
        if (!lock) {
            loading();
            var url = $(this).attr('href');
            if (url.indexOf('?') > 0) {
                url += '&num=' + $('#' + $(this).attr('rel')).val();
            } else {
                url += '?num=' + $('#' + $(this).attr('rel')).val();
            }
            $("#x-frame").attr('src', url);
        }
    });

    $(document).on('click', "a[mini='list']", function (e) {
        e.preventDefault();
        if (!lock) {
            if (confirm("您确定要" + $(this).html())) {
                loading();
                $(this).parents('form').attr('action', $(this).attr('href')).submit();
            }
        }
    });

    $(document).on("click", "a[mini='tuan']", function (e) { //购买的算法
        e.preventDefault();
        if (!lock) {
            lock = 1;
            var url = $(this).attr('href');
            if (url.indexOf('?') > 0) {
                url += '&num=' + $('#' + $(this).attr('rel')).val();
            } else {
                url += '?num=' + $('#' + $(this).attr('rel')).val();
            }
            layer.msg("操作成功，正在跳转中...");
            setTimeout(function () {
                location.href = url;
            }, 2000)

        }
    });

    $(document).on("click", "a[mini='load']", function (e) {
        e.preventDefault();
        if (!lock) {
            loading();
            var obj = $(this);
            $.get(obj.attr('href'), function (data) {
                if (data == 0) {
                    ajaxLogin();
                } else {
                    dialog(obj.text(), data, obj.attr('w'), obj.attr('h'));

                }
                lock = 0;
                ;
            }, 'html');

        }
    });


    //全选
    $(document).on("click", ".checkAll", function (e) {
        var child = $(this).attr('rel');
        $(".child_" + child).prop('checked', $(this).prop("checked"));
    });


    $(document).on("click",".tu_closed",function(){
         $('.tudialog').hide();
    })



    $('.jq_star_show').each(function () {
        var val = $(this).attr('rel');
        var str = '';
        var num = parseInt(val / 10);
        var num2 = 5 - num;
        for (i = 0; i < num; i++) {
            str += '<img src="' + TU_PUBLIC + '/images/star1.jpg"/>';
        }
        for (i = 0; i < num2; i++) {
            str += '<img src="' + TU_PUBLIC + '/images/star2.jpg"/>';
        }
        $(this).html(str);
    });

    $(".jq_opacity_img img").mouseover(function () {
        $(this).stop().animate({
            opacity: '0.5'
        }, 300);
    }).mouseout(function () {
        $(this).stop().animate({
            opacity: '1'
        }, 300);
    });


    $(".jq_back_top").click(function (e) {
        var rel = $(this).attr('rel');
        rel = rel == undefined ? 200 : rel;
        $("html,body").animate({
            scrollTop: 0
        }, rel);
    });



});


 //全选
    $(document).on("click", ".checkAll", function (e) {
        var child = $(this).attr('rel');
        $(".child_" + child).prop('checked', $(this).prop("checked"));
    });
	
	//获取城市、地区、商圈的下拉菜单
function get_option(){

		var city_id = 0;
		var area_id = 0;
		var business_id = 0;
	
		var city_str = '<option value="0">请选择...</option>';
		for (a in cityareas.city) {
			if (city_id == cityareas.city[a].city_id) {
				city_str += '<option selected="selected" value="' + cityareas.city[a].city_id + '">' + cityareas.city[a].name + '</option>';
			} else {
				city_str += '<option value="' + cityareas.city[a].city_id + '">' + cityareas.city[a].name + '</option>';
			}
		}

		$("#city_id").html(city_str);
		$("#city_id").change(function () {
			if ($("#city_id").val() > 0) {
			   var city_id = $("#city_id").val();
					$.ajax({
						  type: 'POST',
						  url: window.CITYURL,
						  data:{cid:city_id},
						  dataType: 'json',
						  success: function(result)
						  {
							 var area_str = ' <option value="0">请选择...</option>';
							for (a in result) {
							  area_str += '<option value="' + result[a].area_id + '">' + result[a].area_name + '</option>';                              
							}
						   $("#area_id").html(area_str);
							$("#business_id").html('<option value="0">请选择...</option>');									
						  }
					});
			} else {
				$("#area_id").html('<option value="0">请选择...</option>');
				$("#business_id").html('<option value="0">请选择...</option>');
			}
		});



		$("#area_id").change(function () {

			if ($("#area_id").val() > 0) {
				area_id = $("#area_id").val();
					$.ajax({
						  type: 'POST',
						  url: window.BUSURL,
						  data:{bid:area_id},
						  dataType: 'json',
						  success: function(result)
						  {
							 var business_str = ' <option value="0">请选择...</option>';
							 for (a in result) {
									business_str += '<option value="' + result[a].business_id + '">' + result[a].business_name + '</option>';
							 }
							$("#business_id").html(business_str);
						 }
					   });
			} else {
				$("#business_id").html('<option value="0">请选择...</option>');
			}
		});


		$("#business_id").change(function () {
			business_id = $(this).val();
		});

}




function changeCAB(c,a,b){
	$("#city_ids").unbind('change');
	$("#area_ids").unbind('change');
	var city_ids = c;
	var area_ids = a;
	var business_ids = b;
	var city_str = ' <option value="0">请选择...</option>';
	for (b in cityareas.city) {
		if (city_ids == cityareas.city[b].city_id) {
			city_str += '<option selected="selected" value="' + cityareas.city[b].city_id + '">' + cityareas.city[b].name + '</option>';
		} else {
			city_str += '<option value="' + cityareas.city[b].city_id + '">' + cityareas.city[b].name + '</option>';
		}
	}
	$("#city_ids").html(city_str);

	$("#city_ids").change(function () {
		if ($("#city_ids").val() > 0) {
			city_id = $("#city_ids").val();
			   $.ajax({
					  type: 'POST',
					  url: window.CITYURL,
					  data:{cid:city_id},
					  dataType: 'json',
					  success: function(result)
					  {
						 var area_str = ' <option value="0">请选择...</option>';
						for (a in result) {
						  area_str += '<option value="' + result[a].area_id + '">' + result[a].area_name + '</option>';                              
						}
					   $("#area_ids").html(area_str);
						$("#business_ids").html('<option value="0">请选择...</option>');										
					  }
				});
			$("#area_ids").html(area_str);
			$("#business_ids").html('<option value="0">请选择...</option>');
		} else {
			$("#area_ids").html('<option value="0">请选择...</option>');
			$("#business_ids").html('<option value="0">请选择...</option>');
		}
	});

	 if (city_ids > 0) {  //编辑加载选中数据     
		var area_str = ' <option value="0">请选择...</option>';
		$.ajax({
		  type: 'POST',
		  url: window.CITYURL,
		  data:{cid:city_ids},
		  dataType: 'json',
		  success: function(result)
		  {
			 for (a in result) {
				if (area_ids == result[a].area_id) {
					area_str += '<option selected="selected" value="' + result[a].area_id + '">' + result[a].area_name + '</option>';
				} else {
					area_str += '<option value="' + result[a].area_id + '">' + result[a].area_name + '</option>';
				}
			  }
			 $("#area_ids").html(area_str);
			}
		});
	}


	$("#area_ids").change(function () {
		if ($("#area_ids").val() > 0) {
			area_id = $("#area_ids").val();
				$.ajax({
					  type: 'POST',
					  url: window.BUSURL,
					  data:{bid:area_id},
					  dataType: 'json',
					  success: function(result)
					  {
						 var business_str = ' <option value="0">请选择...</option>';
						 for (a in result) {
								business_str += '<option value="' + result[a].business_id + '">' + result[a].business_name + '</option>';
						 }
						$("#business_ids").html(business_str);
					 }

				   });
		} else {
			$("#business_ids").html('<option value="0">请选择...</option>');
		}
	});

	if (area_ids > 0) {  //编辑加载选中数据                                 
	   $.ajax({
		  type: 'POST',
		  url: window.BUSURL,
		  data:{bid:area_ids},
		  dataType: 'json',
		  success: function(result)
		  {
			var business_str = ' <option value="0">请选择...</option>';
			for (a in result) {
					if (business_ids == result[a].business_id) {
						business_str += '<option selected="selected" value="' + result[a].business_id + '">' + result[a].business_name + '</option>';
					} else {
					  business_str += '<option value="' + result[a].business_id + '">' + result[a].business_name + '</option>';
					}
			}
			 $("#business_ids").html(business_str);
		  }

	   });

	}


	$("#business_ids").change(function () {
		business_ids = $(this).val();
	});
}
