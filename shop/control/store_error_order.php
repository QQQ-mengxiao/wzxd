<?php
/**
 * 卖家异常打印订单管理
 *
 **/
defined('In718Shop') or exit('Access Invalid!');
    include 'HttpClient.class.php';
    define('USER', 'zhiyk@zih718.com');  //*必填*：飞鹅云后台注册账号
    define('UKEY', 'm9sg3ybD7UF6CwAm');  //*必填*: 飞鹅云后台注册账号后生成的UKEY 【备注：这不是填打印机的KEY】
    

    // //以下参数不需要修改
    define('IP','api.feieyun.cn');      //接口IP或域名
    define('PORT',80);            //接口IP端口
    define('PATH','/Api/Open/');    //接口路径

class store_error_orderControl extends BaseSellerControl {
    const LINK_dayin_LIST = 'index.php?act=store_error_order&op=index';
    /**
	 * 异常订单列表
	 *
	 */

    public function indexOp(){
        $model_order = Model('order');
        $condition  = array();
        if($_GET['order_sn']) {
            $condition['order_sn'] = $_GET['order_sn'];
        }      
        
         $condition['store_id'] = $_SESSION['store_id'];
         $condition['dayin_state'] = 2;
         $condition['order_state'] =  array('neq',0);
         $condition['payment_time'] =  array('neq',0);
         //2021年1月28号以后的订单
         $condition['add_time'] =  array('gt',1611763200);
        //$order_list = $model_order->getOrderList($condition,30);   
          
        $order_list=$model_order->where($condition)->page('10')->select();     
        
        //echo '<pre>';var_dump($order_list);echo "</pre>";die;
        Tpl::output('order_list',$order_list);
        Tpl::output('show_page',$model_order->showpage());
        Tpl::showpage('errororder.index');
    }
     /**
     * 构建云打印
     */
    public function yundayinOp() {
        $model_order = Model('order');
        $condition = array();
        $condition['order_id'] = intval($_GET['order_id']);
        
        //$condition['order_sn'] = $out_trade_no;
        $order_info = $model_order->getFpOrderInfo($condition,array('order_common','order_goods','member'));

        if (empty($order_info)) {
            $order_info = false;
        }else{
           //查询匹配的打印机信息，若自提地址和活动类型一样，取ID靠前的一个打印机信息
            $address_id = $order_info['extend_order_common']['reciver_ziti_id'] ;
            $order_type = $order_info['order_type'] ;

            $sql="SELECT * FROM `718shop_yundayin` where address_id = $address_id AND FIND_IN_SET($order_type,order_type) limit 1";  
            $yundayin =Model()->query($sql);
            //print_r($yundayin);
                    
            //设置打印机接口参数
             $SN = $yundayin[0]['dayin_sn'];      //*必填*：打印机编号，必须要在管理后台里添加打印机或调用API接口添加之后，才能调用API
            //echo $SN;die;
            //调用小票机打印订单接口
            $result = $this->_printorder($SN,$order_info);
            $logdata['order_id'] =  $order_info['order_id'];
            $logdata['dayin_id'] = $yundayin[0]['dayin_id'];
            $logdata['address_id'] = $address_id;
            
            //判断接口是否成功
            if($result['code'] == 1){
                $result['arr'] = json_decode($result['json'] ,true);
                //正确例子
                if($result['arr']['ret'] == 0){
                    //更新订单发送状态
                    $condition1['order_id'] = $order_info['order_id'];
                    $updata['dayin_state'] = 1;
                    $model_order->where($condition1)->update($updata);
                    //删除异常订单表中的数据
                    $error_model = Model ( 'error_dayin' );
                    $error_model->where($condition1)->delete();
                   
                     //添加订单日志
                    $this->_insert_dayinlog($result['arr'], $result['json'], $logdata);
                    showMessage('订单打印成功!',self::LINK_dayin_LIST,'succ','',3);
                }else{
                    //添加订单日志
                    $this->_insert_dayinlog($result['arr'], $result['json'], $logdata);
                    showMessage('订单打印失败!',self::LINK_dayin_LIST);

                }
            }else{
                $result['arr']['ret'] = 'fail';
                $result['arr']['msg'] = 'fail';
                $this->_insert_dayinlog($result['arr'], $result['json'], $logdata);
                showMessage('订单打印失败!',self::LINK_dayin_LIST);
            }
        }
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

        $content = '';//'<CB>物资小店测试打印页</CB><BR>';
        $content .= '商品名称<BR>';//.'　　　　　 单价'.'  数量'.' 金额<BR>';
        $content .= '    单价'.'     数量'.'     金额<BR>';
        $content .= '--------------------------------<BR>';
        foreach ($order_info['extend_order_goods'] as $key=>$value){
            $content .= $value['goods_name'].'<BR>'.'    '.$value['goods_price'].'       '.$value['goods_num'].'       '.$value['goods_pay_price'].'<BR>';
        }
        $content .= '--------------------------------<BR>';
        $content .= '合计：'.$order_info['order_amount'].'元<BR>';
        $content .= '送货地点：'.$order_info['extend_order_common']['reciver_info']['address'].'<BR>';
        $content .= '下单时间：'.date('Y-m-d H:i:s',$order_info['add_time']).'<BR>';
        $content .= '联系电话：'.$order_info['extend_order_common']['reciver_info']['mob_phone'].'<BR>';
        $content .= '买家留言：'.$order_info['extend_order_common']['order_message'].'<BR>';
        $content .= '商家备注：'.$order_info['extend_order_common']['deliver_explain'].'<BR><BR>';
        $content .= '<QR>https://france.banliego.cn/wzxd/applet?id=1</QR>';//把二维码字符串用标签套上即可自动生成二维码

        //$SN = $sn;
        $content = $content;
        $times = 1;

        //打开注释可测试
        $result = $this->printMsg($SN,$content,1);
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
        
    }

	/**
	 * 用户中心右边，小导航
	 *
	 * @param string	$menu_type	导航类型
	 * @param string 	$menu_key	当前导航的menu_key
	 * @return
     */
    private function profile_menu($menu_type='',$menu_key='') {
        Language::read('member_layout');
        switch ($menu_type) {
        	case 'list':
            $menu_array = array(
            array('menu_key'=>'store_order',		'menu_name'=>Language::get('nc_member_path_all_order'),	'menu_url'=>'index.php?act=store_order'),
            array('menu_key'=>'state_new',			'menu_name'=>Language::get('nc_member_path_wait_pay'),	'menu_url'=>'index.php?act=store_order&op=index&state_type=state_new'),
            array('menu_key'=>'state_pay',	        'menu_name'=>Language::get('nc_member_path_wait_send'),	'menu_url'=>'index.php?act=store_order&op=store_order&state_type=state_pay'),
            array('menu_key'=>'state_send',		    'menu_name'=>Language::get('nc_member_path_sent'),	    'menu_url'=>'index.php?act=store_order&op=index&state_type=state_send'),
            array('menu_key'=>'state_success',		'menu_name'=>Language::get('nc_member_path_finished'),	'menu_url'=>'index.php?act=store_order&op=index&state_type=state_success'),
            array('menu_key'=>'state_cancel',		'menu_name'=>Language::get('nc_member_path_canceled'),	'menu_url'=>'index.php?act=store_order&op=index&state_type=state_cancel'),
            );
            break;
        }
        Tpl::output('member_menu',$menu_array);
        Tpl::output('menu_key',$menu_key);
    }
}
