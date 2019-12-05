<?php
class TemplateModel extends CommonModel{
    protected $pk = 'template_id';
    protected $tableName = 'template';
    protected $token = 'template';
	
	//模板类型
	public function getType(){
        return array(
            '1' => '网站模板',
            '2' => '商家模板',
            '3' => '会员模板',
        );
    }
	
    public function fetchAll(){
        $cache = cache(array('type' => 'File', 'expire' => $this->cacheTime));
        if (!($data = $cache->get($this->token))) {
            $result = $this->order($this->orderby)->select();
            $data = array();
            foreach ($result as $row) {
                $data[$row['theme']] = $row;
            }
            $cache->set($this->token, $data);
        }
        return $data;
    }
    public function getDefaultTheme(){
        $data = $this->fetchAll();
        foreach ($data as $k => $v) {
            if ($v['is_default']) {
                return $v['theme'];
            }
        }
        return C('DEFAULT_THEME');
    }
	//获取模板函数，控制器名称，商家ID，类型，函数名字，控制器名字
	public function getTemplate($control,$shop_id,$type = 0,$method){
		if(!$control){
			return false;
		}
		if(!$method){
			return false;
		}
		if($Shop = D('Shop')->find($shop_id)){
			$condition['closed'] = 0;
			if($type == 0){
				$condition['is_mobile'] = 0;
				$condition['template_id'] = $Shop['pc_template_id'];
			}else{
				$condition['is_mobile'] = 1;
				$condition['template_id'] = $Shop['wap_template_id'];
			}
			if($Shop){
				$Template = D('Template')->where($condition)->find();
				if($Template['theme']){
					return $control.'/'.$Template['theme'].'/'.$method;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}else{
			return false;
		}
		return false;
    }
	
	
}