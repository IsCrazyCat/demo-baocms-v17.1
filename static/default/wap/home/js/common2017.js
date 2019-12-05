jQuery.cookie=function(name,value,options){if(typeof value!='undefined'){options=options||{};if(value===null){value='';options.expires=-1}var expires='';if(options.expires&&(typeof options.expires=='number'||options.expires.toUTCString)){var date;if(typeof options.expires=='number'){date=new Date();date.setTime(date.getTime()+(options.expires*24*60*60*1000))}else{date=options.expires}expires='; expires='+date.toUTCString()}var path=options.path?'; path='+(options.path):'';var domain=options.domain?'; domain='+(options.domain):'';var secure=options.secure?'; secure':'';document.cookie=[name,'=',encodeURIComponent(value),expires,path,domain,secure].join('')}else{var cookieValue=null;if(document.cookie&&document.cookie!=''){var cookies=document.cookie.split(';');for(var i=0;i<cookies.length;i++){var cookie=jQuery.trim(cookies[i]);if(cookie.substring(0,name.length+1)==(name+'=')){cookieValue=decodeURIComponent(cookie.substring(name.length+1));break}}}return cookieValue}};
//imagesLoaded
$.fn.imagesLoaded=function(callback){var $this=$(this),$images=$this.find('img').add($this.filter('img')),len=$images.length,blank='data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==';function triggerCallback(){callback.call($this,$images)}function imgLoaded(event){if(--len<=0&&event.target.src!==blank){setTimeout(triggerCallback);$images.unbind('load error',imgLoaded)}}if(!len){triggerCallback()}$images.bind('load error',imgLoaded).each(function(){if(this.complete||typeof this.complete==="undefined"){var src=this.src;this.src=blank;this.src=src}});return $this};

