<?php
/**
 * 商家中心订单导出
 *
 *
 *
 **/
 
require_once BASE_ROOT_PATH.'/PHPExcel/Classes/PHPExcel.php'; //引入文件
require_once BASE_ROOT_PATH.'/PHPExcel/Classes/PHPExcel/IOFactory.php'; //引入文件
require_once BASE_ROOT_PATH.'/PHPExcel/Classes/PHPExcel/Reader/Excel2007.php'; //引入文件
require_once BASE_ROOT_PATH.'/PHPExcel/Classes/PHPExcel/Reader/IReader.php'; //引入文件
defined('In718Shop') or exit('Access Invalid!');

class store_export_psssControl extends BaseSellerControl
{
    public function __construct()
    {
    }

    /**
     * 导出订单
     *
     */
    public function export_orderOp()
    {
       $model_goods = Model('goods');
        $model_order = Model('order');
        $condition = array();
        $condition['order.order_state'] = 20;

        //发货人姓名
        if($_GET['daddress_id']>0){
            $condition['order_goods.deliverer_id'] = $_GET['daddress_id'];
        }
         //配送方式
        if($_GET['delivery_type_id']>0){
            $daddress_list = Model('peisong')->where(array('id' => $_GET['delivery_type_id']))->find();
            $daddress_ids = explode(',', $daddress_list['deliever_id']);
            // foreach ($daddress_list as $key => $value) {
            //     $daddress_ids[] = $value['deliever_id'];
            // }
            $daddress_ids=array_values($daddress_ids);
            $condition['order_goods.deliverer_id'] = array('in',$daddress_ids);
            $name=$daddress_list['p_name'];
        }
        // var_dump($condition);die;
        $time_end = date("Y-m-d",time());
        $time_end .= " 11:30:00";        
        $time_end_str = strtotime($time_end);
        $time_start_str = strtotime('-1 days',$time_end_str);
        //$time_start_str_1 = strtotime('-1 days',$time_end_str);
        $condition['order.payment_time'] = array('between', array($time_start_str, $time_end_str));

        // $if_start_time_pay = $_GET['query_start_date_pay2'];
        // $if_end_time_pay = $_GET['query_end_date_pay2'];
        // $start_unixtime_pay = $if_start_time_pay ? strtotime($_GET['query_start_date_pay2']) : null;
        // $end_unixtime_pay = $if_end_time_pay ? strtotime($_GET['query_end_date_pay2']) : null;
        // if ($start_unixtime_pay || $end_unixtime_pay) {
        //     $condition['order.payment_time'] = array('between', array($start_unixtime_pay, $end_unixtime_pay));
        // }
        
        $data = $model_order->getOrderGoodsExportList($condition,'20000'); 
        
        $ordergoods_arr=array();
        // foreach($data as $k=>$v){
        //      foreach ($v['extend_order_goods'] as $key => $value) { 
        //             if(strpos($value['goods_name'],"预售") !== false ){
        //                  unset($data[$k]['extend_order_goods'][$key]);
        //             }
        //             if($value['goods_type']==1&&$v['extend_order_common']['ziti_ladder_time']>0){
        //                  unset($data[$k]['extend_order_goods'][$key]);
        //             }

        //      }
           
        // }
        // var_dump($data);die;
        // var_dump(date("n.j",$end_unixtime_pay));die;
        // $str_time=date("n.j",$end_unixtime_pay);
        // $str='预售'.$str_time;
        // $str = Model('search')->decorateSearch_pre($str);
        // $where=array();
        // if($_GET['daddress_id']>0){
        //     $where['order_goods.deliverer_id'] = $_GET['daddress_id'];
        // }
        //  //配送方式
        // if($_GET['delivery_type_id']>0){
        //     $where['order_goods.deliverer_id'] = array('in',$daddress_ids);
        // }
        // $where['order.order_state'] = 20;
        // $where['order_goods.goods_name'] = array('like', $str); 
        // $data1 = $model_order->getOrderGoodsExportList($where,'20000'); 
        // foreach($data1 as $kk1=>$vv1){
        //      foreach ($vv1['extend_order_goods'] as $key => $value) { 
        //       if($value['goods_type']==1&&$v['extend_order_common']['ziti_ladder_time']>0){
        //                  unset($data[$k]['extend_order_goods'][$key]);
        //             }

        //      }
           
        // }
        // $where2=array();
        // if($_GET['daddress_id']>0){
        //     $where2['order_goods.deliverer_id'] = $_GET['daddress_id'];
        // }
        //  //配送方式
        // if($_GET['delivery_type_id']>0){
        //     $where2['order_goods.deliverer_id'] = array('in',$daddress_ids);
        // }
        // $where2['order_common.ziti_ladder_time'] = array('between', array($start_unixtime_pay, $end_unixtime_pay));
        // $where2['order.order_state'] = 20;
        // $where2['order_goods.goods_type'] = 1;
        // $data2 = $model_order->getOrderGoodsExportList($where2,'20000'); 
        // // var_dump($where2);die;
        // $dataz=array_merge($data,$data1);
        // $data=array_merge($dataz,$data2);

        foreach($data as $kk=>$vv){
            
            if($vv['is_zorder']==0 ){
                unset($data[$kk]);
                continue;
            }
             foreach ($vv['extend_order_goods'] as $key => $value) { 
                $model_refund_return = Model('refund_return');
                $refund_list = $model_refund_return->getRefundReturnList(array('order_id' => $vv['order_id']));
                if(!empty( $refund_list)&&is_array( $refund_list)){
                    foreach ($refund_list as $key1 => $value1) {
                       if($value1['goods_id']==0){
                            if($value1['seller_state']<3){
                                 unset($data[$kk]);
                            }  
                       }else{
                          if($value1['goods_id']==$value['goods_id']&&$value1['seller_state']<3){
                                 unset($data[$kk]['extend_order_goods'][$key]);
                            }  
                       }
                    }
                }  
                if($_GET['daddress_id']>0){
                    if($value['deliverer_id']!=$_GET['daddress_id']){
                             unset($data[$kk]['extend_order_goods'][$key]);
                        
                    }
                }
                if($_GET['delivery_type_id']>0){
                    if(!in_array($value['deliverer_id'], $daddress_ids)){
                             unset($data[$kk]['extend_order_goods'][$key]);
                        
                    }
                }    
             }
        }
        // var_dump($data);die;
        foreach ($data as $k2 => $v2) {
            foreach ($v2['extend_order_goods'] as $k22 => $v22) {
                $ordergoods_arr[]=$v22;
            }
        }

        foreach ($ordergoods_arr as $k => $v) {
              $data_arr[$v['goods_id']][] = $v;
          }
        // var_dump($ordergoods_arr);die;
          $num_goodsnum=0;
          $num_costprice=0;
          $data_excel=array();
          if(is_array($data_arr)&&!empty($data_arr)){
              foreach ($data_arr as $ke => $va) {
                 $sumall=0;
                  $goods_cost_price_all=0;
                foreach ($va as $ke1 => $va1) {
                    $sumall=$sumall+$va1['goods_num'];
                    $goods_cost_price_all=$goods_cost_price_all+$va1['goods_cost_price']*$va1['goods_num'];
                }
                 $data_array[$ke]['goods_id']=$va[0]['goods_id'];
                 $data_array[$ke]['goods_name']=$va[0]['goods_name'];
                 $data_array[$ke]['sumall']=$sumall;
                 $data_array[$ke]['goods_cost_price_all']= $goods_cost_price_all;
                 $num_costprice=$num_costprice+$goods_cost_price_all;
                 $num_goodsnum=$num_goodsnum+$sumall;
              } 
              $count=count($data_array);
          // var_dump($count);die;
          $data_array=array_values($data_array);
          $data_array[$count]['goods_name']='总计';
          $data_array[$count]['goods_id']='';
          $data_array[$count]['sumall']=$num_goodsnum;
          $data_array[$count]['goods_cost_price_all']=$num_costprice;
          // var_dump($data_array);die;
           // var_dump($condition2);die;
          $data_excel['data_array']=$data_array;
          $data_excel['time1']=$end_unixtime_pay;
          // var_dump($start_unixtime_pay);die;
          if($_GET['daddress_id']>0){
            $data_excel['deliverer_id']=$_GET['daddress_id'];
              $data_excel['name']='';
          }else{
            $data_excel['deliverer_id']=0;
             $data_excel['name']=$name;
          }
        }else{
            showDialog('无数据');
        }
         
          
        //表格数组
        $model_order = Model('order');
        ini_set('max_execution_time', '0');
        $count = count($data);//echo $count;die;
        if (!$_GET['curpage']) {
            if ($count > self::EXPORT_SIZE) {
                $page = ceil($count / self::EXPORT_SIZE);
                for ($i = 1; $i <= $page; $i++) {
                    $limit1 = ($i - 1) * self::EXPORT_SIZE + 1;
                    $limit2 = $i * self::EXPORT_SIZE > $count ? $count : $i * self::EXPORT_SIZE;
                    $array[$i] = $limit1 . ' ~ ' . $limit2;
                }
                Tpl::output('list', $array);
                Tpl::output('murl', 'index.php?act=order&op=index');
                Tpl::showpage('store_export_goods.excel');
            } else {
                //直接下载
                $this->excel_order($data_excel);
            }
        } else {  //下载
            $limit1 = ($_GET['curpage'] - 1) * self::EXPORT_SIZE;
            $limit2 = self::EXPORT_SIZE;
            $data = $model_order->getOrderList3('', $condition, '', '*', 'order_id desc', "{$limit1},{$limit2}", array('order_goods', 'order_common', 'member', 'goods_kuajing_d'));
            $this->excel_order($data_excel);
        }
    }

