<?php
/**
 * 任务计划 - 分钟执行的任务
 *
 *
 *
 *
 * 
 */
defined('In718Shop') or exit('Access Invalid!');

class minutesControl extends BaseCronControl {

    /**
     * 默认方法
     */
    public function indexOp() {
    	//执行通用任务
        $this->_cron_common();
        //更新弹出框状态
       	$this->_tanchu_state();
        //更新首页的商品价格信息
        $this->_web_index_update();
        // 发送邮件消息
        $this->_cron_mail_send();
        //未付款订单超期自动关闭
		$this->_order_timeout_cancel();
        //自动取消新品标记
        $this->_goods_new_unshow();
         //秒杀和限时折扣开始商品活动表存储
        $this->_goods_promotion_xianshi();
        //秒杀和限时折扣开始商品活动表删除
         $this->_goodsdel_promotion_xianshi();
          $this->_goodspromotion_huiyuan();
           $this->_goodspromotion_xinpin();
    }

    /**
     * 更新弹出框状态
     */
    private function _tanchu_state(){
        $condition = array();
        $condition['is_open'] = 0;
        $tanchu_list = Model('tanchu')->table('tanchu')->where($condition)->order("acs asc")->select();
        foreach ($tanchu_list as $val) {
             $now = TIMESTAMP;
            if($now >= $val['start_time'] && $now <= $val['end_time'] ){
                $data['state']       = 1;
            }else{
                $data['state']       = 2;
            }
            $condition1['tanchu_id'] = $val['tanchu_id'];
            Model('tanchu')->table('tanchu')->where($condition1)->update($data);
        }
    }
    /**
     * 更新首页的商品价格信息
     */
    private function _web_index_update(){
         Model('web_config')->updateWebGoods();
    }

    /**
     * 发送邮件消息
     */
    private function _cron_mail_send() {
        //每次发送数量
        $_num = 50;
        $model_storemsgcron = Model('mail_cron');
        $cron_array = $model_storemsgcron->getMailCronList(array(), $_num);
        if (!empty($cron_array)) {
            $email = new Email();
            $mail_array = array();
            foreach ($cron_array as $val) {
                $return = $email->send_sys_email($val['mail'],$val['subject'],$val['contnet']);
                if ($return) {
                    // 记录需要删除的id
                    $mail_array[] = $val['mail_id'];
                }
            }
            // 删除已发送的记录
            $model_storemsgcron->delMailCron(array('mail_id' => array('in', $mail_array)));
        }
    }

    /**
     * 执行通用任务
     */
    private function _cron_common(){

        //查找待执行任务
        $model_cron = Model('cron');
        $cron = $model_cron->getCronList(array('exetime'=>array('elt',TIMESTAMP)));
        if (!is_array($cron)) return ;
        $cron_array = array(); $cronid = array();
        foreach ($cron as $v) {
            $cron_array[$v['type']][$v['exeid']] = $v;
        }
        foreach ($cron_array as $k=>$v) {
            // 如果方法不存是，直接删除id
            if (!method_exists($this,'_cron_'.$k)) {
                $tmp = current($v);
                $cronid[] = $tmp['id'];continue;
            }
            $result = call_user_func_array(array($this,'_cron_'.$k),array($v));
            if (is_array($result)){
                $cronid = array_merge($cronid,$result);
            }
        }
        //删除执行完成的cron信息
        if (!empty($cronid) && is_array($cronid)){
            $model_cron->delCron(array('id'=>array('in',$cronid)));
        }
    }

    /**
     * 上架
     *
     * @param array $cron
     */
    private function _cron_1($cron = array()){
        $condition = array('goods_commonid' => array('in',array_keys($cron)));
        $update = Model('goods')->editProducesOnline($condition);
        if ($update){
            //返回执行成功的cronid
            $cronid = array();
            foreach ($cron as $v) {
                $cronid[] = $v['id'];
            }
        }else{
            return false;
        }
        return $cronid;
    }