function isIE6(){return getIEVersion() === '6'}
function getIEVersion(){
	var a=document;
	if(a.body.style.scrollbar3dLightColor!=undefined){
		if(a.body.style.opacity!=undefined){
			return "9"
		}else if(a.body.style.msBlockProgression!=undefined){
			return "8"
		}else if(a.body.style.msInterpolationMode!=undefined){
			return "7"
		}else if(a.body.style.textOverflow!=undefined){
			return "6"
		}else{
			return "IE5.5"
		}
	}
	return false;
}
var unique = function( array ) {//数组去重
	var ret = [], record = {}, it, tmp;
	var type = {
	"number": function(n){ return "_TUI_num" + n; },
	"string": function(n){ return n; },
	"boolean": function(n){ return "_TUI_boolean" + n; },
	"object": function(n){ return n === null ? "TUI_null" : $.data(n); },
	"undefined": function(n){ return "_TUI_undefined"; }
	};
	for ( var i = 0, length = array.length; i < length; i++ ) {
	it = tmp = array[i];
	tmp = type[typeof it](it);
	if( !record[tmp]) {
	ret.push(it);
	record[tmp] = true;
	}
	}
	return ret;
};
function changeTwoDecimal(x){//保留两位小数，四舍五入
	var f_x = parseFloat(x);
	if (isNaN(f_x)){
		//console.info('function:changeTwoDecimal->parameter error');
		return false;
	}
	if(f_x.toString().lastIndexOf('.')!==-1){
		f_x=f_x.toFixed(2);
	}
	return f_x;
}
function changeTwoDecimal2(x){//保留两位小数，不四舍五入
	var f_x = parseFloat(x);
	if (isNaN(f_x)){
		console.info('function:changeTwoDecimal->parameter error');
		return false;
	}
	f_x=f_x.toFixed(3);
	f_x = f_x.substring(0,f_x.lastIndexOf('.')+3);
	return f_x;
}
/*选项卡切换
对象属性{选项卡序号|按钮序号||选项卡总数量}
*/
function Show_TabADSMenu(tabadid_num,tabadnum,tabNums){
	for(var i=0;i<tabNums;i++){document.getElementById("tabadcontent_"+tabadid_num+i).style.display="none";}
	for(var i=0;i<tabNums;i++){document.getElementById("tabadmenu_"+tabadid_num+i).parentNode.className="";}
	document.getElementById("tabadmenu_"+tabadid_num+tabadnum).parentNode.className="selected";
	document.getElementById("tabadcontent_"+tabadid_num+tabadnum).style.display="block";
	return false;
}
function Show_TabADSMenu2(tabadid_num,tabadnum,tabNums){
	for(var i=0;i<tabNums;i++){document.getElementById("tabadcontent_"+tabadid_num+i).style.display="none";}
	for(var i=0;i<tabNums;i++){document.getElementById("tabadmenu_"+tabadid_num+i).className="";}
	document.getElementById("tabadmenu_"+tabadid_num+tabadnum).className="current";
	document.getElementById("tabadcontent_"+tabadid_num+tabadnum).style.display="block";
}
function loginout(siteUrl){
	var url = siteUrl+"request.ashx?action=loginout&json=1&jsoncallback=?&date=" + new Date();
	$.getJSON(url,function(data){
		
		if(data[0].islogin === '0'){
			if(data[0].bbsopen === "open"){
				var   f=document.createElement("IFRAME")   
				f.height=0;   
				f.width=0;   
				f.src=data[0].bbsloginurl;
				if (f.attachEvent){
					f.attachEvent("onload", function(){
						window.location.reload();
					});
				} else {
					f.onload = function(){
						window.location.reload();
					};
				}
				document.body.appendChild(f);
			}else{
				window.location.reload();
			}
		}else{
			alert("对不起，操作失败！");
		}
	}).error(function(){alert("对不起，操作失败！");});
}
function is_login(siteUrl,tplPath,hasQQ,hasWX,source){
	var sourceS = source || '';
	var url = siteUrl+"request.ashx?action=islogin&tempid="+sourceS+"&json=1&jsoncallback=?&date=" + new Date(),
		node = $("#login_info"),txt='';
	var hash = window.location.href.indexOf("from=")<0?'from='+encodeURIComponent(window.location.href):'';
	var hasQQs = hasQQ === '1';
	var hasWXs = hasWX === '1'
	var qqImg = !!hasQQs?"<li><a href=\""+siteUrl+"member/qq.aspx?"+hash+"\"><img src=\""+tplPath+"images/qq_login.png\" alt=\"\" /></a></li>":'';
	var wxImg = !!hasWXs?"<li><a href=\""+siteUrl+"member/qq.aspx?action=weixin&"+hash+"\"><img src=\""+tplPath+"images/wx_login.png\" alt=\"\" /></a></li>":'';
	
	var sj_btn = "";
	if(typeof siteUrl !== 'undefined'){window['siteUrl'] = siteUrl;}
	if(typeof tplPath !== 'undefined'){window['tplPath'] = tplPath;}
	$.getJSON(url,function(data){
		if(data[0].islogin==="1"){
			if(data[0].jibie === '1'||data[0].jibie === '2'||parseInt(data[0].manageshopid)>0){
				sj_btn = " <a href=\""+siteUrl+"member/userindex_s.aspx\" class=\"shangjia\" target=\"_blank\">商家平台</a>";
			}
			txt="<p class=\"login_success\"><span class=\"username\">"+data[0].name+"</span>，您好！<a href=\""+siteUrl+"member/index.html\">管理</a>"+sj_btn+" <a href=\"javascript:loginout('"+siteUrl+"');\">退出</a></p>";
			txt+="<input value=\"1\" id=\"isLogin\" type=\"hidden\" /><input value=\""+data[0].jibie+"\" id=\"user_jibie\" type=\"hidden\" />";
			loadWEBmessage();//消息系统
			if(typeof getUserState !== 'undefined'){
				window['userDate'] = data[0];
				getUserState();
			}
		}else{
			txt="<ul class=\"clearfix\">"+wxImg+qqImg+"<li><a href=\""+siteUrl+"member/register.html?"+hash+"\">免费注册</a> | <a href=\""+siteUrl+"member/login.html?"+hash+"\">登录</a></li></ul><input value=\"0\" id=\"isLogin\" type=\"hidden\" /><input value=\"\" id=\"user_jibie\" type=\"hidden\" />";
			if(typeof getUserState !== 'undefined'){
				window['userDate'] = {};
				getUserState();
			}
		}
		node.prepend(txt);
	}).error(function(err){
		//alert(err);
	});
}
function is_login2(siteUrl,tplPath,hasQQ,hasWX,source){
	var sourceS = source || '';
	var url = siteUrl+"request.ashx?action=islogin&tempid="+sourceS+"&json=1&jsoncallback=?&date=" + new Date(),
		node = $("#login_info"),txt='';
	
	var hash = window.location.href.indexOf("from=")<0?'from='+encodeURIComponent(window.location.href):'';
	var hasQQs = hasQQ === '1';
	var hasWXs = hasWX === '1'
	var qqImg = !!hasQQs?"<li class=\"bor\"><a href=\""+siteUrl+"member/qq.aspx?"+hash+"\"><img src=\""+tplPath+"images/qq_login_2.png\" alt=\"\" /></a></li>":'';
	var wxImg = !!hasWXs?"<li class=\"bor\"><a href=\""+siteUrl+"member/qq.aspx?action=weixin&"+hash+"\"><img src=\""+tplPath+"images/wx_login_2.png\" alt=\"\" /></a></li>":'';
	var sj_btn = "";
	
	if(typeof siteUrl !== 'undefined'){window['siteUrl'] = siteUrl;}
	if(typeof tplPath !== 'undefined'){window['tplPath'] = tplPath;}
	
	$.getJSON(url,function(data) {
		if(data[0].islogin==="1"){
			if(data[0].jibie === '1'||data[0].jibie === '2'||parseInt(data[0].manageshopid)>0){
				//sj_btn = " <a href=\""+siteUrl+"member/userindex_s.aspx\" class=\"shangjia\" target=\"_blank\">商家平台</a>";
			}
			txt="<li><p class=\"login_success\"><span class=\"username\">"+data[0].name+"</span>，您好！ <a href=\""+siteUrl+"member/index.html\">管理</a>"+sj_btn+" <a href=\"javascript:loginout('"+siteUrl+"');\">退出</a></p></li>";
			txt+="<input value=\"1\" id=\"isLogin\" type=\"hidden\" /><input value=\""+data[0].jibie+"\" id=\"user_jibie\" type=\"hidden\" />";
			loadWEBmessage();//消息系统
			if(typeof getUserState !== 'undefined'){
				window['userDate'] = data[0];
				getUserState();
			}
		}else{
			txt="<li><a href=\""+siteUrl+"member/register.html?"+hash+"\">免费注册</a></li><li class=\"bor\"><a a href=\""+siteUrl+"member/login.html?"+hash+"\">登录</a></li><li class=\"bor\"><a href=\""+siteUrl+"find.aspx\">忘记密码</a></li>"+qqImg+wxImg+"<input value=\"0\" id=\"isLogin\" type=\"hidden\" /><input value=\"\" id=\"user_jibie\" type=\"hidden\" />";
			if(typeof getUserState !== 'undefined'){
				window['userDate'] = {};
				getUserState();
			}
		}
		node.prepend(txt);
	}).error(function(err){
		//alert(err);
	});;
}
function is_login3(siteUrl,tplPath,hasQQ,hasWX,source){
	var sourceS = source || '';
	var url = siteUrl+"request.ashx?action=islogin&tempid="+sourceS+"&json=1&jsoncallback=?&date=" + new Date(),
		node = $("#login_info"),txt='';
	
	var hash = window.location.href.indexOf("from=")<0?'from='+encodeURIComponent(window.location.href):'';
	var hasQQs = hasQQ === '1';
	var hasWXs = hasWX === '1'
	var qqImg = !!hasQQs?'<dl><dt class="zn"><a href="'+siteUrl+'member/qq.aspx?'+hash+'">QQ登录</a></dt></dl><dl class="xian"></dl>':'';
	var wxImg = !!hasWXs?'<dl><dt class="zn"><a href="'+siteUrl+'member/qq.aspx?action=weixin&'+hash+'">微信登录</a></dt></dl><dl class="xian"></dl>':'';
	
	var sj_btn = "";
	
	if(typeof siteUrl !== 'undefined'){window['siteUrl'] = siteUrl;}
	if(typeof tplPath !== 'undefined'){window['tplPath'] = tplPath;}
	
	$.getJSON(url,function(data) {
		if(data[0].islogin==="1"){
			if(data[0].jibie === '1'||data[0].jibie === '2'||parseInt(data[0].manageshopid)>0){
				//sj_btn = " <a href=\""+siteUrl+"member/userindex_s.aspx\" class=\"shangjia\" target=\"_blank\">商家平台</a>";
			}
			txt="<p class=\"login_success\"><span class=\"username\">"+data[0].name+"</span>，您好！ <a href=\""+siteUrl+"member/index.html\">管理</a>"+sj_btn+" <a href=\"javascript:loginout('"+siteUrl+"');\">退出</a></p><dl class=\"xian\"></dl>";
			txt+="<input value=\"1\" id=\"isLogin\" type=\"hidden\" /><input value=\""+data[0].jibie+"\" id=\"user_jibie\" type=\"hidden\" />";
			loadWEBmessage();//消息系统
			if(typeof getUserState !== 'undefined'){
				window['userDate'] = data[0];
				getUserState();
			}
		}else{
			txt='<dl style="width:84px;">'+
				'<dt>你好，<a href="'+siteUrl+'member/login.html?'+hash+'" class="cur">请登录</a></dt>'+
			'</dl><dl>'+
				'<dt class="zn"><a href="'+siteUrl+'member/register.html?'+hash+'">免费注册</a></dt>'+
			'</dl><dl class="xian"></dl>'+qqImg+wxImg+
			'<input value="0" id="isLogin" type="hidden" /><input value="" id="user_jibie" type="hidden" />';
			if(typeof getUserState !== 'undefined'){
				window['userDate'] = {};
				getUserState();
			}
		}
		node.prepend(txt);
	}).error(function(err){
		//alert(err);
	});;
}
/*var orderPollingFrame = (function(){       
	return function(callback,speed){       
		window.setTimeout(callback, speed);       
	};       
})();*/


