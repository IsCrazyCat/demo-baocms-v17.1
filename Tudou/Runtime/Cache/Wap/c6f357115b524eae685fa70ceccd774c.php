<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-cn">
	<head>
		<meta charset="utf-8">
		<title><?php if(!empty($mobile_title)): echo ($mobile_title); ?>_<?php endif; echo ($CONFIG["site"]["sitename"]); ?></title>
        <meta name="keywords" content="<?php echo ($mobile_keywords); ?>" />
        <meta name="description" content="<?php echo ($mobile_description); ?>" />
		<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no">
		<link rel="stylesheet" href="/static/default/wap/css/base.css">
        <link rel="stylesheet" href="<?php echo ($CONFIG['config']['iocnfont']); ?>">
        <link rel="stylesheet" href="<?php echo ($CONFIG['config']['iocnfont2']); ?>">
        <link rel="stylesheet" href="/static/default/wap/css/<?php echo ($ctl); ?>.css?v=<?php echo ($today); ?>"/>
        <link rel="stylesheet" href="/static/default/wap/css/animate.css">
	
        
		<script src="/static/default/wap/js/jquery.js"></script>
        <script src="/static/default/wap/js/base.js"></script>
		<script src="/static/default/wap/other/layer.js"></script>
        <script src="/static/default/wap/js/jquery.form.js"></script>
		<script src="/static/default/wap/other/roll.js"></script>
		<script src="/static/default/wap/js/public.js?v=<?php echo ($today); ?>"></script>
	    <script src="/static/default/wap/js/mobile_common.js?v=jszj"></script>
        <script src="/static/default/wap/js/iscroll-probe.js"></script>
        <script src="/static/default/wap/js/app.js"></script>
    </head>
<body>

<?php if(empty($lat)): ?><script>
		doLocation();		
		//获取距离
		function initLocation(){
			var url = "<?php echo ($url); ?>";
			var geolocation = new BMap.Geolocation();
			geolocation.getCurrentPosition(function(r){
				if(this.getStatus() === 0) {
					var address = r.address.province + r.address.city + r.address.district + r.address.street;
					$.post("/wap/index/dingwei.html",{lat:r.point.lat,lon:r.point.lng,address:address,url:url,type:'browser'},function(response){
						
						$("span[attr-ctrl='distance']").each(function(){   
							var lat = $(this).attr("attr-lat");
							var lon = $(this).attr("attr-lon");
							d = getGreatCircleDistance(lat,lon,response.lat,response.lon);
							$(this).html(d);
						});
						
						if(response.code == 1){
							return false;
						}
						
						//没有匹配到城市
						if(response.code == 6){
							layer.confirm(response.msg, {
							  btn: ['去默认城市','关闭'] //按钮
							},function(){
							  layer.msg('正在带您去默认城市'+response.city_name, {icon:1});
							  location.href = response.url;
							},function(){
							  
							});
						}
						
						//已经匹配到城市
						if(response.code == 2){
							 layer.confirm(response.msg,{icon: 6}, function(){
								location.href = response.url;
							 });
						}
				
						
						
					});
				}else {
					layer.msg('定位失败，原因：' + this.getStatus(),2000,2);
				}        
			},{enableHighAccuracy: true});
		}
		function doLocation(){
			var script = document.createElement("script");
			script.src ="https://api.map.baidu.com/api?v=2.0&ak=te1e01OcptQgwrg4SyBdPx6h&callback=initLocation";
			document.body.appendChild(script);
		}
		
		//计算距离
		var EARTH_RADIUS = 6378137.0; 
		var PI = Math.PI;
		function getRad(d){
			return d*PI/180.0;
		}
		//定为换算
		function getGreatCircleDistance(lat1,lng1,lat2,lng2){
			var radLat1 = getRad(lat1);
			var radLat2 = getRad(lat2);
			var a = radLat1 - radLat2;
			var b = getRad(lng1) - getRad(lng2);
			var s = 2*Math.asin(Math.sqrt(Math.pow(Math.sin(a/2),2) + Math.cos(radLat1)*Math.cos(radLat2)*Math.pow(Math.sin(b/2),2)));
			s = s*EARTH_RADIUS;
			s = Math.round(s*10000)/10000000.0;
			s = s.toFixed(2) + 'KM';
			return s;
		}
    </script><?php endif; ?>
        
      


	


<!--
<?php if(empty($index_mask_show) and $CONFIG['other']['index_mask_show']): ?><div id="notice" style="visibility:visible;display:block;">
        <div class="mask" onclick="showDig()"></div>
        <div class="mask2 ">
            <div class="iboxx n-cus-bb animated bounceIn animationd05">
                <div class="n-cus-b">
                    <div class="top1">
                        <h2><?php echo ($CONFIG[other][index_mask_title]); ?></h2>
                        <span style="left:0px;"><?php echo ($CONFIG[other][index_mask_intro]); ?></span>
                    </div>

                    <div class="bottom1">
                        <div class="notice"><?php echo ($CONFIG[other][index_mask_textarea]); ?></div>
                        <div class="bs">
                            <a href="javascript:;" onclick="showDig('<?php echo U('index/url');?>')" class="url">抢红包</a>
                            <a href="javascript:;" onclick="showDig('<?php echo U('news/index');?>')" class="url2">今日头条</a>
                        </div>
                    </div>
                </div>
            </div>
            <i class="close iconfont animated bounceIn animationd05" onclick="showDig()"></i>
        </div>
    </div><?php endif; ?>

