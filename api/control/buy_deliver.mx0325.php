<?php
/**
 * 即买即送
 *
 *
 *
 ***/
defined('In718Shop') or exit('Access Invalid!');
class buy_deliverControl  extends BaseControl{
    //首页
    const SPECIAL_ID = 1;
    //每页显示的商品个数
    const PAGESIZE = 12;
    //页数
    const PAGENUM = 1;

    //模型对象
    private $_model_search;
    

    /*
    首页,即买即送
     */
    public function indexOp()
    {
        $this->goodsListOp();

    }

    /*
    商品列表
     */
    public function goodsListOp()
    {
        $buy_deliver_id = intval($_POST['buy_deliver_id']);
        $model_buy_deliver_goods = Model('buy_deliver_goods');
        $model_buy_deliver = Model('buy_deliver');
        //获取活动商品列表
        $condition = array();
        $condition['state'] = 1;
        if (isset($_GET['ziti_id'])) {
           $buy_deliver_info = $model_buy_deliver->getBuyDeliverInfo(array('ziti_id' => $_GET['ziti_id']));
            $condition['buy_deliver_id'] = $buy_deliver_info['buy_deliver_id'];
        }
        //$condition['buy_deliver_id'] = intval($_POST['buy_deliver_id']);;
        //$goods_list = $model_buy_deliver_goods->getGoodsList();
        $goods_list = $model_buy_deliver_goods->getBuyDeliverGoodsExtendList($condition);
		foreach ($goods_list as $key=>$value){
            $goods_list[$key]['sale_num'] = Model('goods')->getfby_goods_id($value['goods_id'],'goods_salenum') + Model('goods')->getfby_goods_id($value['goods_id'],'goods_presalenum');
        }

        if($_GET['key']==2) {//综合排序
            if ($_GET['order'] == 'true') {
                $goods_list = $this->multi_array_sort($goods_list, 'goods_sort',SORT_DESC);
            }else{
                $goods_list = $this->multi_array_sort($goods_list, 'goods_sort');
            }
        }elseif ($_GET['key']==1){//销量排序
            if($_GET['order'] == 'true'){
                $goods_list = $this->multi_array_sort($goods_list, 'sale_num');
            }else{
                $goods_list = $this->multi_array_sort($goods_list, 'sale_num',SORT_DESC);
            }
        }elseif ($_GET['key']==3) {//价格排序
            if($_GET['order'] == 'true'){
                $goods_list = $this->multi_array_sort($goods_list, 'goods_price');
            }else{
                $goods_list = $this->multi_array_sort($goods_list, 'goods_price',SORT_DESC);
            }
        }
        else{
            $goods_list = $this->multi_array_sort($goods_list, 'goods_sort',SORT_ASC);
        }
        $totaldata = count($goods_list);
        $data['totaldata'] = $totaldata;
        //总页数
        $totalpage =intval( ($totaldata + self::PAGESIZE - 1)/self::PAGESIZE);
        $data['totalpage'] = $totalpage;
        
        if (!empty($_GET['pagecount'])) {
            $pagecount = $_GET['pagecount'];
        } else {
            $pagecount = self::PAGENUM;
        }
        //当前页码
        $data['pagecount']=$pagecount;

        
        if ($goods_list) {
            $goods_list = array_slice($goods_list, 0, $pagecount * self::PAGESIZE);
        }
        if(!empty($goods_list)){
            $data['goods_list'] = $goods_list;
            $message='查询成功！';
            $res = array('code'=>'10000' , 'message'=>$message,'data'=>$data);
            echo json_encode($res,320);
        }else{
            //echo $this->returnMsg(10001, '查询成功,无活动商品！', '');exit;
            $res = array('code'=>'10001' , 'message'=>'查询成功,无活动商品！','');
            echo json_encode($res,320);
        }
    }

