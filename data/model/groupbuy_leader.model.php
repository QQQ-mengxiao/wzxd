<?php
/**
 * 团长模型
 **/
defined('In718Shop') or exit('Access Invalid!');
class groupbuy_leaderModel extends Model
{
    /**
     * 团长详细信息（查库）
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getGroupbuyLeaderInfo($condition, $field = '*')
    {
        return $this->table('groupbuy_leader')->field($field)->where($condition)->find();
    }

    /**
     * 获取团长列表
     */
    public function getGroupbuyLeaderList($condition, $field = '*', $page = 10, $order)
    {
        return $this->table('groupbuy_leader')->field($field)->where($condition)->page($page)->order($order)->select();
    }

    /**
     * 获取自提点个数
     */
    public function getGroupbuyLeaderZitiAddressCount($condition){
        return $this->table('ziti_address')->where($condition)->count();
    }

    /**
     * 获取助手个数
     */
    public function getGroupbuyLeaderAssistantCount($condition){
        return $this->table('groupbuy_leader_assistant')->where($condition)->count();
    }

    /**
     * 获取自提点列表
     */
    public function getGroupbuyLeaderZitiAddressList($condition){
        return $this->table('ziti_address')->where($condition)->select();
    }
    /**
     * 获取自提点列表(分页)
     */
    public function getZitiAddressList($condition, $field = '*', $page = 10, $order){
        return $this->table('ziti_address')->field($field)->where($condition)->page($page)->order($order)->select();
    }

    /**
     * 获取助手列表
     */
    public function getGroupbuyLeaderAssistantList($condition){
        return $this->table('groupbuy_leader_assistant')->where($condition)->select();
    }
    /**
     * 获取助手列表(分页)
     */
    public function getAssistantList($condition, $field = '*', $page = 10, $order){
        return $this->table('groupbuy_leader_assistant')->field($field)->where($condition)->page($page)->order($order)->select();
    }

    /**
     * 注册团长
     * @param    array $param 会员信息
     * @return    array 数组格式的返回结果
     */
    public function addGroupbuyLeader($param)
    {
        if (empty($param)) {
            return false;
        }
        try {
            $this->beginTransaction();
            $group_leader_info = array();
            $group_leader_info['add_time'] = TIMESTAMP;
            $group_leader_info['wx_openid'] = $param['wx_openid'];
            $group_leader_info['wx_nickname'] = $param['wx_nickname'];
            $groupbuy_leader_id = $this->table('groupbuy_leader')->insert($group_leader_info);
            if (!$groupbuy_leader_id) {
                throw new Exception();
            }
            $this->commit();
            return $groupbuy_leader_id;
        } catch (Exception $e) {
            $this->rollback();
            return false;
        }
    }

    /**
     * 编辑
     * @param array $condition
     * @param array $data
     */
    public function editGroupbuyLeader($condition, $data)
    {
        $update = $this->table('groupbuy_leader')->where($condition)->update($data);
        return $update;
    }

	/**
	 * 删除团长
	 * @param int $id 记录ID
	 * @return array $rs_row 返回数组形式的查询结果
	 */
	public function del($id){
		if (intval($id) > 0){
			$where = " groupbuy_leader_id = '". intval($id) ."'";
			$result = Db::delete('groupbuy_leader',$where);
			return $result;
		}else {
			return false;
		}
	}

	// /**
	//  *  团长列表
	//  */
    // public function getGroupbuyLeaderList($condition = array(), $field = '*', $page = 0, $order = 'member_id desc', $limit = '') {
	// 	return $this->table('member')->where($condition)->field($field)->page($page)->order($order)->limit($limit)->select();
	// }

	/**
	 * 团长自提点列表信息
	 */
	public function getGroupbuyLeaderAndZitiAddressList($condition = array(), $field = '*', $join = 'inner', $page = 10, $order = 'groupbuy_leader_id desc', $limit = ''){
		return $this->table('groupbuy_leader,ziti_address')->field($field)->join($join)->on('groupbuy_leader.groupbuy_leader_id=ziti_address.gl_id')->where($condition)->page($page)->order($order)->limit($limit)->select();
	}

	/**
	 * 团长自提点列表信息
	 */
	public function getGroupbuyLeaderAndAssistantList($condition = array(), $field = '*', $join = 'inner', $page = 10, $order = 'groupbuy_leader_id desc', $limit = ''){
		return $this->table('groupbuy_leader,groupbuy_leader_assistant')->field($field)->join($join)->on('groupbuy_leader.groupbuy_leader_id=groupbuy_leader_assistant.gl_id')->where($condition)->page($page)->order($order)->limit($limit)->select();
	}

	/**
	 * 团长自提点单条信息
	 */
	public function getGroupbuyLeaderAndZitiAddressInfo($condition = array(), $field = '*',$join = 'left'){
		return $this->table('groupbuy_leader,ziti_address')->field($field)->join($join)->on('groupbuy_leader.groupbuy_leader_id=ziti_address.gl_id')->where($condition)->find();
	}

	/**
	 * 团长自提点列表信息(歇业原因)
	 */
	public function getGroupbuyLeaderAndZitiAddressAndReasonList($condition = array(), $field = '*', $join = 'inner', $page = 0, $order = 'groupbuy_leader_id desc', $limit = ''){
		return $this->table('groupbuy_leader,ziti_address,xie_reason')->field($field)->join($join)->on('groupbuy_leader.groupbuy_leader_id=ziti_address.gl_id,ziti_address.xie_reason=xie_reason.reason_id')->where($condition)->page($page)->order($order)->limit($limit)->select();
	}

	/**
	 * 团长自提点单条信息(歇业原因)
	 */
	public function getGroupbuyLeaderAndZitiAddressAndReasonInfo($condition = array(), $field = '*'){
		return $this->table('groupbuy_leader,ziti_address,xie_reason')->field($field)->join('left')->on('groupbuy_leader.groupbuy_leader_id=ziti_address.gl_id,ziti_address.xie_reason=xie_reason.reason_id')->where($condition)->find();
	}

    /**
     * 切换当前地址标识
     */
    public function editAddressCurrentByAddressId($address_id, $groupbuy_leader_id){
        //团长id下所有地址标识更改为0
        $result1 = $this->table('ziti_address')->where(['gl_id'=>$groupbuy_leader_id])->update(['is_current'=>0]);

        if(!$result1){

            return -1;
        }

        $result2 = $this->table('ziti_address')->where(['address_id'=>$address_id])->update(['is_current'=>1]);

        if(!$result2){

            return -2;
        }

        return 1;
    }
}