<style>
.mask{background:rgba(0,0,0,.55);position:absolute;height:110%;width:100%;top:0;left:0;overflow:hidden}
#notice .n-cus-bb{z-index:1001;width:100%;background:#fff;position:relative}
#notice{display:none;position:fixed;top:0;left:0;width:100%;height:100%;z-index:1001;box-sizing:border-box;padding-top:30%}
#notice .n-cus-bb i{z-index:6;font-size:35px;position:absolute;display:block;right:43%;bottom:0;color:#fff}
#notice .mask{display:block;z-index:1001}
#notice .iboxx{overflow:hidden;height:auto;background-color:#fff;margin:0 auto;border-radius:10px 10px 10px 10px}
#notice .top1{background-size:cover;height:80px;text-align:center;padding:20px 20px 20px 20px;background-image:url(/static/default/wap/image/index/bgimg.png)}
#notice .bottom1{height:auto;padding:20px 20px 20px 20px;background-color:#fff}
#notice .top1>h2{color:rgba(248,250,90,.93);height:30px;overflow:hidden;margin-bottom:5px}
#notice .notice{height:auto;color:grey;margin-bottom:15px;font-size:14px;line-height:1.8em}
#notice .bs>a:visited{color:#fff}
#notice .bs>a:link{color:#fff}
#notice .bs{height:40px;width:100%;margin:0 auto;margin-top:20px}
#notice .bs a{height:40px;background-color:#ffd800;text-align:center;color:#fff;line-height:40px;border-radius:20px 20px 20px 20px;float:left;margin-right:10%;width:45%;display:inline-block}
#notice .bs .url2{background-color:rgba(255,106,0,.97);float:right;margin:0}
#notice .imgg{margin-top:20px;background-color:#fff;width:35px;height:35px;border-radius:50%;line-height:50px}
#notice .imgg img{width:23px;height:18px;position:relative;top:-4px}
#notice .top1>span{font-size:13px;color:#fff;position:relative;right:-25px}
#notice .mask2{height:auto;width:80%;margin:0 auto;border-radius:10px 10px 30px 10px;z-index:13}
#notice .close{z-index:1001;font-size:35px;position:relative;display:block;color:#fff;text-align:center;bottom:-10px}
#notice .iboxx .top1 .audio-play{box-shadow:0 0 10px 1px rgba(0,0,0,.06);text-align:center;line-height:40px;width:40px;height:40px;border-radius:50%;box-shadow:0 0 10px #e0e6ec;position:absolute;left:20px;margin-top:10px;text-align:center;background-color:#fff}
#notice .iboxx .audio-play>.music{margin-top:35px;margin-left:13px;position:relative;top:-5px;left:-5px}
#triangle-right1{width:0;height:0;position:absolute;left:13px;top:12px;border-top:10px solid transparent;border-left:20px solid #c00;border-bottom:10px solid transparent}
#notice audio{display:none}
#notice .hidd{display:none}
</style>
<script>
      
        function showDig(url) {
            if($("#notice:visible").length==1){
                if(url != undefined)
					window.location.href = url;
					$("#notice").remove();
            }else{
				$("#notice").show();
			}
        }
    </script>
-->

<?php if(empty($index_mask_show) and $CONFIG['other']['index_mask_show']): ?><div id="notice" style="visibility:visible;display:block;">
        <div class="mask" onclick="showDig()"></div>
        <div class="mask2 ">
            <div class="iboxx n-cus-bb animated bounceIn animationd05">
                <div class="n-cus-b">
                    <div class="top1">
                        <h2><?php echo ($CONFIG[other][index_mask_title]); ?></h2>
                        <span style="left:0px;"><?php echo ($CONFIG[other][index_mask_intro]); ?></span>
                    </div>

                    <div class="bottom1">
                        <div class="notice"><?php echo ($CONFIG[other][index_mask_textarea]); ?></div>
                        <div class="bs">
                            <a href="javascript:;" onclick="showDig('<?php echo U('index/url');?>')" class="url">抢红包</a>
                            <a href="javascript:;" onclick="showDig('<?php echo U('news/index');?>')" class="url2">今日头条</a>
                        </div>
                    </div>
                </div>
            </div>
            <i class="close iconfont animated bounceIn animationd05" onclick="showDig()"></i>
        </div>
    </div><?php endif; ?>

<style>
.mask{background:rgba(0,0,0,.55);position:absolute;height:110%;width:100%;top:0;left:0;overflow:hidden}
#notice .n-cus-bb{z-index:1001;width:100%;background:#fff;position:relative}
#notice{display:none;position:fixed;top:0;left:0;width:100%;height:100%;z-index:1001;box-sizing:border-box;padding-top:30%}
#notice .n-cus-bb i{z-index:6;font-size:35px;position:absolute;display:block;right:43%;bottom:0;color:#fff}
#notice .mask{display:block;z-index:1001}
#notice .iboxx{overflow:hidden;height:auto;background-color:#fff;margin:0 auto;border-radius:10px 10px 10px 10px}
#notice .top1{background-size:cover;height:80px;text-align:center;padding:20px 20px 20px 20px;background-image:url(/static/default/wap/image/index/bgimg.png)}
#notice .bottom1{height:auto;padding:20px 20px 20px 20px;background-color:#fff}
#notice .top1>h2{color:rgba(248,250,90,.93);height:30px;overflow:hidden;margin-bottom:5px}
#notice .notice{height:auto;color:grey;margin-bottom:15px;font-size:14px;line-height:1.8em}
#notice .bs>a:visited{color:#fff}
#notice .bs>a:link{color:#fff}
#notice .bs{height:40px;width:100%;margin:0 auto;margin-top:20px}
#notice .bs a{height:40px;background-color:#ffd800;text-align:center;color:#fff;line-height:40px;border-radius:20px 20px 20px 20px;float:left;margin-right:10%;width:45%;display:inline-block}
#notice .bs .url2{background-color:rgba(255,106,0,.97);float:right;margin:0}
#notice .imgg{margin-top:20px;background-color:#fff;width:35px;height:35px;border-radius:50%;line-height:50px}
#notice .imgg img{width:23px;height:18px;position:relative;top:-4px}
#notice .top1>span{font-size:13px;color:#fff;position:relative;right:-25px}
#notice .mask2{height:auto;width:80%;margin:0 auto;border-radius:10px 10px 30px 10px;z-index:13}
#notice .close{z-index:1001;font-size:35px;position:relative;display:block;color:#fff;text-align:center;bottom:-10px}
#notice .iboxx .top1 .audio-play{box-shadow:0 0 10px 1px rgba(0,0,0,.06);text-align:center;line-height:40px;width:40px;height:40px;border-radius:50%;box-shadow:0 0 10px #e0e6ec;position:absolute;left:20px;margin-top:10px;text-align:center;background-color:#fff}
#notice .iboxx .audio-play>.music{margin-top:35px;margin-left:13px;position:relative;top:-5px;left:-5px}
#triangle-right1{width:0;height:0;position:absolute;left:13px;top:12px;border-top:10px solid transparent;border-left:20px solid #c00;border-bottom:10px solid transparent}
#notice audio{display:none}
#notice .hidd{display:none}
</style>
<script>
      
        function showDig(url) {
            if($("#notice:visible").length==1){
                if(url != undefined)
					window.location.href = url;
					$("#notice").remove();
            }else{
				$("#notice").show();
			}
        }
    </script>