    /**
     * 根据商品id更新商品促销价格
     *
     * @param array $cron
     */
    private function _cron_2($cron = array()){var_dump($cron);
        $condition = array('goods_id' => array('in',array_keys($cron)));
        $update = Model('goods')->editGoodsPromotionPrice($condition);
        if ($update){
            //返回执行成功的cronid
            $cronid = array();
            foreach ($cron as $v) {
                $cronid[] = $v['id'];
            }
        }else{
            return false;
        }
        return $cronid;
    }

    /**
     * 优惠套装过期
     *
     * @param array $cron
     */
    private function _cron_3($cron = array()) {
        $condition = array('store_id' => array('in', array_keys($cron)));
        $update = Model('p_bundling')->editBundlingQuotaClose($condition);
        if ($update) {
            //返回执行成功的cronid
            $cronid = array();
            foreach ($cron as $v) {
                $cronid[] = $v['id'];
            }
        } else {
            return false;
        }
        return $cronid;
    }

    /**
     * 推荐展位过期
     *
     * @param array $cron
     */
    private function _cron_4($cron = array()) {
        $condition = array('store_id' => array('in', array_keys($cron)));
        $update = Model('p_booth')->editBoothClose($condition);
        if ($update) {
            //返回执行成功的cronid
            $cronid = array();
            foreach ($cron as $v) {
                $cronid[] = $v['id'];
            }
        } else {
            return false;
        }
        return $cronid;
    }

    /**
     * 抢购开始更新商品促销价格
     *
     * @param array $cron
     */
    private function _cron_5($cron = array()) {
        $condition = array();
        $condition['goods_id'] = array('in', array_keys($cron));
        $condition['start_time'] = array('lt', TIMESTAMP);
        $condition['end_time'] = array('gt', TIMESTAMP);
        $groupbuy = Model('groupbuy')->getGroupbuyList($condition);
        foreach ($groupbuy as $val) {
            Model('goods')->editGoods(array('goods_promotion_price' => $val['groupbuy_price'], 'goods_promotion_type' => 1), array('goods_id' => $val['goods_id']));
        }
        //返回执行成功的cronid
        $cronid = array();
        foreach ($cron as $v) {
            $cronid[] = $v['id'];
        }
        return $cronid;
    }

    /**
     * 抢购过期
     *
     * @param array $cron
     */
    private function _cron_6($cron = array()) {
        $condition = array('goods_commonid' => array('in', array_keys($cron)));
        //抢购活动过期
        $update = Model('groupbuy')->editExpireGroupbuy($condition);
        if ($update){
            //返回执行成功的cronid
            $cronid = array();
            foreach ($cron as $v) {
                $cronid[] = $v['id'];
            }
        }else{
            return false;
        }
        return $cronid;
    }

    /**
     * 限时折扣过期
     *
     * @param array $cron
     */
    private function _cron_7($cron = array()) {
        $condition = array('xianshi_id' => array('in', array_keys($cron)));
        //限时折扣过期
        $update = Model('p_xianshi')->editExpireXianshi($condition);
        if ($update){
            //返回执行成功的cronid
            $cronid = array();
            foreach ($cron as $v) {
                $cronid[] = $v['id'];
            }
        }else{
            return false;
        }
        return $cronid;
    }
	
	/**
     * 未付款订单超期自动关闭
     */
    private function _order_timeout_cancel() {

        //实物订单超期未支付系统自动关闭
        $_break = false;
        $model_order = Model('order');
        $logic_order = Logic('order');
        $condition = array();
        $condition['order_state'] = ORDER_STATE_NEW;
        $condition['add_time'] = array('BETWEEN',array((TIMESTAMP - (ORDER_AUTO_CANCEL_TIME * 60) - 600),(TIMESTAMP - ORDER_AUTO_CANCEL_TIME * 60)));
        //分批，每批处理100个订单，最多处理5W个订单
        for ($i = 0; $i < 500; $i++){
            if ($_break) {
                break;
            }
            $order_list = $model_order->getOrderList($condition, '', '*', '', 10,array('order_common'));
            if (empty($order_list)) break;
            foreach ($order_list as $order_info) {
                $result = $logic_order->changeOrderStateCancel($order_info,'system','系统','超期未支付系统自动关闭订单',true,false);
            if (!$result['state']) {
                    $this->log('实物订单超期未支付关闭失败SN:'.$order_info['order_sn']); $_break = true; break;
                }else{
                    $this->send($order_info['order_id']);
                    $sql = "SELECT order_id FROM 718shop_order where buyer_id = ".$order_info['buyer_id']." AND ( order_state > 0 OR lock_state > 0 )";
                    $result1 =Model()->query($sql);
                    if(empty($result1)){
                        $model_member = Model('member');
                        $update_array['is_xinren']   = 1;
                        $update = $model_member->table('member')->where(array('member_id'=>$order_info['buyer_id']))->update($update_array);
                    }
                }
            }
        }
    }

