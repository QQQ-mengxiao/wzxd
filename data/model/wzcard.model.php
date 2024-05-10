<?php
/**
 * 集团餐卡
 *
 */
defined('In718Shop') or exit('Access Invalid!');
class wzcardModel extends Model {
    public function __construct() {
        parent::__construct('wzcard');
    }
    
    /*
    *集团餐卡数据库的连接
    */
    private function dbconnect(){
        //测试账号
        // $host = "171.15.132.170:3306";
        // $username = "root";
        // $userpwd = "fulajimierergou123!@#";
        //正式
        $host = "125.46.28.50:33060";
        $username = "wuzijituan";
        $userpwd = "fulajimierergou123!@#";
        try{
            $conn = mysqli_connect($host,$username,$userpwd);
            $db = mysqli_select_db($conn,'zkeco');
            if($db){
                return $conn;
            }
        }
        catch(Exception $e){
            return false;exit;
        }
    }

    /*
     *变更集团餐卡余额
     */
    public function changeCard($type,$data=array()){
        //更新本地日志表
        $data_log=array();
        //更新集团餐卡信息表
        $data_card=array();
        $data_log['cardNo']=$data['cardNo'];
        $data_log['order_sn']=$data['order_sn'];
        $data_log['log_time']=time();
        //查询是否该订单进行过扣款记录
        $is_pay = Model()->table("member_ji_log")->field('log_id')->where(array('order_sn'=>$data['order_sn'],'result'=>1))->find();
        if(!empty($is_pay)){
            $cardPayResult = false;
        }else{
            //进行集团餐卡支付，支付成功为true
            $cardPayResult = $this->updateCardBaseAndConsume($data['cardNo'],$type,$data['amount']);
            //日志插入
            switch($type){
                case 'order_pay': 
                  //卡信息变更类型
                  $card_type='jicardpay';
                  //操作动作，1支付，2退款
                  $data_log['action']=1;
                  //购买人ID
                  $data_log['member_id'] = $data['member_id'];
                  //变更金额
                  $data_log['avai_amount'] =$data['amount'];
                  $data_log['content']='下单，集团餐卡支付，订单号：'.$data['order_sn'];
                break;
                case 'refund':
                  $card_type='refund';
                  //操作动作，1支付，2退款
                  $data_log['action']=2;
                  //退款审批人ID
                  $data_log['member_id']=$data['log_admin'];
                  //变更金额
                  $data_log['avai_amount']=$data['amount'];
                  $data_log['content']='确认退款，退款单号：'.$data['order_sn'];
                break;
            }
            //是否余额变更成功
            if($cardPayResult){
                 $data_log['result'] = 1;
            }else{
                $data_log['result'] = 2;
            }
            
            $insert = Model()->table('member_ji_log')->insert($data_log);
        }
        return $cardPayResult;
    }

     /*
    * 更新余额
    * @param cardno 卡号
    * @param amount 订单支付金额
    * @param available_card_amount 原所剩余额
    *
    * return 支付成功:true，失败:false
    */
    public function updateCardBaseAndConsume($cardno,$type,$amount){
        
        $dbh=$this->dbconnect();
        if($dbh){
            //拼接更新余额sql
            switch($type){
                case 'order_pay':
                    $amountSql="update ipos_issuecard set blance=blance-".$amount." where cardno='$cardno'";
                break;
                case 'refund':
                    $amountSql="update ipos_issuecard set blance=blance+".$amount." where cardno='$cardno'";
                break;
            }
            //处理事务
            try{
                $model = Model();
                $model->beginTransaction();
                //执行更新语句
                $update_result = mysqli_query($dbh,$amountSql);
                if($update_result) {
                    $result=$model->commit();
                    return true;
                }           
                    
            }catch(Exception $e){
                    $model->rollBack();
                    return false; 
                    // echo "Failed: " . $e->getMessage();
                    // showMessage("更新数据库失败",'','html','error');
            }
            
        }else{
           return false; 
        }
    }

}