<script src="/static/default/wap/js/jquery.flexslider-min.js" type="text/javascript"></script>

<style>
.focus-banner-bottom{display:-webkit-box;height:3px}
.focus-banner-bottom li{width:20%;display:block;-moz-box-flex:1;-webkit-box-flex:1;box-flex:1}
.text-dot-1{color:#FF9900;}
.text-dot-2{color:#87D140;}
.text-dot-3{color:#20b4ff}
.text-dot-4{color:#FF5F45}

.home-follow-public-none{margin-top:50px}
.home-follow-public{position:fixed;top:0;right:0;width:100%;background-color:rgba(0,0,0,.7);z-index:9999999}
.home-follow-public .home-follow-public-Wrap{padding:12px 7px 12px 9px}
.home-follow-public .dl{height:31px;display:block;color:#FFF;overflow:hidden;margin-right:32px;min-width: 320px;
    max-width: 640px;
    margin: 0 auto;}
.home-follow-public .home-follow-public-close{position:absolute;right:7px;top:12px;width:32px;height:32px;float:right;background-image:url(/static/default/wap/img/downloadappclosebtn.png);background-repeat:no-repeat;background-position:50%;background-size:20px 20px}
.home-follow-public .adLogo{float:left;height:31px;width:31px;margin-right:5px}
.home-follow-public .adLogo img{width:100%;height:100%;display:block;border-radius:5px}
.home-follow-public-bottom-view .fnt{display:block;overflow:hidden}
.home-follow-public-bottom-view .tit{font-size:15px;font-weight:400;line-height:18px;height:18px;float:left;width:100%;vertical-align:text-top;overflow:hidden}
.home-follow-public-bottom-view .char{font-size:12px;height:13px;line-height:13px;float:left}
.home-follow-public i.iopen{float:right;border:1px solid #68b543;border-radius:3px;color:#68b543;margin-top:3px;font-style:normal;background:0 0;padding:0 8px;height:24px;line-height:24px;font-size:14px}
#home-follow-public-wechat-share-tips{width:100%;height:100%;position:fixed;top:0;left:0;z-index:1000;background:url(share-tips.png) top right no-repeat rgba(0,0,0,.8);background-size:320px}
.home-follow-public-btn-wxlogin{width:290px;height:44px;line-height:44px;background:#9AD222;border:none;border-radius:4px;color:#fff!important;font-size:16px;padding:0;text-align:center;margin:10px auto;display:block}

</style>

<?php if($CONFIG['weixin']['home_follow']): ?><div id="home-follow-public" class="home-follow-public home-follow-public none" style="position:fixed;display:none;">
        <div class="home-follow-public-Wrap home-follow-public-bottom-view">
            <b id="home-follow-public-close" class="home-follow-public-close home-follow-public-close clkstat" onclick="closeDiv()"></b>
            <a class="dl clkstat" id="dl2" href="<?php echo config_img($CONFIG['weixin']['gourl']);?>"> <i class="iopen">立即关注</i> <i class="adLogo"><img src="<?php echo config_img($CONFIG['site']['wxcode']);?>"></i>
               <span class="fnt">
                   <span class="tit"><?php echo ($CONFIG['site']['sitename']); ?> 公众号</span>
                   <span class="char">不关注，同学聚会怎么吐槽？</span>
               </span>
            </a>
        </div>
    </div><?php endif; ?>

	<header class="top-fixed bg-yellow bg-inverse header">
			<div class="top-local">
				<a href="<?php echo U('city/index');?>" class="top-addr">
					<i class="icon-daohang iconfont"></i><?php echo tu_msubstr($city_name,0,4,false);?></a>
			</div>
            <div class="top-search"  style="display:none;">
                <form method="post" action="<?php echo U('all/index');?>">
                    <input name="keyword" placeholder="<?php echo (($CONFIG[other][wap_search_title])?($CONFIG[other][wap_search_title]):'输入关键字'); ?>"  />
                    <button type="submit" class="iconfont icon-search"></button> 
                </form>
            </div>
            <div class="top-signed">
                <a id="search-btn" href="javascript:void(0);"><i class="iconfont icon-search"></i></a>
            </div>
		</header>
		
		<div id="focus" class="focus">
			<div class="hd"><ul></ul></div>
			<div class="bd">
				<ul>
					<?php  $cache = cache(array('type'=>'File','expire'=> 7200)); $token = md5("Ad, closed=0 AND site_id=57 AND city_id IN ({$city_ids}) and bg_date <= '{$today}' AND end_date >= '{$today}' ,0,3,7200,orderby asc,,"); if(!$items= $cache->get($token)){ $items = D("Ad")->where(" closed=0 AND site_id=57 AND city_id IN ({$city_ids}) and bg_date <= '{$today}' AND end_date >= '{$today}' ")->order("orderby asc")->limit("0,3")->select(); $cache->set($token,$items); } ; $index=0; foreach($items as $item): $index++; ?><li>
							<a href="<?php echo U('wap/ad/click',array('ad_id'=>$item['ad_id'],'aready'=>2));?>"><img src="<?php echo config_img($item['photo']);?>" /></a>
						</li> <?php endforeach; ?>
				</ul>
                <div class="focus-banner-bottom">
                    <li class="bg-dot"></li>
                    <li class="bg-mix"></li>
                    <li class="bg-yellow"></li>
                    <li class="bg-blue"></li>
                    <li class="bg-gray"></li>
                </div>
			</div>
		</div>
        
        <div id="jin-app-block-100" class="bg-white border-bottom cl">
          <div class="jin-home-gz2 cl">
                <a href="<?php echo U('sign/signed');?>" class="border-right">
                    <h2 class="text-main">每日签到</h2>
                    <span class="text-gray">赚金币赢大礼</span>
                </a>
                <a href="<?php echo U('pinche/index');?>" class="border-right">
                    <h2 class="text-blue">拼车出游</h2>
                    <span class="text-gray">发现出行便利</span>
                </a>
                <a href="<?php echo U('thread/lists');?>">
                    <h2 class="text-dot">爆料 +</h2>
                    <span class="text-gray">发现更多好玩的</span>
                </a>
            </div>
        </div>
           
        <div id="jin-app-block-102" class="bg-white border-bottom cl">
          <div class="jin-home-kx cl"><span class="k-new bg-red text-white">早知道</span>
            <div id="index-notice" style="height:22px;line-height:22px;overflow:hidden;"> 	   
                <ul class="clear">
                 	<li class="bd clear">
                    	<a>今天：<?php echo ($today); ?></a>
                    	<?php if(is_array($news)): foreach($news as $key=>$item): ?><a href="<?php echo U('news/detail',array('article_id'=>$item['article_id']));?>">
                                <dt><?php echo tu_msubstr($item['title'],0,20,false);?></dt>
                            </a><?php endforeach; endif; ?>
                     </li>
                </ul>     
          </div>
        </div>
            
        <div id="jin-app-block-103" class="bg-white mt10 border-top cl">
          <div class="jin-home-gz3 cl"> 
          		<?php  $cache = cache(array('type'=>'File','expire'=> 600)); $token = md5("Ad, closed=0 AND site_id=62 AND  city_id IN ({$city_ids}) and bg_date <= '{$today}' AND end_date >= '{$today}' ,0,1,600,orderby asc,,"); if(!$items= $cache->get($token)){ $items = D("Ad")->where(" closed=0 AND site_id=62 AND  city_id IN ({$city_ids}) and bg_date <= '{$today}' AND end_date >= '{$today}' ")->order("orderby asc")->limit("0,1")->select(); $cache->set($token,$items); } ; $index=0; foreach($items as $item): $index++; ?><a href="<?php echo U('wap/ad/click',array('ad_id'=>$item['ad_id'],'aready'=>2));?>" class="border-right border-bottom">
                       <img src="<?php echo config_img($item['photo']);?>" class="vertical-align-middle">
                       <h2 class="text-dot-1"><?php echo tu_msubstr($item['title'],0,4,false);?></h2>
                       <span class="text-gray"><?php echo tu_msubstr($item['code'],0,8,false);?></span>
                   </a> <?php endforeach; ?>
               <?php  $cache = cache(array('type'=>'File','expire'=> 600)); $token = md5("Ad, closed=0 AND site_id=63 AND  city_id IN ({$city_ids}) and bg_date <= '{$today}' AND end_date >= '{$today}' ,0,1,600,orderby asc,,"); if(!$items= $cache->get($token)){ $items = D("Ad")->where(" closed=0 AND site_id=63 AND  city_id IN ({$city_ids}) and bg_date <= '{$today}' AND end_date >= '{$today}' ")->order("orderby asc")->limit("0,1")->select(); $cache->set($token,$items); } ; $index=0; foreach($items as $item): $index++; ?><a href="<?php echo U('wap/ad/click',array('ad_id'=>$item['ad_id'],'aready'=>2));?>" class="border-right border-bottom">
                       <img src="<?php echo config_img($item['photo']);?>" class="vertical-align-middle">
                       <h2 class="text-dot-2"><?php echo tu_msubstr($item['title'],0,4,false);?></h2>
                       <span class="text-gray"><?php echo tu_msubstr($item['code'],0,8,false);?></span>
                   </a> <?php endforeach; ?>
               <?php  $cache = cache(array('type'=>'File','expire'=> 600)); $token = md5("Ad, closed=0 AND site_id=64 AND  city_id IN ({$city_ids}) and bg_date <= '{$today}' AND end_date >= '{$today}' ,0,1,600,orderby asc,,"); if(!$items= $cache->get($token)){ $items = D("Ad")->where(" closed=0 AND site_id=64 AND  city_id IN ({$city_ids}) and bg_date <= '{$today}' AND end_date >= '{$today}' ")->order("orderby asc")->limit("0,1")->select(); $cache->set($token,$items); } ; $index=0; foreach($items as $item): $index++; ?><a href="<?php echo U('wap/ad/click',array('ad_id'=>$item['ad_id'],'aready'=>2));?>" class="border-right">
                       <img src="<?php echo config_img($item['photo']);?>" class="vertical-align-middle">
                       <h2 class="text-dot-3"><?php echo tu_msubstr($item['title'],0,4,false);?></h2>
                       <span class="text-gray"><?php echo tu_msubstr($item['code'],0,8,false);?></span>
                   </a> <?php endforeach; ?>
               <?php  $cache = cache(array('type'=>'File','expire'=> 600)); $token = md5("Ad, closed=0 AND site_id=65 AND  city_id IN ({$city_ids}) and bg_date <= '{$today}' AND end_date >= '{$today}' ,0,1,600,orderby asc,,"); if(!$items= $cache->get($token)){ $items = D("Ad")->where(" closed=0 AND site_id=65 AND  city_id IN ({$city_ids}) and bg_date <= '{$today}' AND end_date >= '{$today}' ")->order("orderby asc")->limit("0,1")->select(); $cache->set($token,$items); } ; $index=0; foreach($items as $item): $index++; ?><a href="<?php echo U('wap/ad/click',array('ad_id'=>$item['ad_id'],'aready'=>2));?>" class="border-right">
                       <img src="<?php echo config_img($item['photo']);?>" class="vertical-align-middle">
                       <h2 class="text-dot-4"><?php echo tu_msubstr($item['title'],0,4,false);?></h2>
                       <span class="text-gray"><?php echo tu_msubstr($item['code'],0,8,false);?></span>
                   </a> <?php endforeach; ?>
            </div>
        </div>
    </div>

        
		<script type="text/javascript">
			$(function(){
				$("#search-btn").click(function(){
					if($(".top-search").css("display")=='block'){
						$(".top-search").hide();
						$(".top-title").show(200);
					}
					else{
						$(".top-search").show();
						$(".top-title").hide(200);
					}
				});
			});
			
			$(window).scroll(function(){
                if(($(".top-fixed").length > 0)) { 
                    if(($(this).scrollTop() > 0) && ($(window).width() > 100)){
                    $("header").removeClass("header");
					$("#search-btn").addClass("search-btn");
					$("#home-follow-public").addClass("home-follow-public-none");
					$("#home-follow-public").show(200);
                } else {
					$("#home-follow-public").hide(200);
                    $("header").addClass("header");
					$("#search-btn").removeClass("search-btn");
                }
                };
            });
			
			function closeDiv(){
			  var p = $("#home-follow-public").css("display");
				  if(typeof(p)=="undefined"||p==""||p=="block"){
					$("#home-follow-public").css("display","none");
				  }else{
					$("#home-follow-public").css("display","block");
				  }
			 }
			 
			 $(document).ready(function (){
				 $('.navigation_index_cate').flexslider({
					directionNav: true,
					pauseOnAction: false,
				 });
			 	$('.flexslider_cate').flexslider({
					directionNav: true,
					pauseOnAction: false,
				});
            });
			TouchSlide({ slideCell:"#index-notice",autoPlay:true,effect:"leftLoop",interTime:3000});
			TouchSlide({slideCell: "#focus",titCell: ".hd ul", mainCell: ".bd ul",effect: "left",autoPlay: true,autoPage: true, switchLoad: "_src",});
		</script>

	
        <div id="index" class="page-center-box">
         <script>
       
        </script>
            

        <?php if($CONFIG[other][wap_navigation] == 1): ?><div class="banner_navigation">
                <div class="navigation_index_cate"> 
                    <ul class="slides">
                        <?php if(is_array($nav)): $i = 0; $__LIST__ = $nav;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$item): $mod = ($i % 2 );++$i; if($i%10 == 1): ?><li class="list">
                                    <ul class="cate">
                                        <li>
                                            <a href="<?php echo config_navigation_url($item['url'],2);?>?nav_id=<?php echo ($item['nav_id']); ?>"><img src="<?php echo config_img($item['photo']);?>">
                                                <p><?php echo ($item["nav_name"]); ?></p></a>
                                        </li>
                                        <?php elseif($i%10 == 0): ?>        

                                        <li>
                                            <a href="<?php echo config_navigation_url($item['url'],2);?>?nav_id=<?php echo ($item['nav_id']); ?>"><img src="<?php echo config_img($item['photo']);?>">
                                                <p><?php echo ($item["nav_name"]); ?></p></a>
                                        </li>
                                    </ul>
                                </li>
                                <?php else: ?>
                                <li>
                                    <a href="<?php echo config_navigation_url($item['url'],2);?>?nav_id=<?php echo ($item['nav_id']); ?>"><img src="<?php echo config_img($item['photo']);?>">
                                        <p><?php echo ($item["nav_name"]); ?></p></a>
                                </li><?php endif; endforeach; endif; else: echo "" ;endif; ?>
                    </ul>  
                </div>
            </div>
        <?php else: ?>
			
			<div class="banner mb10">
				<div class="flexslider_cate">
					<ul class="slides">
						<?php if(is_array($nav)): $i = 0; $__LIST__ = $nav;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$item): $mod = ($i % 2 );++$i; if($i%10 == 1): ?><li class="list">
									<ul class="cate">
										<li>
											<a href="<?php echo config_navigation_url($item['url'],2);?>?nav_id=<?php echo ($item['nav_id']); ?>">
												<div class="iconfont <?php echo ($item["ioc"]); ?> <?php echo ($item["colour"]); ?>"></div>
												<p>
													<?php echo ($item["nav_name"]); ?>
												</p>
											</a>
										</li>
										<?php elseif($i%10 == 0): ?>
										<li>
											<a href="<?php echo config_navigation_url($item['url'],2);?>?nav_id=<?php echo ($item['nav_id']); ?>">
												<div class="iconfont <?php echo ($item["ioc"]); ?> <?php echo ($item["colour"]); ?>"></div>
												<p>
													<?php echo ($item["nav_name"]); ?>
												</p>
											</a>
										</li>
									</ul>
								</li>
								<?php else: ?>
								<li>
									<a href="<?php echo config_navigation_url($item['url'],2);?>?nav_id=<?php echo ($item['nav_id']); ?>">
										<div class="iconfont <?php echo ($item["ioc"]); ?> <?php echo ($item["colour"]); ?>"></div>
										<p>
											<?php echo ($item["nav_name"]); ?>
										</p>
									</a>
								</li><?php endif; endforeach; endif; else: echo "" ;endif; ?>
					</ul>
				</div><?php endif; ?>
			</div>
  
           
             
   		<div class="blank-10 bg"></div>
			<div class="tab index-tab" data-toggle="click">
				<div class="tab-head">
					<ul class="tab-nav line">
                        <li class="x2 active"><a href="#tab-ele">点餐</a></li>
                        <li class="x2"><a href="#tab-shop">商家</a></li>
                        <li class="x2"><a href="#tab-tuan">抢购</a></li>
						<li class="x2"><a href="#tab-life">信息</a></li>
						<li class="x2"><a href="#tab-news">资讯</a></li>
                       	<li class="x2"><a href="#tab-live">直播</a></li>
					</ul>
				</div>
				<div class="tab-body">
                  <div class="tab-panel active" id="tab-ele">
						<ul class="index-tuan">
							<?php if(is_array($ele)): $index = 0; $__LIST__ = $ele;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$item): $mod = ($index % 2 );++$index; $Shop = D('Shop') -> where('shop_id='.$item['shop_id'])->find(); $intro = tu_msubstr($item['intro'],0,30,false); ?>
								<div class="container1" onclick="location='<?php echo U('ele/shop',array('shop_id'=>$item['shop_id']));?>'">
									<img class="x2" src="<?php echo config_img($Shop['photo']);?>">
									<div class="des x10">
                                        <h5><?php echo tu_msubstr($item['shop_name'],0,14,false);?>
                                            <?php if(($item["is_pay"]) == "1"): ?><span class="fu">付</span><?php endif; ?>
                                            <?php if(($item["is_full"]) == "1"): ?><span class="fan">惠</span><?php endif; ?>
                                            <?php if(($item["is_new"]) == "1"): ?><span class="jian">减</span><?php endif; ?>
                                        </h5>				
                                        <p class="des-addr">
                                        <i class="mui-icon mui-icon-location"></i>
                                        &yen;<?php echo round($item['since_money']/100,2);?>起送/配送费 &yen;<?php echo round($item['logistics']/100,2);?>/已售<?php echo ($item['sold_num']); ?>
                                        </p>
                                        <?php if(($var["is_new"]) == "1"): ?><p class="des-addr">
                                                <span class="man_money full_money">新单立减</span>
                                                单笔满&yen;<?php echo round($item['full_money']/100,2);?>元减 &yen;<?php echo round($item['new_money']/100,2);?>元
                                            </p><?php endif; ?>
                                        <?php if(!empty($item['logistics_full'])): ?><p class="des-addr">
                                                <span class="man_money logistics_full">免配送</span>
                                                单笔满&yen;<?php echo round($item['logistics_full']/100,2);?>元免配送费
                                            </p><?php endif; ?>
                                        <?php if(($item["is_full"]) == "1"): ?><p class="des-addr">
                                                <span class="man_money order_price_full">满减</span>
                                                <?php if(!empty($item['order_price_full_1'])): ?>单笔满&yen;<?php echo round($item['order_price_full_1']/100,2);?>元减 &yen;<?php echo round($item['order_price_reduce_1']/100,2);?>元<?php endif; ?>
                                                <?php if(!empty($item['order_price_full_2'])): ?>，单笔满&yen;<?php echo round($item['order_price_full_2']/100,2);?>元减 &yen;<?php echo round($item['order_price_reduce_2']/100,2);?>元<?php endif; ?>
                                            </p><?php endif; ?>
                                        <?php if(!empty($item['radius'])): ?><p class="des-addr"><i class="icon-motorcycle"></i> 配送半径：<?php echo ($item["radius"]); ?> KM</p><?php endif; ?>
                                        
									</div>
								</div><?php endforeach; endif; else: echo "" ;endif; ?>
						</ul>
						<div class="more"><a href="<?php echo U('ele/index');?>">查看更多外卖</a></div>
					</div>
                    
                   
                   
                   <div class="tab-panel" id="tab-shop">
						<ul class="index-tuan">
							<?php if(is_array($shoplist)): $index = 0; $__LIST__ = $shoplist;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$item): $mod = ($index % 2 );++$index;?><div class="container1" onclick="location='<?php echo U('shop/detail',array('shop_id'=>$item['shop_id']));?>'">
                                    <img class="x2" src="<?php echo config_img($item['photo']);?>">	
                                    <div class="des x10">
                                    <?php $business = D('Business') -> where('business_id ='.$item['business_id']) -> find(); $business_name = $business['business_name']; ?>
                                        <h5><?php echo tu_msubstr($item['shop_name'],0,10,false);?></h5>
                                        <?php if(!empty($item['score'])): ?><p class="intro"><span class="ui-starbar" style="margin-top:0.2rem;"><span style="width:<?php echo round($item['score']*2,2);?>%"></span></span></p>
                                        <?php else: ?>
                                            <p class="intro"> 暂无评价 </p><?php endif; ?>
                                        <p class="intro">地址：<?php echo tu_msubstr($item['addr'],0,12,false);?></p>
                                    </div>
                                 </div><?php endforeach; endif; else: echo "" ;endif; ?>
						</ul>
						<div class="more"><a href="<?php echo U('shop/index');?>">查看更多商家</a></div>
					</div>
                    
                   
                    <div class="tab-panel" id="tab-tuan">
						<ul class="line index-tuan">
							<?php if(is_array($tuanlist)): $index = 0; $__LIST__ = $tuanlist;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$item): $mod = ($index % 2 );++$index;?><div class="container1" onclick="location='<?php echo U('tuan/detail',array('tuan_id'=>$item['tuan_id']));?>'">
                                <img class="x2" src="<?php echo config_img($item['photo']);?>">	
                                <div class="des x10">
                                    <h5><?php echo tu_msubstr($item['title'],0,10,false);?> </h5>
                                    <p class="info">
                                        <span class="text-dot">抢购价：￥ <em><?php echo round($item['tuan_price']/100,2);?></em></span> <del>¥ <?php echo round($item['price']/100,2);?></del>
                                        <span class="text-little float-right badge bg-yellow margin-small-top padding-right">已售<?php echo ($item["sold_num"]); ?></span>
                                    </p>
                                    <p class="intro">简介：<?php echo tu_msubstr($item['intro'],0,12,false);?></p>
                                </div>
                             </div><?php endforeach; endif; else: echo "" ;endif; ?>
						</ul>
						<div class="more">
							<a href="<?php echo U('tuan/index');?>">查看抢购信息</a>
						</div>
					</div>
                    
                    
                     
                     
					<div class="tab-panel" id="tab-life">
						<ul class="line index-tuan">
                       
							<?php if(is_array($life)): $index = 0; $__LIST__ = $life;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$item): $mod = ($index % 2 );++$index;?><div class="container1" onclick="location='<?php echo U('life/detail',array('life_id'=>$item['life_id']));?>'">
									<img class="x2" src="<?php echo config_img($item['photo']);?>">
									<div class="des x10">
										<h5><?php echo tu_msubstr($item['title'],0,10,false);?></h5>
										<p class="intro">地址：<?php echo tu_msubstr($item[ 'addr'],0,12,false);?></p>
									</div>
								</div><?php endforeach; endif; else: echo "" ;endif; ?>
						</ul>
						<div class="more">
							<a href="<?php echo U('life/index');?>">查看更多信息</a>
						</div>
					</div>
                    
                    
					<div class="tab-panel" id="tab-news">
						<ul class="index-tuan">
							<?php if(is_array($news)): $index = 0; $__LIST__ = $news;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$item): $mod = ($index % 2 );++$index;?><div class="container1" onclick="location='<?php echo U('news/detail',array('article_id'=>$item['article_id']));?>'">
									<img class="x2" src="<?php echo config_img($item['photo']);?>">
									<div class="des x8">
										<h5><?php echo tu_msubstr($item['title'],0,14,false);?></h5>
										<p class="info"><span>作者：<?php echo ($item["source"]); ?></span></p>
									</div>
									<div class="des x2">
										<div class="intro2">
											<?php echo ($item["views"]); ?>
										</div>
									</div>
								</div><?php endforeach; endif; else: echo "" ;endif; ?>
						</ul>
						<div class="more"><a href="<?php echo U('news/index');?>">查看更多资讯</a></div>
					</div>
                    
                    
                    <div class="tab-panel" id="tab-live">
						<ul class="index-tuan">
							<?php if(is_array($livelist)): $index = 0; $__LIST__ = $livelist;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$item): $mod = ($index % 2 );++$index;?><div class="container1" onclick="location='<?php echo U('live/view',array('live_id'=>$item['live_id']));?>'">
									<img class="x2" src="<?php echo config_img($item['photo']);?>">
									<div class="des x8">
										<h5><?php echo tu_msubstr($item['name'],0,14,false);?></h5>
										<p class="info"><span>直播介绍：<?php echo ($item["intro"]); ?></span></p>
									</div>
									<div class="des x2">
										<div class="intro2">
											<?php echo ($item["views"]); ?>
										</div>
									</div>
								</div><?php endforeach; endif; else: echo "" ;endif; ?>
						</ul>
						<div class="more"><a href="<?php echo U('live/index');?>">查看更多直播</a></div>
					</div>
                   
                    
                    
				</div>
			</div>
			<div class="blank-10"></div>

			

			<div class="blank-10 bg"></div>
			
            <div class="www-hatudou-com-goods-box">
            <div class="www-hatudou-com-goods">
                <h2>———— 精品推荐 ————</h2>
                <div id="J_ItemList">
                  
                  
                  <?php if(is_array($goods)): foreach($goods as $key=>$item): ?><ul class="product single_item info" id="more_element_1">
                      <li>
                            <a href="<?php echo U('mall/detail',array('goods_id'=>$item['goods_id']));?>" title="<?php echo ($item["title"]); ?>">
                            </a><div class="index_pro"><a href="<?php echo U('mall/detail',array('goods_id'=>$item['goods_id']));?>" title="<?php echo ($item["title"]); ?>">
                              <div class="products_kuang">
                                <img src="<?php echo config_img($item['photo']);?>"></div>
                              <div class="goods_name"><?php echo tu_msubstr($item['title'],0,22,false);?></div>
                              </a><div class="price"><a href="<?php echo U('mall/detail',array('goods_id'=>$item['goods_id']));?>" title="<?php echo ($item["title"]); ?>">
                                </a><a href="<?php echo U('mall/detail',array('goods_id'=>$item['goods_id']));?>" class="btns">
                                    <img src="/static/default/wap/image/index_flow.png">
                                </a>
                              <span href="<?php echo U('mall/detail',array('goods_id'=>$item['goods_id']));?>" class="price_pro"> ￥<?php echo round($item['mall_price']/100,2);?>元</span>
                              </div>
                              </div>
                            
                          </li>
                            </ul><?php endforeach; endif; ?>
                  
                  
                  </div>
              </div>
              
              </div>
              
              
             <div class="www-hatudou-com-join">
                <ul>
                 	<a class="button button-block button-big bg-yellow text-center" href="<?php echo U('mall/index',array('order'=>5));?>">查看更多商品 <i class="iconfont icon-angle-right"></i></a>
                </ul>
            </div>
            <div class="blank-10"></div>
            <div class="blank-10 bg"></div>
            
            



   
