<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title><?php echo ($CONFIG["site"]["title"]); ?>管理后台</title>
        <meta name="description" content="<?php echo ($CONFIG["site"]["title"]); ?>管理后台" />
        <meta name="keywords" content="<?php echo ($CONFIG["site"]["title"]); ?>管理后台" />
        <link href="__TMPL__statics/css/style.css" rel="stylesheet" type="text/css" />
        <link href="__TMPL__statics/css/land.css" rel="stylesheet" type="text/css" />
        <link href="__TMPL__statics/css/pub.css" rel="stylesheet" type="text/css" />
        <link href="__TMPL__statics/css/main.css" rel="stylesheet" type="text/css" />
        <link href="__PUBLIC__/js/jquery-ui.css" rel="stylesheet" type="text/css" />
        <script> var TU_PUBLIC = '__PUBLIC__'; var TU_ROOT = '__ROOT__'; </script>
        <script src="__PUBLIC__/js/jquery.js"></script>
        <script src="__PUBLIC__/js/jquery-ui.min.js"></script>
        <script src="__PUBLIC__/js/my97/WdatePicker.js"></script>
        <script src="/Public/js/layer/layer.js"></script>
        <script src="__PUBLIC__/js/admin.js"></script>
        <script src="__PUBLIC__/js/echarts-all-3.js"></script>
        <link rel="stylesheet" type="text/css" href="/static/default/webuploader/webuploader.css">
		<script src="/static/default/webuploader/webuploader.min.js"></script>
    </head>
    
    
    </head>
<style type="text/css">
#ie9-warning{ background:#F00; height:38px; line-height:38px; padding:10px;
position:absolute;top:0;left:0;font-size:12px;color:#fff;width:97%;text-align:left; z-index:9999999;}
#ie6-warning a {text-decoration:none; color:#fff !important;}
</style>

<!--[if lte IE 9]>
<div id="ie9-warning">您正在使用 Internet Explorer 9以下的版本，请用谷歌浏览器访问后台、部分浏览器可以开启极速模式访问！不懂点击这里！ <a href="http://www.juhucms.com/10478.html" target="_blank">查看为什么？</a>
</div>
<script type="text/javascript">
function position_fixed(el, eltop, elleft){  
       // check if this is IE6  
       if(!window.XMLHttpRequest)  
              window.onscroll = function(){  
                     el.style.top = (document.documentElement.scrollTop + eltop)+"px";  
                     el.style.left = (document.documentElement.scrollLeft + elleft)+"px";  
       }  
       else el.style.position = "fixed";  
}
       position_fixed(document.getElementById("ie9-warning"),0, 0);
</script>
<![endif]-->


    <body>
         <iframe id="x-frame" name="x-frame" style="display:none;"></iframe>
   <div class="main">
<div class="mainBt">
	<ul>
		<li class="li1">设置</li>
		<li class="li2">基本设置</li>
		<li class="li2 li3">站点设置</li>
	</ul>
</div>
<p class="attention">
	<span>注意：</span>这个设置和全局有关系，不懂的上www.juhucms.com看教程！二开联系qq：286、099、981
