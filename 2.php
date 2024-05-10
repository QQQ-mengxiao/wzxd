
<?php
echo date("Y-m-d H-i",1614935294);die;
var_dump($_REQUEST['ref_url']);die;
    $host = "39.98.82.154";//20190130修改6666666666666MX1722个各回各家
    $username = "sa";//"lg&zosc";
    $userpwd = "hnZZzl(!@#)jksp@2018";
    // $opts_values = array(PDO::MYSQL_ATTR_INIT_COMMAND=>'set names utf8');
     // $opts_values = array('charset' => 'utf8');
        // $conn=new PDO("odbc:Driver={waterrun};Server=$host;Database=$dbname",$username,$userpwd);
        // try{
            $conn=new PDO("dblib:host=$host;dbname=sysdb;charset=utf8",$username,$userpwd);
        // $conn=odbc_connect("Driver={SQL Servif($conn){
            // try{
                echo'的我';
                 //单号 不可为空
                // $PFSaleNo = 4000000003792801;
                $PFSaleNo=4000000003793901;
                //可为空
                $BillNo = '777';
                //送货地址 可为空
                $DeliverAddr1 = '郑欧体验中心店';
                 //用户编码 可为空
                $CustomerCode = 2312323;
                //用户姓名 可为空
                $CustomerName = 3333;
                //有限期 可为空
                $ValidDate = '';
                //批发日期 可为空
                $PFSaleDate = '';
                //付款日期 可为空
                $PayDate = '';
                    $PluCode = 4811309014218;
                    //批发价 可为空
                    $PfPrice = 5;
                    //数量 可为空
                    $Counts = 5;
                    //赠送数量 不可为空
                    $ZpCount = 9;
                    //批发金额 不可为空
                    $PFTotal = 66;
                    //下单日期 可为空
                    $LrDate = time();
                    //录入时间 可为空
                    $LrTime = time();;
                    //用户编码 可为空
                    $UserCode = '';
                    //用户姓名 可为空
                    $UserName = '';
                    //送货人姓名 可为空
                    $SendorName = '';
                    //电话 可为空
                    $Phone = 17512222222;
                    //备注 可为空
                    $Place=1;
                    $Remark = '';
                    $OrgCode='33';
                    //地址信息 可为空
                    //$AddInfo1 = $order_info['extend_order_common']['reciver_info']['address'];
                    //截取适合字段类型的字符
                    //$AddInfo=substr($AddInfo1,8);
                    $AddInfo = '';
                    //手机验证码6971464040175
                    $VerificationCode = '1111';
                    $pickup_code='1111';
              $barcode=4000000003793901;
                   $barcode=4811309014201;//4811309014201
              $plucode='0101010002';
              $OrgCode='01001';
              $scity='郑欧体验中心店';
                  // $sql = "insert into PFSaleMid VALUES ($PFSaleNo,'$BillNo',$OrgCode,$CustomerCode,'$CustomerName','$ValidDate','$PFSaleDate','$PayDate','$Place','$PluCode',$PfPrice,$Counts,$ZpCount,$PFTotal,'$LrDate','$LrTime','$UserCode','$UserName','$DeliverAddr','$SendorName','$Phone','$Remark','$AddInfo','$VerificationCode')";
              // $sql = "update PFSaleMid set VerificationCode = $pickup_code where PFSaleNo='$PFSaleNo'";
               // print_r($sql);
              $OrgType=1;
               // $OrgCode=C001;
               // $sql = "select * from PFSaleMid  where PFSaleNo='$PFSaleNo'";
                // $sql = "select * from SubShop  ";
                // $sql="select * from Goods where Barcode='$barcode'";
               // $sql = "select * from SubShop where OrgName='$scity'";
                $sql = "select * from GoodsOrg where OrgCode=$OrgCode AND PluCode=$plucode";
                // $exec=odbc_exec($conn,$sql);
                $exec=$conn->query($sql);
                print_r($exec);
                // $card_info=odbc_fetch_array($exec);
                $goods_info=$exec->fetch();
                 print_r($goods_info);
                 while ($row =$exec->fetch()) {
                    $plucode = ($row['OrgName']);
                }
                print_r($plucode) ;
            // // }
            // catch(Exception $e){
            //     showMessage("查询数据库失败",'','html','error');            
            // }
        // }
        // catch(Exception $e){
        //     echo'666';
        // }
?>
 