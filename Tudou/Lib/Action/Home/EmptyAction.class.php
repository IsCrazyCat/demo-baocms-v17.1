<?php

class  EmptyAction extends  CommonAction{
   
    public function index(){
        $citys = D('City')->fetchAll();
        $model = strtolower(MODULE_NAME);

        foreach($citys as $val){
            if($val['pinyin'] == $model){
                cookie('city_id',$val['city_id'],86400*30); //保存一个月
                header('Location: http://'.TU_HOST_PREFIX.'.'. TU_DOMAIN);die;
                die;
            }            
        }
        header("HTTP/1.0 404 Not Found");//使HTTP返回404状态码 
        $this->display("public:404"); 
    }  
    
    
}