</p>
<form target="x-frame" action="<?php echo U('setting/site');?>" method="post">
	<div class="mainScAdd">
		<div class="tableBox">
			<table bordercolor="#dbdbdb" cellspacing="0" width="100%" border="1px" style=" border-collapse: collapse; margin:0px; vertical-align:middle; background-color:#fff;">
			<tr>
				<td class="lfTdBt">
					站点名称：
				</td>
				<td class="rgTdBt">
					<input type="text" name="data[sitename]" value="<?php echo ($CONFIG["site"]["sitename"]); ?>" class="scAddTextName " />
					<code>注意这个不是网站的Title，一般建议是网站的品牌名</code>
				</td>
			</tr>
			<tr>
				<td class="lfTdBt">
					站点网址：
				</td>
				<td class="rgTdBt">
					<input type="text" name="data[host]" value="<?php echo ($CONFIG["site"]["host"]); ?>" class="scAddTextName " />
					<code>例如：http://www.juhucms.com 如果你在二级目录下面就需要带上二级目录</code>
				</td>
			</tr>
            
            <tr>
				<td class="lfTdBt">
					强制开始https：
				</td>
				<td class="rgTdBt">
					<label><input type="radio" name="data[https]" <?php if(($CONFIG["site"]["https"]) == "1"): ?>checked="checked"<?php endif; ?> value="1"  />开启</label>
					<label><input type="radio" name="data[https]" <?php if(($CONFIG["site"]["https"]) == "0"): ?>checked="checked"<?php endif; ?>  value="0"  />不开启</label>
					<code>如果网站配置了https必须开启</code>
				</td>
			</tr>
			<tr>
				<td class="lfTdBt">
					站点根域名：
				</td>
				<td class="rgTdBt">
					<input type="text" name="data[hostdo]" value="<?php echo ($CONFIG["site"]["hostdo"]); ?>" class="scAddTextName " />
					<code>例如：juhucms.com 用于分站二级域名，这里的域名一定要泛解析，否则失效！</code>
				</td>
			</tr>
            <tr>
				<td class="lfTdBt">
					域名前缀：
				</td>
				<td class="rgTdBt">
					<input type="text" name="data[host_prefix]" value="<?php echo ($CONFIG["site"]["host_prefix"]); ?>" class="scAddTextName " />
					<code>默认填写www,您已可以修改为bbs，O2O，其他任意字母,这里必须配置，否则访问失败，当前域名：http://<?php echo ($CONFIG["site"]["host_prefix"]); ?>.<?php echo ($CONFIG["site"]["hostdo"]); ?></code>
				</td>
			</tr>
			<tr>
				<td class="lfTdBt">
					图片域名：
				</td>
				<td class="rgTdBt">
					<input type="text" name="data[imgurl]" value="<?php echo ($CONFIG["site"]["imgurl"]); ?>" class="scAddTextName " />
					<code>例如：http://www.juhucms.com一般情况下是和站点网址一样，如果做了CDN加速一般就可能是其他的域名</code>
				</td>
			</tr>
			<tr>
				<td class="lfTdBt">
					android下载地址：
				</td>
				<td class="rgTdBt">
					<input type="text" name="data[android]" value="<?php echo ($CONFIG["site"]["android"]); ?>" class="scAddTextName " />
					<code>android下载地址</code>
				</td>
			</tr>
			<tr>
				<td class="lfTdBt">
					IOS下载地址：
				</td>
				<td class="rgTdBt">
					<input type="text" name="data[ios]" value="<?php echo ($CONFIG["site"]["ios"]); ?>" class="scAddTextName " />
					<code>IOS下载地址</code>
				</td>
			</tr>
			<tr>
				<td class="lfTdBt">
					网站LOGO：
				</td>
				<td class="rgTdBt">
					<div style="width: 300px;height: 100px; float: left;">
						<input type="hidden" name="data[logo]" value="<?php echo ($CONFIG["site"]["logo"]); ?>" id="data_logo" />
						<div id="fileToUpload">
							上传网站LOGO
						</div>
					</div>
					<div style="width: 300px;height: 100px; float: left;">
						<img id="logo_img" width="120" height="80" src="<?php echo config_img($CONFIG[site][logo]);?>" />
						<a href="<?php echo U('setting/attachs');?>">缩略图设置</a>
                        建议尺寸
						<?php echo ($config["attachs"]["sitelogo"]["thumb"]); ?>
					</div>
					<script>                                            
						var width_sitelogo = '<?php echo thumbSize($CONFIG[attachs][sitelogo][thumb],0);?>';                         
						var height_sitelogo = '<?php echo thumbSize($CONFIG[attachs][sitelogo][thumb],1);?>';                         
						var uploader = WebUploader.create({                             
						auto: true,                             
						swf: '/static/default/webuploader/Uploader.swf',                             
						server: '<?php echo U("app/upload/uploadify",array("model"=>"sitelogo"));?>',                             
						pick: '#fileToUpload',                             
						resize: true,  
						compress : {width: width_sitelogo,height: height_sitelogo,quality: 80,allowMagnify: false,crop: true}                       
					});                                                 
					uploader.on( 'uploadSuccess', function( file,resporse) {                             
						$("#data_logo").val(resporse.url);                             
						$("#logo_img").attr('src',resporse.url).show();                         
					});                                                
					uploader.on( 'uploadError', function( file ) {                             
						alert('上传出错');                         
					});                     
                    </script>
				</td>
			</tr>
			<tr>
				<td class="lfTdBt">
					微信二维码：
				</td>
				<td class="rgTdBt">
					<div style="width: 300px;height: 100px; float: left;">
						<input type="hidden" name="data[wxcode]" value="<?php echo ($CONFIG["site"]["wxcode"]); ?>" id="data_wxcode" />
						<div id="fileToUpload_wxcode">
							上传微信二维码
						</div>
					</div>
					<div style="width: 300px;height: 100px; float: left;">
						<img id="wxcode_img" width="120" height="80" src="<?php echo config_img($CONFIG[site][wxcode]);?>" />
					</div>
					<script>                                                                
						var uploader = WebUploader.create({                             
						auto: true,                             
						swf: '/static/default/webuploader/Uploader.swf',                             
						server: '<?php echo U("app/upload/uploadify",array("model"=>"shopphoto"));?>',                             
						pick: '#fileToUpload_wxcode',                             
						resize: true,  
					});                                                 
					uploader.on( 'uploadSuccess', function( file,resporse) {                             
						$("#data_wxcode").val(resporse.url);                             
						$("#wxcode_img").attr('src',resporse.url).show();                         
					});                                                
					uploader.on( 'uploadError', function( file ) {                             
						alert('上传出错');                         
					});                     
                    </script>
				</td>
			</tr>
			<tr>
				<td class="lfTdBt">
					客服QQ：
				</td>
				<td class="rgTdBt">
					<input type="text" name="data[qq]" value="<?php echo ($CONFIG["site"]["qq"]); ?>" class="scAddTextName " />
					<code>前台模板显示调用！</code>
				</td>
			</tr>
			<tr>
				<td class="lfTdBt">
					电话：
				</td>
				<td class="rgTdBt">
					<input type="text" name="data[tel]" value="<?php echo ($CONFIG["site"]["tel"]); ?>" class="scAddTextName " />
					<code>前台模板显示调用！</code>
				</td>
			</tr>
			<tr>
				<td class="lfTdBt">
					邮件：
				</td>
				<td class="rgTdBt">
					<input type="text" name="data[email]" value="<?php echo ($CONFIG["site"]["email"]); ?>" class="scAddTextName " />
					<code>前台模板显示调用！</code>
				</td>
			</tr>
			<tr>
				<td class="lfTdBt">
					ICP备案：
				</td>
				<td class="rgTdBt">
					<input type="text" name="data[icp]" value="<?php echo ($CONFIG["site"]["icp"]); ?>" class="scAddTextName " />
					<code>前台模板显示调用！</code>
				</td>
			</tr>
			<tr>
				<td class="lfTdBt">
					站点标题：
				</td>
				<td class="rgTdBt">
					<input type="text" name="data[title]" value="<?php echo ($CONFIG["site"]["title"]); ?>" class="scAddTextName " />
					<code>seo设置中调用</code>
				</td>
			</tr>
			<tr>
				<td class="lfTdBt">
					站点关键字：
				</td>
				<td class="rgTdBt">
					<textarea name="data[keyword]" cols="50" rows="5"><?php echo ($CONFIG["site"]["keyword"]); ?></textarea>
					<code>seo设置中调用，建议认真填写！</code>
				</td>
			</tr>
			<tr>
				<td class="lfTdBt">
					站点描述：
				</td>
				<td class="rgTdBt">
					<textarea name="data[description]" cols="50" rows="5"><?php echo ($CONFIG["site"]["description"]); ?></textarea>
					<code>seo设置中调用</code>
				</td>
			</tr>
			<tr>
				<td class="lfTdBt">
					网站头部信息：
				</td>
				<td class="rgTdBt">
					<textarea name="data[headinfo]" cols="150" rows="8"><?php echo ($CONFIG["site"]["headinfo"]); ?></textarea>
					<code>首页顶部的信息在这里删除！一般情况下无需填写！</code>
				</td>
			</tr>
			<tr>
				<td class="lfTdBt">
					统计代码：
				</td>
				<td class="rgTdBt">
					<textarea name="data[tongji]" cols="50" rows="5"><?php echo ($CONFIG["site"]["tongji"]); ?></textarea>
					<code>模板中调用，有统计代码的填写在这里，或者直接添加模板里面。</code>
				</td>
			</tr>
			  <tr>
                    <td class="lfTdBt">默认城市：</td>
                    <td class="rgTdBt">
                        <select name="data[city_id]" class="selectOption">
                            <?php  foreach($citys as $val){?>
                            <option <?php if($val['city_id'] == $CONFIG['site']['city_id']) echo 'selected="selected"' ;?> value="<?php echo ($val["city_id"]); ?>"><?php echo ($val["name"]); ?></option>
                            <?php }?>
                        </select>
                        <code>请填写您的默认城市。</code>
                    </td>
                </tr>
			<tr>
				<td class="lfTdBt" style="padding-right: 0px;">
					城市中心地图坐标：
				</td>
				<td class="rgTdBt">
                        经度  
					<input type="text" name="data[lng]" value="<?php echo ($CONFIG["site"]["lng"]); ?>" class="scAddTextName " />
                        纬度 
					<input type="text" name="data[lat]" value="<?php echo ($CONFIG["site"]["lat"]); ?>" class="scAddTextName " />
					<code>关系到全局默认，请认真填写，这里的默认城市坐标应该跟站点设置里面的坐标一致，切记！</code>
				</td>
			</tr>
			<tr>
				<td class="lfTdBt">
					自动收货时间：
				</td>
				<td class="rgTdBt">
                        商城
					<input type="text" name="data[goods]" value='<?php echo ($CONFIG["site"]["goods"]); ?>' style="width: 50px;"  class="scAddTextName " />（天）
                        外卖
					<input type="text" name="data[ele]" value='<?php echo ($CONFIG["site"]["ele"]); ?>' style="width: 50px;"  class="scAddTextName " />（小时）
                        便利店
					<input type="text" name="data[market]" value='<?php echo ($CONFIG["site"]["market"]); ?>' style="width: 50px;"  class="scAddTextName " />（小时）
                        菜市场
					<input type="text" name="data[store]" value='<?php echo ($CONFIG["site"]["store"]); ?>' style="width: 50px;"  class="scAddTextName " />（小时）
                    <code>这里直接是影响商家中心用户自动确认收货的时间，请认真填写，一般外卖，菜市场，便利店3小时，商城7天</code>
				</td>
			</tr>
			<tr>
				<td class="lfTdBt">
					贴吧发帖免审核：
				</td>
				<td class="rgTdBt">
					<label><input type="radio" name="data[postaudit]" <?php if(($CONFIG["site"]["postaudit"]) == "1"): ?>checked="checked"<?php endif; ?> value="1"  />开启</label>
					<label><input type="radio" name="data[postaudit]" <?php if(($CONFIG["site"]["postaudit"]) == "0"): ?>checked="checked"<?php endif; ?>  value="0"  />不开启</label>
					<code>开启之后帖子发布免审核！</code>
				</td>
			</tr>
			<tr>
				<td class="lfTdBt">
					贴吧回帖免审核：
				</td>
				<td class="rgTdBt">
					<label><input type="radio" name="data[replyaudit]" <?php if(($CONFIG["site"]["replyaudit"]) == "1"): ?>checked="checked"<?php endif; ?> value="1"  />开启</label>
					<label><input type="radio" name="data[replyaudit]" <?php if(($CONFIG["site"]["replyaudit"]) == "0"): ?>checked="checked"<?php endif; ?>  value="0"  />不开启</label>
					<code>开启之后帖子发布免审核！</code>
				</td>
			</tr>
			<tr>
				<td class="lfTdBt">
					小区发帖免审核：
				</td>
				<td class="rgTdBt">
					<label><input type="radio" name="data[xiaoqu_post_audit]" <?php if(($CONFIG["site"]["xiaoqu_post_audit"]) == "1"): ?>checked="checked"<?php endif; ?> value="1"  />开启</label>
					<label><input type="radio" name="data[xiaoqu_post_audit]" <?php if(($CONFIG["site"]["xiaoqu_post_audit"]) == "0"): ?>checked="checked"<?php endif; ?>  value="0"  />不开启</label>
					<code>开启之后小区帖子发布免审核！</code>
				</td>
			</tr>
			<tr>
				<td class="lfTdBt">
					小区回帖免审核：
				</td>
				<td class="rgTdBt">
					<label><input type="radio" name="data[xiaoqu_reply_audit]" <?php if(($CONFIG["site"]["xiaoqu_reply_audit"]) == "1"): ?>checked="checked"<?php endif; ?> value="1"  />开启</label>
					<label><input type="radio" name="data[xiaoqu_reply_audit]" <?php if(($CONFIG["site"]["xiaoqu_reply_audit"]) == "0"): ?>checked="checked"<?php endif; ?>  value="0"  />不开启</label>
					<code>开启之后小区回帖发布免审核！</code>
				</td>
			</tr>
			<tr>
				<td class="lfTdBt">
					新闻评论免审核：
				</td>
				<td class="rgTdBt">
					<label><input type="radio" name="data[article_reply_audit]" <?php if(($CONFIG["site"]["article_reply_audit"]) == "1"): ?>checked="checked"<?php endif; ?> value="1"  />开启</label>
					<label><input type="radio" name="data[article_reply_audit]" <?php if(($CONFIG["site"]["article_reply_audit"]) == "0"): ?>checked="checked"<?php endif; ?>  value="0"  />不开启</label>
					<code>开启后新闻评论免审核！小心使用！</code>
				</td>
			</tr>
			<tr>
				<td class="lfTdBt">
					物业通知免审核：
				</td>
				<td class="rgTdBt">
					<label><input type="radio" name="data[xiaoqu_news_audit]" <?php if(($CONFIG["site"]["xiaoqu_news_audit"]) == "1"): ?>checked="checked"<?php endif; ?> value="1"  />开启</label>
					<label><input type="radio" name="data[xiaoqu_news_audit]" <?php if(($CONFIG["site"]["xiaoqu_news_audit"]) == "0"): ?>checked="checked"<?php endif; ?>  value="0"  />不开启</label>
					<code>开启之后物业发送通知免审核，自己看吧，怕就不要开启自动！</code>
				</td>
			</tr>
			<tr>
				<td class="lfTdBt">
					部落发帖免审核：
				</td>
				<td class="rgTdBt">
					<label><input type="radio" name="data[tribeaudit]" <?php if(($CONFIG["site"]["tribeaudit"]) == "1"): ?>checked="checked"<?php endif; ?> value="1"  />开启</label>
					<label><input type="radio" name="data[tribeaudit]" <?php if(($CONFIG["site"]["tribeaudit"]) == "0"): ?>checked="checked"<?php endif; ?>  value="0"  />不开启</label>
					<code>开启之后帖子发布免审核！</code>
				</td>
			</tr>
            
            <tr>
				<td class="lfTdBt">
					分类信息免审核：
				</td>
				<td class="rgTdBt">
					<label><input type="radio" name="data[fabu_life_audit]" <?php if(($CONFIG["site"]["fabu_life_audit"]) == "1"): ?>checked="checked"<?php endif; ?> value="1"  />开启</label>
					<label><input type="radio" name="data[fabu_life_audit]" <?php if(($CONFIG["site"]["fabu_life_audit"]) == "0"): ?>checked="checked"<?php endif; ?>  value="0"  />不开启</label>
					<code>发布分类信息是不是免审核，请谨慎选择！</code>
				</td>
			</tr>
			
			<tr>
				<td class="lfTdBt">
					全局通知手机号码
				</td>
				<td class="rgTdBt">
					<input type="text" name="data[config_mobile]" value="<?php echo ($CONFIG["site"]["config_mobile"]); ?>" class="scAddTextName " />
					<code>填写有有的场景需要通知给管理员的手机号！</code>
				</td>
			</tr>
			<tr>
				<td class="lfTdBt">
					全局通知邮箱
				</td>
				<td class="rgTdBt">
					<input type="text" name="data[config_email]" value="<?php echo ($CONFIG["site"]["config_email"]); ?>" class="scAddTextName " />
					<code>这里是在必要情况下，给站长发邮箱的时候的接受信箱。</code>
				</td>
			</tr>
			<tr>
				<td class="lfTdBt">
					是否关闭网站：
				</td>
				<td class="rgTdBt">
					<label><input type="radio" name="data[web_close]" <?php if(($CONFIG["site"]["web_close"]) == "1"): ?>checked="checked"<?php endif; ?> value="1"  />开启</label>
					<label><input type="radio" name="data[web_close]" <?php if(($CONFIG["site"]["web_close"]) == "0"): ?>checked="checked"<?php endif; ?>  value="0"  />关闭</label>
					<code style="color:#F00">关闭之后前台打不开哦，突发情况以及备案的时候可以关闭，其他时候不要去动！关闭后不影响后台跟商家后台！</code>
				</td>
			</tr>
			<tr>
				<td class="lfTdBt">
					关闭网站原因：
				</td>
				<td class="rgTdBt">
					<textarea name="data[web_close_title]" cols="50" rows="4"><?php echo ($CONFIG["site"]["web_close_title"]); ?></textarea>
					<code>这里填写关站原因，将会显示到前台首页！如果不关闭网站不需要填写~</code>
				</td>
			</tr>
			</table>
		</div>
		<div class="smtQr">
			<input type="submit" value="确认保存" class="smtQrIpt"/>
		</div>
	</div>
</form>
  		</div>
	</body>
</html>