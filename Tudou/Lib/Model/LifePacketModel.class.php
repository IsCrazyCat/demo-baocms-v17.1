<?php
class LifePacketModel extends CommonModel{
    protected $pk = 'packet_id';
    protected $tableName = 'life_packet';
	
	
	 public function getStatus() {
        return array(
            '1' => '领取中',
            '2' => '退款中',
            '3' => '已退款',
            '4' => '已完成',
        );
    }
	
   
}