var lock = 0;


function showLoader(msg) {
	parent.layer.load(2);
}

function hideLoader(){
	$(".tumsgbox").hide();
    lock = 0;
    $("#loader").hide();
	parent.layer.closeAll('loading');
}

function hidde() {
    $(".tumsgbox").hide();
    lock = 0;
}


function success(msg, timeout, callback){
	hideLoader();
    parent.layer.msg(msg);
    setTimeout(function () {
        eval(callback);
    }, timeout ? timeout : 3000);
}


function error(msg, timeout, callback){
	hideLoader();
    parent.layer.msg(msg);
    setTimeout(function () {
        eval(callback);
    }, timeout ? timeout : 3000);
}



function jumpUrl(url) {
    if(url) {
        location.href = url;
    }else{
        history.back(-1);
    }
}
//点击后左侧弹内容
$(document).ready(function (e) {
	$(".tips").click(function () {
		var tipnr = $(this).attr('rel');
		layer.tips(tipnr, $(this), {
			tips: [4, '#1ca290'],
			time: 4000
		});
	})
});
			
//后台导出密码开始,www.juhucms.com
$(document).ready(function () {
    layer.config({
       extend: 'extend/layer.ext.js'
    });
    $(".export").click(function () {
       var admin_id = $(this).attr('admin_id');
       var url = $(this).attr('rel');
       var info = $(this).attr('info');
       parent.layer.prompt({formType: 1, value: '', title: info}, function (value) {
           if(value != "" && value != null) {
                $.post(url, {admin_id: admin_id,value:value}, function (data) {
                   if(data.status == 'success'){
                       layer.msg(data.msg, {icon: 1});
					   	   layer.close(value);
                           setTimeout(function(){
                           location.href = data.url;
                           },1000)
                        }else{
                            layer.msg(data.msg, {icon: 2});
                        }
						
                    }, 'json')
                }else{
                     layer.msg('填写密码', {icon: 2});
               }
            });
      })
	  
})
//后台导出密码结束			
function yzmCode() { //更换验证码
    $(".yzm_code").click();
}

function dialog(title, content, width, height) {
    var dialogHtml = '<div class="dialogBox" title="' + title + '"></div>';
    if ($(".dialogBox").length == 0) {
        $("body").append(dialogHtml);
    }

    $(".dialogBox").attr('title', title);
    $(".dialogBox").html(content);
    $(".dialogBox").dialog({
		show: true,
		hide: true,
        zIndex: 1000,
        width: width ? width : 300,
        height: height ? height : 200,
        modal: true
    });

}

function selectCallBack(id, name, v1, v2) {
    $("#" + id).val(v1);
    $("#" + name).val(v2);
    $(".dialogBox").dialog('close');
}


$(document).ready(function (e) {

    $(document).on("click", "input[type='submit']", function (e) {
        e.preventDefault();
        if (!lock) {
            if($(this).attr('rel')){
                $("#"+$(this).attr('rel')).submit();
            }else{
                $(this).parents('form').submit();    
            }
        }
    });
	
	
    $(".yzm_code").click(function () {
        $(this).find('img').attr('src', TU_ROOT + '/index.php?g=app&m=verify&a=index&mt=' + Math.random());
    });

    $(document).on("click", "a[mini='act']", function (e) {
        e.preventDefault();
		var url = $(this).attr('href');
        if (!lock) {
			parent.layer.confirm("您确定要" + $(this).html() + "吗？", {area: '150px', btn: ['是的', '不'], shade: false}, function (){
				showLoader();
                $("#x-frame").attr('src', url);
            })
        }
    });

    //全选
    $(document).on("click", ".checkAll", function (e) {
        var child = $(this).attr('rel');
        $(".child_" + child).prop('checked', $(this).prop("checked"));
    });


    $(document).on('click', "a[mini='list']", function (e) {
        e.preventDefault();
        if (!lock) {
            if (confirm("您确定要" + $(this).html())) {
                showLoader();
                $(this).parents('form').attr('action', $(this).attr('href')).submit();
            }
        }
    });


    $(document).on("click", "a[mini='load']", function (e) {
        e.preventDefault();
        if (!lock) {
            showLoader();
            var obj = $(this);
            $.get(obj.attr('href'), function (data) {
                if (data) {
                    dialog(obj.text(), data, obj.attr('w'), obj.attr('h'));
                }
                hideLoader();
            }, 'html');

        }
    });
	
	
    $(document).on("click", "a[mini='select']", function (e) {
        e.preventDefault();
        if (!lock) {
            showLoader();
            var obj = $(this);
            dialog(obj.text(), '<iframe id="select_frm" name="select_frm" src="' + obj.attr('href') + '" style="border:0px;width:' + (obj.attr('w') - 30) + 'px;height:' + (obj.attr('h') - 80) + 'px;"></iframe>', obj.attr('w'), obj.attr('h'));
            hideLoader();
        }
    });


    $(".searchG").click(function () {

        if ($(this).hasClass('searchGadd')) {
            $(this).removeClass("searchGadd");
        } else {
            $(this).addClass("searchGadd");
        }

        $(".selectNr2").slideToggle(200);
        $(".seleHidden").toggle(400);
    });



});