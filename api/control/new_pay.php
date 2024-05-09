<?php
defined('In718Shop') or exit('Access Invalid!');
class new_payControl extends BaseControl
{
    //每页显示商品数
    const PAGESIZE = 10;
    //页数
    const PAGENUM = 1;

    /**
     * 消费明细
     */
    public function new_paylogOp()
    {
        $model_member = Model('member');
        $member_id = $_GET['member_id'];
        if (empty($member_id)) {
            $message = '用户信息异常';
            $res = array('code' => '200', 'message' => $message, 'data' => '');
            echo json_encode($res, 320);exit();
        }
        $log_list = Model()->table('member_exchange_log')->where(array('member_id' => $member_id, 'result' => 1))->order('log_id desc')->select();
        foreach ($log_list as $key => $value) {
            // if($log_list[$key]['action']==1){
            //    $log_list[$key]['action']='下单';
            // }else{
            //    $log_list[$key]['action']='退款';
            // }
            $log_list[$key]['log_time'] = date('Y-m-d H:i:s', $value['log_time']);
            // unset($log_list[$key]['result']);
        }
        $totaldata = count($log_list);
        // var_dump( $totaldata);die;
        $totalpage = ceil($totaldata / self::PAGESIZE);
        $data['totalpage'] = $totalpage;
        if (!empty($_GET['num'])) {
            $num = $_GET['num'];
        } else {
            $num = self::PAGENUM;
        }
        //当前页码
        $data['current'] = $num;
        $data['size'] = self::PAGESIZE;
        $data['pages'] = $totalpage;
        $data['total'] = $totaldata;
        if ($log_list) {
            $log_list = array_slice($log_list, ($num - 1) * self::PAGESIZE, self::PAGESIZE);
            $data['log_list'] = $log_list;
            $message = 'sucess';
            $res = array('code' => '100', 'message' => $message, 'data' => $data);
            echo json_encode($res, 320);
        } else {
            $message = 'fail';
            $res = array('code' => '300', 'message' => $message, 'data' => '');
            echo json_encode($res, 320);
        }
    }
    public function select_balanceOp()
    {
        $member_id = $_GET['member_id'];
        if (empty($member_id)) {
            $message = '用户信息异常';
            $res = array('code' => '200', 'message' => $message, 'data' => '');
            echo json_encode($res, 320);exit();
        }
        $cardno = Model()->table('member_card')->where(array('member_id' => $member_id))->limit(1)->find();

        $card_info = Model('card_new')->getMemberCardInfobygh(strval($cardno['gonghao']));
        if ($card_info) {
            $message = 'sucess';
            $res = array('code' => '100', 'message' => $message, 'balance' => ncPriceFormat($card_info['balance16']));
            echo json_encode($res, 320);
        } else {
            $message = 'fail';
            $res = array('code' => '300', 'message' => $message, 'data' => '');
            echo json_encode($res, 320);
        }
    }

}