    /*
    商品详情
     */
    public function goodsInfoOp()
    {
        // $member_id = intval($_POST['member_id']);
        // if($member_id != 0){
        //     $model_member = model('member');
        //     $member_info = $model_member->table('member')->where(array('member_id'=>$member_id))->find();
        //     if(!$member_info){
        //         echo $this->returnMsg(10003, '本系统无此用户!', array('member_id'=>$member_id));exit;
        //     }elseif($member_info['is_xinren'] == 2){
        //         echo $this->returnMsg(10004, '此用户为老用户，不享受新人专享!', array('member_id'=>$member_id));exit;
        //     }
        // }
       
        //$xinren_goods_id = intval($_POST['buy_delive_goods_id']);
        $goods_id = intval($_GET['goods_id']);
        //查询新人专享表信息
        // $model_xinren_goods = Model('p_xinren_goods');
        // $condition['xinren_goods_id'] = $xinren_goods_id;
        // $condition['goods_id'] = $goods_id;
        // $xinren_goods_list = $model_xinren_goods->getXinRenGoodsExtendList($condition);
        // if(!$xinren_goods_list){
        //     echo $this->returnMsg(10005, '本系统无此专享商品!', array('xinren_goods_id'=>$xinren_goods_id,'goods_id'=>$goods_id));exit;
        // }
        // $xinren_goods = $xinren_goods_list[0];
        //$xinren_goods = $model_xinren_goods->table('p_xinren_goods')->where(array('xinren_goods_id'=>$xinren_goods_id,'goods_id'=>$goods_id))->find();
        //print_r($xinren_goods);die;
        // 商品详细信息
        $model_goods = Model('goods');
        $goods_detail = $model_goods->getGoodsDetail($goods_id);
        //新人专享表信息整合
        // $goods_detail['goods_info']['xinren_goods_id'] = $xinren_goods['xinren_goods_id'];
        // $goods_detail['goods_info']['xinren_price'] = $xinren_goods['xinren_price'];
        // $goods_detail['goods_info']['xinren_app_price'] = $xinren_goods['xinren_app_price'];
        // $goods_detail['goods_info']['xinren_state'] = $xinren_goods['state'];
        // $goods_detail['goods_info']['xinren_discount'] = $xinren_goods['xinren_discount'];
    
        $goods_info = $goods_detail['goods_info'];
        $goods_info['goods_image'] = cthumb($goods_info['goods_image']);
        //print_r($goods_info);die;
       if ($goods_info['goods_state'] != 1 || $goods_info['goods_verify'] != 1 ) {
            //echo $this->returnMsg(10006, 'SORRY,此商品下架，请选购别的商品!', $goods_info);exit;
            $res = array('code'=>'10006' , 'message'=>'SORRY,此商品下架，请选购别的商品!',$goods_info);
            echo json_encode($res,320);
            exit;
            // $message='fail';
            // $res = array('code'=>'10006' , 'message'=>$message,'data'=>$goods_info);
            //  echo json_encode($res,320);exit();
        }
        if (empty($goods_info)) {
            //echo $this->returnMsg(10007, 'SORRY,此商品消失，请选购别的商品!', $goods_info);
            $res = array('code'=>'10007' , 'message'=>'SORRY,此商品消失，请选购别的商品!',$goods_info);
            echo json_encode($res,320);
            exit;
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
        $goods_detail['goods_info']=$goods_info;
        //print_r($goods_detail);die;
        if ($goods_detail) {
            $goods_detail['goods_info']['is_group_ladder'] = 5;
            //echo $this->returnMsg(10000, 'success!', $goods_detail);exit;
            $res = array('code'=>'10000' , 'message'=>'success',$goods_detail);
            echo json_encode($res,320);
            exit;
            // $message='success';
            // $res = array('code'=>'100' , 'message'=>$message,'data'=>$goods_detail);
            //  echo json_encode($res,320);
        } else {
            //echo $this->returnMsg(10008, 'fail!', $goods_detail);exit;
            $res = array('code'=>'10008' , 'message'=>'fail',$goods_detail);
            echo json_encode($res,320);
            exit;
            // $message='fail';
            // $res = array('code'=>'200' , 'message'=>$message,'data'=>$goods_detail);
            //  echo json_encode($res,320);
        }

    }
   
}