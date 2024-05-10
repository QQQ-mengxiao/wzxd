<?php
/**
 *一卡通数据库操作
 */
defined('In718Shop') or exit('Access Invalid!');

class cardModel extends Model {
    public function __construct(){
        parent::__construct('card');
    }
    /*
    *数据库的连接
    */
    private function dbconnect($dbname){
        // echo '33333333333333';die;
        $host = "117.159.3.240";
              // $host = "10.10.9.21";//20190130修改
        $username = "sa";//"lg&zosc";
        $userpwd = "lgchen@718";
        // $conn=new PDO("odbc:Driver={waterrun};Server=$host;Database=$dbname",$username,$userpwd);
        try{
            $conn=new PDO("dblib:host=$host;dbname=$dbname;charset=utf8",$username,$userpwd);
        // $conn=odbc_connect("Driver={SQL Server};Server=$host;Database=$dbname",$username,$userpwd);
            if($conn){
                return $conn;
            }
        }
        catch(Exception $e){
            showMessage("连接一卡通数据库失败！",'','html','error');
        }
    }
    /*
    *获取一卡通卡内信息
    */
    public function getMemberCardInfo($cardno){
        // $conn=$this->dbconnect('waterrun');
        $conn=$this->dbconnect("waterrun");
        if($conn){
            try{
                $sql="select * from HCCardBase where Sno in ($cardno)";
                // $exec=odbc_exec($conn,$sql);
                $exec=$conn->query($sql);
                // $card_info=odbc_fetch_array($exec);
                $card_info=$exec->fetch();
                return $card_info;
            }
            catch(Exception $e){
                showMessage("查询数据库失败",'','html','error');            
            }
        }
    }
     /*
    *获取一卡通卡内信息根据工号
    */
    public function getMemberCardInfobygh($gonghao){
        // $conn=$this->dbconnect('waterrun');
        $conn=$this->dbconnect("waterrun");
        if($conn){
            try{
                $sql="select * from HCCardBase where PersonalID in ($gonghao)";
                // $exec=odbc_exec($conn,$sql);
                $exec=$conn->query($sql);
                // $card_info=odbc_fetch_array($exec);
                $card_info=$exec->fetch();
                return $card_info;
            }
            catch(Exception $e){
                showMessage("查询数据库失败",'','html','error');            
            }
        }
    }
    /*
    *更新卡内余额等
    * @param type-->余额变更类型 
    */
    public function updateCardBase($cardno,$data_card,$type){
        $conn=$this->dbconnect("waterrun");
        if($conn){        
            switch($type){
                case 'zihpay':
                $sql="update HCCardBase set Balance=Balance-".$data_card['amount'].",LastDate='".$data_card['LastDate']."' where Sno='$cardno'";
                break;
                case 'refund':
                $sql="update HCCardBase set Balance=Balance+".$data_card['amount']." where Sno='$cardno'";
                break;
                case 'pwd':     //后续加密
                $sql="update HCCardBase set Password='".$data_card['Password']."' where Sno='$cardno'";
                break;
            }
            try{
                $result=$conn->exec($sql);
                if($result){
                    return $result;          
                }
            }
            catch(Exception $e){
                showMessage("更新数据库失败",'','html','error');            
            }
        }
    }
    /*
    *更新一卡通消费详细表
    */
    public function updateCardConsume($cardno,$data_consume){
        // var_dump($cardno);die;
        // $conn=$this->dbconnect("waterhis");
        $conn=$this->dbconnect("waterrun");
        if($conn){
            $card_info=$this->getMemberCardInfo($cardno);
            $CardNo=$card_info['CardNo'];//这个字段跟数据库查询语句有关，本来SQL server不区分大小写，但要按查询select 的字段返回的写
            // var_dump($card_info);die;
            $ConsumeDate=$data_consume['ConsumeDate'];
            $ConsumeTime=$data_consume['ConsumeTime'];
            $sql="insert into HCConsume values('$cardno','$CardNo',1,10080,".$data_consume['ConsumeType'].",'$ConsumeDate','$ConsumeTime',".$data_consume['Amount'].",".$card_info['Balance'].",'$ConsumeDate','$ConsumeTime',".$data_consume['OrderSno'].")";//
            // $result=odbc_exec($conn,$sql);
            try{
                $result=$conn->exec($sql);
                if($result){
                    return $result;
                }else{
                     return $result;
                }   
            }
            catch(Exception $e){
                showMessage('更新数据库失败','','html','error');
            }
        }
    }
	
