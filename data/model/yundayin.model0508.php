<?php
/**
 * 飞鹅云打印MODEL
 **/
defined('In718Shop') or exit('Access Invalid!');
    include 'HttpClient.class.php';
    define('USER', 'zhiyk@zih718.com');  //*必填*：飞鹅云后台注册账号
    define('UKEY', 'm9sg3ybD7UF6CwAm');  //*必填*: 飞鹅云后台注册账号后生成的UKEY 【备注：这不是填打印机的KEY】
    

    // //以下参数不需要修改
    define('IP','api.feieyun.cn');      //接口IP或域名
    define('PORT',80);            //接口IP端口
    define('PATH','/Api/Open/');    //接口路径

class yundayinModel extends Model {
    public function __construct() {
        parent::__construct('yundayin');
    }
    /**
     * 构建云打印
    */
    public function _yundayin($order_sn,$is_refund = 0) {
        $model_order = Model('order');
        $condition = array();
        $condition['order_sn'] = $order_sn;
        
        //$condition['order_sn'] = $out_trade_no;
        $order_info = $model_order->getFpOrderInfo($condition,array('order_common','order_goods','member'));
        //print_r($order_info);
        if (empty($order_info)) {
            $order_info = false;
        }else{
            if($is_refund != 0){
                $order_id = $order_info['order_id'].','.$is_refund;
                $insert_data['is_refund'] = $is_refund;
            }else{
                $order_id = $order_info['order_id'];
            }
            $insert_data['order_id'] = $order_id;
            $is_dayin = Model()->table('isdayin')->insert($insert_data);
            if($is_dayin){
                //echo '<br/>'.$order_info['order_type'].'KUN';die;
                //查询匹配的打印机信息，若自提地址和活动类型一样，取ID靠前的一个打印机信息
                $address_id = $order_info['extend_order_common']['reciver_ziti_id'] ;
                
                //r如果是小店或是小店预售商品
                if($order_info['storage_id'] == 5 || $order_info['storage_id'] == 6 || $order_info['storage_id'] == 7 ){
                    $yundayin = Model()->table('yundayin')->where(array('address_id'=>$address_id,'storage_id'=>$order_info['storage_id']))->find();
                }else{
                    $yundayin = Model()->table('yundayin')->where(array('storage_id'=>$order_info['storage_id']))->find();
                }
                //设置打印机接口参数
                $SN = $yundayin['dayin_sn'];      //*必填*：打印机编号，必须要在管理后台里添加打印机或调用API接口添加之后，才能调用API
                //echo $SN;die;
                //调用小票机打印订单接口
                $result = $this->_printorder($SN,$order_info,$is_refund);
                $logdata['order_id'] =  $order_info['order_id'];
                $logdata['dayin_id'] = $yundayin['dayin_id'];
                $logdata['address_id'] = $address_id;
                $logdata['is_refund'] = $is_refund;
            
                //判断接口是否成功，不成功重复请求
                if($result['code'] == 1){
                    $result['arr'] = json_decode($result['json'] ,true);
                    //正确例子
                    if($result['arr']['ret'] == 0){
                        //更新订单发送状态
                        $condition1['order_id'] = $order_info['order_id'];
                        $updata['dayin_state'] = 1;
                        $a = $model_order->table('order')->where($condition1)->update($updata);
                        // var_dump($a);die;
                       
                        //添加订单日志
                        $this->_insert_dayinlog($result['arr'], $result['json'], $logdata);
                    }else{
                        //添加订单日志
                        $this->_insert_dayinlog($result['arr'], $result['json'], $logdata);
                        $result1 = $this->_printorder($SN,$order_info,$is_refund);
                        if($result1['code'] == 1){
                            $result1['arr'] = json_decode($result1['json'] ,true);
                            //正确例子
                            if($result1['arr']['ret'] == 0){
                                //更新订单发送状态
                                $condition2['order_id'] = $order_info['order_id'];
                                $updata1['dayin_state'] = 1;
                                $model_order->table('order')->where($condition2)->update($updata1);

                                //添加订单日志
                                $this->_insert_dayinlog($result1['arr'], $result1['json'], $logdata);
                            }else{
                                //两次请求都失败，插入到异常打印单表
                                $this->_insert_errordayin($result1['arr'], $result1['json'], $logdata);
                                $this->_insert_dayinlog($result1['arr'], $result1['json'], $logdata);
                            }
                        }else{
                            $result1['arr']['ret'] = 'fail';
                            $result1['arr']['msg'] = 'fail';
                            //两次请求都失败，插入到异常打印单表
                            $this->_insert_errordayin($result1['arr'], $result1['json'], $logdata);
                            $this->_insert_dayinlog($result1['arr'], $result1['json'], $logdata);
                        }
                    }
                }else{
                    $result['arr']['ret'] = 'fail';
                    $result['arr']['msg'] = 'fail';
                    $this->_insert_dayinlog($result['arr'], $result['json'], $logdata);
                    $result2 = $this->_printorder($SN,$order_info,$is_refund);

                    if($result2['code'] == 1){
                        $result2['arr'] = json_decode($result2['json'] ,true);
                        //正确例子
                        if($result2['arr']['ret'] == 0){
                            //更新订单发送状态
                            $condition3['order_id'] = $order_info['order_id'];
                            $updata2['dayin_state'] = 1;

                            $model_order->table('order')->where($condition3)->update($updata2);

                            //添加订单日志
                            $this->_insert_dayinlog($result2['arr'], $result2['json'], $logdata);
                        }else{
                            //两次请求都失败，插入到异常打印单表
                            $this->_insert_errordayin($result2['arr'], $result2['json'], $logdata);
                            $this->_insert_dayinlog($result2['arr'], $result2['json'], $logdata);
                        }
                    }else{
                        $result2['arr']['ret'] = 'fail';
                        $result2['arr']['msg'] = 'fail';
                        //两次请求都失败，插入到异常打印单表
                        $this->_insert_errordayin($result2['arr'], $result2['json'], $logdata);
                        $this->_insert_dayinlog($result2['arr'], $result2['json'], $logdata);
                    }
                }
            }
            
        }
        //return $result;
    }
    /*********************************小票机打印订单接口*********************
    //***接口返回值说明***
    //正确例子：{"msg":"ok","ret":0,"data":"123456789_20160823165104_1853029628","serverExecutedTime":6}
    //错误例子：{"msg":"错误信息.","ret":非零错误码,"data":null,"serverExecutedTime":5}
    */
    /**
    private function _printorder($SN, $order_info ,$times = 1 ) {
        //标签说明：
        //单标签:
        //"<BR>"为换行,"<CUT>"为切刀指令(主动切纸,仅限切刀打印机使用才有效果)
        //"<LOGO>"为打印LOGO指令(前提是预先在机器内置LOGO图片),"<PLUGIN>"为钱箱或者外置音响指令
        //成对标签：
        //"<CB></CB>"为居中放大一倍,"<B></B>"为放大一倍,"<C></C>"为居中,<L></L>字体变高一倍
        //<W></W>字体变宽一倍,"<QR></QR>"为二维码,"<BOLD></BOLD>"为字体加粗,"<RIGHT></RIGHT>"为右对齐

        //拼凑订单内容时可参考如下格式
        //根据打印纸张的宽度，自行调整内容的格式，可参考下面的样例格式
        $content = '<CB>物资小店测试打印页</CB><BR>';
        $content .= '名称　　　　　 单价  数量 金额<BR>';
        $content .= '--------------------------------<BR>';
        $content .= '饭　　　　　 　10.0   10  100.0<BR>';
        $content .= '炒饭　　　　　 10.0   10  100.0<BR>';
        $content .= '蛋炒饭　　　　 10.0   10  100.0<BR>';
        $content .= '鸡蛋炒饭　　　 10.0   10  100.0<BR>';
        $content .= '西红柿炒饭　　 10.0   10  100.0<BR>';
        $content .= '西红柿蛋炒饭　 10.0   10  100.0<BR>';
        $content .= '西红柿鸡蛋炒饭 10.0   10  100.0<BR>';
        $content .= '--------------------------------<BR>';
        $content .= '备注：加辣<BR>';
        $content .= '合计：xx.0元<BR>';
        $content .= '送货地点：广州市南沙区xx路xx号<BR>';
        $content .= '联系电话：13888888888888<BR>';
        $content .= '订餐时间：2014-08-08 08:08:08<BR>';
        $content .= '<QR>http://www.feieyun.com</QR>';//把二维码字符串用标签套上即可自动生成二维码

        //$SN = $sn;
        $content = $content;
        $times = 1;

        //打开注释可测试
        $result = $this->printMsg($SN,$content,1);
        return $result;
    }
    */
    
