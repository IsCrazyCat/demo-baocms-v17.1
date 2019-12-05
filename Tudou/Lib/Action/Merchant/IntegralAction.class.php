<?php

class IntegralAction extends CommonAction{
    
    public function index(){
        if($this->isPost()){
            $num = (int)$this->_post('num');
            if($num <=0){
                $this->tuError('要兑换的数量不能为空');
            }
            if($this->member['gold'] < $num){
                $this->tuError('账户余额不足');
            }
            if(D('Users')->addGold($this->uid,-$num,'金块兑换积分')){
                D('Users')->addIntegral($this->uid,$num*100,'金块兑换积分');          
            }            
            $this->tuSuccess('兑换积分成功！',U('integral/index')); 
        }else{
             $this->display();
        }
    }
    
  
    
    
}