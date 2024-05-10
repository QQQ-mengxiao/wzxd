<?php
/**
 * 新人专享
 *
 *
 *
 ***/
defined('In718Shop') or exit('Access Invalid!');
class xinrenControl  extends BaseControl{
    //首页
    const SPECIAL_ID = 1;
    //每页显示的商品个数
    const PAGESIZE = 12;
    //页数
    const PAGENUM = 1;

    //模型对象
    private $_model_search;
    /* 链接测试
    */
    public function testOp(){
        echo $this->returnMsg(10000, '请求成功！', '');exit;
    }

    /* 首页新人专项区
    */
    public function xinrenquOp(){
        $member_id = intval($_POST['member_id']);
        $model_member = Model('member');
        $member_info = $model_member->table('member')->where(array('member_id'=>$member_id))->find();
        if($member_info['is_xinren'] == 2){
            echo $this->returnMsg(10003, '此用户为老用户，不享受新人专享!', array('member_id'=>$member_id));exit;
        }
        $model_xinren_goods = Model('p_xinren_goods');
        //获取新人专享商品列表
        $condition = array();
        $condition['goods_state'] = 1;
        $xinren_goods_list = $model_xinren_goods->getApiXinRenGoodsExtendList($condition,4);
		
        foreach ($xinren_goods_list as $k => $goods_info) {
            
            $xinren_goods_list[$k]['image_url_240'] = cthumb($goods_info['goods_image'], 240, $goods_info['store_id']);
            $xinren_goods_list[$k]['image_url'] = cthumb($goods_info['goods_image'], 240, $goods_info['store_id']);
           
        }
        if(!empty($xinren_goods_list)){
            echo $this->returnMsg(10000, '查询成功！', $xinren_goods_list);exit;
        }else{
            echo $this->returnMsg(10001, '查询成功,无专享商品！', '');exit;
        }
        
    }
    /* 更多新人专项商品列表
    */
    public function xinrenlistOp(){
        $member_id = intval($_POST['member_id']);
        $model_member = Model('member');
        $member_info = $model_member->table('member')->where(array('member_id'=>$member_id))->find();
        if($member_info['is_xinren'] == 2){
            echo $this->returnMsg(10003, '此用户为老用户，不享受新人专享!', array('member_id'=>$member_id));exit;
        }
        $model_xinren_goods = Model('p_xinren_goods');
        //获取新人专享商品列表
        $condition = array();
        $condition['state'] = 1;
        $condition['goods_state'] = 1;
        $xinren_goods_list = $model_xinren_goods->getApiXinRenGoodsExtendList($condition);foreach($xinren_goods_list as $k=>$v){
            $xinren_goods_list[$k]['sale_num'] = $v['goods_salenum'] + $v['goods_presalenum'];
        }
        if($_POST['key']==2) {//综合排序
            if ($_POST['order'] == 'true') {
                $xinren_goods_list = $this->multi_array_sort($xinren_goods_list, 'goods_sort',SORT_DESC);
            }else{
                $xinren_goods_list = $this->multi_array_sort($xinren_goods_list, 'goods_sort');
            }
        }elseif ($_POST['key']==1){//销量排序
            if($_POST['order'] == 'true'){
                $xinren_goods_list = $this->multi_array_sort($xinren_goods_list, 'sale_num');
            }else{
                $xinren_goods_list = $this->multi_array_sort($xinren_goods_list, 'sale_num',SORT_DESC);
            }
        }elseif ($_POST['key']==3) {//价格排序
            if($_POST['order'] == 'true'){
                $xinren_goods_list = $this->multi_array_sort($xinren_goods_list, 'xinren_price');
            }else{
                $xinren_goods_list = $this->multi_array_sort($xinren_goods_list, 'xinren_price',SORT_DESC);
            }
        }
        $totaldata = count($xinren_goods_list);
        $data['totaldata'] = $totaldata;
        //总页数
        $totalpage =intval( ($totaldata + self::PAGESIZE - 1)/self::PAGESIZE);
        $data['totalpage'] = $totalpage;
        
        if (!empty($_POST['pagecount'])) {
            $pagecount = $_POST['pagecount'];
        } else {
            $pagecount = self::PAGENUM;
        }
        //当前页码
        $data['pagecount']=$pagecount;
        
        if ($xinren_goods_list) {
            $xinren_goods_list = array_slice($xinren_goods_list, 0, $pagecount * self::PAGESIZE);
            
            $data['xinren_goods_list'] = $xinren_goods_list;
            echo $this->returnMsg(10000, '查询成功！', $data);exit;
        } else {
            echo $this->returnMsg(10001, '查询成功,无专享商品！', '');exit;
        }
        
    }
    /* 新人专项商品详情
    */
    public function goodsdetailOp() {
         $member_id = intval($_POST['member_id']);
        if($member_id != 0){
            $model_member = Model('member');
            $member_info = $model_member->table('member')->where(array('member_id'=>$member_id))->find();
            if(!$member_info){
                echo $this->returnMsg(10004, '本系统无此用户!', array('member_id'=>$member_id));exit;
            }elseif($member_info['is_xinren'] == 2){
                echo $this->returnMsg(10003, '此用户为老用户，不享受新人专享!', array('member_id'=>$member_id));exit;
            }
        }
       
        $xinren_goods_id = intval($_POST['xinren_goods_id']);
        $goods_id = intval($_POST['goods_id']);
        //查询新人专享表信息
        $model_xinren_goods = Model('p_xinren_goods');
        $condition['xinren_goods_id'] = $xinren_goods_id;
        $condition['goods_id'] = $goods_id;
        $xinren_goods_list = $model_xinren_goods->getXinRenGoodsExtendList($condition);
        if(!$xinren_goods_list){
            echo $this->returnMsg(10005, '本系统无此专享商品!', array('xinren_goods_id'=>$xinren_goods_id,'goods_id'=>$goods_id));exit;
        }
        $xinren_goods = $xinren_goods_list[0];
        //$xinren_goods = $model_xinren_goods->table('p_xinren_goods')->where(array('xinren_goods_id'=>$xinren_goods_id,'goods_id'=>$goods_id))->find();
        //print_r($xinren_goods);die;
        // 商品详细信息
        $model_goods = Model('goods');
        $goods_detail = $model_goods->getGoodsDetail($goods_id);
        //新人专享表信息整合
        $goods_detail['goods_info']['xinren_goods_id'] = $xinren_goods['xinren_goods_id'];
        $goods_detail['goods_info']['xinren_price'] = $xinren_goods['xinren_price'];
        $goods_detail['goods_info']['xinren_app_price'] = $xinren_goods['xinren_app_price'];
        $goods_detail['goods_info']['xinren_state'] = $xinren_goods['state'];
        $goods_detail['goods_info']['xinren_discount'] = $xinren_goods['xinren_discount'];
    
        $goods_info = $goods_detail['goods_info'];
        $goods_info['goods_image'] = cthumb($goods_info['goods_image'],360);
        //print_r($goods_info);die;
       if ($goods_info['goods_state'] != 1 || $goods_info['goods_verify'] != 1 ) {
            //echo $this->returnMsg(10006, 'SORRY,此商品下架，请选购别的商品!', $goods_info);exit;
        echo $this->returnMsg(10005, 'SORRY,此商品下架，请选购别的商品!', $goods_info);exit;
            // $message='fail';
            // $res = array('code'=>'10006' , 'message'=>$message,'data'=>$goods_info);
            //  echo json_encode($res,320);exit();
        }
        if (empty($goods_info)) {
            //echo $this->returnMsg(10007, 'SORRY,此商品消失，请选购别的商品!', $goods_info);exit;
            echo $this->returnMsg(10005, 'SORRY,此商品消失，请选购别的商品!', $goods_info);exit;
            // $message='fail';
            // $res = array('code'=>'200' , 'message'=>$message,'data'=>$goods_info);
            //  echo json_encode($res,320);exit();
        }
        $rs = $model_goods->getGoodsList(array('goods_commonid'=>$goods_info['goods_commonid']));
        $count = 0;
        foreach($rs as $v){
            $count += $v['goods_salenum'];
        }
        $goods_info['goods_salenum'] = $count;
        $goods_info['goods_presalenum'] =$goods_info['goods_presalenum'] + $count;
         if(!is_array($goods_detail['goods_image'][0])){
            $goods_detail['goods_image'][0] = explode(",",$goods_detail['goods_image'][0]);
         }
        // 生成缓存的键值
        $hash_key = $goods_info['goods_id'];
        $_cache = rcache($hash_key, 'product');
        if (empty($_cache)) {
            // 查询SNS中该商品的信息
            $snsgoodsinfo = Model('sns_goods')->getSNSGoodsInfo(array('snsgoods_goodsid' => $goods_info['goods_id']), 'snsgoods_likenum,snsgoods_sharenum');
            $data = array();
            $data['likenum'] = $snsgoodsinfo['snsgoods_likenum'];
            $data['sharenum'] = $snsgoodsinfo['snsgoods_sharenum'];
            // 缓存商品信息
            wcache($hash_key, $data, 'product');
        }
        $goods_info = array_merge($goods_info, $_cache);

        // 如果使用售卖区域
        if ($goods_info['transport_id'] > 0) {
            // 取得三种运送方式默认运费
            $model_transport = Model('transport');
            $transport = $model_transport->getExtendList(array('transport_id' => $goods_info['transport_id'], 'is_default' => 1));
            if (!empty($transport) && is_array($transport)) {
                foreach ($transport as $v) {
                    $goods_info[$v['type'] . "_price"] = $v['sprice'];
                }
            }
        }
        if (!empty($goods_info['deliverer_id'])) {
           $deliverer=Model('daddress')->where(array('address_id'=>$goods_info['deliverer_id']))->find();
        }else{
            $deliverer=[];
        }
        //抛出已购买人信息
        $model_order = Model('order');
        $sales = $model_order->getOrderAndOrderGoodsSalesRecordList(array('order_goods.goods_id'=>$goods_id), 'order_goods.*, order.buyer_name, order.add_time', 10);
            $buy_numbers_list=array();
        foreach ($sales as $key => $value) {
           $buy_numbers=Model('member')->where(array('member_id'=>$value['buyer_id']))->field('member_name,member_avatar')->select();
            $buy_numbers_list[$value['buyer_id']]['member_avatar']=UPLOAD_SITE_URL.DS.ATTACH_AVATAR.DS.$buy_numbers[0]['member_avatar'];
            $buy_numbers_list[$value['buyer_id']]['member_name'] = mb_substr($buy_numbers[0]['member_name'], 0, 1, 'utf-8') . "***" . mb_substr($buy_numbers[0]['member_name'], -1, 1,'utf-8'); 
        }
        // $buy_numbers_list=array_slice($buy_numbers_list,0,3);//只取三个
        $buy_numbers_list=array_values($buy_numbers_list);
        $goods_detail['buy_numbers_list']=$buy_numbers_list;
        //优惠信息
        // $logic_buy = Logic('buy');
        // $result = $logic_buy->buyStep1($_POST['cart_id'], $_POST['ifcart'], $_SESSION['member_id'], $_SESSION['store_id']);
        // var_dump($result);die;
         //小程序码
        $payment_code = 'wxpay_jsapi';
        $condition = array();
        $condition['payment_code'] = $payment_code;
        $payment_info = Model()->table('mb_payment')->where($condition)->find();
        $appletcode=$payment_info['appletcode'];
        $goods_detail['appletcode']=UPLOAD_SITE_URL.DS.ATTACH_MOBILE.'/appletcode/'.$appletcode;

        //评价信息
        $goods_evaluate_info = Model('evaluate_goods')->getEvaluateGoodsInfoByGoodsID($goods_id);
        $goods_info = array_merge($goods_info, $goods_evaluate_info);
        $goods_info = array_merge($goods_info, $deliverer);
		//var_dump($goods_detail['goods_image']);die;
		foreach($goods_detail['goods_image'] as $key=>$value){
			$goods_detail['goods_image'][$key][0] = $value[2];
		}
		
		$model_store_plate = Model('store_plate');
        if($goods_info['plateid_top']!=0) {
            $plated_top = $model_store_plate->getStorePlateInfoByID($goods_info['plateid_top']);
            $goods_info['mobile_body'] = $plated_top['plate_content'].$goods_info['mobile_body'];
            //$goods_info['plate_top'] = $plated_top['plate_content'];
        }
        if($goods_info['plateid_bottom']!=0){
            $plate_bottom = $model_store_plate->getStorePlateInfoByID($goods_info['plateid_bottom']);
            $goods_info['mobile_body'] = $goods_info['mobile_body'].$plate_bottom['plate_content'];
            //$goods_info['plate_bottom'] = $plate_bottom['plate_content'];
        }
		//邮寄标识
        //发货人ID
        $deliverer_id = Model()->table('goods')->getfby_goods_id($goods_id,'deliverer_id');
        //仓库ID
        $storage_id = Model()->table('daddress')->getfby_address_id($deliverer_id,'storage_id');
        //根据仓库查询他的邮寄状态
        $b_post = Model()->table('storage')->getfby_storage_id ($storage_id,'by_post');
        if($b_post == 1){
        //邮寄
            $by_post = 2;
        }else{
            $by_post = 1;
        }
        $goods_info['by_post'] = $by_post; 
        $goods_detail['goods_info']=$goods_info;
        //print_r($goods_detail);die;
        if ($goods_detail) {
            echo $this->returnMsg(10000, 'success!', $goods_detail);exit;
            // $message='success';
            // $res = array('code'=>'100' , 'message'=>$message,'data'=>$goods_detail);
            //  echo json_encode($res,320);
        } else {
            echo $this->returnMsg(10005, 'SORRY,此商品消失，请选购别的商品!', $goods_info);exit;
           // echo $this->returnMsg(10008, 'fail!', $goods_detail);exit;
            // $message='fail';
            // $res = array('code'=>'200' , 'message'=>$message,'data'=>$goods_detail);
            //  echo json_encode($res,320);
        }

    }
   
}