var message_pid="-1";
var message_isstop = false;//页面是否丢失服务权
var message_isforced = false;//是否被强制拉回服务权页面,被丢失时又强制拉回权时,完全停止弱探测
function loadWEBmessage(){
	var url = window['siteUrl']+'api/request.ashx?pid=' +message_pid + '&jsoncallback=?';
	$.getJSON(url,function(data){
		if(data[0].islogin === '1'){WebMessageShow(data);}
		if(data[0].islogin === '1' || data[0].islogin === '0'){
			/*if( message_pid != '-1' &&  message_pid != data[0].pid){
		  		$('#message_show').html('活动页面丢失,被重新找回连接权');
		    }*/
			message_pid=data[0].pid;
			window.setTimeout(function(){loadWEBmessage()},200);//高速探测:间隔时间短100-200毫秒,弱探测:间隔1-2分钟以上
		}else{
			/*$('#message_show').html('信息获取被另一页面取代，本页面抓取信息进入弱探测');*/
			message_isstop = true;
			if(message_isforced){
				message_isforced=false;
			}else{
				if( message_pid === '-1' )message_pid='0';
			    window.setTimeout(function(){loadWEBmessage()},1*60000);////被取代后每2分钟尝试一次连接,检测活动页面是否丢失
			}
		}
	}).error(function(err){//失败2分钟后尝试一次
		window.setTimeout(function(){loadWEBmessage()},2*60000);
	});
	/* 
	data[0].islogin:0无信息,1:有信息MSG,2:停止高速探测,改为弱探测区别是间隔时间.
	*/
	/*$(window).blur(function(){
		RunOnunload();
	});
	$(window).focus(function(){
		newloadWEBmessage();
	});*/
}
function newloadWEBmessage(){
	//当页面发生任何刷新或鼠标动作或任意操作时,表示前活动页面已经不是焦点页面,当前页面重新初始参数强行抓回信息获取权
	//问题:如何防止本页面并行执行loadWEBmessage(),自动执行一次,强制执行一次.
	if(message_isstop){
	  	message_isstop = false;
		message_isforced =true;
    	message_pid="-1";
	    loadWEBmessage();
    }
}
function RunOnunload(){//当前页面关闭时执行,将程序里当前链接关闭,无需返回任何数据
	var url = window['siteUrl']+'api/request.ashx?action=close&pid=' +message_pid + '&jsoncallback=?';
	$.getJSON(url,function(data){});
}
function WebMessageShow(data){
	var idata = data[0]['MSG'];
	
	var newOrderId='webMessage';
	function countItem(){
		var len = $('#'+newOrderId).find('.item').length;
		$('#WebMessageNum').html(len);
		if(len === 0){
			$('#'+newOrderId).hide();	
		}
	}
	if(typeof idata['mp3'] !== 'undefined' && idata['mp3'] !==''){
		WebMessageMusic(idata['mp3']);
	}
	if(!$('#'+newOrderId)[0]){
		var divs = document.createElement('div');
		divs.id = newOrderId;
		$('body').append(divs);
		divs.innerHTML = '<div class="hd">您有<span id="WebMessageNum">0</span>条新信息</div><div class="bd" id="WebMessageInner"></div><a href="#" class="close">收起</a><a href="#" class="remove">移除</a>';
		$('#'+newOrderId).find('.close').click(function(e){
			e.preventDefault();
			$('#WebMessageInner').slideToggle();
			$(this).toggleClass('open');
		}).end().find('.remove').click(function(e){
			e.preventDefault();
			$('#'+newOrderId).hide();
		}).end().on( "click", ".view", function(e){
			if(typeof idata['notViewCloseALL'] !=='undefined' && idata['notViewCloseALL'] === '1'){//点击查看移除全部同类型消息
				$(this).parent().parent().remove();
			}else{
				$('#'+newOrderId).find('.tplid_'+$(this).attr('data-tplid')).remove();
			}
			countItem();
		}).on( "click", ".del", function(e){
			e.preventDefault();
			$(this).parent().parent().remove();
			countItem();
		});
	}else{
		$('#'+newOrderId).show();
		$('#WebMessageInner').slideDown();
	}
	var txt = $('<div class="item tplid_'+idata.tplid+'">'+idata.title+'<p class="date">'+idata.dtappenddate+'</p><span class="panel"><a href="'+window['siteUrl']+'api/request.ashx?action=click&msgid='+idata.msgid+'&smsurl='+idata.smsurl+'" class="view" data-tplid="'+idata.tplid+'" target="_blank">查看详细</a> <a href="#" class="del">忽略</a></span><s class="s"></s></div>');
	var WebMessageInner = $('#WebMessageInner');
	setTimeout(function(){WebMessageInner.append(txt);WebMessageInner[0].scrollTop = WebMessageInner[0].scrollHeight;},50);
	$('#WebMessageNum').html(parseInt($('#WebMessageNum').html())+1);
}
function WebMessageMusic(file){
	if(typeof window['my_jPlayer'] === 'undefined'){
		$.ajax({url:window['tplPath']+"js/jquery.jplayer.min.js",dataType:'script'}).done(function(){
			setTimeout(function(){
				$('body').append('<div id="jquery_jplayer"></div>');
				window['my_jPlayer'] = $("#jquery_jplayer");
				my_jPlayer.jPlayer({
					ready: function (event) {
						$(this).jPlayer("setMedia",{mp3: file});
						if(typeof notplay === 'undefined'){window['my_jPlayer'].jPlayer('play');}
					},
					swfPath: window['tplPath']+"js", // jquery.jplayer.swf 文件存放的位置
					supplied: "mp3",
					wmode: "window"
				});
			},200);
		});
	}else{
		window['my_jPlayer'].jPlayer("setMedia",{mp3: file});
		window['my_jPlayer'].jPlayer('play');
	}
	return false;
}
function get_user_isOnline(sid){
	var url = window['siteUrl']+'api/request.ashx?action=useronline&userids='+sid + '&jsoncallback=?';
	$.getJSON(url,function(data){
		var res = data[0];
		if(res.islogin === '1'){
			for(var i=0;i<res['MSG'].length;i++){
				if(res['MSG'][i]['isonline'] === '1'){
					$('.user_'+res['MSG'][i]['userid']).addClass('online');
				}
			}
		}
	});
}
function get_bbs(bbsUrl,cat,num,node){
	var moban = cat==='new'?'bbs.html':'bbs2.html';
	var timer=Math.random(),
		url = '../request.ashx?action=getdata&timer='+timer+'&tempid=20&template='+moban;
	$.get(url,function(dt){
		node.empty();		
		node.attr('data-success','1').html(dt).removeClass('loadding');
	});
}
function get_live(liveUrl,cat,num,node){
	var timer=Math.random(),
		arr_c = cat.split('_'),
		moban = 'index_'+arr_c[0]+'.html',
		ids = arr_c[1],
		url = '../request.ashx?action=getdata&timer='+timer+'&tempid=20&template='+moban+'&id='+ids+'&jsoncallback=?',
		i=0,
		txt='';
	$.get(url,function(result){
		dataArr = eval('('+result+')');
		showData(dataArr[0].arr);
	});
	function showData(dataArr){
		$.each(dataArr,function(i,item){
			if(num>i&&typeof item.id!=='undefined'){txt+='<li><a href="'+item.catURL+'" target="_blank" class="gray">['+item.cat+']</a> <a href="'+item.sURL+'" target="_blank" style="color:'+item.color+'; font-weight:'+item.fontbold+';">'+item.title+'</a><em>'+item.sdate+'</em></li>';}
		});
		node.attr('data-success','1').html(txt).removeClass('loadding');
	}
}
function get_other(otherUrl,cat,num,node){
	var timer=Math.random(),
		arr_c = cat.split('_'),
		moban = 'index_'+arr_c[0]+'.html',
		ids = arr_c[1],
		url = '../request.ashx?action=getdata&timer='+timer+'&tempid=20&template='+moban+'&id='+ids+'&jsoncallback=?',
		i=0,
		txt='';
	$.get(url,function(result){
		dataArr = eval('('+result+')');
		showData(dataArr[0].arr);
	});
	function showData(dataArr){
		$.each(dataArr,function(i,item){
			if(num>i&&typeof item.id!=='undefined'){txt+='<li><a href="'+item.sURL+'" target="_blank"><img src="'+item.img+'" width="95" height="70" alt="" /><span class="title">'+item.title+'</span><span class="price">'+item.price+'</span></a></li>';}
		});
		node.attr('data-success','1').html(txt).removeClass('loadding');
	}
}
$.fn.confirmForm = function(){
	this.each(function(){
		var list = $(this).find('.gx_confirm');
		var forname = $(this).attr('data-name');
		var sid=$('input[name="'+forname+'"]:checked').attr('value');
		
		if(sid ==='1'){
			$(this).find('.gx_confirm').addClass('current').attr('data-val','1');
		}
		list.click(function(e){
			e.preventDefault();
			if($(this).attr('data-val') === '0'){
				$(this).attr('data-val','1');
				$(this).addClass('current');
				$('input[name="'+forname+'"][value="1"]').prop('checked',true);
			}else{
				$(this).attr('data-val','0');
				$(this).removeClass('current');
				$('input[name="'+forname+'"][value="0"]').prop('checked',true);
			}
		});
	});
}
$.fn.radioForm = function(){
	this.each(function(){
		var list = $(this).find('.gx_radio');
		var forname = $(this).attr('data-name');
		var sid=$('input[name="'+forname+'"]:checked').attr('value');
		if(sid !=='' && !!sid){
			$(this).find('.gx_radio').removeClass('current');
			$(this).find('.gx_radio[data-val="'+sid+'"]').addClass('current');
		}
		list.click(function(e){
			e.preventDefault();
			var target = $('input[name="'+forname+'"][value="'+$(this).attr('data-val')+'"]');
			if(!!target.prop('disabled')){return false;}
			target.prop('checked',true);
			list.removeClass('current');
			$(this).addClass('current');
		});
	});
}
$.fn.checkboxForm = function(){
	this.each(function(){
		var t = $(this);
		var list = $(this).find('.gx_check');
		var forname = $(this).attr('data-name');
		var form_list = $(this).find('input[name="'+forname+'"]:checked');
		form_list.each(function(){
			var sid = $(this).attr('value');
			t.find('.gx_check[data-val="'+sid+'"]').addClass('current');
		});
		list.click(function(e){
			e.preventDefault();
			var target = $('input[name="'+forname+'"][value="'+$(this).attr('data-val')+'"]');
			if(!!target.prop('disabled')){return false;}
			target.prop('checked',!target.prop('checked'));
			$(this).toggleClass('current');
		});
	});
}
$.fn.TabADS_tieba = function(){
	var obj = $(this);
	var currentClass = "current";
	var tabs = obj.find(".hd-inner").find("a");
	var conts = obj.find(".news");
	var t;
	tabs.eq(0).addClass(currentClass);
	conts.hide();
	conts.eq(0).show();
	tabs.each(function(i){
		$(this).bind("mouseover",function(){
			 t = setTimeout(function(){
				conts.hide().eq(i).show();
				tabs.removeClass(currentClass).eq(i).addClass(currentClass);
			},300);
		}).bind("mouseout",function(){
			clearTimeout(t);
		});
	});
}
$.fn.TabADS4 = function(thisUrl,num,callback,isGO){
	var obj = $(this),
		currentClass = "selected",
		tabs = obj.find(".tab-hd").find(".item"),
		conts = obj.find(".tab-cont"),
		tab,cat,cont,is_success,t;
	
	callback = callback==='bbs'?get_bbs:(callback==='live'?get_live:get_other);
	tabs.eq(0).addClass(currentClass);
	conts.hide();
	conts.eq(0).show();
	isGO&&callback.call(this,thisUrl,tabs.eq(0).attr('data-cat'),num,conts.eq(0))
	tabs.each(function(i){
		$(this).bind("mouseover",function(){
			 t = setTimeout(function(){
				tab=tabs.eq(i);
				cont=conts.eq(i);
				conts.hide();
				cont.show();
				tabs.removeClass(currentClass);
				tab.addClass(currentClass);
			 	is_success=cont.attr('data-success')==='1'?true:false;
				cat=tab.attr('data-cat');
				!is_success&&callback.call(obj,thisUrl,cat,num,cont);
			},300);
		}).bind("mouseout",function(){
			clearTimeout(t);
		});
	});
}
$.fn.TabADS3 = function(thisUrl,num,callback,isGO){
	var obj = $(this),
		currentClass = "current",
		tabs = obj.find(".hd-inner").find("a"),
		conts = obj.find(".news"),
		tab,cat,cont,is_success,t;
	
	callback = callback==='bbs'?get_bbs:(callback==='live'?get_live:get_other);
	tabs.eq(0).addClass(currentClass);
	conts.hide();
	conts.eq(0).show();
	isGO&&callback.call(this,thisUrl,tabs.eq(0).attr('data-cat'),num,conts.eq(0))
	tabs.each(function(i){
		$(this).bind("mouseover",function(){
			 t = setTimeout(function(){
				tab=tabs.eq(i);
				cont=conts.eq(i);
				conts.hide();
				cont.show();
				tabs.removeClass(currentClass);
				tab.addClass(currentClass);
			 	is_success=cont.attr('data-success')==='1'?true:false;
				cat=tab.attr('data-cat');
				!is_success&&callback.call(obj,thisUrl,cat,num,cont);
			},300);
		}).bind("mouseout",function(){
			clearTimeout(t);
		});
	});
}

