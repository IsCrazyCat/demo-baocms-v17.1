/* 哈土豆添加失败不跳转 */
$(function () {
    $('.ajaxForm').ajaxForm({
        success: complete, // 这是提交后的方法
        dataType: 'json'
    });
});
//失败不跳转
function complete(data) {
    if (data.code == 1) {
		layer.msg(data.msg,{icon:1});
			setTimeout(function(){
			location.href = data.url;
		},3000);
		
    } else {
		layer.msg(data.msg);
		setTimeout(function () {
			lock = 0;
			eval(callback);
		}, timeout ? timeout : 3000);
    }
}

var niulock = 1;
var niunum = 1;

function showLoader(msg) {
	layer.load();
}

function hideLoader(){
    $("#loader").hide();
	layer.closeAll('loading');
}

function loaddata(page, obj, sc) {
    var link = page.replace('0000', niunum);
    showLoader('正在加载中....');

    $.get(link, function (data) {
        if (data != 0) {
            obj.append(data);
        }
        niulock = 0;
        hideLoader();
    }, 'html');
    if (sc === true) {
        $(window).scroll(function () {
			var wh = $(window).scrollTop();
			var xh = $(document).height() - $(window).height() - 70;
            if (!niulock && wh >= xh ) {
                niulock = 1;
                niunum++;
                var link = page.replace('0000', niunum);
				
                showLoader('正在加载中....');
				var timeout = setTimeout(function(){
					niulock = 0;
					hideLoader();
				},5000);
                $.get(link, function (data) {
                    if (data != 0) {
						if(timeout){ //清除定时器
							clearTimeout(timeout);
							timeout = null;
						}
                        obj.append(data);
                    }
                    niulock = 0;
                    hideLoader();
                }, 'html');
            }
        });
    }
}

function bind_mobile(url1,url2){
	layer.open({
		type: 1,
		title:'<h4>绑定手机后操作</h4>',
		skin: 'layui-layer-molv', //加上边框
		area: ['90%', '320px'], //宽高
		shift:6,
		content: '<div class="form-auto form-x"><div class="form-group"><div class="label"><label>手机号</label></div><div class="field form-inline"><input class="input input-auto" name="mobile" id="mobile" value="" placeholder="填写手机号码" size="20" type="text"> <button class="weui-btn weui-btn_primary" id="jq_send">获取验证码</button></div></div><div class="form-group"><div class="label" ><label>验证码</label></div><div class="field"><input class="input input-auto" name="yzm" id="yzm" value="" size="10" placeholder="填写验证码" type="text"></div></div><div class="form-button"><button id="go_mobile" class="weui-btn weui-btn_primary" type="submit">立刻认证</button></div></div>'
	});
	//获取验证码
	var mobile_timeout;
	var mobile_count = 100;
	var mobile_lock = 0;
	$(function () {
		$("#jq_send").click(function () {

			if (mobile_lock == 0) {
				mobile_lock = 1;
				$.ajax({
					url: url1,
					data: 'mobile=' + $("#mobile").val(),
					type: 'post',
					success: function (data) {
						if (data.status == 'success') {
							mobile_count = 60;
							layer.msg(data.msg,{icon:1});
							BtnCount();
						} else {
							mobile_lock = 0;
							layer.msg(data.msg,{icon:2});
						}
					}
				});
			}

		});
	});
	BtnCount = function () {
		if (mobile_count == 0) {
			$('#jq_send').html("重新发送");
			mobile_lock = 0;
			clearTimeout(mobile_timeout);
		}
		else {
			mobile_count--;
			$('#jq_send').html("重新发送(" + mobile_count.toString() + ")秒");
			mobile_timeout = setTimeout(BtnCount, 1000);
		}
	};
	//提交
	$('#go_mobile').click(function(){
		var ml = $('#mobile').val();
		var y = $('#yzm').val();
		$.post(url2,{mobile:ml,yzm:y},function(result){										
			if(result.status == 'success'){
				layer.msg(result.msg);
				setTimeout(function(){
					location.reload(true);
				},3000);
			}else{
				layer.msg(result.msg,{icon:2});
			}														
		},'json');
	})
	
	$('.layui-layer-content').css('padding','15px');
	
}