    /**
     * mq发送数据
     */
    private function send($data){

        $rabbitMQ = new RabbitMQ();

        $connection = $rabbitMQ->connection('10.10.11.141', 5672, 'wzxd', 'WZXDRMQpython~XX2');

        if($connection){

            $rabbitMQ->sendTopic($connection,$data,'order_cancel_topic_exchange','cancel.#');

            $rabbitMQ->close($connection);

        }

    }

     /** 
     * 自动取消新品标记
    */
    private function _goods_new_unshow(){
        //获取所有新品
        $goods_list = Model()->query("SELECT goods_commonid FROM 718shop_goods_common WHERE is_new=1 and goods_puton_time+goods_show_time<=".TIMESTAMP);
        if($goods_list){
            $goods_commonid_arr = array_column($goods_list,'goods_commonid');
            $goods_commonid_str = implode(',',$goods_commonid_arr);
            //更新goods_common表，新品上架时间、持续时间、折扣率置为空，新品标志置为零
            //更新goods表，商品活动如果是7改为0，其余不做更改
            $sql = "UPDATE 718shop_goods_common gc,718shop_goods g SET gc.goods_puton_time=NULL,gc.goods_show_time=NULL,gc.goods_new_discount=NULL,gc.is_new=0,g.is_group_ladder=0 WHERE gc.goods_commonid IN (".$goods_commonid_str.") AND g.goods_commonid IN (".$goods_commonid_str.") AND g.is_group_ladder =7";
                
            Model()->execute($sql);
            // add
            $model_goodspromotion=Model('goods_promotion'); 
            $goods_list = Model('goods')->getGoodsList(array('goods_commonid' => array('in', $goods_commonid_arr),'is_deleted'=>0), 'goods_id');

             $where=array();
            $where['goods_id']=array('in',array_column($goods_list,'goods_id'));
            $where['promotion_type']=40;
            $result=$model_goodspromotion->delgoods_promotion($where);

        }
    }
     /** 
     * 自动同步限时商品表到商品活动表
    */
    private function _goods_promotion_xianshi(){
        //获取已经开始的活动
        $model_xianshi_goods = Model('p_xianshi_goods');
        $model_goodspromotion=Model('goods_promotion'); 
        $condition=array();
        $condition['is_tongbu']=0;//未同步
        $condition['start_time']=array('lt', TIMESTAMP);
        $condition['end_time']= array('gt', TIMESTAMP);
        $condition['state']=1;
        $xianshigoods = $model_xianshi_goods->getXianshiGoodsList($condition);
        $insert_arr=array();
        $xiasnhigoods_idarr=array_column($xianshigoods,'xianshi_goods_id');
        // var_dump($xiasnhigoods_idarr);die;
        foreach ($xianshigoods as $key => $value) {
            $tmp=array();
            $tmp['goods_id']=$value['goods_id'];
            $xianshi_info =Model('p_xianshi')->getXianshiInfo(array('xianshi_id' => $value['xianshi_id']));
            //区分限时折扣和限时秒杀
            if($xianshi_info['xianshi_type']==1){
                $tmp['promotion_type']=10;
            }else{
                 $tmp['promotion_type']=20;
            }
            $tmp['price']=$value['xianshi_price'];
            // $tmp['price']=222;
            $tmp['upper_limit']=$value['upper_limit'];
            $tmp['commis_rate']=$value['commis_rate'];
            $tmp['end_time']=$value['end_time'];
            $tmp['promotion_type_id']=$value['xianshi_goods_id'];
            // $result =$model_goodspromotion->addgoods_promotion($tmp);
            $insert_arr[] = $tmp;

        }
        // var_dump( $insert_arr);die;
        //批量增加
        $result =$model_goodspromotion->addallgoods_promotion($insert_arr);
         //更新xianshigoods表的同步状态
        if($result){
            $where=array();
        $where['xianshi_goods_id']=array('in',$xiasnhigoods_idarr);
        $model_xianshi_goods->editXianshiGoods(array('is_tongbu'=>1), $where);
        }
        
            // var_dump($result );die;
    }
        
