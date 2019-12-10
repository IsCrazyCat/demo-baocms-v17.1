<?php
class ShopweixinaccessModel extends CommonModel
{
    protected $pk = 'shop_id';
    protected $tableName = 'shop_weixin_access';
    public function getToken($shop_id)
    {
        $data['status']='0';
        if(!empty($shop_id)){
            $data['shop_id']=$shop_id;
        }
        $cur_accessToken = $this->where($data)->order('id desc')->find();
        if (empty($cur_accessToken)) {
            return false;
        }
        if (strtotime($data['expir_time']) - NOW_TIME <= 0) {
            return false;
        }
        return $data['access_token'];
    }
    public function setToken($shop_id, $token)
    {

        $data['status']='0';
        if(!empty($shop_id)){
            $data['shop_id']=$shop_id;
        }
        $cur_accessToken = $this->where($data)->find();
        if (empty($cur_accessToken)) {
            $data['id'] = $cur_accessToken['id'];
            $data['real_expir_time'] = NOW_TIME_FORMAT;//accessToken实际失效时间
            //如果存在access_token 将上一条记录修改已过期状态，并记录过期时间（程序无错误的话当前只有一条status为0 生效中的数据）
            $this->save($data);
        }else{
            $data['create_time']=NOW_TIME_FORMAT;
            $data['expir_time'] = date("Y-m-d H:i:s",time()+7000);
            $data['access_token']=$token;
            //添加新的access_token
            $this->add(array('shop_id' => $shop_id, 'access_token' => $token, 'expir_time' => NOW_TIME + 7000));
        }
        return true;
    }
}