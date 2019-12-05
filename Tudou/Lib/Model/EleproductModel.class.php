<?php
class EleproductModel extends CommonModel{
    protected $pk = 'product_id';
    protected $tableName = 'ele_product';
	
	
		//余额购买分类信息
	public function gauging_tableware_price($tableware_price,$settlement_price){
		$config = D('Setting')->fetchAll();
		if($config['ele']['tableware_price_max']){
			if($tableware_price > $config['ele']['tableware_price_max']*100){
			   $this->error = '餐具价格最高不能高于'.$config['ele']['tableware_price_max'].'元';
				return false;
			}
		}
		if($config['ele']['tableware_price_mini']){
			if($tableware_price < $config['ele']['tableware_price_mini']*100){
			   $this->error = '餐具价格最低不能低于'.$config['ele']['tableware_price_mini'].'元';
				return false;
			}
		}
		
		if($config['ele']['tableware_price_max'] || $config['ele']['tableware_price_mini']){
			if($tableware_price >= $settlement_price){
				$this->error = '餐具价格不能大于等于结算价：【'.round($settlement_price/100,2).'】元';
				return false;
			}
		}
		return true;
	}
	
	
	
	
	
	
}