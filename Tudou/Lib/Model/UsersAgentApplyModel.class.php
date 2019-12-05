<?php
class UsersAgentApplyModel extends CommonModel {
    protected $pk = 'apply_id';
    protected $tableName = 'user_agent_applys';
	
	public function getError() {
        return $this->error;
    }
	//审核订单
	public function AgentApplyAudit($apply_id) {
		$detail = $this->find($apply_id);
		if($detail = $this->find($apply_id)){
			$obj = D('Cityagent');
			$Cityagent = $obj->find($detail['agent_id']);
			if(!$Cityagent['user_id']){
				if($obj->where(array('agent_id' => $detail['agent_id']))->save(array('user_id' => $detail['user_id']))){
					if($this->save(array('apply_id' => $apply_id, 'audit' => 1))){
						D('Users')->prestige($detail ['user_id'], 'city');
						//D('Sms')->sms_agent_apply_audit_user($detail ['user_id']);
						return true;
					}else{
						$this->error = '更新数据库失败';
						return false;
					}
				}else{
					$this->error = '更新城市表失败';
					return false;	
				}
			}else{
				$this->error = '当前城市已经有管理员了';
				return false;	
			}
		}else{
			$this->error = '找不到该订单';
			return false;
		}
        return $this->error;
    }
				
	
}
