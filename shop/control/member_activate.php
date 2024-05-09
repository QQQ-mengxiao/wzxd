<?php
/**
 * 账号激活
 ***/


defined('In718Shop') or exit('Access Invalid!');

class member_activateControl extends BaseMemberControl{
    /**
     * 用户中心
     *
     * @param
     * @return
     */
    public function indexOp(){
        $model_vip_card = Model('vip_card');
        $member_id = $model_vip_card->getVipCardInfo(array('used_member_id'=>$_SESSION['member_id']))['used_member_id'];
        Tpl::output('member_id',$member_id);
        Tpl::showpage('member_activate');
    }

    public function activateOp(){
        if (!$_POST['vip_card_num']) {
            showDialog('会员卡号不能为空', 'reload', 'error');
        }
        if (!$_POST['vip_card_pwd']) {
            showDialog('会员卡密码不能为空', 'reload', 'error');
        }
        $model_vip_card = Model('vip_card');
        $model_member = Model('member');
        $model_setting = Model('setting');
        $vip_card_info = $model_vip_card->getVipCardInfo(array('vip_card_num' => $_POST['vip_card_num'], 'vip_card_pwd' => $_POST['vip_card_pwd']));
        if (!$vip_card_info) {
            showDialog('会员卡号与密码不匹配', 'reload', 'error');
        } else {
            if($vip_card_info['is_used']){
                showDialog('此会员卡已被激活', 'reload', 'error');
            }
            $member_exppoints = $model_member->getfby_member_id($_SESSION['member_id'], 'member_exppoints');//获取用户当前经验值
            //获取等级经验值
            $list_setting = $model_setting->getListSetting();
            $member_grade = $list_setting['member_grade'] ? unserialize($list_setting['member_grade']) : array();
            $exppoints = $member_grade[$vip_card_info['vip_card_grade']]['exppoints'];//目标达到的经验值
            $exp_difference = $exppoints - $member_exppoints;//差值
            if ($exp_difference > 0) {
                $result = $model_member->editMember(array('member_id' => $_SESSION['member_id']), array('member_exppoints' => $exppoints));
                $result1 = $model_vip_card->editVipCard(array('vip_card_num' => $_POST['vip_card_num']), array('is_used' => 1, 'used_member_id' => $_SESSION['member_id'], 'use_time' => time(), 'exp_difference' => $exp_difference));
                if (!$result || !$result1) {
                    showDialog('激活失败', 'reload', 'error');
                }
            } else {
                $result = $model_vip_card->editVipCard(array('vip_card_num' => $_POST['vip_card_num']), array('is_used' => 1, 'used_member_id' => $_SESSION['member_id'], 'use_time' => time(), 'exp_difference' => 0));
                if (!$result) {
                    showDialog('激活失败', 'reload', 'error');
                }
            }
        }
        showDialog('激活成功', 'reload', 'succ');
    }
}