$.fn.TabADS2 = function(thisUrl,num,callback,isGO){
	var obj = $(this),
		currentClass = "selected",
		tabs = obj.find(".tab-hd").find("li"),
		conts = obj.find(".tab-cont"),
		tab,cat,cont,is_success,t;
	
	callback = callback==='bbs'?get_bbs:(callback==='live'?get_live:get_other);
	tabs.eq(0).addClass(currentClass);
	conts.hide();
	conts.eq(0).show();
	isGO&&callback.call(this,thisUrl,tabs.eq(0).attr('data-cat'),num,conts.eq(0))
	tabs.each(function(i){
		$(this).bind("mouseover",function(){
			 t = setTimeout(function(){
				tab=tabs.eq(i);
				cont=conts.eq(i);
				conts.hide();
				cont.show();
				tabs.removeClass(currentClass);
				tab.addClass(currentClass);
			 	is_success=cont.attr('data-success')==='1'?true:false;
				cat=tab.attr('data-cat');
				!is_success&&callback.call(obj,thisUrl,cat,num,cont);
			},300);
		}).bind("mouseout",function(){
			clearTimeout(t);
		});
	});
}

$.fn.TabADS = function(selector){
	var obj = $(this);
	var currentClass = "selected";
	if(typeof selector !== 'undefined'){
		var tabs = obj.find(".tab-hd").find(selector);
	}else{
		var tabs = obj.find(".tab-hd").find("li");
	}
	var conts = obj.find(".tab-cont");
	var t;
	tabs.eq(0).addClass(currentClass);
	conts.hide();
	conts.eq(0).show();
	tabs.each(function(i){
		$(this).bind("mouseover",function(){
			 t = setTimeout(function(){
				conts.hide().eq(i).show();
				tabs.removeClass(currentClass).eq(i).addClass(currentClass);
			},300);
		}).bind("mouseout",function(){
			clearTimeout(t);
		});
	});
}
$.fn.modCity = function(){
	var selectBox = $('#modCity_link');
	var dropDown = $('#modCity');
	dropDown.bind('show',function(){  
		if(dropDown.is(':animated')){ return false; }  
		selectBox.addClass('expanded');
		dropDown.fadeIn();
	}).bind('hide',function(){
		if(dropDown.is(':animated')){ return false; }
		selectBox.removeClass('expanded');
		dropDown.fadeOut();  
	}).bind('toggle',function(){
		if(selectBox.hasClass('expanded')){  
			dropDown.trigger('hide');
		}else{
			dropDown.trigger('show');  
		}
	});
	selectBox.click(function(event){
		dropDown.trigger('toggle');
		event.preventDefault();
	}); 
	$(document).click(function(e){
		dropDown.trigger('hide');
	});  
}
$.fn.showMore = function(){
	var $this = $(this),$po = $this.find('.po');
	$this.hover(function(){
		$po.toggleClass('show')
	});
}
$.fn.showMore2 = function(){
	var $this = $(this);
	$this.hover(function(){
		$this.toggleClass('show')
	});
}
$.returnTop=function(node){
	var node = $('<a href="#" alt="返回顶端" id="returnTop">返回顶端</a>');
	$(document).ready(function(){$('body').append(node)});
	var b = node.click(function(event){
		event.preventDefault();
		$("html,body").animate({scrollTop: 0},300);
	}),
	c = null;
	$(window).bind("scroll",function(){
	   var d = $(document).scrollTop(),
	   e = $(window).height();
	   0 < d ? b.css("bottom", "45px") : b.css("bottom", "-200px");
	   isIE6() && (b.hide(),clearTimeout(c),c = setTimeout(function(){
			0 < d ? b.show() : b.hide();
			clearTimeout(c);
		},
		300), b.css("top", d + e - 51))
	});
}
$.fn.returnTop2014=function(node){
	var iGo2Top = $('#iGo2Top');
	var find_serv = $('#find_serv');
	var iExtraction = $('#iExtraction');
	find_serv.hover(function(){
		find_serv.addClass('open');
		iExtraction.show().animate({width: "184px"}, 300 );
	},function(){
		find_serv.removeClass('open');
		iExtraction.css({width: "0px"}).hide();
	});
	iGo2Top.click(function(event){
		event.preventDefault();
		$("html,body").animate({scrollTop: 0},300);
	});
	
	$(window).bind("scroll",function(){
	   var d = $(document).scrollTop();
	   0 < d ? iGo2Top.show() : iGo2Top.hide();
	});
}
$.fn.listNav = function(){
	var $this = $(this);
	$this.delegate('.t','click',function(e){
		$(this).parent().toggleClass('open');
	});
}
$.fn.listNav2 = function(){
	var $this = $(this);
	$this.delegate('.hd','click',function(e){
		//$this.find('.open').removeClass('open');
		//$this.find('.hd_open').removeClass('hd_open');
		$(this).toggleClass('hd_open');
		$(this).next().toggleClass('open');
		//e.preventDefault();
	});
}
$.fn.fixed = function(can){
	if(isIE6()){return false;}
	var b = $(this),h = b.height(),offset = b.offset(),top = offset.top,bottom = $('#footer').outerHeight(true),d_h = $(document).height(),w_h = $(window).height();
	if(can.height()<h){return;}
	$(window).bind("scroll",function(){
		var d = $(document).scrollTop(),h = b.height(),s_h = d_h-bottom-h,s_b = $('#footer').offset().top-h-15;
		if(top < d){
			if(0>(s_h - d)){
				b.css({'position':'absolute','top':s_b});
			}else{
				b.css({'position':'fixed','top':'0'});
			}
		}else{
			b.css({'position':'static'});
		}
	});
}
$.fn.changeList = function(){
	var _this = $(this),list=_this.find('li');
	list.bind('mouseover',function(){list.removeClass('frist');list.children('.img').hide();$(this).addClass('frist');$(this).children('.img').show();});
	list.eq(0).trigger('mouseover');
}
$('#channel_nav').ready(function(){
	var that=$('#channel_nav'),
		url = window.location.href,
		url_L = url.toLowerCase(),
		channel = that.find('a'),
		forlink;
		
	if(typeof window['isKuaiDianNav'] !== 'undefined'){
		url_L = window['isKuaiDianUrl'].toLowerCase();
	}
	channel.each(function(){
		forlink = $(this).attr("href").toLowerCase();
		if(url_L.indexOf(forlink)>=0){
			that.find('.select').removeClass();
			$(this).addClass("select");
		}
	});
	if(typeof window['istiebaNav'] !== 'undefined'){
		that.find('.select').removeClass();
		that.find('a[href*="tieba"]').addClass("select");
	}
});
$.fn.reviewTgImg = function(){
	var $this = $(this),imgs=$this.find('img');
	imgs.each(function(){
		var txt = $(this).attr('data-img-src');
		var arr1 = txt.split('/');
		var imgName = arr1.pop();
		var arr2 = imgName.split('.');
		var filename = arr2[0];
		var filetype = '.'+arr2[1];
		var new_name = arr1.join('/')+'/'+imgName.slice(0,filename.length-1)+'2'+filetype;
		
		$(this).attr('src',new_name);
	});
}
$.fn.get_TG_num = function(selector){
	var _t = $(this),list = _t.find(selector),arr_id = [],txt_id='';
	list.each(function(index,item){
		arr_id.push($(item).attr('data-tgid'));
	});
	txt_id = arr_id.join(',');
	var url = window['siteUrl']+'request.ashx?action=chrnum&key=tg&jsoncallback=?&id='+txt_id;
	$.getJSON(url, function(data){
		if(data[0]['islogin'] === '1'){
			for(var i=0;i<data[0]['MSG'].length;i++){
				for(var k in data[0]['MSG'][i]){
					_t.find('.tg_chrnum_'+k).html(data[0]['MSG'][i][k][0]['chrnum'])
				}
			}
		}
	});
}
$.fn.format_zushou = function(){
	var t = $(this),l = t.find('li');
	l.each(function(i,item){
		var unit = $(item).hasClass('zu')?"元":"万元";
		var price = $(item).find('.price');
		var price_num = price.attr('data-price');
		price_num === '0'?(price.append('面议')):(price.append(price_num+unit));
	});
}
$.fn.rmenuShow2016 = function(){
	var rtop = $("#top");
	var $t = $(this),w_w = $(window).width(),r_css = 0;
	r_css = parseInt((w_w-1200)/2 - $t.width() - 50);
	r_css = r_css<0?0:r_css;
	$t.css({'right':r_css+'px'})
	rtop.click(function(e){
		e.preventDefault();
		$("html,body").animate({scrollTop: 0},300);
	});
	$(window).bind("scroll",function(){
		var d = $(document).scrollTop();
		0 < d ? rtop.show() : rtop.hide();
	}).bind('resize',function(){
		if($(window).width()<1406){
			$t.css({'right':'10px'});
		}else{
			$t.css({'right':r_css+'px'});
		}
	});
}
$.fn.resizeIMG = function(width,height){
	var that = $(this);
	var imgList = that.find('img');
	var len = imgList.length;
	if(len>0){
		imgList.each(function(i,item){
			$(item).imagesLoaded(function(){
				AutoResizeImage(width,height,item);
			});
		});
	}
	function AutoResizeImage(maxWidth,maxHeight,objImg){
		var img = new Image();
		img.src = objImg.src;
		var hRatio;
		var wRatio;
		var Ratio = 1;
		var w = img.width;
		var h = img.height;
		wRatio = maxWidth / w;
		hRatio = maxHeight / h;
		if (maxWidth ==0 && maxHeight==0){
		Ratio = 1;
		}else if (maxWidth==0){//
		if (hRatio<1) Ratio = hRatio;
		}else if (maxHeight==0){
		if (wRatio<1) Ratio = wRatio;
		}else if (wRatio<1 || hRatio<1){
		Ratio = (wRatio<=hRatio?wRatio:hRatio);
		}
		if (Ratio<1){
		w = w * Ratio;
		h = h * Ratio;
		}
		objImg.height = h;
		objImg.width = w;
		$(objImg).css({'width':w+'px','height':h+'px'});
	}
}
function getdata2014(url,obj){
	var  Digital=new Date();
	Digital=Digital+40000;
	url=url+"&k="+(Digital);
	$.ajax({url:url,success:function(data){
		$('#'+obj).html(data);
	}});
}
function setarticle(tt,id){
	var url="/request.aspx?action=ding&tt="+tt+"&id="+id;
	var  Digital=new  Date();
	Digital=Digital+40000;
	url=url+"&k="+(Digital);
	
	$.ajax({url:url,success:function(data){
		
		if(data=="1"){
			if(tt==1){
				$("#cainews").html( (parseInt($("#cainews").html())+1) );
			}else{
				$("#dingnews").html( (parseInt($("#dingnews").html())+1) );
			}
		}else{
			MSGwindowShow('revert','0',data,'','');
		}
	}});
	return false;
}
function windowlocationhref(url){
	if(url.length > 5){window.location.href=url;}
}
function MSGwindowShow(action,showid,str,url,formcode){
	var sys_tips = '<div class="sys_tips" id="sys_tips" style="display:none;"><div class="hd" id="sys_tips_title"></div><div class="bd"><p id="sys_tips_info"></p><div class="btn"><a href="#" class="btn2" id="sys_tips_submit">确定</a></div></div></div>';
	if(!$('#sys_tips')[0]){
		$('body').append(sys_tips);
	}
	var pay_tips = $('#pay_tips'),sys_tips = $('#sys_tips'),sys_tips_title = $('#sys_tips_title'),sys_tips_info = $('#sys_tips_info'),sys_tips_submit = $('#sys_tips_submit');
	if(action === "pay"){
		$('#have_login').hide();
		if(showid=="2"){//正常提交
			if(document.getElementById('formcode')){
				document.getElementById('formcode').value=formcode;//赋值code
				document.forms['submitpay'].submit();//提交支付
				//这里添加支付中信息提示窗口
				pay_tips.show();
				var w_h = $(window).height(),d_h = pay_tips.height(),s_h = $(document).scrollTop(),top_val = (w_h-d_h)/2+s_h;
				pay_tips.css({'top':top_val+'px'});
			}
		}else if(showid=="1"){//成功提示加跳转
			if(!!win){win.close();}
			showConsole('恭喜您！',!0);
		}else if(showid=="0"){//提示不跳转
			if(!!win){win.close();}
			showConsole('温馨提示',!1);
		}else if(showid=="4"){//错误提示不跳转
			if(!!win){win.close();}
			showConsole('出错了',!1);
		}else{//提示加跳转
			if(!!win){win.close();}
			showConsole('温馨提示',!0);
		}
		if(document.getElementById('formcode')){
			document.getElementById('formcode').value="payok";//设置默认值防止二次提交
		}
	}else if(action === "jifen"){
		if(typeof formcode !== 'undefined' && formcode!=='0' && formcode!==0){
			str = str + '，增加'+formcode +window['jifenneme']+'！';
		}
		showConsole('提示',true);
	}else if(action === "jiaoyou_buy"){
		if(typeof getPageInfo !== 'undefined'){
			getPageInfo();
		}
	}else if(action === "jiaoyou_keep"){
		$('#keeptxt').html('已收藏');
		$('#keepnum').html(formcode);
	}else if(action === "jiaoyou_flowers"){
		$('#flowernum').html(formcode);
		$('#flower').find('.closes').trigger('click');
		showConsole('提示',false);
	}else{
		if(showid=="0"){ //只提示不跳转
			showConsole('提示',false);
		}else if(showid=="1"){ //提示加跳转
			showConsole('提示',true);
		}else if(showid=="2"){ //直接跳转
			windowlocationhref(url);
		}
		else if(showid=="3"){ //错误信息加跳转
			showConsole('出错了',true);
		}else if(showid=="4"){ //错误信息加只提示不跳转
			showConsole('出错了',false);
		}else{
			return false;
		}
	}
	
	function showConsole(tit,isredirect){
		
		sys_tips_info.html(str);
		sys_tips_title.html(tit);
		sys_tips_submit.bind('click',function(e){
			e.preventDefault();
			sys_tips.hide();
			isredirect&&windowlocationhref(url);
		});
		sys_tips.show();
		var w_h = $(window).height(),d_h = sys_tips.height(),s_h = $(document).scrollTop(),top_val = (w_h-d_h)/2+s_h;
		sys_tips.css({'top':top_val+'px'});
	}
}//春节效果
/*$(document).ready(function(){
	$('.wrapper').wrap('<div class="sp_year" id="sp_year" />');	
	var btnStr = '<a href="#" class="close_year" id="close_year">关闭</a>';
	$('.wrapper').append(btnStr);
	
	var btn = $('#close_year');
	
	btn.bind('click',function(e){
		e.preventDefault();
		$('#sp_year').removeClass('sp_year');
	})
});*/