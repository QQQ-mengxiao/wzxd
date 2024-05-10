<?php
/**
 * 预存款
 *
 */
defined('In718Shop') or exit('Access Invalid!');
class withdraw_commissionModel extends Model {
        /**
     * 取得佣金列表
     * @param unknown $condition
     * @param string $pagesize
     * @param string $fields
     * @param string $order
     */
    public function getPdRechargeList($condition = array(), $pagesize = '', $fields = '*', $order = '', $limit = '') {
         $a= $this->table('fx_cash')->where($condition)->field($fields)->order($order)->limit($limit)->page($pagesize)->select();
        return $a;
    }

    /**
     * 编辑
     * @param unknown $data
     * @param unknown $condition
     */
    public function editPdRecharge($data,$condition = array()) {
        return $this->table('fx_cash')->where($condition)->update($data);
    }

    /**
     * 取得单条佣金信息
     * @param unknown $condition
     * @param string $fields
     */
    public function getPdRechargeInfo($condition = array(), $fields = '*') {
        return $this->table('fx_cash')->where($condition)->field($fields)->find();
    }

    /**
     * 取佣金信息总数
     * @param unknown $condition
     */
    public function getPdRechargeCount($condition = array()) {
        return $this->table('fx_cash')->where($condition)->count();
    }

    /**
     * 取提现单信息总数
     * @param unknown $condition
     */
    public function getPdCashCount($condition = array()) {
        return $this->table('fx_cash')->where($condition)->count();
    }

    /**
     * 取日志总数
     * @param unknown $condition
     */
    public function getPdLogCount($condition = array()) {
        return $this->table('fx_cash')->where($condition)->count();
    }

    /**
     * 取得佣金变更日志列表
     * @param unknown $condition
     * @param string $pagesize
     * @param string $fields
     * @param string $order
     */
    public function getPdLogList($condition = array(), $pagesize = '', $fields = '*', $order = '', $limit = '') {
        return $this->table('fx_cash')->where($condition)->field($fields)->order($order)->limit($limit)->page($pagesize)->select();
    }

    /**
     * 删除佣金提现记录
     * @param unknown $condition
     */
    public function delWdRecharge($condition) {
        return $this->table('fx_cash')->where($condition)->delete();
    }

    /**
     * 取得佣金提现列表
     * @param unknown $condition
     * @param string $pagesize
     * @param string $fields
     * @param string $order
     */
    public function getPdCashList($condition = array(), $pagesize = '', $fields = '*', $order = '', $limit = '') {
        return $this->table('fx_cash')->where($condition)->field($fields)->order($order)->limit($limit)->page($pagesize)->select();
    }

    /**
     * 添加佣金提现记录
     * @param array $data
     */
    public function addWdCash($data) {
        return $this->table('fx_cash')->insert($data);
    }

    /**
     * 编辑提现记录
     * @param unknown $data
     * @param unknown $condition
     */
    public function editWdCash($data,$condition = array()) {
        return $this->table('fx_cash')->where($condition)->update($data);
    }
        /**
     * 编辑提现记录 MJQ
     * @param unknown $data
     * @param unknown $condition
     */
    public function editcomCash($data,$condition = array()) {
         return $this->table('fx_cash')->where($condition)->update($data); 
    }

    /**
     * 取得单条提现信息
     * @param unknown $condition
     * @param string $fields
     */
    public function getWdCashInfo($condition = array(), $fields = '*') {
        return $this->table('fx_cash')->where($condition)->field($fields)->find();
    }
    /**
     * 取得单条提现信息  MJQ 
     * @param unknown $condition
     * @param string $fields
     */
    public function getcomInfo($condition = array(), $fields = '*') {
        return $this->table('fx_cash')->where($condition)->field($fields)->find();
    }
    /**
     * 删除提现记录
     * @param unknown $condition
     */
    public function delWdCash($condition) {
        return $this->table('fx_cash')->where($condition)->delete();
    }
        /**
     * 变更预存款
     * @param unknown $change_type
     * @param unknown $data
     * @throws Exception
     * @return unknown
     */
    public function changePd($change_type,$data = array()) {
        //var_dump($data);die;
        $data_pd = array();
        switch ($change_type){
            case 'withdraw_commission':
            $model_member = Model('member');
        // var_dump($info);die;
            $member_info = $model_member->getMemberInfo(array('member_id'=>$data['fxc_member_id']));
            $data_pd['available_predeposit'] = $member_info['available_predeposit'];
            $data_pd['fx_in_account'] = $member_info['fx_in_account'];
            //$data_pd['fx_settled_account'] = $member_info['fx_settled_account'];
            if ($data['fx_cash_way'] == 1) {
                $data_pd['available_predeposit'] = $data['fxc_amount']+$data_pd['available_predeposit'];
                $data_pd['fx_in_account'] = $data_pd['fx_in_account']-$data['fxc_amount'];
            }else{
                $data_pd['fx_in_account'] = $data_pd['fx_in_account']-$data['fxc_amount'];
            }
            break;
            //end
            default:
                throw new Exception('参数错误');
                break;
        }
         //var_dump($data_pd);die;
        // var_dump($data);die;
        $update = Model('member')->editMember(array('member_id'=>$data['fxc_member_id']),$data_pd);
        //var_dump($update);die;
        if (!$update) {
            throw new Exception('操作失败');
        }
    }
}
