<?php
class NearAction extends CommonAction{
    public function index(){
        $lat = cookie('lat_ok');
        $lng = cookie('lng_ok');
        if (empty($lat) || empty($lng)) {
            $lat = cookie('lat');
            $lng = cookie('lng');
        }
        if (!empty($this->uid)) {
            if (empty($lat) || empty($lng)) {
                $usrdata = D('Users')->find($this->uid);
                $lat = $usrdata['lat'];
                $lng = $usrdata['lng'];
            }
        }
        if (empty($lat) || empty($lng)) {
            $lat = $this->_CONFIG['site']['lat'];
            $lng = $this->_CONFIG['site']['lng'];
        }
        $place = cookie('place');
        if (empty($place)) {
            $place = $this->getArea($lat, $lng);
            cookie('place', $place);
        }
        $type = (int) $this->_param('type');
        $this->assign('type', $type);
        $this->assign('place', $place);
        $this->display();
    }
    /* 通过接口将坐标转地理位置 */
    function getArea($lat, $lng){
        $url = 'https://api.map.baidu.com/geocoder/v2/?ak=C9613fa45f450daa331d85184c920119&location=' . $lat . ',' . $lng . '&output=json&pois=1';
        $arr = file_get_contents($url);
        $arr = json_decode($arr);
        $place = $pois = $po = array();
        foreach ($arr->result->pois as $value) {
            $po['name'] = $value->name;
            $po['addr'] = $value->addr;
            $pois[] = $po;
        }
        $place['formatted_address'] = $arr->result->formatted_address;
		$place['city'] = $arr -> result -> addressComponent -> city;
		$place['district'] = $arr -> result -> addressComponent -> district;
        $place['pois'] = $pois;
        return $place;
    }
    /**
     * 腾讯地图坐标转百度地图坐标
     * @param [String] $lat 腾讯地图坐标的纬度
     * @param [String] $lng 腾讯地图坐标的经度
     * @return [Array] 返回记录纬度经度的数组
     */
    function ErroToBd($lat, $lng)
    {
        $x_pi = 3.141592653589793 * 3000.0 / 180.0;
        $x = $lng;
        $y = $lat;
        $z = sqrt($x * $x + $y * $y) + 2.0E-5 * sin($y * $x_pi);
        $theta = atan2($y, $x) + 3.0E-6 * cos($x * $x_pi);
        $bd_lon = $z * cos($theta) + 0.0065;
        $bd_lat = $z * sin($theta) + 0.006;
        return array('lat' => $bd_lat, 'lng' => $bd_lon);
    }
    /*对象转换为数组*/
    function object_array($array)
    {
        if (is_object($array)) {
            $array = (array) $array;
        }
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                $array[$key] = object_array($value);
            }
        }
        return $array;
    }
    //重复数组
    function a_array_unique($array)
    {
        $out = array();
        foreach ($array as $key => $value) {
            if (!in_array($value, $out)) {
                $out[$key] = $value;
            }
        }
        return $out;
    }
    //坐标范围
    function returnSquarePoint($lng, $lat, $distance)
    {
        $dlng = 2 * asin(sin($distance / (2 * 6378.2)) / cos(deg2rad($lat)));
        $dlng = rad2deg($dlng);
        $dlat = $distance / 6378.2;
        $dlat = rad2deg($dlat);
        return array('left-top' => array('lat' => $lat + $dlat, 'lng' => $lng - $dlng), 'right-top' => array('lat' => $lat + $dlat, 'lng' => $lng + $dlng), 'left-bottom' => array('lat' => $lat - $dlat, 'lng' => $lng - $dlng), 'right-bottom' => array('lat' => $lat - $dlat, 'lng' => $lng + $dlng));
    }
    //偏移换算
    function placeToBaidu($lng, $lat)
    {
        $p = 3.141592653589793 * 6378.2 / 360.0;
        $x = $lng;
        $y = $lat;
        $z = sqrt($x * $x + $y * $y) + 2.0E-5 * sin($y * $p);
        $theta = atan2($y, $x) + 3.0E-6 * cos($x * $p);
        $bd_lng = $z * cos($theta) + 0.0065;
        $bd_lat = $z * sin($theta) + 0.006;
        return array('lng' => $bd_lng, 'lat' => $bd_lat);
    }
    public function dingwei(){
        $lat = cookie('lat');
        $lng = cookie('lng');
        if (cookie('localed') != 2 || empty($lat) || empty($lng)) {
            $local = array($this->_param('lat'), $this->_param('lng'));
            cookie('lat_ok', $local[0], 3600);
            cookie('lng_ok', $local[1], 3600);
            cookie('lat', $local[0], 3600);
            cookie('lng', $local[1], 3600);
            $addr = $this->getArea($local[0], $local[1]);
            cookie('addr', $addr, 3600);
            if (!empty($addr)) {
                cookie('localed', 2);
            }
        }
        echo '1';
    }
	//首页城市定位  可定位至4级区域，区/县
		public function csdwpl() {
		$lat = $this->_param('lat');
		$lng = $this->_param('lng');
		cookie('lat_ok', $lat, 3600);
		cookie('lng_ok', $lng, 3600);
		cookie('lat', $lat, 3600);
		cookie('lng', $lng, 3600);
		$addr = $this -> getArea($lat, $lng);
		cookie('addr', $addr, 3600);
		if (!empty($addr)) {
			cookie('localed', 2);
		}
		$city = mb_substr($addr['city'], 0, -1, 'utf-8');
		$district = mb_substr($addr['district'], 0, -1, 'utf-8');
		$wdw = $city .$district;
		$city = D('Pinyin') -> pinyin($city);
		$district = D('Pinyin') -> pinyin($district);
		$xiancheng = D('City') -> where(array('pinyin' => $district,'is_open' => 1)) -> find();
		$chengshi = D('City') -> where(array('pinyin' => $city,'is_open' => 1)) -> find();
		$mcityid = cookie('city_id');
		$cityop = cookie('cityop');
		if (!empty($mcityid)) {
			if ($mcityid == $xiancheng['city_id'] || $mcityid == $chengshi['city_id'] || $cityop == 1) {
				$cityid = 9999;
				$outArr = array('cityid' => $cityid); //当默认城市和当前获取坐标城市一样时，不进行弹出提示
			} else {
				$mcity = D('City') -> where(array('city_id' => $mcityid)) -> getField('name');
				if (!empty($xiancheng)) {
					$cityid = $xiancheng['city_id'];
					$city = $xiancheng['name'];
					$outArr = array('moren'=>1,'city' => $city, 'cityid' => $cityid, 'mcity' => $mcity, 'mcityid' => $mcityid);
				} elseif (!empty($chengshi)) {
					$cityid = $chengshi['city_id'];
					$city = $chengshi['name'];
					$outArr = array('moren'=>1,'city' => $city, 'cityid' => $cityid, 'mcity' => $mcity, 'mcityid' => $mcityid);
				}
			}
		} else {
			if (!empty($xiancheng)) {
				$cityid = $xiancheng['city_id'];
				$city = $xiancheng['name'];
				$outArr = array('city' => $city, 'cityid' => $cityid);
			} elseif (!empty($chengshi)) {
				$cityid = $chengshi['city_id'];
				$city = $chengshi['name'];
				$outArr = array('city' => $city, 'cityid' => $cityid);
			} else {
				$cityid = 0;
				$city = $wdw;
				$outArr = array('city' => $city, 'cityid' => $cityid);
			}
		}
		echo json_encode($outArr);
	}
    public function address(){
        $addr = cookie('addr');
        echo $addr['formatted_address'];
    }
    public function reset(){
        $local = array($this->_param('lat'), $this->_param('lng'));
        cookie('lat_ok', $local['0'], 3600);
        cookie('lng_ok', $local['1'], 3600);
        cookie('lat', $local['0'], 3600);
        cookie('lng', $local['1'], 3600);
        $addr = $this->getArea($local['0'], $local['1']);
        cookie('addr', $addr, 3600);
        if (!empty($addr)) {
            cookie('localed', 1);
        }
        echo $addr['formatted_address'];
    }
    public function search(){
        $keyword = urlencode($this->_param('keyword', 'htmlspecialchars'));
        $type = (int) $this->_param('type');
        $this->assign('type', $type);
        $this->assign('nextpage', LinkTo('near/loaddata', array('type' => $type, 'keyword' => $keyword, 't' => NOW_TIME, 'p' => '0000')));
        $this->assign('keyword', urldecode($keyword));
        $this->display();
    }
	
    public function loaddata(){
		
		$obj = D('Near');
        import('ORG.Util.Page');
		
		
		/*if($keyword = htmlspecialchars(auto_charset($_REQUEST['keyword']))){
			$urldecode_keyword = urldecode($keyword);
            $map['name|address|telephone'] = array('LIKE', '%' . $urldecode_keyword . '%');
			$this->assign('keyword', $keyword);
        }*/
		
		
    	if($keyword = $this->_param('keyword','htmlspecialchars')) {
			$urldecode_keyword = urldecode($keyword);
            $map['name|address|telephone'] = array('LIKE', '%' . $urldecode_keyword . '%');
            $this->assign('keyword', $keyword);
        }
		
		if($type = (int) $this->_param('type')){
			$map['type'] = $type;	
			$this->assign('type', $type);
		}
		
		$count = $obj-> where($map)->count(); 
        $Page = new Page($count, 10);
        $show = $Page->show();
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if($Page->totalPages < $p){
            die('0');
        }
		$list = $obj->where($map)->order(array('pois_id' => 'asc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('keyword', urldecode($keyword));
        $this->assign('poi', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function detail($id){
        //获取用户自定坐标
        $local = $this->location();
        $lat = $local['lat'];
        $lng = $local['lng'];
        //判断查询类型
        if (is_numeric($id)) {
            $detail = D('Near')->where(array('pois_id' => $id))->find();
        } else {
            $detail = D('Near')->where(array('uid' => $id))->find();
        }
        //如果是入驻商家
        if (!empty($detail['shop_id'])) {
            $shop = D('Shop')->find($detail['shop_id']);
            $this->assign('shop', $shop);
        }
        //本地没有数据到远程获取
        if (empty($detail) && !is_numeric($id)) {
            $bdurl = 'https://api.map.baidu.com/place/v2/detail?uid=' . $id . '&ak=C9613fa45f450daa331d85184c920119&output=json&scope=2';
            $bdtxt = file_get_contents($bdurl);
            $bdarr = json_decode($bdtxt);
            $detail['uid'] = $bdarr->result->uid;
            $detail['name'] = $bdarr->result->name;
            $detail['type'] = $bdarr->result->detail_info->type;
            $detail['lat'] = $bdarr->result->location->lat;
            $detail['lng'] = $bdarr->result->location->lng;
            $detail['telephone'] = $bdarr->result->telephone;
            $detail['address'] = $bdarr->result->address;
            $detail['tag'] = $bdarr->result->detail_info->tag;
        }
        $distance = getDistanceCN($detail['lat'], $detail['lng'], $lat, $lng);
        $this->assign('distance', $distance);
        $this->assign('detail', $detail);
        $this->assign('lat', $lat);
        $this->assign('lng', $lng);
        $this->display();
    }
    public function gps($id){
        //获取用户自定坐标
        $local = $this->location();
        $lat = $local['lat'];
        $lng = $local['lng'];
        //判断查询类型
        if (is_numeric($id)) {
            $detail = D('Near')->where(array('pois_id' => $id))->find();
        } else {
            $detail = D('Near')->where(array('uid' => $id))->find();
        }
        //本地没有数据到远程获取
        if (empty($detail) && !is_numeric($id)) {
            $bdurl = 'https://api.map.baidu.com/place/v2/detail?uid=' . $id . '&ak=C9613fa45f450daa331d85184c920119&output=json&scope=2';
            $bdtxt = file_get_contents($bdurl);
            $bdarr = json_decode($bdtxt);
            $detail['uid'] = $bdarr->result->uid;
            $detail['name'] = $bdarr->result->name;
            $detail['type'] = $bdarr->result->detail_info->type;
            $detail['lat'] = $bdarr->result->location->lat;
            $detail['lng'] = $bdarr->result->location->lng;
            $detail['telephone'] = $bdarr->result->telephone;
            $detail['address'] = $bdarr->result->address;
            $detail['tag'] = $bdarr->result->detail_info->tag;
        }
        $this->assign('detail', $detail);
        $this->assign('lat', $lat);
        $this->assign('lng', $lng);
        $this->display();
    }
    public function select(){
        $lat = cookie('lat_ok');
        $lng = cookie('lng_ok');
        if (empty($lat) || empty($lng)) {
            $lat = cookie('lat');
            $lng = cookie('lng');
        }
        if (!empty($this->uid)) {
            if (empty($lat) || empty($lng)) {
                $usrdata = D('Users')->find($this->uid);
                $lat = $usrdata['lat'];
                $lng = $usrdata['lng'];
            }
        }
        //获取系统默认坐标
        if (empty($lat) || empty($lng)) {
            $lat = $this->_CONFIG['site']['lat'];
            $lng = $this->_CONFIG['site']['lng'];
        }
        //获取地理位置
        $place = cookie('place');
        if (empty($place)) {
            $place = getArea($lat, $lng);
            cookie('place', $place);
        }
        //获取地址推荐
        $url = 'https://api.map.baidu.com/geocoder/v2/?ak=C9613fa45f450daa331d85184c920119&location=' . $lat . ',' . $lng . '&output=json&pois=1';
        $json = file_get_contents($url);
        $geo = object_array(json_decode($json));
        $this->assign('geo', $geo);
        $this->assign('place', $place);
        $this->assign('lat', $lat);
        $this->assign('lng', $lng);
        $this->display();
    }
    public function load(){
        $lat = $this->_param('lat', 'htmlspecialchars');
        $lng = $this->_param('lng', 'htmlspecialchars');
        //获取地址推荐
        $url = 'https://api.map.baidu.com/geocoder/v2/?ak=C9613fa45f450daa331d85184c920119&location=' . $lat . ',' . $lng . '&output=json&pois=1';
        $json = file_get_contents($url);
        $geo = object_array(json_decode($json));
        $this->assign('geo', $geo);
        $this->display();
    }
    public function selected(){
        $lat = $this->_param('lat', 'htmlspecialchars');
        $lng = $this->_param('lng', 'htmlspecialchars');
        cookie('lat_ok', null);
        cookie('lng_ok', null);
        cookie('lat_ok', $lat);
        cookie('lng_ok', $lng);
        $this->tuAlert('您的位置已经重置，请返回继续浏览', U('index/index'));
    }
    public function location(){
        $lat = cookie('lat_ok');
        $lng = cookie('lng_ok');
        if (empty($lat) || empty($lng)) {
            $lat = cookie('lat');
            $lng = cookie('lng');
        }
        if (!empty($this->uid)) {
            if (empty($lat) || empty($lng)) {
                $usrdata = D('Users')->find($this->uid);
                $lat = $usrdata['lat'];
                $lng = $usrdata['lng'];
            }
        }
        //获取系统默认坐标
        if (empty($lat) || empty($lng)) {
            $lat = $this->_CONFIG['site']['lat'];
            $lng = $this->_CONFIG['site']['lng'];
        }
        $arr = array('lat' => $lat, 'lng' => $lng);
        return $arr;
    }
	
	public function get_location() {
		$lat = I('lat', '', 'intval,trim');
		$lng = I('lng', '', 'intval,trim');
		cookie('lat_ok', $lat, 3600);
		cookie('lng_ok', $lng, 3600);
		cookie('lat', $lat, 3600);
		cookie('lng', $lng, 3600);
		$addr = $this -> getArea($lat, $lng);
		cookie('addr', $addr, 3600);
		if (!empty($addr)) {
			cookie('localed', 2);
		}
		$city = mb_substr($addr['city'], 0, -1, 'utf-8');
		$district = mb_substr($addr['district'], 0, -1, 'utf-8');
		$xdgsg = $addr['formatted_address'];
		$xiancheng = D('City') -> where(array('name' => array('LIKE', '%' . $district . '%'))) -> find();
		$chengshi = D('City') -> where(array('name' => array('LIKE', '%' . $city . '%'))) -> find();
		$mcityid = cookie('city_id');
		if (!empty($mcityid)) {
			if ($mcityid == $xiancheng['city_id'] || $mcityid == $chengshi['city_id']) {
				$cityid = 9999;
				$outArr = array('cityid' => $cityid); //当默认城市和当前获取坐标城市一样时，不进行弹出提示
			} else {
				$mcity = D('City') -> where(array('city_id' => $mcityid)) -> getField('name');
				if (!empty($xiancheng)) {
					$cityid = $xiancheng['city_id'];
					$city = $xiancheng['name'];
					$outArr = array('moren'=>1,'city' => $city, 'cityid' => $cityid, 'mcity' => $mcity, 'mcityid' => $mcityid);
				} elseif (!empty($chengshi)) {
					$cityid = $chengshi['city_id'];
					$city = $chengshi['name'];
					$outArr = array('moren'=>1,'city' => $city, 'cityid' => $cityid, 'mcity' => $mcity, 'mcityid' => $mcityid);
				}
			}
		} else {
			if (!empty($xiancheng)) {
				$cityid = $xiancheng['city_id'];
				$city = $xiancheng['name'];
				$outArr = array('city' => $city, 'cityid' => $cityid);
			} elseif (!empty($chengshi)) {
				$cityid = $chengshi['city_id'];
				$city = $chengshi['name'];
				$outArr = array('city' => $city, 'cityid' => $cityid);
			} else {
				$cityid = 0;
				$city = $city;
				$outArr = array('city' => $city, 'cityid' => $cityid);
			}
		}
		echo json_encode($outArr);
	}

}