    /**
    public function printOp(){
        $model_order = Model('order');
        $condition = array();
        $condition['order_sn'] = '6000000005178701';

        //$condition['order_sn'] = $out_trade_no;
        $order_info = $model_order->getFpOrderInfo($condition,array('order_common','order_goods','member'));
        $this->_printorder(1,$order_info);
    }
    */
    /*手动打印单*/
    public function sd_printorder($SN, $order_info ,$is_refund = 0,$times = 1 ) {
        return $this->_printorder($SN,$order_info,$is_refund);
    }
    private function _printorder($SN, $order_info ,$is_refund = 0,$times = 1 ) {
        //标签说明：
        //单标签:
        //"<BR>"为换行,"<CUT>"为切刀指令(主动切纸,仅限切刀打印机使用才有效果)
        //"<LOGO>"为打印LOGO指令(前提是预先在机器内置LOGO图片),"<PLUGIN>"为钱箱或者外置音响指令
        //成对标签：
        //"<CB></CB>"为居中放大一倍,"<B></B>"为放大一倍,"<C></C>"为居中,<L></L>字体变高一倍
        //<W></W>字体变宽一倍,"<QR></QR>"为二维码,"<BOLD></BOLD>"为字体加粗,"<RIGHT></RIGHT>"为右对齐

        //拼凑订单内容时可参考如下格式
        //根据打印纸张的宽度，自行调整内容的格式，可参考下面的样例格式
        if($is_refund == 0){
          $content = '';//'<CB>物资小店测试打印页</CB><BR>';
        }else{
          //$content = '<AUDIO-REFUND>';
          $content = '<B>退单</B><BR>';//'<CB>物资小店测试打印页</CB><BR>';
        }
        
        $content .= '订单号：'.$order_info['order_sn'].'<BR>';
        $content .= '商品名称/货号<BR>';//.'　　　　　 单价'.'  数量'.' 金额<BR>';
        $content .= '    单价'.'     数量'.'     金额<BR>';
        $content .= '--------------------------------<BR>';
        foreach ($order_info['extend_order_goods'] as $key=>$value){
            // $content .= $value['goods_name'].'/'.Model('goods')->getfby_goods_id($value['goods_id'],'goods_serial').'<BR>'.'    '.$value['goods_price'].'       '.$value['goods_num'].'       '.$value['goods_pay_price'].'<BR>';
            $content .= $value['goods_name'].'/'.Model()->table('goods')->getfby_goods_id($value['goods_id'],'goods_serial').'<BR>'.'    '.$value['goods_price'].'       '.$value['goods_num'].'       '.$value['goods_pay_price'].'<BR>';
        }
        $content .= '--------------------------------<BR>';
        $content .= '合计：'.$order_info['order_amount'].'元<BR>';
        
        $content .= '送货地点：'.$order_info['extend_order_common']['reciver_info']['address'].'<BR>';
		
		$order_type = Model()->table('order')->getfby_order_id($order_info['order_id'],'order_type');
        if($order_type == 5){
            //$mall_info = Model('address')->getAddressList(array('member_id'=>$order_info['buyer_id']));
            //$content .= '详细地址：'.$mall_info[0]['mall_info'].'<BR>';
			$mall_info = Model('order')->getOrderCommonInfo(array('order_id'=>$order_info['order_id']));
            $content .= '详细地址：'.$mall_info['mall_info'].'<BR>';
        }
		
        $content .= '下单时间：'.date('Y-m-d H:i:s',$order_info['add_time']).'<BR>';
        if($order_info['extend_order_common']['ziti_ladder_time'] != 0 ){
            $content .= '自提时间：'.date('Y-m-d H:i:s',$order_info['extend_order_common']['ziti_ladder_time']).'<BR>';
        }
        $content .= '联系电话：'.$order_info['extend_order_common']['reciver_info']['mob_phone'].'<BR>';
        $content .= '买家留言：'.$order_info['extend_order_common']['order_message'].'<BR>';
        $content .= '小票打印时间：'.date('Y-m-d H:i:s',time()).'<BR>';
        // $content .= '商家备注：'.$order_info['extend_order_common']['deliver_explain'].'<BR><BR>';
        //$content .= '<QR>https://france.banliego.cn/wzxd/applet?id=1</QR>';
        //把二维码字符串用标签套上即可自动生成二维码
        $content .= '<QR>act=saoma&op=ruku&order_sn='.$order_info['order_sn'].'&order_id='.$order_info['order_id'].'</QR>';

        //$SN = $sn;
        $content = $content;
        $times = 2;

        //打开注释可测试
        $result = $this->printMsg($SN,$content,2);
        return $result;
    }
    
    
    /**
   * [打印订单接口 Open_printMsg]
   * @param  [string] $sn      [打印机编号sn]
   * @param  [string] $content [打印内容]
   * @param  [string] $times   [打印联数]
   * @return [string]          [接口返回值]
   */
  function printMsg($sn,$content,$times){
    $time = time();         //请求时间
    $msgInfo = array(
      'user'=>USER,
      'stime'=>$time,
      'sig'=>$this->signature($time),
      'apiname'=>'Open_printMsg',
      'sn'=>$sn,
      'content'=>$content,
      'times'=>$times//打印次数
    );
     //print_r($msgInfo);die;
    $client = new HttpClient(IP,PORT);
    if(!$client->post(PATH,$msgInfo)){
        $result['code'] = 0;
        $result['json'] = '[]'; 
    }else{
      //服务器返回的JSON字符串，建议要当做日志记录起来
        
       $result['json'] = $client->getContent();
       $result['code'] = 1; 
       
    }
    return $result;
  }
  /**
   * [signature 生成签名]
   * @param  [string] $time [当前UNIX时间戳，10位，精确到秒]
   * @return [string]       [接口返回值]
   */
  function signature($time){
    return sha1(USER.UKEY.$time);//公共参数，请求公钥
  }
  /**
     * 添加打印订单日志
     */
    public function sd_insert_dayinlog($arr, $json, $logdata){
        $this->_insert_dayinlog($arr, $json, $logdata);
    }
    /**
     * 添加打印订单日志
     */
    private function _insert_dayinlog($arr, $json, $logdata) {
       $data['code'] = $arr['ret'];
       $data['order_id'] = $logdata['order_id'];
       $data['dayin_id'] = $logdata['dayin_id'];
       $data['address_id'] = $logdata['address_id'];
       $data['message'] = $arr['msg'];
       $data['json'] = $json;
       $data['send_time'] = time();

       Model()->table('dayin_log')->insert($data);
        
    }
    /**
     * 插入异常订单打印表
     */
    private function _insert_errordayin($arr, $json, $logdata) {
       $data['code'] = $arr['ret'];
       $data['order_id'] = $logdata['order_id'];
       $data['dayin_id'] = $logdata['dayin_id'];
       $data['address_id'] = $logdata['address_id'];
       $data['message'] = $arr['msg'];
       $data['json'] = $json;
       $data['send_time'] = time();

       Model()->table('error_dayin')->insert($data);
       Model()->table('isdayin')->where(array('order_id'=>intval($logdata['order_id'])))->delete();
        
    }
     
}