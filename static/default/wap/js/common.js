var map;
var geoc;


//特殊单选
$(document).on("click", ".tui-check-select li", function() {
	$(this).parent().find(".active").removeClass("active");
	$(this).parent().find("input").attr('checked',false);
	$(this).find("input").attr('checked','checked');
	$(this).addClass("active");
});



// 经纬定位
function initLocation(){
	var geolocation = new BMap.Geolocation();
	geolocation.getCurrentPosition(function(r) {
	 	if(this.getStatus() === 0) {
	  		var address = r.address.province + r.address.city + r.address.district + r.address.street;
	  		$.post("/mobile/index/location.html",{lat:r.point.lat,lon:r.point.lng,address:address,type:'browser'},function(response){
	  			//计算为定位的并更新
	  			$("span[attr-ctrl='distance']").each(function(){   
  					var lat = $(this).attr("attr-lat");
  					var lon = $(this).attr("attr-lon");
  					d = getGreatCircleDistance(lat,lon,response.lat,response.lon);
  					$(this).html(d);
	  			});
	  		});
		}else {
	    	$.toast('定位失败，原因：' + this.getStatus(),2000,2);
		}        
	},{enableHighAccuracy: true});
}
function doLocation(){
	var script = document.createElement("script");
	script.src ="http://api.map.baidu.com/api?v=2.0&ak=te1e01OcptQgwrg4SyBdPx6h&callback=initLocation";
	document.body.appendChild(script);
}


//计算距离
var EARTH_RADIUS = 6378137.0; 
var PI = Math.PI;
function getRad(d){
    return d*PI/180.0;
}
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


//监听页面滚动
 $(".content").scroll(function() {
 	var h = $(this).scrollTop();
 	var view = '<div class="upscroller">↑ 回到顶部</div>' ;
    if(h > 300){
    	if($(this).parent().find(".upscroller").length == 0){
    		$(this).parent().append(view);
    	}else{
    		$(this).parent().find(".upscroller").addClass('show');
    	}
    }else{
    	$(".upscroller").removeClass('show');
    }
 
});

//点击到顶部
$(document).on("click", ".upscroller", function() {
	$(this).parent().find('.content').animate({scrollTop:0}, 'slow');
});