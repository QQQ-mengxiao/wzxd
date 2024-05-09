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
    商品列表
     */
    public function indexOp()
    {
        $member_id      = $_POST['member_id'];
        $member_grade   = $member_id? (Model('member')->getGrade($member_id))+1 :-1;//用户等级
        $member_info    = Model()->table('member')->where(array('member_id' => $member_id, 'is_xinren' => 1))->find();
        $postorder      = $_POST['order'];
        $sortkey        = $_POST['key'];
        $ziti_id        = $_POST['ziti_id']?$_POST['ziti_id']:3;
        if ($_GET['pagecount']) {
            $offset     = (intval($_GET['pagecount'])-1)*10;
        }else{
            $offset     = 0;
        }
        if ($member_info) { //是新人
            $xinren_str = "";
        } else { //非新人
            $xinren_str = " AND sgp.promotion_type != 50 ";
        }

        $sortorder = "(CASE WHEN sg.goods_storage = 0 THEN 1 ELSE 0 END),sbdg.goods_sort ASC,sg.goods_storage DESC,goods_id DESC";
        if($sortkey==2){
            if($postorder == 'false'){
                $sortorder = "(CASE WHEN sg.goods_storage = 0 THEN 1 ELSE 0 END),sbdg.goods_sort ASC,sg.goods_storage DESC,goods_id DESC";
            }else{
                $sortorder = "( CASE WHEN sg.goods_storage = 0 THEN 1 ELSE 0 END ),sbdg.goods_sort ASC,sg.goods_storage,goods_id DESC";
            }
        }elseif($sortkey==1){
            if($postorder =='false'){
                $sortorder = "( CASE WHEN sg.goods_storage = 0 THEN 1 ELSE 0 END ),sbdg.goods_sort ASC,sale_num DESC,goods_id DESC";
            }else{
                $sortorder = "( CASE WHEN sg.goods_storage = 0 THEN 1 ELSE 0 END ),sbdg.goods_sort ASC,sale_num ASC,goods_id DESC";
            }
        }elseif($sortkey==3){
            if($postorder=='false'){
                $sortorder = "( CASE WHEN sg.goods_storage = 0 THEN 1 ELSE 0 END ),sbdg.goods_sort ASC,goods_price DESC,goods_id DESC";
            }else{
                $sortorder = "( CASE WHEN sg.goods_storage = 0 THEN 1 ELSE 0 END ),sbdg.goods_sort ASC,goods_price ASC,goods_id DESC";
            }
        }

        $sql = "SELECT sg.goods_id,sg.goods_marketprice,sg.goods_name,sg.goods_price,sg.goods_image,ifnull(min(sgp.price),sg.goods_price) AS goods_promotion_price,sum(sg.goods_storage) AS goods_storage,(sg.goods_salenum+sg.goods_presalenum) AS sale_num FROM 718shop_buy_deliver_goods sbdg LEFT JOIN 718shop_goods sg ON sbdg.goods_id=sg.goods_id LEFT JOIN 718shop_goods_promotion sgp ON sbdg.goods_id=sgp.goods_id WHERE sbdg.ziti_id=".$ziti_id." AND sg.goods_verify=1 AND sg.goods_state=1 AND sg.is_deleted=0".$xinren_str." GROUP BY sg.goods_id ORDER BY ".$sortorder." limit 10 offset ".$offset;
        $goods_list = Model()->query($sql);
        if($goods_list && is_array($goods_list)){
            foreach($goods_list as $key=>$goods){
                //通过goods_id在活动表中查询最低价格以及标签，最多三个
                // $sql = "SELECT sgp.goods_id,sgp.promotion_type,sgp.price FROM 718shop_goods_promotion sgp WHERE sgp.goods_id=".$goods['goods_id']." AND sgp.promotion_type !=50 AND CASE sgp.promotion_type WHEN 30 THEN sgp.member_levels<=".$member_grade." ELSE 1 END GROUP BY sgp.promotion_type ORDER BY sgp.price ASC LIMIT 3";
                $sql = "SELECT a.* FROM ((SELECT sgp.promotion_type,sgp.goods_id,sgp.price,sgp.goods_promotion_id FROM 718shop_goods_promotion sgp WHERE sgp.goods_id=".$goods['goods_id']."  ".$xinren_str." AND sgp.promotion_type !=30) UNION (SELECT sgp1.promotion_type,sgp1.goods_id,sgp1.price,sgp1.goods_promotion_id FROM 718shop_goods_promotion sgp1 WHERE sgp1.goods_id=".$goods['goods_id']." AND sgp1.promotion_type=30 AND sgp1.member_levels<=".$member_grade." ORDER BY sgp1.price LIMIT 1)) a GROUP BY a.promotion_type ORDER BY a.price ASC,a.goods_promotion_id ASC LIMIT 3";
                $goods_promotion_info = Model()->query($sql);
                if(count($goods_promotion_info)==3){
                    $goods_list[$key]['promotion_name'] = promotion_typeName($goods_promotion_info[0]['promotion_type']).'/'.promotion_typeName($goods_promotion_info[1]['promotion_type']).'/'.promotion_typeName($goods_promotion_info[2]['promotion_type']);
                    $goods_list[$key]['promotion_type'] = $goods_promotion_info[0]['promotion_type'];
                    $goods_list[$key]['promotion_name1'] = promotion_typeName($goods_promotion_info[0]['promotion_type']);
                    $goods_list[$key]['promotion_name2'] = promotion_typeName($goods_promotion_info[1]['promotion_type']);
                    $goods_list[$key]['promotion_name3'] = promotion_typeName($goods_promotion_info[2]['promotion_type']);
                    //会员折扣率计算
                    if($goods_promotion_info[0]['promotion_type'] == 30){
                        $discount = number_format($goods_promotion_info[0]['price'] / $goods['goods_price'],2) * 10;
                        $goods_list[$key]['hui_discount'] = $discount.'折';
                    }
                }elseif(count($goods_promotion_info)==2){
                    $goods_list[$key]['promotion_name'] = promotion_typeName($goods_promotion_info[0]['promotion_type']).'/'.promotion_typeName($goods_promotion_info[1]['promotion_type']);
                    $goods_list[$key]['promotion_type'] = $goods_promotion_info[0]['promotion_type'];
                    $goods_list[$key]['promotion_name1'] = promotion_typeName($goods_promotion_info[0]['promotion_type']);
                    $goods_list[$key]['promotion_name2'] = promotion_typeName($goods_promotion_info[1]['promotion_type']);
                    //会员折扣率计算
                    if($goods_promotion_info[0]['promotion_type'] == 30){
                        $discount = number_format($goods_promotion_info[0]['price'] / $goods['goods_price'],2) * 10;
                        $goods_list[$key]['hui_discount'] = $discount.'折';
                    }
                }elseif(count($goods_promotion_info)==1){
                    $goods_list[$key]['promotion_name'] = promotion_typeName($goods_promotion_info[0]['promotion_type']);
                    $goods_list[$key]['promotion_type'] = $goods_promotion_info[0]['promotion_type'];
                    $goods_list[$key]['promotion_name1'] = promotion_typeName($goods_promotion_info[0]['promotion_type']);
                    //会员折扣率计算
                    if($goods_promotion_info[0]['promotion_type'] == 30){
                        $discount = number_format($goods_promotion_info[0]['price'] / $goods['goods_price'],2) * 10;
                        $goods_list[$key]['hui_discount'] = $discount.'折';
                    }
                }else{
                    $goods_list[$key]['promotion_name'] = '';//普通商品
                    $goods_list[$key]['promotion_type'] = 0;
                }
                $goods_list[$key]['promotion_length'] = count($goods_promotion_info);//长度
                $goods_list[$key]['goods_image'] = cthumb($goods['goods_image']);
                $goods_list[$key]['image_url'] = cthumb($goods['goods_image']);
                $goods_list[$key]['member_grade'] = $member_grade;
                //划线价格优化
                if($goods['goods_promotion_price']>0 && $goods['goods_price']==$goods['goods_promotion_price']){
                    $goods_list[$key]['goods_price'] = $goods['goods_marketprice'];
                }
            }
        }

        if (count($goods_list)==10 && $_GET['pagecount'] != 0) {
            $list['end'] = 0;
        } else {
            $list['end'] = 1;
        }
        
        if ($goods_list) {
            $list['goods_list'] = $goods_list;
            die(json_encode(array('code' => '10000', 'message' => 'succ', 'data' => $list), 320));
        } else {
            die(json_encode(array('code' => '10001', 'message' => 'fail', 'data' => []), 320));
        }
    }

    /*
    商品列表gengduo
     */
    public function goodsListOp()
    {
        $member_id      = $_GET['member_id'];
        $member_grade   = $member_id? (Model('member')->getGrade($member_id))+1 :-1;//用户等级
        $member_info    = Model()->table('member')->where(array('member_id' => $member_id, 'is_xinren' => 1))->find();
        $postorder      = $_GET['order'];
        $sortkey        = $_GET['key'];
        $ziti_id        = $_GET['ziti_id']?$_GET['ziti_id']:3;
        if ($_GET['pagecount']) {
            $offset     = (intval($_GET['pagecount'])-1)*10;
        }else{
            $offset     = 0;
        }
        if ($member_info) { //是新人
            $xinren_str = "";
        } else { //非新人
            $xinren_str = " AND sgp.promotion_type != 50 ";
        }

        $sortorder = "(CASE WHEN sg.goods_storage = 0 THEN 1 ELSE 0 END),sbdg.goods_sort ASC,sg.goods_storage DESC,goods_id DESC";
        if($sortkey==2){
            if($postorder == 'false'){
                $sortorder = "(CASE WHEN sg.goods_storage = 0 THEN 1 ELSE 0 END),sbdg.goods_sort ASC,sg.goods_storage DESC,goods_id DESC";
            }else{
                $sortorder = "( CASE WHEN sg.goods_storage = 0 THEN 1 ELSE 0 END ),sbdg.goods_sort ASC,sg.goods_storage,goods_id DESC";
            }
        }elseif($sortkey==1){
            if($postorder =='false'){
                $sortorder = "( CASE WHEN sg.goods_storage = 0 THEN 1 ELSE 0 END ),sbdg.goods_sort ASC,sale_num DESC,goods_id DESC";
            }else{
                $sortorder = "( CASE WHEN sg.goods_storage = 0 THEN 1 ELSE 0 END ),sbdg.goods_sort ASC,sale_num ASC,goods_id DESC";
            }
        }elseif($sortkey==3){
            if($postorder=='false'){
                $sortorder = "( CASE WHEN sg.goods_storage = 0 THEN 1 ELSE 0 END ),sbdg.goods_sort ASC,goods_price DESC,goods_id DESC";
            }else{
                $sortorder = "( CASE WHEN sg.goods_storage = 0 THEN 1 ELSE 0 END ),sbdg.goods_sort ASC,goods_price ASC,goods_id DESC";
            }
        }

        $sql = "SELECT sg.goods_id,sg.goods_marketprice,sg.goods_name,sg.goods_price,sg.goods_image,ifnull(min(sgp.price),sg.goods_price) AS goods_promotion_price,sum(sg.goods_storage) AS goods_storage,(sg.goods_salenum+sg.goods_presalenum) AS sale_num,ds.by_post FROM 718shop_buy_deliver_goods sbdg LEFT JOIN 718shop_goods sg ON sbdg.goods_id=sg.goods_id LEFT JOIN (SELECT 718shop_daddress.address_id,718shop_storage.by_post FROM 718shop_daddress LEFT JOIN 718shop_storage ON 718shop_daddress.storage_id = 718shop_storage.storage_id) ds ON ds.address_id = sg.deliverer_id LEFT JOIN 718shop_goods_promotion sgp ON sbdg.goods_id=sgp.goods_id WHERE sbdg.ziti_id=".$ziti_id." AND sg.goods_verify=1 AND sg.goods_state=1 AND sg.is_deleted=0".$xinren_str." GROUP BY sg.goods_id ORDER BY ".$sortorder." limit 10 offset ".$offset;
        $goods_list = Model()->query($sql);
        if($goods_list && is_array($goods_list)){
            foreach($goods_list as $key=>$goods){
                //通过goods_id在活动表中查询最低价格以及标签，最多三个
                // $sql = "SELECT sgp.goods_id,sgp.promotion_type,sgp.price FROM 718shop_goods_promotion sgp WHERE sgp.goods_id=".$goods['goods_id']." AND sgp.promotion_type !=50 AND CASE sgp.promotion_type WHEN 30 THEN sgp.member_levels<=".$member_grade." ELSE 1 END GROUP BY sgp.promotion_type ORDER BY sgp.price ASC LIMIT 3";
                $sql = "SELECT a.* FROM ((SELECT sgp.promotion_type,sgp.goods_id,sgp.price,sgp.goods_promotion_id FROM 718shop_goods_promotion sgp WHERE sgp.goods_id=".$goods['goods_id']."  ".$xinren_str." AND sgp.promotion_type !=30) UNION (SELECT sgp1.promotion_type,sgp1.goods_id,sgp1.price,sgp1.goods_promotion_id FROM 718shop_goods_promotion sgp1 WHERE sgp1.goods_id=".$goods['goods_id']." AND sgp1.promotion_type=30 AND sgp1.member_levels<=".$member_grade." ORDER BY sgp1.price LIMIT 1)) a GROUP BY a.promotion_type ORDER BY a.price ASC,a.goods_promotion_id ASC LIMIT 3";
                $goods_promotion_info = Model()->query($sql);
                if(count($goods_promotion_info)==3){
                    $goods_list[$key]['promotion_name'] = promotion_typeName($goods_promotion_info[0]['promotion_type']).'/'.promotion_typeName($goods_promotion_info[1]['promotion_type']).'/'.promotion_typeName($goods_promotion_info[2]['promotion_type']);
                    $goods_list[$key]['promotion_type'] = $goods_promotion_info[0]['promotion_type'];
                    $goods_list[$key]['promotion_name1'] = promotion_typeName($goods_promotion_info[0]['promotion_type']);
                    $goods_list[$key]['promotion_name2'] = promotion_typeName($goods_promotion_info[1]['promotion_type']);
                    $goods_list[$key]['promotion_name3'] = promotion_typeName($goods_promotion_info[2]['promotion_type']);
                    //会员折扣率计算
                    if($goods_promotion_info[0]['promotion_type'] == 30){
                        $discount = number_format($goods_promotion_info[0]['price'] / $goods['goods_price'],2) * 10;
                        $goods_list[$key]['hui_discount'] = $discount.'折';
                    }
                }elseif(count($goods_promotion_info)==2){
                    $goods_list[$key]['promotion_name'] = promotion_typeName($goods_promotion_info[0]['promotion_type']).'/'.promotion_typeName($goods_promotion_info[1]['promotion_type']);
                    $goods_list[$key]['promotion_type'] = $goods_promotion_info[0]['promotion_type'];
                    $goods_list[$key]['promotion_name1'] = promotion_typeName($goods_promotion_info[0]['promotion_type']);
                    $goods_list[$key]['promotion_name2'] = promotion_typeName($goods_promotion_info[1]['promotion_type']);
                    //会员折扣率计算
                    if($goods_promotion_info[0]['promotion_type'] == 30){
                        $discount = number_format($goods_promotion_info[0]['price'] / $goods['goods_price'],2) * 10;
                        $goods_list[$key]['hui_discount'] = $discount.'折';
                    }
                }elseif(count($goods_promotion_info)==1){
                    $goods_list[$key]['promotion_name'] = promotion_typeName($goods_promotion_info[0]['promotion_type']);
                    $goods_list[$key]['promotion_type'] = $goods_promotion_info[0]['promotion_type'];
                    $goods_list[$key]['promotion_name1'] = promotion_typeName($goods_promotion_info[0]['promotion_type']);
                    //会员折扣率计算
                    if($goods_promotion_info[0]['promotion_type'] == 30){
                        $discount = number_format($goods_promotion_info[0]['price'] / $goods['goods_price'],2) * 10;
                        $goods_list[$key]['hui_discount'] = $discount.'折';
                    }
                }else{
                    $goods_list[$key]['promotion_name'] = '';//普通商品
                    $goods_list[$key]['promotion_type'] = 0;
                }
                $goods_list[$key]['promotion_length'] = count($goods_promotion_info);//长度
                $goods_list[$key]['goods_image'] = cthumb($goods['goods_image']);
                $goods_list[$key]['image_url'] = cthumb($goods['goods_image']);
                $goods_list[$key]['member_grade'] = $member_grade;
                //划线价格优化
                if($goods['goods_promotion_price']>0 && $goods['goods_price']==$goods['goods_promotion_price']){
                    $goods_list[$key]['goods_price'] = $goods['goods_marketprice'];
                }
            }
        }

        if (count($goods_list)<=10 && $_GET['pagecount'] != 0) {
            $list['end'] = 0;
        } else {
            $list['end'] = 1;
        }
        
        if ($goods_list) {
            $list['goods_list'] = $goods_list;
            die(json_encode(array('code' => '10000', 'message' => 'succ', 'data' => $list), 320));
        } else {
            die(json_encode(array('code' => '10001', 'message' => 'fail', 'data' => []), 320));
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