<div class="footer">
    
    当前城市：<a class="button button-small text-yellow" href="<?php echo U('city/index',array('type'=>$ctl));?>" title="<?php echo tu_msubstr($city_name,0,4,false);?>"><?php echo tu_msubstr($city_name,0,4,false);?></a>   
    
    <?php $SHOP = D('Shop')->where(array('user_id'=>$MEMBER['user_id']))->find(); $footer_menu = $CONFIG['other']['footer_menu'] ? $CONFIG['other']['footer_menu'] : '5'; ?>
    
	<style>
       .footer-search{padding:15px;background:#fff;border-bottom:thin solid #eee;padding-bottom:5px}
	   <?php if($footer_menu == 3): ?>.foot-fixed .foot-item {width:33.3333333336% !important;}
	   <?php elseif($footer_menu == 4): ?>
	   		.foot-fixed .foot-item {width:25% !important;}
	   <?php elseif($footer_menu == 5): ?>
	   		.foot-fixed .foot-item {width:20% !important;}
	   <?php elseif($footer_menu == 6): ?>
	   		.foot-fixed .foot-item {width:16.666666667% !important;}<?php endif; ?>
    </style>   
        
    <?php if(empty($SHOP)): if($ctl == index): ?><a href="<?php echo U('user/apply/index');?>"> 入驻商家</a><?php endif; ?>
    	<?php if($ctl == shop): ?><a href="<?php echo U('user/apply/index');?>"> 入驻商家</a><?php endif; ?>
        <?php if($ctl == ele): ?><a href="<?php echo U('user/apply/index');?>"> 入驻外卖频道</a><?php endif; ?>
        <?php if($ctl == coupon): ?><a href="<?php echo U('user/apply/index');?>"> 我要发布优惠券</a><?php endif; ?>
        <?php if($ctl == farm): ?><a href="<?php echo U('user/apply/index');?>"> 入驻农家乐</a><?php endif; ?>
        <?php if($ctl == market): ?><a href="<?php echo U('user/apply/index');?>"> 入驻菜市场乐</a><?php endif; ?>
        <?php if($ctl == store): ?><a href="<?php echo U('user/apply/index');?>"> 入驻便利店</a><?php endif; ?>
        <?php if($ctl == mall): ?><a href="<?php echo U('user/apply/index');?>"> 我要发布商品</a><?php endif; endif; ?>
    
    <?php if($MEMBER['user_id']): ?><a href="<?php echo U('user/member/index');?>" title="个人中心">个人中心</a>
    <?php else: ?>
    	<a href="<?php echo U('wap/passport/login');?>" title="登录">登录</a>
        <a href="<?php echo U('wap/passport/register');?>" title="注册">注册</a><?php endif; ?>
</div>




<div class="blank-20"></div>
<?php if($CONFIG[other][footer] == 1): ?><footer class="foot-fixed">
    	<?php $kkkk=0; ?>
        <?php if(is_array($nav_footer)): $i = 0; $__LIST__ = $nav_footer;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$item): $mod = ($i % 2 );++$i; $kkkk++;if($kkkk <= $footer_menu){ ?>
                <a class="foot-item <?php if($ctl == $item['title']): ?>active<?php endif; ?>" href="<?php echo config_navigation_url($item['url'],2);?>?nav_id=<?php echo ($item['nav_id']); ?>">		
                    <span class="<?php echo ($item["ioc"]); ?> iconfont"></span>
                    <span class="foot-label"><?php echo tu_msubstr($item['nav_name'],0,2,false);?></span>
                </a>
            <?php } endforeach; endif; else: echo "" ;endif; ?>
    </footer><?php endif; ?>