    private function excel_order($data_excel){  
        // var_dump($data_tmp);die;
        $data_tmp=$data_excel['data_array'];
        $model_daddress = Model('daddress');
        if($data_excel['deliverer_id']>0){
        $address = $model_daddress->getAddressInfo(array('address_id'=>$data_excel['deliverer_id']));
                $name=$address['seller_name'];
                $address_info=$address['address'];
                $tel=$address['telphone'];
        }else{
             $name=$data_excel['name'];
             $address_info='';
             $tel='';
        }
        
        $time=date('Y-m-d',$data_excel['time1']);
        // var_dump($time);die;
        $excel = new PHPExcel();
        $letter = array('A', 'B', 'C', 'D','E' );
        // $tableheader = array( '商品名称', '商品名称','商品条码', '商品数量', '商品总成本价',);
        // 设置行高度
        $excel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);      //第一行是否加粗
        $excel->getActiveSheet()->getStyle('A1')->getFont()->setSize(16);         //第一行字体大小
        $excel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(20); //设置默认行高
        $excel->getActiveSheet()->getRowDimension('1')->setRowHeight(40);    //第一行行高
        // 设置水平居中
        $excel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        //设置单元格宽度
        $excel->getActiveSheet()->getColumnDimension('A')->setWidth(80);
        $excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
        $excel->getActiveSheet()->mergeCells('A1:E1');
        // for ($i = 0; $i < count($tableheader); $i++) {
        //     $excel->getActiveSheet()->setCellValue("$letter[$i]1", "$tableheader[$i]");
        //     $excel->getActiveSheet()->getStyle("$letter[$i]1", "$tableheader[$i]")->getFont()->setBold(true);
        // }
        $a=count($data_tmp)+3;
        $b='A'.$a;$c='E'.$a;
        $d=$a+1;$e='A'.$d;$f='E'.$d;
        $g=$a+2;$h='A'.$g;$i='E'.$g;
        $excel->getActiveSheet()->mergeCells($b.':'.$c);
        $excel->getActiveSheet()->mergeCells($e.':'.$f);
         $excel->getActiveSheet()->mergeCells($h.':'.$i);           
        $excel->setActiveSheetIndex(0)
                ->setCellValue('A1', $time.$name.'配货清单')
                ->setCellValue('A2', '商品名称')
                ->setCellValue('B2', '商品条码')
                ->setCellValue('C2', '商品货号 ')
                 ->setCellValue('D2', '商品数量')
                  ->setCellValue('E2', '商品总成本价')
                 ->setCellValue($b, '供货商：'.$name.'             交接人：                     送货时间：                           签收人：')
                  ->setCellValue($e, '供货商地址：'.$address_info.'                联系电话：'.$tel)
                  ->setCellValue($h, '配送地址：郑州市管城区航海路物资集团仓库        联系人：田经理13592636294');
        $styleThinBlackBorderOutline = array(
        'borders' => array(
            'allborders' => array( //设置全部边框
                'style' => \PHPExcel_Style_Border::BORDER_THIN //粗的是thick
            ),

        ),
    );  
        $x=$a+2;
        $z='E'.$x;
        $w='A1';
        // var_dump($z);die;
    $excel->getActiveSheet()->getStyle($w.':'.$z)->applyFromArray($styleThinBlackBorderOutline);
        //表格数组
        $order_data = [];
        // var_dump($data_tmp);die;
        foreach ($data_tmp as $k => $v) {
            $model_goods = Model('goods');
        $goods_detail = $model_goods->getGoodsInfo(array('goods_id'=>$v['goods_id']));
        // var_dump($goods_detail);die;
               $order_data[] =  array(
                $v['goods_name'],
                $goods_detail['goods_barcode'],
                $goods_detail['goods_serial'],
                $v['sumall'],
                $v['goods_cost_price_all'],
            );
           
        }
        //填充表格信息
        for ($i = 3; $i <= count($order_data) + 2; $i++) {
            $j = 0;
            foreach ($order_data[$i - 3] as $key => $value) {
                $excel->getActiveSheet()->setCellValue("$letter[$j]$i", "$value");
                $excel->getActiveSheet()->setCellValueExplicit("$letter[$j]$i","$value",PHPExcel_Cell_DataType::TYPE_STRING);
                $j++;
            }
        }
        var_dump($order_data);die;
        //创建Excel输入对象
        $write = new PHPExcel_Writer_Excel5($excel);
        $filename =  $time.$name.'配货商品表.xls';
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");;
        header('Content-Disposition:attachment;filename=' . $filename);
        header("Content-Transfer-Encoding:binary");
        $write->save('php://output');
        die;
    }
     /**
     * 商品pl导出订单
     *
     */
    public function export_orderplOp()
    {    
        $model_order = Model('order');
        $condition = array();
       
            $condition['order.order_state'] = 20;

       
         
                 //配送方式
        if($_GET['delivery_type_id']>0){
            $daddress_list = Model('peisong')->where(array('id' => $_GET['delivery_type_id']))->find();
            $daddress_ids = explode(',', $daddress_list['deliever_id']);
            // foreach ($daddress_list as $key => $value) {
            //     $daddress_ids[] = $value['deliever_id'];
            // }
            // var_dump($daddress_ids);die;
         
            $daddress_ids=array_values($daddress_ids);
            $count=count($daddress_ids);
            $date=date('Y-m-dhis',time());
            $dir=BASE_ROOT_PATH.'/excel/goods1/'. $date. '/';
            // var_dump($daddress_ids);die;
            if (!is_dir ($dir)){
            $a=mkdir($dir,0777,true); //创建文件夹
            } 
            for ($i=0; $i < $count; $i++) { 
                // var_dump($daddress_ids[$i]);die;
                 set_time_limit(0);
               $condition['order_goods.deliverer_id'] =$daddress_ids[$i];
               if(!empty($daddress_ids[$i])){
                 $daddress_id=$daddress_ids[$i];
               $name=$daddress_list['p_name'];
               $if_start_time_pay = $_GET['query_start_date_pay2'];
                $if_end_time_pay = $_GET['query_end_date_pay2'];
                $start_unixtime_pay = $if_start_time_pay ? strtotime($_GET['query_start_date_pay2']) : null;
                $end_unixtime_pay = $if_end_time_pay ? strtotime($_GET['query_end_date_pay2']) : null;
                if ($start_unixtime_pay || $end_unixtime_pay) {
                    $condition['order.payment_time'] = array('between', array($start_unixtime_pay, $end_unixtime_pay));
                }
            
            $data = $model_order->getOrderGoodsExportList($condition,'20000'); 
            // echo'3333333';die;
            $ordergoods_arr=array();
            
            foreach($data as $kk=>$vv){
                
                if($vv['is_zorder']==0 ){
                    unset($data[$kk]);
                    continue;
                }
                 foreach ($vv['extend_order_goods'] as $key => $value) { 
                    $model_refund_return = Model('refund_return');
                    $refund_list = $model_refund_return->getRefundReturnList(array('order_id' => $vv['order_id']));
                    if(!empty( $refund_list)&&is_array( $refund_list)){
                        foreach ($refund_list as $key1 => $value1) {
                           if($value1['goods_id']==0){
                                if($value1['seller_state']<3){
                                     unset($data[$kk]);
                                }  
                           }else{
                              if($value1['goods_id']==$value['goods_id']&&$value1['seller_state']<3){
                                     unset($data[$kk]['extend_order_goods'][$key]);
                                }  
                           }
                        }
                    }  
                    if($daddress_id>0){
                        if($value['deliverer_id']!=$daddress_ids[$i]){
                                 unset($data[$kk]['extend_order_goods'][$key]);
                            
                        }
                    }
                    // if($_GET['delivery_type_id']>0){
                    //     $daddress_id=$_GET['delivery_type_id'];
                    //      if(!in_array($value['deliverer_id'], $daddress_ids)){
                    //              unset($data[$kk]['extend_order_goods'][$key]);
                            
                    //     }
                    // }    
                 }
                $data[$kk]['extend_order_goods']=array_values($data[$kk]['extend_order_goods']);
            }
            // $ordergoods_arr=array();
           foreach ($data as $k2 => $v2) {
            foreach ($v2['extend_order_goods'] as $k22 => $v22) {
                $ordergoods_arr[]=$v22;
            }
        }
          $data_arr=array();
        foreach ($ordergoods_arr as $k => $v) {
              $data_arr[$v['goods_id']][] = $v;
          }
        // var_dump($data_arr);die;
          $num_goodsnum=0;
          $num_costprice=0;
          $data_excel=array();
          $data_array=array();
          // if(is_array($data_arr)&&!empty($data_arr)){
              foreach ($data_arr as $ke => $va) {
                 $sumall=0;
                  $goods_cost_price_all=0;
                foreach ($va as $ke1 => $va1) {
                    $sumall=$sumall+$va1['goods_num'];
                    $goods_cost_price_all=$goods_cost_price_all+$va1['goods_cost_price']*$va1['goods_num'];
                }
                 $data_array[$ke]['goods_id']=$va[0]['goods_id'];
                 $data_array[$ke]['goods_name']=$va[0]['goods_name'];
                 $data_array[$ke]['sumall']=$sumall;
                 $data_array[$ke]['goods_cost_price_all']= $goods_cost_price_all;
                 $num_costprice=$num_costprice+$goods_cost_price_all;
                 $num_goodsnum=$num_goodsnum+$sumall;
              } 
              $count1=count($data_array);
              // var_dump($count);die;
              $data_array=array_values($data_array);
              $data_array[$count1]['goods_name']='总计';
              $data_array[$count1]['goods_id']='';
              $data_array[$count1]['sumall']=$num_goodsnum;
              $data_array[$count1]['goods_cost_price_all']=$num_costprice;
              // var_dump($data_array);die;
               // var_dump($condition2);die;
              $data_excel['data_array']=$data_array;
              $data_excel['time1']=$end_unixtime_pay;
              // var_dump($start_unixtime_pay);die;
              
                $data_excel['deliverer_id']=$daddress_id;
                 $data_excel['name']=$i;          
            // }else{
            //     showDialog('无数据');
            // }
         // var_dump($i);
          
        //表格数组
            $model_order = Model('order');
            ini_set('max_execution_time', '0');
            if($daddress_id>0){
                $model_daddress = Model('daddress');
                $address = $model_daddress->getAddressInfo(array('address_id'=>$daddress_id));
                $name=$address['seller_name'];
            }
            $time=date('Y-m-d',$end_unixtime_pay);
             $name= $time.$name.'配货商品表.xls';
            $name=iconv('utf-8','gb2312',$name);
            $filename = $time.$i.'.xls';
            $data_list[]= $dir.$filename;
            $names[]=  $name;
            // var_dump($i);
            $this->excel_orderpl($data_excel,$dir);
               }
              
            }
            //打包
            $zipname= $dir.'excel.zip';
            $files= $data_list;
        //     $zip_excel = Model('zip_excel');
        // $address = $zip_excel->zip($files,$zipname);
            // var_dump($files);
            // die;
            $zip = new ZipArchive();
            $res = $zip->open($zipname, ZipArchive::CREATE );
            // var_dump($res);die;
            if ($res== TRUE) {
             // foreach ($files as $file) {
                  foreach ($files as $k => $v) {
             //这里直接用原文件的名字进行打包，也可以直接命名，需要注意如果文件名字一样会导致后面文件覆盖前面的文件，所以建议重新命名
                           $value = explode("/", $v);
                            $end = end($value);
                            $a=$zip->addFile($v, $end);
                              $zip->renameName($end, $names[$k]);
              // $a=$zip->addFile($file, $new_filename);
              // var_dump( $a);
             }
               //关闭文件
            $zip->close();
            }
          //这里是下载zip文件
           $dir= substr($dir, 0, -1);
           // var_dump($dir);die;
       set_time_limit(0);
                        header("Cache-Control: public");
                        header("Content-Description: File Transfer");
                        header('Content-disposition: attachment; filename='.date('Y-m-d',time()).'.zip'); //文件名
                        header("Content-Type: application/zip"); //zip格式的
                        header("Content-Transfer-Encoding: binary"); //告诉浏览器，这是二进制文件
                        header('Content-Length: ' . filesize($zipname)); //告诉浏览器，文件大小
            
                        @readfile($zipname);

                    //删除临时文件
                    $a=unlink($zipname);
      
        }else{
            showDialog('请选择配送方式');
        }
        
    }
    private function excel_orderpl($data_excel,$dir){  
        // var_dump($data_tmp);die;
        $data_tmp=$data_excel['data_array'];
        $model_daddress = Model('daddress');
        if($data_excel['deliverer_id']>0){
        $address = $model_daddress->getAddressInfo(array('address_id'=>$data_excel['deliverer_id']));
                $name_biao=$data_excel['name'];
                $name=$address['seller_name'];
                $address_info=$address['address'];
                $tel=$address['telphone'];
        }else{
             $name=$data_excel['name'];
             $address_info='';
             $tel='';
        }
        
        $time=date('Y-m-d',$data_excel['time1']);
        // var_dump($time);die;
        $excel = new PHPExcel();
        $letter = array('A', 'B', 'C', 'D','E' );
        // $tableheader = array( '商品名称', '商品名称','商品条码', '商品数量', '商品总成本价',);
        // 设置行高度
        $excel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);      //第一行是否加粗
        $excel->getActiveSheet()->getStyle('A1')->getFont()->setSize(16);         //第一行字体大小
        $excel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(20); //设置默认行高
        $excel->getActiveSheet()->getRowDimension('1')->setRowHeight(40);    //第一行行高
        // 设置水平居中
        $excel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        //设置单元格宽度
        $excel->getActiveSheet()->getColumnDimension('A')->setWidth(80);
        $excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
        $excel->getActiveSheet()->mergeCells('A1:E1');
        // for ($i = 0; $i < count($tableheader); $i++) {
        //     $excel->getActiveSheet()->setCellValue("$letter[$i]1", "$tableheader[$i]");
        //     $excel->getActiveSheet()->getStyle("$letter[$i]1", "$tableheader[$i]")->getFont()->setBold(true);
        // }
        $a=count($data_tmp)+3;
        $b='A'.$a;$c='E'.$a;
        $d=$a+1;$e='A'.$d;$f='E'.$d;
        $g=$a+2;$h='A'.$g;$i='E'.$g;
        $excel->getActiveSheet()->mergeCells($b.':'.$c);
        $excel->getActiveSheet()->mergeCells($e.':'.$f);
         $excel->getActiveSheet()->mergeCells($h.':'.$i);           
        $excel->setActiveSheetIndex(0)
                ->setCellValue('A1', $time.$name.'配货清单')
                ->setCellValue('A2', '商品名称')
                ->setCellValue('B2', '商品条码')
                ->setCellValue('C2', '商品货号 ')
                 ->setCellValue('D2', '商品数量')
                  ->setCellValue('E2', '商品总成本价')
                 ->setCellValue($b, '供货商：'.$name.'             交接人：                     送货时间：                           签收人：')
                  ->setCellValue($e, '供货商地址：'.$address_info.'                联系电话：'.$tel)
                  ->setCellValue($h, '配送地址：郑州市管城区航海路物资集团仓库        联系人：田经理13592636294');
        $styleThinBlackBorderOutline = array(
        'borders' => array(
            'allborders' => array( //设置全部边框
                'style' => \PHPExcel_Style_Border::BORDER_THIN //粗的是thick
            ),

        ),
    );  
        $x=$a+2;
        $z='E'.$x;
        $w='A1';
        // var_dump($z);die;
    $excel->getActiveSheet()->getStyle($w.':'.$z)->applyFromArray($styleThinBlackBorderOutline);
        //表格数组
        $order_data = [];
        // var_dump($data_tmp);die;
        foreach ($data_tmp as $k => $v) {
            $model_goods = Model('goods');
        $goods_detail = $model_goods->getGoodsInfo(array('goods_id'=>$v['goods_id']));
        // var_dump($goods_detail);die;
               $order_data[] =  array(
                $v['goods_name'],
                $goods_detail['goods_barcode'],
                $goods_detail['goods_serial'],
                $v['sumall'],
                $v['goods_cost_price_all'],
            );
           
        }
        //填充表格信息
        for ($i = 3; $i <= count($order_data) + 2; $i++) {
            $j = 0;
            foreach ($order_data[$i - 3] as $key => $value) {
                $excel->getActiveSheet()->setCellValue("$letter[$j]$i", "$value");
                $excel->getActiveSheet()->setCellValueExplicit("$letter[$j]$i","$value",PHPExcel_Cell_DataType::TYPE_STRING);
                $j++;
            }
        }
        
        //创建Excel输入对象
        $write = new PHPExcel_Writer_Excel5($excel);
        $filename =  $time.$name_biao.'.xls';
        // header("Pragma: public");
        // header("Expires: 0");
        // header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        // header("Content-Type:application/force-download");
        // header("Content-Type:application/vnd.ms-execl");
        // header("Content-Type:application/octet-stream");
        // header("Content-Type:application/download");;
        // header('Content-Disposition:attachment;filename=' . $filename);
        // header("Content-Transfer-Encoding:binary");
       $filename = iconv('utf-8','gb2312',$filename);
        $write->save($dir.$filename);

    }
     /**
     * pl导出订单
     *
     */
    public function export_order2plOp()
    {    
        $model_order = Model('order');
        $condition = array();
       
            $condition['order.order_state'] = 20;

        //发货人姓名
        if($_GET['daddress_id']>0){
            $condition['order_goods.deliverer_id'] = $_GET['daddress_id'];
            $daddress_id=$_GET['daddress_id'];
            $name='';
        }
         
                 //配送方式
        if($_GET['delivery_type_id']>0){
            $daddress_list = Model('peisong')->where(array('id' => $_GET['delivery_type_id']))->find();
            $daddress_ids = explode(',', $daddress_list['deliever_id']);
            // foreach ($daddress_list as $key => $value) {
            //     $daddress_ids[] = $value['deliever_id'];
            // }
            
            $daddress_ids=array_values($daddress_ids);
            $count=count($daddress_ids);
            $date=date('Y-m-dhis',time());
            $dir=BASE_ROOT_PATH.'/excel/goods2/'. $date. '/';
            // var_dump($dir);die;
            if (!is_dir ($dir)){
            $a=mkdir($dir,0777,true); //创建文件夹
            }
            for ($i=0; $i < $count; $i++) { 
                // var_dump($daddress_ids[$i]);die;
               $condition['order_goods.deliverer_id'] =$daddress_ids[$i];
               $daddress_id=$daddress_ids[$i];
               $name=$daddress_list['p_name'];
               $if_start_time_pay = $_GET['query_start_date_pay2'];
            $if_end_time_pay = $_GET['query_end_date_pay2'];
            $start_unixtime_pay = $if_start_time_pay ? strtotime($_GET['query_start_date_pay2']) : null;
            $end_unixtime_pay = $if_end_time_pay ? strtotime($_GET['query_end_date_pay2']) : null;
            if ($start_unixtime_pay || $end_unixtime_pay) {
                $condition['order.payment_time'] = array('between', array($start_unixtime_pay, $end_unixtime_pay));
            }
            
            $data = $model_order->getOrderGoodsExportList($condition,'20000'); 
            
            $ordergoods_arr=array();
            
            foreach($data as $kk=>$vv){
                
                if($vv['is_zorder']==0 ){
                    unset($data[$kk]);
                    continue;
                }
                 foreach ($vv['extend_order_goods'] as $key => $value) { 
                    $model_refund_return = Model('refund_return');
                    $refund_list = $model_refund_return->getRefundReturnList(array('order_id' => $vv['order_id']));
                    if(!empty( $refund_list)&&is_array( $refund_list)){
                        foreach ($refund_list as $key1 => $value1) {
                           if($value1['goods_id']==0){
                                if($value1['seller_state']<3){
                                     unset($data[$kk]);
                                }  
                           }else{
                              if($value1['goods_id']==$value['goods_id']&&$value1['seller_state']<3){
                                     unset($data[$kk]['extend_order_goods'][$key]);
                                }  
                           }
                        }
                    }  
                    if($daddress_ids>0){
                        if($value['deliverer_id']!=$daddress_ids[$i]){
                                 unset($data[$kk]['extend_order_goods'][$key]);
                            
                        }
                    }
                    // if($_GET['delivery_type_id']>0){
                    //     $daddress_id=$_GET['delivery_type_id'];
                    //      if(!in_array($value['deliverer_id'], $daddress_ids)){
                    //              unset($data[$kk]['extend_order_goods'][$key]);
                            
                    //     }
                    // }    
                 }
                $data[$kk]['extend_order_goods']=array_values($data[$kk]['extend_order_goods']);
            }
            $data = array_values($data);
            // var_dump($data);die;
            $sum = 0;
            $limit = array();
            foreach ($data as $key => $value) {
                $sum += $data[$key]['order_goods_count'];
                if ($sum > 1000) {
                    $limit[] = $key - 1;
                    $sum = $data[$key]['order_goods_count'];
                }
            }
            if($daddress_id>0){
                $model_daddress = Model('daddress');
                $address = $model_daddress->getAddressInfo(array('address_id'=>$daddress_id));
                $name=$address['seller_name'];
            }
            $time=date('Y-m-d',$start_unixtime_pay);
             $name= $time.$name.'配货订单表.xls';
            $name=iconv('utf-8','gb2312',$name);
            $filename = $time.$i.'.xls';
            $data_list[]= $dir.$filename;
            $names[]=  $name;
            $this->excel_order_subpl($data,$start_unixtime_pay,$daddress_id, $sum,$i,$dir);
            }
            //打包
            $zipname= $dir.'excel.zip';
            $files= $data_list;
        //     $zip_excel = Model('zip_excel');
        // $address = $zip_excel->zip($files,$zipname);
            // var_dump($files);die;
            $zip = new ZipArchive();
            $res = $zip->open($zipname, ZipArchive::CREATE );
            // var_dump($res);die;
            if ($res== TRUE) {
             // foreach ($files as $file) {
                  foreach ($files as $k => $v) {
             //这里直接用原文件的名字进行打包，也可以直接命名，需要注意如果文件名字一样会导致后面文件覆盖前面的文件，所以建议重新命名
                           $value = explode("/", $v);
                            $end = end($value);
                            $a=$zip->addFile($v, $end);
                              $zip->renameName($end, $names[$k]);
              // $a=$zip->addFile($file, $new_filename);
              // var_dump( $a);
             }
               //关闭文件
            $zip->close();
            }
          //这里是下载zip文件
           $dir= substr($dir, 0, -1);
           // var_dump($dir);die;
       set_time_limit(0);
                        header("Cache-Control: public");
                        header("Content-Description: File Transfer");
                        header('Content-disposition: attachment; filename='.date('Y-m-d',time()).'.zip'); //文件名
                        header("Content-Type: application/zip"); //zip格式的
                        header("Content-Transfer-Encoding: binary"); //告诉浏览器，这是二进制文件
                        header('Content-Length: ' . filesize($zipname)); //告诉浏览器，文件大小
            
                        @readfile($zipname);

                    //删除临时文件
                    $a=unlink($zipname);
      
        }else{
            showDialog('请选择配送方式');
        }
        
    }
     private function excel_order_subpl($data_tmp,$time=0,$address_id=0, $sum,$name,$dir)
    {   $type=1;
       // var_dump($data_tmp);die;
        if($address_id>0){
        $model_daddress = Model('daddress');
        $address = $model_daddress->getAddressInfo(array('address_id'=>$address_id));
        // $name=$address['seller_name']; 
        }
        
        $time=date('Y-m-d',$time);
        $excel = new PHPExcel();
        $letter = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q');
        $tableheader = array('订单号', '收货人', '发货人', '商品数量', '商品名称', '收货人电话', '详细地址', '买家','支付时间','完成时间','送货时间','买家留言','发货备注','订单状态','备注(退款信息)','促销信息','商品总成本');
        for ($i = 0; $i < count($tableheader); $i++) {
            $excel->getActiveSheet()->setCellValue("$letter[$i]1", "$tableheader[$i]");
            $excel->getActiveSheet()->getStyle("$letter[$i]1", "$tableheader[$i]")->getFont()->setBold(true);
        }
            $styleThinBlackBorderOutline = array(
        'borders' => array(
            'allborders' => array( //设置全部边框
                'style' => \PHPExcel_Style_Border::BORDER_THIN //粗的是thick
            ),

        ),
    );
         $x=$sum;
         // var_dump($sum);die;
        $z='Q'.$x;
        $w='A1';
        // var_dump($z);die;
        $excel->getActiveSheet()->getStyle($w.':'.$z)->applyFromArray($styleThinBlackBorderOutline);
        $model_order_log = Model('order_log');
        foreach ($data_tmp as $key => $order_info) {
            // var_dump($order_info);die;
            for ($ii = 0; $ii < count($order_info['extend_order_goods']); $ii++) {
                $reciver_info = unserialize($order_info['reciver_info']);
                $address = $reciver_info['area'];
                $street = $reciver_info['street'];
                $arr_str = explode(" ", preg_replace('#\s+#', ' ', trim($address)));
                if (!empty($arr_str)) {
                    $sheng = $arr_str[0] . '省';
                    $shi = $arr_str[1];
                    $qu = $arr_str[2];
                    $jie = $street;
                } else {
                    $sheng = ' ';
                    $shi = ' ';
                    $qu = ' ';
                    $jie = ' ';
                }
                 $model_class = Model('goods_class');
                  $goods_class1 = $model_class->getGoodsClassInfoById($order_info['extend_order_goods'][$ii]['gc_id']);//第一级商品分类
                  $goods_class2 = $model_class->getGoodsClassInfoById($goods_class1['gc_parent_id']);
                  $goods_class3 = $model_class->getGoodsClassInfoById($goods_class2['gc_parent_id']);
                   $goods_classname=$goods_class3['gc_name'];
                if ($ii == 0) {
                    if ($order_info['extend_order_goods'][$ii]['voucher_price'] > 0) {
                        $yhqzf = $order_info['extend_order_goods'][$ii]['voucher_price'];
                    } else {
                        $yhqzf = $order_info['voucher_price'];
                    }
                    if ($order_info['extend_order_goods'][$ii]['voucher_price'] > 0) {
                        $sjzf = number_format($order_info['extend_order_goods'][$ii]['goods_price'] * $order_info['extend_order_goods'][$ii]['goods_num'] - $order_info['extend_order_goods'][$ii]['voucher_price'], 2);
                    } else {
                        $sjzf = number_format($order_info['extend_order_goods'][$ii]['goods_price'] * $order_info['extend_order_goods'][$ii]['goods_num'], 2);
                    }
                } else {
                    if ($order_info['extend_order_goods'][$ii]['voucher_price'] > 0) {
                        $yhqzf = $order_info['extend_order_goods'][$ii]['voucher_price'];
                    } else {
                        $yhqzf = 0.00;
                    }
                    if ($order_info['extend_order_goods'][$ii]['voucher_price'] > 0) {
                        $sjzf = number_format($order_info['extend_order_goods'][$ii]['goods_price'] * $order_info['extend_order_goods'][$ii]['goods_num'] - $order_info['extend_order_goods'][$ii]['voucher_price'], 2);
                    } else {
                        $sjzf = number_format($order_info['extend_order_goods'][$ii]['goods_price'] * $order_info['extend_order_goods'][$ii]['goods_num'], 2);
                    }
                }

                if ($order_info['is_mode'] == 0) {
                    $is_mode = '一般贸易';
                } elseif ($order_info['is_mode'] == 1) {
                    $is_mode = '备货模式';
                } elseif ($order_info['is_mode'] == 2) {
                    $is_mode = '集货模式';
                }

                $model_refund_return = Model('refund_return');
                //部分退款与全部退款 
                $goodsid = $model_refund_return->getRefundReturnList(array('order_id' => $order_info['order_id']));
                if ($order_info['refund_state'] == '1') {
                   
                    foreach ($goodsid as $key => $vv) {
                        if ($order_info['extend_order_goods'][$ii]['goods_id'] == $vv['goods_id']) {
                             $state = '部分退款';
                            $refund_amount = $vv['refund_amount'];
                        }else{
                            $state = strip_tags(orderState($order_info));;
                            $refund_amount = '0.00';
                        }
                    }
                } else if ($order_info['refund_state'] == '2') {
                    $state = '已关闭';
                    $refund_amount = $order_info['refund_amount'];
                } else {
                    $state = strip_tags(orderState($order_info));
                    $refund_amount = '0.00';
                }
                if(!empty($goodsid)&&is_array($goodsid)){
                    foreach ($goodsid as $key => $vv) {
                 if($order_info['extend_order_goods'][$ii]['goods_id'] == $vv['goods_id']){
                     //备注
                $result = $model_refund_return->getRefundReturnList(array('order_id'=>$order_info['order_id']));
                //退款时间
                 if($result[0]['admin_time']>0){
                        $refund_time=date('Y-m-d H:i:s',$result[0]['admin_time']) ;
                    }else{
                         $refund_time='无';
                    }
                    if($result[0]['seller_state']=='1'){
                        $seller_state = '待审核';
                    }else if($result[0]['seller_state']=='2'){
                        $seller_state= '同意';
                    }else if($result[0]['seller_state']=='3'){
                        $seller_state = '不同意';
                    }else{
                        $seller_state = '';
                    }
                    if($result[0]['seller_state']=='2'){
                        if($result[0]['refund_state']=='1'){
                            $admin_state = '处理中';
                        }else if($result[0]['refund_state']=='2'){
                            $admin_state = '待管理员处理';
                        }else if($result[0]['refund_state']=='3'){
                            $admin_state ='已完成';
                        }else{
                            $admin_state ='无';
                        }
                    }else{
                        $admin_state ='无';
                    }
                    $seller_message = $result[0]['seller_message'];
                    $admin_message= $result[0]['admin_message'];
                     $buyer_message =$result[0]['reason_info'];
                if ($result) {
                    if ($result[0]['refund_type'] == 1) {
                        if ($result[0]['refund_state'] == 1 || $result[0]['refund_state'] == 2) {
                            $beizhu = '退款中'; //退款中
                        } else if ($result[0]['refund_state'] == 3) {
                            if ($result[0]['seller_state'] == 2) {
                                $beizhu = '退款完成'; //退款完成
                            } else if ($result[0]['seller_state'] == 3) {
                                $beizhu = '退款失败'; //退款失败
                            }
                        } else {
                            $beizhu = '';
                        }
                    } elseif ($result[0]['refund_type'] == 2) {
                        if ($result[0]['refund_state'] == 1 || $result[0]['refund_state'] == 2) {
                            $beizhu = '退款退货中';//退款退货中
                        } else if ($result[0]['refund_state'] == 3) {
                            $beizhu = '退款退货完成'; //退款退货完成
                        } else {
                            $beizhu = ' ';
                        }
                    }
                } else {
                    $beizhu = '';
                }
                    break;
                 }else if($vv['goods_id']==0){
                         //备注
                    $result = $model_refund_return->getRefundReturnList(array('order_id'=>$order_info['order_id']));
                    //退款时间
                     if($result[0]['admin_time']>0){
                            $refund_time=date('Y-m-d H:i:s',$result[0]['admin_time']) ;
                        }else{
                             $refund_time='无';
                        }
                        if($result[0]['seller_state']=='1'){
                            $seller_state = '待审核';
                        }else if($result[0]['seller_state']=='2'){
                            $seller_state= '同意';
                        }else if($result[0]['seller_state']=='3'){
                            $seller_state = '不同意';
                        }else{
                            $seller_state = '';
                        }
                        if($result[0]['seller_state']=='2'){
                            if($result[0]['refund_state']=='1'){
                                $admin_state = '处理中';
                            }else if($result[0]['refund_state']=='2'){
                                $admin_state = '待管理员处理';
                            }else if($result[0]['refund_state']=='3'){
                                $admin_state ='已完成';
                            }else{
                                $admin_state ='无';
                            }
                        }else{
                            $admin_state ='无';
                        }
                        $seller_message = $result[0]['seller_message'];
                        $admin_message= $result[0]['admin_message'];
                         $buyer_message =$result[0]['reason_info'];
                    if ($result) {
                        if ($result[0]['refund_type'] == 1) {
                            if ($result[0]['refund_state'] == 1 || $result[0]['refund_state'] == 2) {
                                $beizhu = '退款中'; //退款中
                            } else if ($result[0]['refund_state'] == 3) {
                                if ($result[0]['seller_state'] == 2) {
                                    $beizhu = '退款完成'; //退款完成
                                } else if ($result[0]['seller_state'] == 3) {
                                    $beizhu = '退款失败'; //退款失败
                                }
                            } else {
                                $beizhu = '';
                            }
                        } elseif ($result[0]['refund_type'] == 2) {
                            if ($result[0]['refund_state'] == 1 || $result[0]['refund_state'] == 2) {
                                $beizhu = '退款退货中';//退款退货中
                            } else if ($result[0]['refund_state'] == 3) {
                                $beizhu = '退款退货完成'; //退款退货完成
                            } else {
                                $beizhu = ' ';
                            }
                        }
                    } else {
                        $beizhu = '';
                    }

                 }else{
                     $refund_time='';
                    $seller_state = '';
                    $admin_state ='';
                    $seller_message = '';
                    $admin_message='';
                    $buyer_message ='';
                       $beizhu = ' ';
                 }
              }
              }else{
                         $refund_time='';
                        $seller_state = '';
                        $admin_state ='';
                        $seller_message = '';
                        $admin_message='';
                        $buyer_message ='';
                           $beizhu = ' ';
              }
              
               

                if ($order_info['order_type'] == 0) {
                    $order_type = '无活动';
                } elseif ($order_info['order_type'] == 1) {
                    $order_type = '阶梯价';
                } elseif ($order_info['order_type'] == 2) {
                    $order_type = '团购';
                } elseif ($order_info['order_type'] == 3) {
                    $order_type = '新人专享';
                } elseif ($order_info['order_type'] == 4) {
                    $order_type = '限时秒杀';
                } elseif ($order_info['order_type'] == 5) {
                    $order_type = '即买即送';
                }

                $voucher = unserialize($order_info['voucher_code']);
                if (!empty($voucher)) {
                    foreach ($voucher as $voucherk => $voucherv) {
                        if (!empty($voucherv['voucher_code'])) {
                            $voucher_code = $voucherv['voucher_code'];
                            $voucher_name = Model('voucher')->getVoucherInfo(array('voucher_code'=>$voucher_code),'voucher_title');
                            $vou = $voucher_name['voucher_title'];
                        } else {
                            $vou = ' ';
                        }
                    }
                } else {
                    $vou = ' ';
                }
                      $order_data[] = [
                    'order_sn'=>$ii == 0 ? $order_info['order_sn'] : ' ',
                    'reciver_name'=>$order_info['reciver_name'],
                    'storage'=>$order_info['extend_order_goods'][$ii]['order_goods_deliverer_id']?Model('daddress')->getAddressInfo(array('address_id' => $order_info['extend_order_goods'][$ii]['order_goods_deliverer_id']))['seller_name']:Model('daddress')->getAddressInfo(array('address_id' => $order_info['extend_order_goods'][$ii]['deliverer_id']))['seller_name'],
                    'goods_num'=>$order_info['extend_order_goods'][$ii]['goods_num'],
                    'goods_name'=>$order_info['extend_order_goods'][$ii]['goods_name'],
                    'reciver_phone'=>$reciver_info['phone'],
                    'recive_address'=>$jie,//省，市，区，街
                    'buyer'=>$ii == 0 ? $order_info['buyer_name'] : ' ',
                    'pay_time'=>$order_info['payment_time'] != 0 ? date('Y-m-d H:i:s', $order_info['payment_time']) : ' ',
                    'complete_time'=>$order_info['finnshed_time'] != 0 ? date('Y-m-d H:i:s', $order_info['finnshed_time']) : ' ',
                    'ziti_time'=>$order_info['ziti_ladder_time'] != 0 ? date('Y-m-d H:i:s', $order_info['ziti_ladder_time']) : ' ',
                    'order_message'=>$order_info['order_message'] != '' ? $order_info['order_message'] : ' ',
                    'deliver_explain'=>$order_info['deliver_explain'] != '' ? $order_info['deliver_explain'] : ' ',
                    'order_state'=>$state,
                    'beizhu'=>$beizhu,
                    'order_type'=>$order_info['add_time']<1618931572?goodsTypeName($order_info['order_type']):goodsTypeName($order_info['extend_order_goods'][$ii]['goods_type']),
                    //'buyer_message'=>$buyer_message,
                    'goods_costall'=>number_format($order_info['extend_order_goods'][$ii]['goods_cost_price'] * $order_info['extend_order_goods'][$ii]['goods_num'],2),
                    ];
            }
            unset($data_tmp[$key]);
        }
        // var_dump($order_data);die;
        // if ($type == 1) {
        //     $order_data = $order_data_1;
        // }elseif ($type == 2) {
        //     $order_data = $order_data_2;
        // }else{
        //     $order_data = $order_data_0;
        // }
        //填充表格信息
        
        for ($i = 2; $i <= count($order_data) + 1; $i++) {
            $j = 0;
            foreach ($order_data[$i - 2] as $key => $value) {
                $w='A'.$i;$z='Q'.$i;
                $bubiao=array('5','6');
                // var_dump($address_id);die;
                if(!in_array($address_id,$bubiao)){
                $excel->getActiveSheet()->getStyle($w.':'.$z)->getFont()->setColor( new PHPExcel_Style_Color( PHPExcel_Style_Color::COLOR_RED ) ); 
                }
                $excel->getActiveSheet()->setCellValue("$letter[$j]$i", "$value");
                $excel->getActiveSheet()->setCellValueExplicit("$letter[$j]$i","$value",PHPExcel_Cell_DataType::TYPE_STRING);
                $j++;
            }
        }
        //创建Excel输入对象
        $write = new PHPExcel_Writer_Excel5($excel);
        $filename = $time.$name.'.xls';
        // header("Pragma: public");
        // header("Expires: 0");
        // header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        // header("Content-Type:application/force-download");
        // header("Content-Type:application/vnd.ms-execl");
        // header("Content-Type:application/octet-stream");
        // header("Content-Type:application/download");;
        // header('Content-Disposition:attachment;filename=' . $filename);
        // header("Content-Transfer-Encoding:binary");
        // $write->save('php://output');
        // ob_end_clean();
        //  header( ' content-Type :text/html; Charset=utf-8; ');
         $filename = iconv('utf-8','gb2312',$filename);
        $write->save($dir.$filename);
        // die;
    }
    /**
     * 导出订单
     *
     */
    public function export_order2Op()
    {    
        $model_order = Model('order');
        $condition = array();
       
            $condition['order.order_state'] = 20;

        //发货人姓名
        if($_GET['daddress_id']>0){
            $condition['order_goods.deliverer_id'] = $_GET['daddress_id'];
            $daddress_id=$_GET['daddress_id'];
            $name='';
        }
         
                 //配送方式
        if($_GET['delivery_type_id']>0){
            $daddress_list = Model('peisong')->where(array('id' => $_GET['delivery_type_id']))->find();
            $daddress_ids = explode(',', $daddress_list['deliever_id']);
            // foreach ($daddress_list as $key => $value) {
            //     $daddress_ids[] = $value['deliever_id'];
            // }
            $daddress_ids=array_values($daddress_ids);
            $condition['order_goods.deliverer_id'] = array('in',$daddress_ids);
            $daddress_id=0;
             $name=$daddress_list['p_name'];
        }
        // var_dump($condition);die;
        $if_start_time_pay = $_GET['query_start_date_pay2'];
        $if_end_time_pay = $_GET['query_end_date_pay2'];
        $start_unixtime_pay = $if_start_time_pay ? strtotime($_GET['query_start_date_pay2']) : null;
        $end_unixtime_pay = $if_end_time_pay ? strtotime($_GET['query_end_date_pay2']) : null;
        if ($start_unixtime_pay || $end_unixtime_pay) {
            $condition['order.payment_time'] = array('between', array($start_unixtime_pay, $end_unixtime_pay));
        }
        
        $data = $model_order->getOrderGoodsExportList($condition,'20000'); 
        
        $ordergoods_arr=array();
        // foreach($data as $k=>$v){
        //      foreach ($v['extend_order_goods'] as $key => $value) { 
        //             if(strpos($value['goods_name'],"预售") !== false ){
        //                  unset($data[$k]['extend_order_goods'][$key]);
        //             }
        //               if($value['goods_type']==1&&$v['extend_order_common']['ziti_ladder_time']>0){
        //                  unset($data[$k]['extend_order_goods'][$key]);
        //             }

        //      }
           
        // }
        // var_dump($data);die;
        // var_dump(date("n.j",$end_unixtime_pay));die;
        // $str_time=date("n.j",$end_unixtime_pay);
        // $str='预售'.$str_time;
        // $str = Model('search')->decorateSearch_pre($str);
        // $where=array();
        // if($_GET['daddress_id']>0){
        //     $where['order_goods.deliverer_id'] = $_GET['daddress_id'];
        // }
        //  //配送方式
        // if($_GET['delivery_type_id']>0){
        //     $where['order_goods.deliverer_id'] = array('in',$daddress_ids);
        // }
        // $where['order.order_state'] = 20;
        // $where['order_goods.goods_name'] = array('like', $str); 
        // $data1 = $model_order->getOrderGoodsExportList($where,'20000'); 
        // foreach($data1 as $kk1=>$vv1){
        //      foreach ($vv1['extend_order_goods'] as $key => $value) { 
        //         if($value['goods_type']==1&&$v['extend_order_common']['ziti_ladder_time']>0){
        //                  unset($data[$k]['extend_order_goods'][$key]);
        //             }

        //      }
           
        // }
        // $where2=array();
        // if($_GET['daddress_id']>0){
        //     $where2['order_goods.deliverer_id'] = $_GET['daddress_id'];
        // }
        //  //配送方式
        // if($_GET['delivery_type_id']>0){
        //     $where2['order_goods.deliverer_id'] = array('in',$daddress_ids);
        // }
        // $where2['order_common.ziti_ladder_time'] = array('between', array($start_unixtime_pay, $end_unixtime_pay));
        // $where2['order.order_state'] = 20;
        // $where2['order_goods.goods_type'] = 1;
        // $data2 = $model_order->getOrderGoodsExportList($where2,'20000'); 
        // // var_dump($where2);die;
        // $dataz=array_merge($data,$data1);
        // $data=array_merge($dataz,$data2);
        foreach($data as $kk=>$vv){
            
            if($vv['is_zorder']==0 ){
                unset($data[$kk]);
                continue;
            }
             foreach ($vv['extend_order_goods'] as $key => $value) { 
                $model_refund_return = Model('refund_return');
                $refund_list = $model_refund_return->getRefundReturnList(array('order_id' => $vv['order_id']));
                if(!empty( $refund_list)&&is_array( $refund_list)){
                    foreach ($refund_list as $key1 => $value1) {
                       if($value1['goods_id']==0){
                            if($value1['seller_state']<3){
                                 unset($data[$kk]);
                            }  
                       }else{
                          if($value1['goods_id']==$value['goods_id']&&$value1['seller_state']<3){
                                 unset($data[$kk]['extend_order_goods'][$key]);
                            }  
                       }
                    }
                }  
                if($_GET['daddress_id']>0){
                    if($value['deliverer_id']!=$_GET['daddress_id']){
                             unset($data[$kk]['extend_order_goods'][$key]);
                        
                    }
                }
                if($_GET['delivery_type_id']>0){
                    $daddress_id=$_GET['delivery_type_id'];
                     if(!in_array($value['deliverer_id'], $daddress_ids)){
                             unset($data[$kk]['extend_order_goods'][$key]);
                        
                    }
                }    
             }
            $data[$kk]['extend_order_goods']=array_values($data[$kk]['extend_order_goods']);
        }
        $data = array_values($data);
        // var_dump($data);die;
        $sum = 0;
        $limit = array();
        foreach ($data as $key => $value) {
            $sum += $data[$key]['order_goods_count'];
            if ($sum > 1000) {
                $limit[] = $key - 1;
                $sum = $data[$key]['order_goods_count'];
            }
        }
        
            $this->excel_order_sub($data,$start_unixtime_pay,$daddress_id, $sum,$name);
    }

    private function excel_order_sub($data_tmp,$time=0,$address_id=0, $sum,$name)
    {   $type=1;
        if($address_id>0){
            $model_daddress = Model('daddress');
        $address = $model_daddress->getAddressInfo(array('address_id'=>$address_id));
        $name=$address['seller_name']; 
        }
        
        $time=date('Y-m-d',$time);
        $excel = new PHPExcel();
        $letter = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q');
        $tableheader = array('订单号', '收货人', '发货人', '商品数量', '商品名称', '收货人电话', '详细地址', '买家','支付时间','完成时间','送货时间','买家留言','发货备注','订单状态','备注(退款信息)','促销信息','商品总成本');
        for ($i = 0; $i < count($tableheader); $i++) {
            $excel->getActiveSheet()->setCellValue("$letter[$i]1", "$tableheader[$i]");
            $excel->getActiveSheet()->getStyle("$letter[$i]1", "$tableheader[$i]")->getFont()->setBold(true);
        }
            $styleThinBlackBorderOutline = array(
        'borders' => array(
            'allborders' => array( //设置全部边框
                'style' => \PHPExcel_Style_Border::BORDER_THIN //粗的是thick
            ),

        ),
    );
         $x=$sum;
         // var_dump($sum);die;
        $z='Q'.$x;
        $w='A1';
        // var_dump($z);die;
    $excel->getActiveSheet()->getStyle($w.':'.$z)->applyFromArray($styleThinBlackBorderOutline);
        $model_order_log = Model('order_log');
        foreach ($data_tmp as $key => $order_info) {
            for ($ii = 0; $ii < count($order_info['extend_order_goods']); $ii++) {
                $reciver_info = unserialize($order_info['reciver_info']);
                $address = $reciver_info['area'];
                $street = $reciver_info['street'];
                $arr_str = explode(" ", preg_replace('#\s+#', ' ', trim($address)));
                if (!empty($arr_str)) {
                    $sheng = $arr_str[0] . '省';
                    $shi = $arr_str[1];
                    $qu = $arr_str[2];
                    $jie = $street;
                } else {
                    $sheng = ' ';
                    $shi = ' ';
                    $qu = ' ';
                    $jie = ' ';
                }
                 $model_class = Model('goods_class');
                  $goods_class1 = $model_class->getGoodsClassInfoById($order_info['extend_order_goods'][$ii]['gc_id']);//第一级商品分类
                  $goods_class2 = $model_class->getGoodsClassInfoById($goods_class1['gc_parent_id']);
                  $goods_class3 = $model_class->getGoodsClassInfoById($goods_class2['gc_parent_id']);
                   $goods_classname=$goods_class3['gc_name'];
                if ($ii == 0) {
                    if ($order_info['extend_order_goods'][$ii]['voucher_price'] > 0) {
                        $yhqzf = $order_info['extend_order_goods'][$ii]['voucher_price'];
                    } else {
                        $yhqzf = $order_info['voucher_price'];
                    }
                    if ($order_info['extend_order_goods'][$ii]['voucher_price'] > 0) {
                        $sjzf = number_format($order_info['extend_order_goods'][$ii]['goods_price'] * $order_info['extend_order_goods'][$ii]['goods_num'] - $order_info['extend_order_goods'][$ii]['voucher_price'], 2);
                    } else {
                        $sjzf = number_format($order_info['extend_order_goods'][$ii]['goods_price'] * $order_info['extend_order_goods'][$ii]['goods_num'], 2);
                    }
                } else {
                    if ($order_info['extend_order_goods'][$ii]['voucher_price'] > 0) {
                        $yhqzf = $order_info['extend_order_goods'][$ii]['voucher_price'];
                    } else {
                        $yhqzf = 0.00;
                    }
                    if ($order_info['extend_order_goods'][$ii]['voucher_price'] > 0) {
                        $sjzf = number_format($order_info['extend_order_goods'][$ii]['goods_price'] * $order_info['extend_order_goods'][$ii]['goods_num'] - $order_info['extend_order_goods'][$ii]['voucher_price'], 2);
                    } else {
                        $sjzf = number_format($order_info['extend_order_goods'][$ii]['goods_price'] * $order_info['extend_order_goods'][$ii]['goods_num'], 2);
                    }
                }

                if ($order_info['is_mode'] == 0) {
                    $is_mode = '一般贸易';
                } elseif ($order_info['is_mode'] == 1) {
                    $is_mode = '备货模式';
                } elseif ($order_info['is_mode'] == 2) {
                    $is_mode = '集货模式';
                }

                $model_refund_return = Model('refund_return');
                //部分退款与全部退款 
                $goodsid = $model_refund_return->getRefundReturnList(array('order_id' => $order_info['order_id']));
                if ($order_info['refund_state'] == '1') {
                   
                    foreach ($goodsid as $key => $vv) {
                        if ($order_info['extend_order_goods'][$ii]['goods_id'] == $vv['goods_id']) {
                             $state = '部分退款';
                            $refund_amount = $vv['refund_amount'];
                        }else{
                            $state = strip_tags(orderState($order_info));;
                            $refund_amount = '0.00';
                        }
                    }
                } else if ($order_info['refund_state'] == '2') {
                    $state = '已关闭';
                    $refund_amount = $order_info['refund_amount'];
                } else {
                    $state = strip_tags(orderState($order_info));
                    $refund_amount = '0.00';
                }
                if(!empty($goodsid)&&is_array($goodsid)){
                    foreach ($goodsid as $key => $vv) {
                 if($order_info['extend_order_goods'][$ii]['goods_id'] == $vv['goods_id']){
                     //备注
                $result = $model_refund_return->getRefundReturnList(array('order_id'=>$order_info['order_id']));
                //退款时间
                 if($result[0]['admin_time']>0){
                        $refund_time=date('Y-m-d H:i:s',$result[0]['admin_time']) ;
                    }else{
                         $refund_time='无';
                    }
                    if($result[0]['seller_state']=='1'){
                        $seller_state = '待审核';
                    }else if($result[0]['seller_state']=='2'){
                        $seller_state= '同意';
                    }else if($result[0]['seller_state']=='3'){
                        $seller_state = '不同意';
                    }else{
                        $seller_state = '';
                    }
                    if($result[0]['seller_state']=='2'){
                        if($result[0]['refund_state']=='1'){
                            $admin_state = '处理中';
                        }else if($result[0]['refund_state']=='2'){
                            $admin_state = '待管理员处理';
                        }else if($result[0]['refund_state']=='3'){
                            $admin_state ='已完成';
                        }else{
                            $admin_state ='无';
                        }
                    }else{
                        $admin_state ='无';
                    }
                    $seller_message = $result[0]['seller_message'];
                    $admin_message= $result[0]['admin_message'];
                     $buyer_message =$result[0]['reason_info'];
                if ($result) {
                    if ($result[0]['refund_type'] == 1) {
                        if ($result[0]['refund_state'] == 1 || $result[0]['refund_state'] == 2) {
                            $beizhu = '退款中'; //退款中
                        } else if ($result[0]['refund_state'] == 3) {
                            if ($result[0]['seller_state'] == 2) {
                                $beizhu = '退款完成'; //退款完成
                            } else if ($result[0]['seller_state'] == 3) {
                                $beizhu = '退款失败'; //退款失败
                            }
                        } else {
                            $beizhu = '';
                        }
                    } elseif ($result[0]['refund_type'] == 2) {
                        if ($result[0]['refund_state'] == 1 || $result[0]['refund_state'] == 2) {
                            $beizhu = '退款退货中';//退款退货中
                        } else if ($result[0]['refund_state'] == 3) {
                            $beizhu = '退款退货完成'; //退款退货完成
                        } else {
                            $beizhu = ' ';
                        }
                    }
                } else {
                    $beizhu = '';
                }
                    break;
                 }else if($vv['goods_id']==0){
                         //备注
                    $result = $model_refund_return->getRefundReturnList(array('order_id'=>$order_info['order_id']));
                    //退款时间
                     if($result[0]['admin_time']>0){
                            $refund_time=date('Y-m-d H:i:s',$result[0]['admin_time']) ;
                        }else{
                             $refund_time='无';
                        }
                        if($result[0]['seller_state']=='1'){
                            $seller_state = '待审核';
                        }else if($result[0]['seller_state']=='2'){
                            $seller_state= '同意';
                        }else if($result[0]['seller_state']=='3'){
                            $seller_state = '不同意';
                        }else{
                            $seller_state = '';
                        }
                        if($result[0]['seller_state']=='2'){
                            if($result[0]['refund_state']=='1'){
                                $admin_state = '处理中';
                            }else if($result[0]['refund_state']=='2'){
                                $admin_state = '待管理员处理';
                            }else if($result[0]['refund_state']=='3'){
                                $admin_state ='已完成';
                            }else{
                                $admin_state ='无';
                            }
                        }else{
                            $admin_state ='无';
                        }
                        $seller_message = $result[0]['seller_message'];
                        $admin_message= $result[0]['admin_message'];
                         $buyer_message =$result[0]['reason_info'];
                    if ($result) {
                        if ($result[0]['refund_type'] == 1) {
                            if ($result[0]['refund_state'] == 1 || $result[0]['refund_state'] == 2) {
                                $beizhu = '退款中'; //退款中
                            } else if ($result[0]['refund_state'] == 3) {
                                if ($result[0]['seller_state'] == 2) {
                                    $beizhu = '退款完成'; //退款完成
                                } else if ($result[0]['seller_state'] == 3) {
                                    $beizhu = '退款失败'; //退款失败
                                }
                            } else {
                                $beizhu = '';
                            }
                        } elseif ($result[0]['refund_type'] == 2) {
                            if ($result[0]['refund_state'] == 1 || $result[0]['refund_state'] == 2) {
                                $beizhu = '退款退货中';//退款退货中
                            } else if ($result[0]['refund_state'] == 3) {
                                $beizhu = '退款退货完成'; //退款退货完成
                            } else {
                                $beizhu = ' ';
                            }
                        }
                    } else {
                        $beizhu = '';
                    }

                 }else{
                     $refund_time='';
                    $seller_state = '';
                    $admin_state ='';
                    $seller_message = '';
                    $admin_message='';
                    $buyer_message ='';
                       $beizhu = ' ';
                 }
              }
          }else{
                     $refund_time='';
                    $seller_state = '';
                    $admin_state ='';
                    $seller_message = '';
                    $admin_message='';
                    $buyer_message ='';
                       $beizhu = ' ';
          }
              
               

                if ($order_info['order_type'] == 0) {
                    $order_type = '无活动';
                } elseif ($order_info['order_type'] == 1) {
                    $order_type = '阶梯价';
                } elseif ($order_info['order_type'] == 2) {
                    $order_type = '团购';
                } elseif ($order_info['order_type'] == 3) {
                    $order_type = '新人专享';
                } elseif ($order_info['order_type'] == 4) {
                    $order_type = '限时秒杀';
                } elseif ($order_info['order_type'] == 5) {
                    $order_type = '即买即送';
                }

                $voucher = unserialize($order_info['voucher_code']);
                if (!empty($voucher)) {
                    foreach ($voucher as $voucherk => $voucherv) {
                        if (!empty($voucherv['voucher_code'])) {
                            $voucher_code = $voucherv['voucher_code'];
                            $voucher_name = Model('voucher')->getVoucherInfo(array('voucher_code'=>$voucher_code),'voucher_title');
                            $vou = $voucher_name['voucher_title'];
                        } else {
                            $vou = ' ';
                        }
                    }
                } else {
                    $vou = ' ';
                }
                      $order_data[] = [
                    'order_sn'=>$ii == 0 ? $order_info['order_sn'] : ' ',
                    'reciver_name'=>$order_info['reciver_name'],
                    'storage'=>$order_info['extend_order_goods'][$ii]['order_goods_deliverer_id']?Model('daddress')->getAddressInfo(array('address_id' => $order_info['extend_order_goods'][$ii]['order_goods_deliverer_id']))['seller_name']:Model('daddress')->getAddressInfo(array('address_id' => $order_info['extend_order_goods'][$ii]['deliverer_id']))['seller_name'],
                    'goods_num'=>$order_info['extend_order_goods'][$ii]['goods_num'],
                    'goods_name'=>$order_info['extend_order_goods'][$ii]['goods_name'],
                    'reciver_phone'=>$reciver_info['phone'],
                    'recive_address'=>$jie,//省，市，区，街
                    'buyer'=>$ii == 0 ? $order_info['buyer_name'] : ' ',
                    'pay_time'=>$order_info['payment_time'] != 0 ? date('Y-m-d H:i:s', $order_info['payment_time']) : ' ',
                    'complete_time'=>$order_info['finnshed_time'] != 0 ? date('Y-m-d H:i:s', $order_info['finnshed_time']) : ' ',
                    'ziti_time'=>$order_info['ziti_ladder_time'] != 0 ? date('Y-m-d H:i:s', $order_info['ziti_ladder_time']) : ' ',
                    'order_message'=>$order_info['order_message'] != '' ? $order_info['order_message'] : ' ',
                    'deliver_explain'=>$order_info['deliver_explain'] != '' ? $order_info['deliver_explain'] : ' ',
                    'order_state'=>$state,
                    'beizhu'=>$beizhu,
                    'order_type'=>$order_info['add_time']<1618931572?goodsTypeName($order_info['order_type']):goodsTypeName($order_info['extend_order_goods'][$ii]['goods_type']),
                    //'buyer_message'=>$buyer_message,
                    'goods_costall'=>number_format($order_info['extend_order_goods'][$ii]['goods_cost_price'] * $order_info['extend_order_goods'][$ii]['goods_num'],2),
                    ];
            }
            unset($data_tmp[$key]);
        }
        // if ($type == 1) {
        //     $order_data = $order_data_1;
        // }elseif ($type == 2) {
        //     $order_data = $order_data_2;
        // }else{
        //     $order_data = $order_data_0;
        // }
        //填充表格信息
        for ($i = 2; $i <= count($order_data) + 1; $i++) {
            $j = 0;
            foreach ($order_data[$i - 2] as $key => $value) {
                $w='A'.$i;$z='Q'.$i;
                $bubiao=array('5','6');
                // var_dump($address_id);die;
                if(!in_array($address_id,$bubiao)){
                $excel->getActiveSheet()->getStyle($w.':'.$z)->getFont()->setColor( new PHPExcel_Style_Color( PHPExcel_Style_Color::COLOR_RED ) ); 
                }
                $excel->getActiveSheet()->setCellValue("$letter[$j]$i", "$value");
                $excel->getActiveSheet()->setCellValueExplicit("$letter[$j]$i","$value",PHPExcel_Cell_DataType::TYPE_STRING);
                $j++;
            }
        }
        //创建Excel输入对象
        $write = new PHPExcel_Writer_Excel5($excel);
        $filename = $time.$name.'配货清单表.xls';
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");;
        header('Content-Disposition:attachment;filename=' . $filename);
        header("Content-Transfer-Encoding:binary");
        $write->save('php://output');
        // $write->save(BASE_ROOT_PATH.'/excel');
        die;
    }

    /**
     * 订单列表
     *
     */
    public function indexOp()
    {
        //配送方式
        $delivery_type_list = Model('peisong')->where()->field('id,p_name')->select();
        Tpl::output('delivery_type_list', $delivery_type_list);
         //关联发货人
        $daddress_list = Model('daddress')->getAddressList(array('store_id' => $_SESSION['store_id']),'address_id,seller_name');
        Tpl::output('daddress_list', $daddress_list);
       
         //显示自提地址列表(搜索)
         $condition2 = array();
         $model_daddress = Model('ziti_address');
         $address_list = $model_daddress->getAddressList($condition2);
         // var_dump($address_list);die;
         Tpl::output('address_list',$address_list); 
         Tpl::showpage('store_export_ps.index');
    }




    /**
     * 用户中心右边，小导航
     */
    private function profile_menu($menu_type = '', $menu_key = '')
    {
        Language::read('member_layout');
        switch ($menu_type) {
            case 'list':
                $menu_array = array(
                    array('menu_key' => 'store_order', 'menu_name' => Language::get('nc_member_path_all_order'), 'menu_url' => 'index.php?act=store_order'),
                    array('menu_key' => 'state_new', 'menu_name' => Language::get('nc_member_path_wait_pay'), 'menu_url' => 'index.php?act=store_order&op=index&state_type=state_new'),
                    array('menu_key' => 'state_pay', 'menu_name' => Language::get('nc_member_path_wait_send'), 'menu_url' => 'index.php?act=store_order&op=store_order&state_type=state_pay'),
                    array('menu_key' => 'state_send', 'menu_name' => Language::get('nc_member_path_sent'), 'menu_url' => 'index.php?act=store_order&op=index&state_type=state_send'),
                    array('menu_key' => 'state_success', 'menu_name' => Language::get('nc_member_path_finished'), 'menu_url' => 'index.php?act=store_order&op=index&state_type=state_success'),
                    array('menu_key' => 'state_cancel', 'menu_name' => Language::get('nc_member_path_canceled'), 'menu_url' => 'index.php?act=store_order&op=index&state_type=state_cancel'),
                );
                break;
        }
        Tpl::output('member_menu', $menu_array);
        Tpl::output('menu_key', $menu_key);
    }
}
