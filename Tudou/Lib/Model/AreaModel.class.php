<?php
class AreaModel extends CommonModel{
    protected $pk   = 'area_id';
    protected $tableName =  'area';
    protected $token = 'area';
    protected $orderby = array('orderby'=>'asc');
   
    public function setToken($token){
        $this->token = $token;
    }
	
	public function get_shop_num($area_id){
        $shop = D('Shop')->where(array('area_id' => $area_id,'closed' => 0))->count();
        if($shop) {
            return $shop;
        }else{
			return 0;
		}
		
	}
	
	public function get_business_num($area_id){
        $Business = D('Business')->where(array('area_id' => $area_id))->count();
        if ($Business) {
            return $Business;
         }else{
			return 0;
		}
	}
 
}