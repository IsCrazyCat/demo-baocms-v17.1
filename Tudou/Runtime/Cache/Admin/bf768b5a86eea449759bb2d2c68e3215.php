<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
<meta charset="utf-8"/>
<title><?php echo ($CONFIG["site"]["title"]); ?>管理后台</title>
<meta name="description" content="<?php echo ($CONFIG["site"]["title"]); ?>管理后台" />
<meta name="keywords" content="<?php echo ($CONFIG["site"]["title"]); ?>管理后台" />
<meta name="author" content="DeathGhost" />
<link rel="stylesheet" type="text/css" href="/Public/admin/css/style.css" tppabs="css/style.css" />
<style>
body{height:100%;background:#16a085;overflow:hidden;}
canvas{z-index:-1;position:absolute;}
.admin_login dd .checkcode { background: none;}
.admin_login dd p { margin:0;}
</style>
<iframe id="x-frame" name="x-frame" style="display:none;"></iframe>
<script src="/Public/js/jquery.js"></script>
<script src="/Public/admin/js/verificationNumbers.js" tppabs="js/verificationNumbers.js"></script>
<script src="/Public/admin/js/Particleground.js" tppabs="js/Particleground.js"></script>
<script src="/Public/js/layer/layer.js"></script>
<script src="/Public/js/admin.js"></script>
<script>
	var TU_PUBLIC = '__PUBLIC__';
	var TU_ROOT = '__ROOT__';
	$(document).ready(function() {
	  $('body').particleground({
		dotColor: '#5cbdaa',
		lineColor: '#5cbdaa'
	  });
	});
</script>
</head>
<body>
<dl class="admin_login">
 <dt>
  <strong><?php echo ($CONFIG["site"]["title"]); ?>总后台管理系统</strong>
  <em>www.juhucms.com</em>
 </dt>
 <form method="post" action="<?php echo U('login/loging');?>" target="x-frame" >
 <dd class="user_icon">
  <input type="text" placeholder="账号" name="username" class="login_txtbx"/>
 </dd>
 <dd class="pwd_icon">
  <input type="password" placeholder="密码"  name="password"   class="login_txtbx"/>
 </dd>
 <dd class="val_icon">
  <div class="checkcode">
    <input type="text"  placeholder="验证码"  name="yzm"  maxlength="4" class="login_txtbx">
    
  </div>
  <span class="yzm_code" style="margin:2px 0 0px; display:block; cursor:pointer;"><img style="width:60px; height:30px;"  src="__ROOT__/index.php?g=app&m=verify&a=index&mt=<?php echo time();?>" /></span>
 </dd>
 <dd>
  <input type="submit" value="立即登陆" class="submit_btn"/>
 </dd>
 </form> 
 <dd>
  <p>© 2015-2016  技术支持：<?php echo ($CONFIG["site"]["title"]); ?></p>
  <p>演示账户密码咨询qq：<a target="_blank" href="tencent://message/?uin=2593523213&Site=sc.chinaz.com&Menu=yes"><img src="http://demo.lanrenzhijia.com/2015/service0916/images/qq.png" align="absmiddle">在线客服</a></a></p>
 </dd>
</dl>
</body>
</html>