             /** 
     * 自动删除到期限时商品表到商品活动表
    */
    private function _goodsdel_promotion_xianshi(){
        //获取已经开始的活动
        $model_goodspromotion=Model('goods_promotion'); 
        $condition=array();
        $condition['end_time']= array('lt', TIMESTAMP);
        $condition['promotion_type']=array('in', array(10,20));
        $goodspromotion = $model_goodspromotion->getgoods_promotionList($condition);
        $goodspromotion_idarr=array_column($goodspromotion,'goods_promotion_id');
        //批量删除
        // var_dump($goodspromotion_idarr);die;
        if(!empty($goodspromotion_idarr)){
         $where=array();
        $where['goods_promotion_id']=array('in', $goodspromotion_idarr);
        // $where['promotion_type']=array('elt',20);
        $res=$model_goodspromotion->delgoods_promotion($where);
        }
        
        // var_dump($res);die;
    }
     private function _goodspromotion_huiyuan(){
                $model_goods = Model('goods');
                $goodscommon_list = $model_goods->getGoodsCommonList(array('is_vip_price'=>1),'goods_commonid',0);
                $goods_commonidarr=array_column($goodscommon_list,'goods_commonid');
                $condition=array();
                $condition['goods_commonid']=array('in', $goods_commonidarr);
                $condition['is_deleted']=0;
                $goods_list = $model_goods->getGoodsList($condition, 'goods_id,goods_price,hui_discount');
                $model_goodspromotion=Model('goods_promotion'); 
                // var_dump($goods_list);die;
                foreach ($goods_list as $key => $value) {
                    $vipgoods=array();
                    $vipgoods['goods_id']=$value['goods_id'];
                    $vipgoods['promotion_type']=30;
                    $vipgoods_info=$model_goodspromotion->getgoods_promotionInfo($vipgoods); 
                     if(empty($vipgoods_info)){
                             $model_goodspromotion->addgoods_promotion_vip($value['goods_id'],$value['goods_price'],$value['hui_discount']);
                        }
                }
        }
         private function _goodspromotion_xinpin(){
        $model_goodspromotion=Model('goods_promotion'); 
        $model_goods = Model('goods');
        $model_setting = Model('setting');
        $goods_show_discount = $model_setting->getRowSetting('goods_show_discount');
        $condition=array();
        $condition['is_group_ladder']=7;//未同步//未同步
        $condition['is_deleted']=0;
        $goods_list =$model_goods->getGoodsList($condition, 'goods_id,goods_price');
        foreach ($goods_list as $key => $value) {
            $where=array();
            $where['goods_id']=$value['goods_id'];
            $where['promotion_type']=40;
            $goodsxinpin_info=$model_goodspromotion->getgoods_promotionInfo($where);
            if(empty( $goodsxinpin_info)){
                $tmp=array();
                $tmp['goods_id']=$value['goods_id'];
                $tmp['price'] = $goods_show_discount['value'] * $value['goods_price'] / 100;
                $tmp['promotion_type']=40;
                $insert_arr[] = $tmp;
           }    
        }

        //批量增加
        $result=$model_goodspromotion->addallgoods_promotion($insert_arr);
    }
}
