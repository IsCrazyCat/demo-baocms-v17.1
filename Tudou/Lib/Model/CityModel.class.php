<?php
class CityModel extends CommonModel{
    protected $pk = 'city_id';
    protected $tableName = 'city';
    protected $token = 'city';
    protected $orderby = array('orderby' => 'asc');
    public function setToken($token)
    {
        $this->token = $token;
    }
	public function check_city_domain($city_id,$NOWHOST,$TU_DOMAIN){
        $cityid = D('city')->where(array('city_id' => $city_id))->Field('pinyin,domain')->select();
        if ($cityid[0]['domain'] == '1' && $NOWHOST != $cityid[0]['pinyin']) {
			$url = "http://" . $cityid[0]['pinyin'] . '.' . $TU_DOMAIN . $_SERVER['REQUEST_URI'];
            return $url;
        }
		return false;
	}
	
	public function is_open($city_id){
        if($this->save(array('city_id' => $city_id,'is_open' => 1))) {
            return true;
        }else{
			$this->error = '审核失败';
			return false;
		}
		
	}
	
	public function get_shop_num($city_id){
        $shop = D('Shop')->where(array('city_id' => $city_id,'closed' => 0))->count();
        if($shop) {
            return $shop;
        }else{
			return 0;
		}
		
	}
	
	public function get_area_num($city_id){
        $Area = D('Area')->where(array('city_id' => $city_id))->count();
        if ($Area) {
            return $Area;
         }else{
			return 0;
		}
	}
}