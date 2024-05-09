<?php
/**
 * 事务前插入日志记录
 */
defined('In718Shop') or exit('Access Invalid!');

class card_balance_logModel extends Model {
    public function __construct(){
        parent::__construct('card_balance_log');
    }

    public function addcard_balance_log($member_id,$optype){
        $cardno=Model()->table('member_card')->where(array('member_id'=>$member_id))->limit(1)->find();
        $card_info=Model('card')->getMemberCardInfo($cardno['cardno']);
        $card_balance_log = array(); 
        $card_balance_log['member_id'] = $member_id;
        $card_balance_log['cardno'] = $cardno['cardno'];
        $card_balance_log['addtime'] = time();
        $card_balance_log['optype'] = $optype;
        $card_balance_log['balance'] = $card_info['Balance'];
        $result = $this->table('card_balance_log')->insert($card_balance_log);
        if($result){
            return 1;
        }else{
            return 0;
        }
    }

}
