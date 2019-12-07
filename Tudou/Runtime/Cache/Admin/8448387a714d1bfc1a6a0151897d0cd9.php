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
<style>


.comiis_19ditu_bg{width:100%;overflow: hidden;background:#f1f1f1;padding:10px; margin:10px;}		
.comiis_19forum{margin:0 5px 5px 0;float:left;width:210px;height:200px;background:#fff;overflow: hidden;}		
.comiis_19forum_one{margin:0 5px 5px 0;float:left;width:640px;height:200px;background:#fff;overflow: hidden;}	
.comiis_19forum_one .comiis_19forum_div{margin:10px;}
.comiis_19forum_one .comiis_19forum_title{height:53px;position:relative;}
.comiis_19forum_one .comiis_19forum_icon{width:48px;height:48px;position:absolute;top:0;right:0px;background:url(comiis_ico.gif) no-repeat 0 top;border-radius:5px;}
.comiis_19forum_one .comiis_19forum_title h2{height:26px;overflow:hidden;}
.comiis_19forum_one .comiis_19forum_title h2 a{color:#F00;font:100 22px/24px "Microsoft Yahei","SimHei";text-decoration:none;}
.comiis_19forum_one .comiis_19forum_title em{color:#F00;display:block;line-height:24px;height:24px;overflow: hidden;font-style: normal;}
.comiis_19forum_one .comiis_19forum_list{height:130px;color:#999;overflow:hidden;}
.comiis_19forum_one .comiis_19forum_list a{}
.comiis_19forum_one .comiis_19forum_list h3{line-height:22px;width:100%;margin-right:3px;float:left;height:22px;font-size:14px;overflow:hidden;font-weight:400;    color: #666;}
.comiis_19forum .comiis_19forum_list h3 a{font-size:12px;color: #666;}
.comiis_19forum .comiis_19forum_div{margin:10px;}
.comiis_19forum .comiis_19forum_title{height:53px;position:relative;}
.comiis_19forum .comiis_19forum_icon{width:48px;height:48px;position:absolute;top:0;right:0px;background:url(comiis_ico.gif) no-repeat 0 top;border-radius:5px;}
.comiis_19forum .comiis_19forum_title h2{height:26px;overflow:hidden;}
.comiis_19forum .comiis_19forum_title h2 a{color:#666;;font:100 22px/24px "Microsoft Yahei","SimHei";text-decoration:none;}
.comiis_19forum .comiis_19forum_title em{color:#999;display:block;line-height:24px;height:24px;overflow: hidden;font-style: normal;}
.comiis_19forum .comiis_19forum_list{height:130px;color:#999;overflow:hidden;}
.comiis_19forum .comiis_19forum_list a{}
.comiis_19forum .comiis_19forum_list h3{line-height:22px;width:100%;margin-right:3px;float:left;height:22px;font-size:14px;overflow:hidden;font-weight:400;    color: #666;}
.comiis_19forum .comiis_19forum_list h3 a{font-size:12px;color: #666;}
.comiis_19forum_style1{width:377px}
.comiis_19forum_style1 .comiis_19forum_div{width:166px;height:142px;float:left;display:inline;}
.comiis_19forum_style1 .comiis_19forum_rightad{width:186px;height:162px;float:right;display:inline;overflow:hidden;}
.comiis_19forum_style2{height:333px;}
.comiis_19forum_style2 .comiis_19forum_div{width:166px;height:142px;}
.comiis_19forum_style2 .comiis_19forum_bottomad{width:186px;height:164px;overflow:hidden;padding-top:5px;}
.comiis_19forum_style3{width:377px;height:333px;}
.comiis_19forum_style3 .comiis_19forum_div{width:357px;height:142px;}
.comiis_19forum_style3 .comiis_19forum_bottomad{width:377px;height:164px;overflow:hidden;padding-top:5px;}
.comiis_19forum_style3 .comiis_19forum_topad{position:absolute;top:0;right:50px;width:150px;height:48px;overflow:hidden;}
.comiis_19forum_style3 .comiis_19forum_list h3{width:86px;margin-right:3px;}
.comiis_19forum_top{border-top:#fff 2px solid;zoom:1;}
.comiis_hover .comiis_19forum_icon{background-position:0 bottom;}
.comiis_hover{box-shadow:0 0 6px rgba(50,50,50,0.3);}
.comiis_19ditu_bg .comiis_19forum .comiis_ad{padding:6px 8px 8px;}
.comiis_19ditu980 .comiis_19ditu_bg {width:975px;}
.comiis_19ditu980 .comiis_19forum{width:190px;}
.comiis_19ditu980 .comiis_19forum .comiis_19forum_list h3{width:82px;}
.red{ color:#F00 !important}
.mainBt ul span{ background:#F00; color:#FFF; padding:5px 15px; margin-right:40px;}

.main-sc .attention{padding-left:15px;}
.tableBox td {padding:5px;}
.attention{margin-bottom:20px;display:block;}
.attention2{margin:0px 0px 10px 20px;display:block;overflow: auto;}
.attention2 .inptText{width:200px !important;height:28px;}
.attention2 .inptButton{width:120px !important;}
.attention a.tudoukuaijie
</style>
<div class="mainBt">
    <ul>
        <li class="li1">首页</li>
        <li class="li2">后台首页</li>
        <li class="li2 li3">待办事项</li>
        <?php if($warning['is_ip'] == 1): if(!empty($admin['username'])): ?><span style="float:right">尊敬的&nbsp;<?php echo ($admin["username"]); ?>&nbsp;您上次登录IP跟本次登录IP地址不一致，建议您立即修改密码！</span><?php endif; endif; ?>  
    </ul>
</div>


<div class="main-jsgl main-sc">

        <p class="attention">
        	<a class="remberBtn_small_quxiao">快捷操作导航菜单》》》</a>
            <a href="<?php echo U('app/api/cronyes',array('order_id'=>'0','type'=>'1','admin_id'=>$admin['admin_id']));?>" mini="act" class="remberBtn_small_quxiao">订单批量确认收货【新】</a>
            <a class="remberBtn_small_quxiao tudoukuaijie" href="<?php echo U('clean/cache');?>">清理系统日志</a>
            <a mini="act" class="remberBtn_small_quxiao tudoukuaijie" href="<?php echo U('index/action_delete_all');?>">批量清空后台操作日志</a>
            <a class="remberBtn_small_quxiao tudoukuaijie" href="<?php echo U('clean/qrcode');?>">重新生成微信二维码</a>
            <a class="remberBtn_small_quxiao tudoukuaijie" href="<?php echo U('clean/poster');?>">重新生成会员海报</a>
            <a mini="act" class="remberBtn_small_quxiao tudoukuaijie" href="<?php echo U('shop/buildqrcode',array('admin_id'=>$admin['admin_id']));?>">批量生成商家二维码</a>
            <a mini="act" class="remberBtn_small_quxiao tudoukuaijie" href="<?php echo U('shop/delqrcode',array('admin_id'=>$admin['admin_id']));?>">批量删除商家二维码</a>
        </p>

        <?php if(!empty($action)): ?><div class="tableBox">
                <table bordercolor="#e1e6eb" cellspacing="0" width="100%" border="1px" style=" border-collapse: collapse; margin:0px;vertical-align:middle; background-color:#FFF;"  >
                    <tr>
                        <td class="w50">ID</td>  
                        <td>操作人昵称</td>
                        <td>日志记录</td>
                        <td>操作时间</td>
                        <td>操作</td>
                    <?php if(is_array($action)): foreach($action as $key=>$var): ?><tr>
                            <td><?php echo ($var["log_id"]); ?></td>
                            <td><?php echo ($var["admin"]["username"]); ?></td>
                            <td><?php echo ($var["intro"]); ?></td>
                            <td><?php echo (date('Y-m-d H:i:s',$var["create_time"])); ?></td>
                            <td>
                              <a href="<?php echo U('index/action_delete',array('log_id'=>$var['log_id']));?>" mini="act" >删除</a>
                            </td>
                     </tr><?php endforeach; endif; ?>
                </table>
                <?php echo ($page2); ?>
             </div><?php endif; ?>            
          
		
        <form  method="post" target="main_frm" action="<?php echo U('index/main');?>"> 
          <p class="attention2">
             <input type="text"  class="inptText" name="keyword" value="<?php echo ($keyword); ?>"  placeholder="功能找不到？点击搜索！" />
             <input type="submit" value="搜索后台功能"  class="inptButton"/>
         
             <input style="margin-left:20px;" type="text" id="barcode2" class="inptText" name="barcode" value="6912456878954"  placeholder="请输入条码" />
             <a id="barcode" class="inptButton">立即生成条码</a>
         
           </p>
        </form>
        
        <script>
            $("#barcode").click(call);
            function call(){
                var barcode = $('#barcode2').val();
               
                $.post("<?php echo U('index/getBarcodeGen');?>",{barcode:barcode},function (result){
                    if(result.img != ""){
                    	var img = result.img;
                    }else{
                    	var img = 1;
                    }
                    if(result.code == "1"){
                        layer.open({
                          type: 1,
                          title: '恭喜您正生成条码'+barcode+'成功' ,
                          skin: 'layui-layer-demo', //样式类名
                          area: ['380px','260px'], //宽高
                          closeBtn: 0, //不显示关闭按钮
                          anim: 2,
                          shadeClose: true, //开启遮罩关闭
                          content: '<div class="barcode-img" style="margin:20px;"><img src='+img+'><br/><a>右键另存为条码</a></div>'
                        });
                    }else{
                    layer.msg(result.msg,{icon:2});
                    }
                }, 'json');
                        
            };
        </script>  
            
        <?php if(!empty($lists)): ?><div class="tableBox">
                <table bordercolor="#e1e6eb" cellspacing="0" width="100%" border="1px"  style=" border-collapse: collapse; margin:0px; vertical-align:middle; background-color:#FFF;"  >
                    <tr>
                        <td class="w50">ID</td>  
                        <td>名称</td>
                        <td>操作</td>
                    <?php if(is_array($lists)): foreach($lists as $key=>$var): ?><tr>
                            <td><?php echo ($var["menu_id"]); ?></td>
                            <td><?php echo ($var["menu_name"]); ?></td>
                        <td>
                          <a href="<?php echo ($CONFIG["site"]["host"]); ?>/admin/<?php echo ($var['menu_action']); ?>" target="main_frm">操作</a>
                        </td>
                      </tr><?php endforeach; endif; ?>
                </table>
                <?php echo ($page); ?>
            </div><?php endif; ?>            
            
            


<div class="comiis_19ditu ">
<ul class="comiis_19ditu_bg cl masonry" style="position:relative;">

<li class="comiis_19forum comiis_19forum_style0 masonry-brick">
<div class="comiis_19forum_top comiis_19forum_id1">
    <div class="comiis_19forum_div">
        <div class="comiis_19forum_title">
            <span class="comiis_19forum_icon"></span>
            <h2><a href="">系统概况</a></h2>
            <em>欢迎：<?php echo ($admin["username"]); ?>（<?php echo ($admin["role_name"]); ?>）</em>
        </div>
            <div class="comiis_19forum_list">
            <h3><a href="##">1：上次登录地址：<?php echo ($ad["last_ip"]); ?></a></h3>
            <h3><a href="##">2：更新到<?php echo ($v); ?></a></h3>
            <h3><a href="##">3：php版本：<?php echo phpversion();?></a></h3>
            <h3><a href="http://www.juhucms.com/forum-37-1.html">4：点击查看今日更新</a></h3>
            <h3><a href="http://www.juhucms.com/list-87.html">5：更多精品源码下载</a></h3>
            <h3><a href="http://www.juhucms.com/list-93.html">6：不会？点击看教程</a></h3>

            </div>
    </div>
</div>
</li>


<li class="comiis_19forum_one comiis_19forum_style0 masonry-brick">
<div class="comiis_19forum_top comiis_19forum_id1">
    <div id="www_hatudou_com_1" style="width:580px;height:220px;"></div>
</div>
</li>



<!-- 为ECharts准备一个具备大小（宽高）的Dom -->
    
    <script type="text/javascript">
        // 基于准备好的dom，初始化echarts实例
        var myChart = echarts.init(document.getElementById('www_hatudou_com_1'));

        // 指定图表的配置项和数据
        var option = {
            title: {
                text: '网站销售额统计，总销售"<?php echo round($money['all']/100,2);?>"元'
            },
            tooltip: {},
            legend: {
                data:['销售额']
            },
            xAxis: {
                data: ["会员充值","外卖","商城","家政","酒店","农家乐"]
            },
            yAxis: {},
            series: [{
                name: '销售额（元）',
                type: 'bar',
                data: ["<?php echo round($money['money']/100,2);?>", "<?php echo round($money['ele']/100,2);?>", "<?php echo round($money['goods']/100,2);?>",  "<?php echo round($money['appoint']/100,2);?>", "<?php echo round($money['hotel']/100,2);?>", "<?php echo round($money['farm']/100,2);?>"]
            }]
        };

        // 使用刚指定的配置项和数据显示图表。
        myChart.setOption(option);
    </script>
    
    

<li class="comiis_19forum comiis_19forum_style0 masonry-brick">
<div class="comiis_19forum_top comiis_19forum_id1">
    <div class="comiis_19forum_div">
        <div class="comiis_19forum_title">
            <span class="comiis_19forum_icon"></span>
            <h2><a href="<?php echo U('user/index');?>">会员数据</a></h2>
            <em>总：<?php echo ($counts["users"]); ?>个会员</em>
        </div>
            <div class="comiis_19forum_list">
            <h3><a href="<?php echo U('user/index');?>" class="dot">1：今日新增<a class="red"><?php echo ($counts["totay_user"]); ?></a>个会员</a></h3>
            <h3><a href="<?php echo U('user/index');?>">2：已有<?php echo ($counts["user_moblie"]); ?>人验证手机号</a></h3>
            <h3><a href="<?php echo U('user/index');?>">3：已有<?php echo ($counts["user_email"]); ?>人绑定邮箱</a></h3>
            <h3><a href="<?php echo U('user/index');?>">4：qq注册<?php echo ($counts["user_qq"]); ?>人.</a></h3>
            <h3><a href="<?php echo U('user/index');?>">5：微信登录<?php echo ($counts["user_weixin"]); ?>人.</a></h3>
            <h3><a href="<?php echo U('user/index');?>">6：微博注册<?php echo ($counts["user_weibo"]); ?>人.</h3>

 
            </div>
    </div>
</div>
</li>


<li class="comiis_19forum comiis_19forum_style0 masonry-brick">
<div class="comiis_19forum_top comiis_19forum_id1">
    <div class="comiis_19forum_div">
        <div class="comiis_19forum_title">
            <span class="comiis_19forum_icon"></span>
            <h2><a href="#">资金统计</a></h2>
            <em>会员总资金<?php echo round($counts['money_and']/100,2);?>元</em>
        </div>
            <div class="comiis_19forum_list">
            <h3><a href="<?php echo U('usermoneylogs/index');?>">1：会员总资金<?php echo round($counts['money_and']/100,2);?>元</a></h3>
            <h3><a href="<?php echo U('usermoneylogs/index');?>">2：会员总积分<?php echo ($counts['money_integral']); ?>分</a></h3>
            <h3><a href="<?php echo U('usercash/index');?>">3：今日提现<a class="red"><?php echo round($counts['money_cash_day']/100,2);?></a>元，<a class="red"><?php echo ($counts['money_cash_audit']); ?></a>人待审</a></h3>
            </div>
    </div>
</div>


<li class="comiis_19forum_one comiis_19forum_style0 masonry-brick">
<div class="comiis_19forum_top comiis_19forum_id1">
    <div class="comiis_19forum_div">
        <div class="comiis_19forum_title">
            <span class="comiis_19forum_icon"></span>
            <h2><a href="">运营必看</a></h2>
            <em>请先看下面的说明</em>
        </div>
         <div class="comiis_19forum_list">
            <h3><a href="javascript:void(0)">1：由于政策原因，除非特殊情况：请关闭一元云购/众筹/频道，重要事情说三遍！</a></h3>
            <h3><a href="javascript:void(0)">2：数据库/源码建议每天备份1-2次，这个是必须做的事情！</a></h3>
            <h3><a href="javascript:void(0)">3：建议开通七牛云存储，速度会快很多，以后网站搬家已方便！</a></h3>
            <h3><a href="javascript:void(0)">4：网站后台的管理员密码不要设置过去简单，建议8-12位，大小写结合！</a></h3>
            <h3><a href="javascript:void(0)">5：不需要运营的功能请勿开启，开启了就一般不要去关闭，切勿泄露源码，否则停止更新！</a></h3>
            <h3><a href="https://rdsnew.console.aliyun.com/console/buy#/create/bards">6：数据库建议用云RDS数据库！</a></h3>
        </div>
    </div>
</div>
</li>


<li class="comiis_19forum comiis_19forum_style0 masonry-brick">
<div class="comiis_19forum_top comiis_19forum_id1">
    <div class="comiis_19forum_div">
        <div class="comiis_19forum_title">
            <span class="comiis_19forum_icon"></span>
            <h2><a href="<?php echo U('shop/index');?>">商家数据</a></h2>
            <em>共<?php echo ($counts["shop"]); ?>个商家</em>
        </div>
            <div class="comiis_19forum_list">
            <h3><a href="##">1：今日<a class="red"><?php echo ($counts["totay_shop"]); ?></a>商家申请入驻</a></h3>
            <h3><a href="<?php echo U('shop/apply');?>">2：待审核<a class="red"><?php echo ($counts["totay_shop_audit"]); ?></a>个商家</a></h3>
            <h3><a href="<?php echo U('shop/recognition');?>">3：待认领<a class="red"><?php echo ($counts["shoprecognition"]); ?></a>个商家</a></h3>
            <h3><a href="<?php echo U('audit/index');?>">4：已有<?php echo ($counts["shop_audit"]); ?>商家已认证</a></h3>
            <h3><a href="<?php echo U('weidian/index');?>">5：有<?php echo ($counts["shop_weidian"]); ?>微店,待审核<a class="red"><?php echo ($counts["shop_weidian_audit"]); ?></a></a></h3>

            </div>
    </div>
</div>
</li>




<li class="comiis_19forum comiis_19forum_style0 masonry-brick">
<div class="comiis_19forum_top comiis_19forum_id1">
    <div class="comiis_19forum_div">
        <div class="comiis_19forum_title">
            <span class="comiis_19forum_icon"></span>
            <h2><a href="<?php echo U('goods/index');?>">商城数据</a></h2>
            <em>共有<?php echo ($counts['goods']); ?>个商品</em>
        </div>
            <div class="comiis_19forum_list">
            <h3><a href="<?php echo U('goods/index');?>">1：共有<?php echo ($counts['goods']); ?>个商品</a></h3>
            <h3><a href="<?php echo U('goods/index');?>">2：今日新增<a class="red"><?php echo ($counts['goods_day']); ?></a>个，待审核<a class="red"><?php echo ($counts['goods_audit']); ?></a>个</a></h3>
            <h3><a href="<?php echo U('order/index');?>">3：订单<?php echo ($counts['order']); ?>个，今日<a class="red"><?php echo ($counts['order_day']); ?></a>个</a></h3>
            <h3><a href="<?php echo U('order/index');?>">4：商城退款申请：<a class="red"><?php echo ($counts["order_tui"]); ?></a>笔</a></h3>
            <h3><a href="<?php echo U('malldianping/index');?>">5：商城共：<?php echo ($counts["dianping_goods"]); ?>条点评</a></h3>
            <h3><a href="<?php echo U('malldianping/index');?>">6：今日商城点评：<a class="red"><?php echo ($counts["totay_dianping_goods"]); ?></a>次</a></h3>
            </div>
    </div>
</div>
</li>


<?php if($CONFIG['operation']['mall']): ?><li class="comiis_19forum comiis_19forum_style0 masonry-brick">
<div class="comiis_19forum_top comiis_19forum_id1">
    <div class="comiis_19forum_div">
        <div class="comiis_19forum_title">
            <span class="comiis_19forum_icon"></span>
            <h2><a href="<?php echo U('tuan/index');?>">抢购数据</a></h2>
            <em>共有<?php echo ($counts["tuan"]); ?>个抢购</em>
        </div>
            <div class="comiis_19forum_list">
            <h3><a href="<?php echo U('tuan/index');?>">1：共有<?php echo ($counts["tuan"]); ?>个抢购</a></h3>
            <h3><a href="<?php echo U('tuan/index');?>">2：今日上单<a class="red"><?php echo ($counts["tuan_day"]); ?></a>个，待审核<a class="red"><?php echo ($counts["tuan_audit"]); ?></a>个</a></h3>
            <h3><a href="<?php echo U('tuanorder/index');?>">3：订单<?php echo ($counts["order_tuan"]); ?>个，今日<a class="red"><?php echo ($counts["totay_order_tuan"]); ?></a>个</a></h3>
            <h3><a href="<?php echo U('tuancode/index');?>">4：还有<a class="red"><?php echo ($counts["tuan_code_used"]); ?></a>抢购劵待验证</a></h3>
            <h3><a href="<?php echo U('tuandianping/index');?>">5：团购共<?php echo ($counts["dianping_tuan"]); ?>条点评</a></h3>
            <h3><a href="<?php echo U('tuandianping/index');?>">6：今日团购点评<a class="red"><?php echo ($counts["totay_dianping_tuan"]); ?></a>次</a></h3>
            </div>
    </div>
</div>
</li><?php endif; ?>

<?php if($CONFIG['operation']['ele']): ?><li class="comiis_19forum comiis_19forum_style0 masonry-brick">
    <div class="comiis_19forum_top comiis_19forum_id1">
        <div class="comiis_19forum_div">
            <div class="comiis_19forum_title">
                <span class="comiis_19forum_icon"></span>
                <h2><a href="<?php echo U('ele/index');?>">外卖数据</a></h2>
                <em>共有<?php echo ($counts["ele"]); ?>个外卖商家</em>
            </div>
                <div class="comiis_19forum_list">
                <h3><a href="<?php echo U('ele/index');?>">1：共有<?php echo ($counts["eleproduct"]); ?>个菜品</a></h3>
                <h3><a href="<?php echo U('ele/index');?>">2：今日上单<a class="red"><?php echo ($counts["eleproduct_day"]); ?></a>个，待审核<a class="red"><?php echo ($counts["eleproduct_audit"]); ?></a>个</a></h3>
                <h3><a href="<?php echo U('eleorder/index');?>">3：总订单<?php echo ($counts["order_waimai"]); ?>笔，今日外卖：<a class="red"><?php echo ($counts["totay_order_waimai"]); ?></a>单</a></h3>
                <h3><a href="<?php echo U('eleorder/index');?>">4：外卖退款申请<a class="red"><?php echo ($counts["order_waimai_tui"]); ?></a>笔</a></h3>
                <h3><a href="<?php echo U('eleorder/index');?>">5：外卖总点评<?php echo ($counts["dianping_waimai"]); ?></a></h3>
                <h3><a href="<?php echo U('eleorder/index');?>">6：今日外卖点评<a class="red"><?php echo ($counts["totay_dianping_waimai"]); ?></a>次</a></h3>
                </div>
        </div>
    </div>
    </li><?php endif; ?>

<?php if($CONFIG['operation']['market']): ?><li class="comiis_19forum comiis_19forum_style0 masonry-brick">
    <div class="comiis_19forum_top comiis_19forum_id1">
        <div class="comiis_19forum_div">
            <div class="comiis_19forum_title">
                <span class="comiis_19forum_icon"></span>
                <h2><a href="<?php echo U('market/index');?>">菜市场数据</a></h2>
                <em>共有<?php echo ($counts["market"]); ?>个菜市场商家</em>
            </div>
                <div class="comiis_19forum_list">
                <h3><a href="<?php echo U('market/index');?>">1：共有<?php echo ($counts["marketproduct"]); ?>个商品</a></h3>
                <h3><a href="<?php echo U('market/index');?>">2：今日上单<a class="red"><?php echo ($counts["marketproduct_day"]); ?></a>个，待审核<a class="red"><?php echo ($counts["marketproduct_audit"]); ?></a>个</a></h3>
                <h3><a href="<?php echo U('marketorder/index');?>">3：总订单<?php echo ($counts["order_market"]); ?>笔，今日菜市场：<a class="red"><?php echo ($counts["totay_order_market"]); ?></a>单</a></h3>
                <h3><a href="<?php echo U('marketorder/index');?>">4：菜市场退款申请<a class="red"><?php echo ($counts["order_market_tui"]); ?></a>笔</a></h3>
                <h3><a href="<?php echo U('marketorder/index');?>">5：菜市场总点评<?php echo ($counts["dianping_market"]); ?></a></h3>
                <h3><a href="<?php echo U('marketorder/index');?>">6：今日菜市场点评<a class="red"><?php echo ($counts["totay_dianping_market"]); ?></a>次</a></h3>
                </div>
        </div>
    </div>
    </li><?php endif; ?>


<?php if($CONFIG['operation']['store']): ?><li class="comiis_19forum comiis_19forum_style0 masonry-brick">
    <div class="comiis_19forum_top comiis_19forum_id1">
        <div class="comiis_19forum_div">
            <div class="comiis_19forum_title">
                <span class="comiis_19forum_icon"></span>
                <h2><a href="<?php echo U('store/index');?>">便利店数据</a></h2>
                <em>共有<?php echo ($counts["store"]); ?>个便利店商家</em>
            </div>
                <div class="comiis_19forum_list">
                <h3><a href="<?php echo U('store/index');?>">1：共有<?php echo ($counts["storeproduct"]); ?>个商品</a></h3>
                <h3><a href="<?php echo U('store/index');?>">2：今日上单<a class="red"><?php echo ($counts["storeproduct_day"]); ?></a>个，待审核<a class="red"><?php echo ($counts["storeproduct_audit"]); ?></a>个</a></h3>
                <h3><a href="<?php echo U('storeorder/index');?>">3：总订单<?php echo ($counts["order_store"]); ?>笔，今便利店：<a class="red"><?php echo ($counts["totay_order_store"]); ?></a>单</a></h3>
                <h3><a href="<?php echo U('storeorder/index');?>">4：便利店退款申请<a class="red"><?php echo ($counts["order_store_tui"]); ?></a>笔</a></h3>
                <h3><a href="<?php echo U('mstoreorder/index');?>">5：便利店总点评<?php echo ($counts["dianping_store"]); ?></a></h3>
                <h3><a href="<?php echo U('storeorder/index');?>">6：今日便利店点评<a class="red"><?php echo ($counts["totay_dianping_store"]); ?></a>次</a></h3>
                </div>
        </div>
    </div>
    </li><?php endif; ?>

<?php if($CONFIG['operation']['coupon']): ?><li class="comiis_19forum comiis_19forum_style0 masonry-brick">
    <div class="comiis_19forum_top comiis_19forum_id1">
        <div class="comiis_19forum_div">
            <div class="comiis_19forum_title">
                <span class="comiis_19forum_icon"></span>
                <h2><a href="<?php echo U('coupon/index');?>">优惠劵下载</a></h2>
                <em>共：<?php echo ($counts["coupon"]); ?>单</em>
            </div>
                <div class="comiis_19forum_list">
                <h3><a href="<?php echo U('coupon/index');?>">1：网站共<?php echo ($counts["coupon"]); ?>单优惠劵</a></h3>
                <h3><a href="<?php echo U('coupon/index');?>">2：今日新增<a class="red"><?php echo ($counts["coupon_day"]); ?></a>单，<a class="red"><?php echo ($counts["coupon_audit"]); ?></a>待审</a></h3>
                <h3><a href="<?php echo U('coupon/index');?>">3：优惠劵总下载<?php echo ($counts["coupon_download"]); ?>次</a></h3>
                <h3><a href="<?php echo U('coupondownload/index');?>">4：今日下载优惠劵<a class="red"><?php echo ($counts["coupon_download_day"]); ?></a>次</a></h3>
                </div>
        </div>
    </div>
    </li><?php endif; ?>
<?php if($CONFIG['operation']['life']): ?><li class="comiis_19forum comiis_19forum_style0 masonry-brick">
    <div class="comiis_19forum_top comiis_19forum_id1">
        <div class="comiis_19forum_div">
            <div class="comiis_19forum_title">
                <span class="comiis_19forum_icon"></span>
                <h2><a href="<?php echo U('life/index');?>">分类信息数据</a></h2>
                <em>总：<?php echo ($counts["life"]); ?>条分类信息</em>
            </div>
                <div class="comiis_19forum_list">
                <h3><a href="<?php echo U('life/index');?>">1：总：<?php echo ($counts["life"]); ?>条，<?php echo ($counts["life_audit"]); ?>待审核</a></h3>
                <h3><a href="<?php echo U('life/index');?>">2：今日<a class="red"><?php echo ($counts["totay_life"]); ?></a>条分类信息</a></h3>
                <h3><a href="<?php echo U('life/index');?>">3：分类信息总浏览<?php echo ($counts["life_views"]); ?>次</a></h3>
                </div>
        </div>
    </div>
    </li><?php endif; ?>

<?php if($CONFIG['operation']['community']): ?><li class="comiis_19forum comiis_19forum_style0 masonry-brick">
    <div class="comiis_19forum_top comiis_19forum_id1">
        <div class="comiis_19forum_div">
            <div class="comiis_19forum_title">
                <span class="comiis_19forum_icon"></span>
                <h2><a href="<?php echo U('community/index');?>">小区数据</a></h2>
                <em>总<?php echo ($counts["community"]); ?>个小区</em>
            </div>
                <div class="comiis_19forum_list">
                <h3><a href="<?php echo U('community/index');?>">1：总<?php echo ($counts["community"]); ?>个小区</a></h3>
                <h3><a href="<?php echo U('communityposts/index');?>">2：总有<?php echo ($counts["community_bbs"]); ?>篇帖子，<a class="red"><?php echo ($counts["community_bbs_audit"]); ?></a>篇待审核</a></h3>
                <h3><a href="<?php echo U('feedback/index');?>">3：小区报修<?php echo ($counts["community_feedback"]); ?>条</a></h3>
                <h3><a href="<?php echo U('convenientphone/index');?>">4：小区便民电话<?php echo ($counts["community_phone"]); ?>条</a></h3>
                <h3><a href="<?php echo U('communitynews/index');?>">5：共<?php echo ($counts["community_news"]); ?>条通，知今日发送<a class="red"><?php echo ($counts["community_news_day"]); ?></条></a></h3>
                <h3><a href="<?php echo U('logs/index');?>">6：还有<a class="red"><?php echo round($counts['community_order']/100,2);?></a>元物业费未缴</a></h3>
                </div>
        </div>
    </div>
    </li><?php endif; ?>


    <li class="comiis_19forum comiis_19forum_style0 masonry-brick">
    <div class="comiis_19forum_top comiis_19forum_id1">
        <div class="comiis_19forum_div">
            <div class="comiis_19forum_title">
                <span class="comiis_19forum_icon"></span>
                <h2><a href="<?php echo U('article/index');?>">自媒体</a></h2>
                <em>总<?php echo ($counts["article"]); ?>篇文章</em>
            </div>
                <div class="comiis_19forum_list">
                <h3><a href="<?php echo U('article/index');?>">1：共<?php echo ($counts["article"]); ?>篇文章。</a></h3>
                <h3><a href="<?php echo U('article/index');?>">2：今日<a class="red"><?php echo ($counts["article_day"]); ?></a>篇文章，<a class="red"><?php echo ($counts["article_audit"]); ?></a>篇待审核</a></h3>
                <h3><a href="<?php echo U('article/index');?>">3：总文章回复<?php echo ($counts["article_reply"]); ?>次，点赞<?php echo ($counts["article_zan"]); ?>次</a></h3>
                <h3><a href="<?php echo U('article/index');?>">4：总文章浏览<?php echo ($counts["article_vies"]); ?>次</a></h3>
                </div>
        </div>
    </div>
    </li>


</ul>
<div class="cl"></div>
</div>
</div>

 

  		</div>
	</body>
</html>