	/*
    *获取一卡通卡内信息
    */
    public function getMemberCardList(){
        // $conn=$this->dbconnect('waterrun');
        $conn=$this->dbconnect("waterrun");
        if($conn){
            try{
                $sql="select * from HCCardBase";
                // $exec=odbc_exec($conn,$sql);
                $exec=$conn->query($sql);
                // $card_info=odbc_fetch_array($exec);
                $card_list=$exec->fetchall();
                return $card_list;
            }
            catch(Exception $e){
                showMessage("查询数据库失败",'','html','error');            
            }
        }
    }
    
    
    /*
    * 更新余额、消费记录
    * 2021.8.17新增
    * @param cardno 卡号
    * @param data_card 更新余额信息
    * @param type 余额变更类型
    * @param data_consume 更新消费信息
    *
    * return 支付成功:true，失败:false/null
    */
    public function updateCardBaseAndConsume($cardno,$data_card,$type,$data_consume){
        
        //参数处理
        //支付订单号
        $orderSno = $data_consume['OrderSno'];
        if ($orderSno <= 0) {
            return false;
        }
        
        $dbh=$this->dbconnect("waterrun");
        if($dbh){
            
            //支付记录/退款记录查询sql
            switch($type){
                case 'zihpay':
                    $isPaidSql = "select count(*) from HCConsume where OrderSno='$orderSno' and Amount>0";
                    //判断是否有记录
                    $stmt = $dbh->query($isPaidSql);
                    if ($stmt) {
                        $count = $stmt->fetchColumn();
                        if ($count > 0) {
                            //有记录，返回
                            return false;
                        }
                    }
                break;
                    
                case 'refund':
                    $isPaidSql = "select count(*) from HCConsume where OrderSno='$orderSno' and Amount<0";
                break;
            }
            
            //拼接更新余额sql
            switch($type){
                case 'zihpay':
                    $amount = $data_card['amount'];
                    $lastData = $data_card['LastDate'];
                    $amountSql="update HCCardBase set Balance=Balance-$amount,LastDate='$lastData' where Sno='$cardno'";
                break;
                case 'refund':
                    $amountSql="update HCCardBase set Balance=Balance+".$data_card['amount']." where Sno='$cardno'";
                break;
            }
            
            //拼接更新消费记录sql
            $ConsumeType = $data_consume['ConsumeType'];
            $ConsumeDate = $data_consume['ConsumeDate'];
            $ConsumeTime = $data_consume['ConsumeTime'];
            $ConsumeAmount = $data_consume['Amount'];
            $consumeSql="insert into HCConsume select '$cardno',CardNo,1,10080,$ConsumeType,'$ConsumeDate','$ConsumeTime',$ConsumeAmount,Balance,'$ConsumeDate','$ConsumeTime','$orderSno' FROM HCCardBase WHERE Sno='$cardno'";
            
            //处理事务
            try{
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                $dbh->beginTransaction();
                
                $dbh->exec($amountSql);
                $dbh->exec($consumeSql);
                
                $result=$dbh->commit();
                if($result) {
                    return true;
                }
            }
            catch(Exception $e){
                $dbh->rollBack();
                echo "Failed: " . $e->getMessage();
                showMessage("更新数据库失败",'','html','error');
            }
        }
    }
    
}
