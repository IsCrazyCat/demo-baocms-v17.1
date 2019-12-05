<?php

class ThreadModel extends CommonModel{
    protected $pk   = 'thread_id';
    protected $tableName =  'thread';

	//获取名字
	public function comments_get_thread_name($post_id){
        $detail = $this->where(array('post_id'=>$post_id,'closed'=>0))->find();
        if(!empty($detail)){
		   return $detail['thread_name'];
		}else{
		   return '该部落不存在';   
	    }
    }
    
}