<iframe id="x-frame" name="x-frame" style="display:none;"></iframe>
</body>
</html>



        
        

<script src="https://res.wx.qq.com/open/js/jweixin-1.0.0.js" type="text/javascript"></script>
<script>
    wx.config({
	debug: false,
	appId: '<?php echo ($signPackage["appId"]); ?>',
    timestamp: '<?php echo ($signPackage["timestamp"]); ?>',
    nonceStr: '<?php echo ($signPackage["nonceStr"]); ?>',
    signature: '<?php echo ($signPackage["signature"]); ?>',
	jsApiList: [
		'checkJsApi',
		'onMenuShareTimeline',
		'onMenuShareAppMessage',
		'onMenuShareQQ',
		'onMenuShareWeibo',
		'onMenuShareQZone'
		]
	});

wx.ready(function () {
    wx.onMenuShareTimeline({
            title: '<?php echo ($CONFIG["site"]["title"]); ?>',
        	link: "<?php echo ($CONFIG["site"]["description"]); ?>", 
        	imgUrl: "<?php echo config_weixin_img($CONFIG['site']['logo']);?>", 
            success: function (){
				layer.msg('分享成功');
			},
            cancel: function (){ 
				layer.msg('分享失败');
			}
     });
     //分享给朋友
     wx.onMenuShareAppMessage({
            title: '<?php echo ($CONFIG["site"]["title"]); ?>',
            desc: '<?php echo ($CONFIG["site"]["description"]); ?>',
            link: "<?php echo ($CONFIG["site"]["host"]); ?>", 
        	imgUrl: "<?php echo config_weixin_img($CONFIG['site']['logo']);?>", 
            type: '',
            dataUrl: '',
            success: function (){
				layer.msg('分享成功');
			},
            cancel: function (){ 
				layer.msg('分享失败');
			}
      });
});


</script>