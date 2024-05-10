<?php
defined('In718Shop') or exit('Access Invalid!');
class ladder_priceControl extends BaseControl
{
    //每页显示商品数
    const PAGESIZE = 12;
    //页数
    const PAGENUM = 1;

    /* 阶梯价(进口商品)首页
     */
    public function ladder_priceOp()
    {
        $member_id      = $_POST['member_id'];
        $member_grade   = $member_id?(Model('member')->getGrade($member_id))+1:-1;//用户等级
        $member_info    = Model()->table('member')->where(array('member_id' => $member_id, 'is_xinren' => 1))->find();
        $ziti_id        = $_POST['ziti_id']?$_POST['ziti_id']:3;

        if ($member_info) { //是新人
            $xinren_str = "";
        } else { //非新人
            $xinren_str = " AND sgp.promotion_type != 50 ";
        }

        $sortorder = "(CASE WHEN goods_storage = 0 THEN 1 ELSE 0 END),goods_storage DESC,goods_id DESC";
        $sql = "SELECT * FROM (SELECT e.goods_commonid,e.goods_id,e.goods_name,MIN(e.goods_promotion_price) AS goods_promotion_price,e.goods_price as goods_price,sum(e.goods_storage) AS goods_storage,sum(e.goods_salenum+e.goods_presalenum) AS sale_num,e.goods_marketprice,e.goods_image,e.is_vip_price FROM (SELECT sg.goods_commonid,sg.goods_name,sg.goods_marketprice,sg.goods_storage,sg.goods_image,sg.goods_salenum,sg.goods_presalenum,sgc.is_vip_price,sg.goods_id,sbdg.buy_deliver_goods_id,ifnull(d.price,sg.goods_price) AS goods_promotion_price,sg.goods_price FROM 718shop_goods sg INNER JOIN (SELECT DISTINCT sg.goods_commonid FROM 718shop_goods sg) g ON g.goods_commonid=sg.goods_commonid LEFT JOIN (SELECT sgp.goods_id,min(price) AS price FROM 718shop_goods_promotion sgp WHERE CASE sgp.promotion_type WHEN 30 THEN sgp.member_levels<=".$member_grade." ELSE 1 END ".$xinren_str." GROUP BY sgp.goods_id) d ON d.goods_id=sg.goods_id LEFT JOIN 718shop_buy_deliver_goods sbdg ON sg.goods_id=sbdg.goods_id LEFT JOIN 718shop_goods_common sgc ON sg.goods_commonid=sgc.goods_commonid WHERE sg.goods_state=1 AND sg.p_ladder_id>0 AND (sbdg.ziti_id=".$ziti_id." OR sbdg.ziti_id IS NULL) AND sg.goods_verify=1 AND sg.is_deleted=0 ORDER BY goods_promotion_price LIMIT 99999) e GROUP BY goods_commonid) a ORDER BY".$sortorder." limit 12";

        $goods_list = Model()->query($sql);
        if($goods_list && is_array($goods_list)){
            foreach($goods_list as $key=>$goods){
                //通过goods_id在活动表中查询最低价格以及标签，最多三个
                // $sql = "SELECT sgp.goods_id,sgp.promotion_type,sgp.price FROM 718shop_goods_promotion sgp WHERE sgp.goods_id=".$goods['goods_id']." AND sgp.promotion_type !=50 AND CASE sgp.promotion_type WHEN 30 THEN sgp.member_levels<=".$member_grade." ELSE 1 END GROUP BY sgp.promotion_type ORDER BY sgp.price ASC LIMIT 3";
                $sql = "SELECT a.*FROM ((SELECT sgp.promotion_type,sgp.goods_id,sgp.price FROM 718shop_goods_promotion sgp WHERE sgp.goods_id=".$goods['goods_id']."  ".$xinren_str." AND sgp.promotion_type !=30) UNION (SELECT sgp1.promotion_type,sgp1.goods_id,sgp1.price FROM 718shop_goods_promotion sgp1 WHERE sgp1.goods_id=".$goods['goods_id']." AND sgp1.promotion_type=30 AND sgp1.member_levels<=".$member_grade." ORDER BY sgp1.price LIMIT 1)) a ORDER BY a.price ASC LIMIT 3";
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
                $goods_list[$key]['member_grade'] = $member_grade;
            }
        }

        if ($goods_list) {
            $list['xs_goods_list'] = $goods_list;
            die(json_encode(array('code' => '100', 'message' => 'succ', 'data' => $list), 320));
        } else {
            die(json_encode(array('code' => '200', 'message' => 'fail', 'data' => []), 320));
        }
    }
    /* 阶梯价(进口商品)
     */
    public function ladder_price_listOp()
    {
        $member_id      = $_POST['member_id'];
        $member_grade   = $member_id?(Model('member')->getGrade($member_id))+1:-1;//用户等级
        $member_info    = Model()->table('member')->where(array('member_id' => $member_id, 'is_xinren' => 1))->find();
        $postorder      = $_POST['order']?$_POST['order']:'false';
        $sortkey        = $_POST['key']?$_POST['key']:2;
        $ziti_id        = $_POST['ziti_id']?$_POST['ziti_id']:3;
        if ($_POST['num_page']) {
            $offset     = (intval($_POST['num_page'])-1)*10;
        }else{
            $offset     = 0;
        }
        if ($member_info) { //是新人
            $xinren_str = "";
        } else { //非新人
            $xinren_str = " AND sgp.promotion_type != 50 ";
        }

        $sortorder = "(CASE WHEN goods_storage = 0 THEN 1 ELSE 0 END),goods_storage DESC,goods_id DESC";
        if($sortkey==2){
            if($postorder == 'false'){
                $sortorder = "(CASE WHEN goods_storage = 0 THEN 1 ELSE 0 END),goods_storage DESC,goods_id DESC";
            }else{
                $sortorder = "( CASE WHEN goods_storage = 0 THEN 1 ELSE 0 END ),goods_storage,goods_id DESC";
            }
        }elseif($sortkey==1){
            if($postorder =='false'){
                $sortorder = "( CASE WHEN goods_storage = 0 THEN 1 ELSE 0 END ),sale_num DESC,goods_id DESC";
            }else{
                $sortorder = "( CASE WHEN goods_storage = 0 THEN 1 ELSE 0 END ),sale_num ASC,goods_id DESC";
            }
        }elseif($sortkey==3){
            if($postorder=='false'){
                $sortorder = "( CASE WHEN goods_storage = 0 THEN 1 ELSE 0 END ),goods_price DESC,goods_id DESC";
            }else{
                $sortorder = "( CASE WHEN goods_storage = 0 THEN 1 ELSE 0 END ),goods_price ASC,goods_id DESC";
            }
        }
        $sql = "SELECT * FROM (SELECT e.goods_commonid,e.goods_id,e.goods_name,MIN(e.goods_promotion_price) AS goods_promotion_price,e.goods_price as goods_price,sum(e.goods_storage) AS goods_storage,sum(e.goods_salenum+e.goods_presalenum) AS sale_num,e.goods_marketprice,e.goods_image,e.is_vip_price,e.by_post FROM (SELECT sg.goods_commonid,sg.goods_name,sg.goods_marketprice,sg.goods_storage,sg.goods_image,sg.goods_salenum,sg.goods_presalenum,sgc.is_vip_price,sg.goods_id,sbdg.buy_deliver_goods_id,ifnull(d.price,sg.goods_price) AS goods_promotion_price,sg.goods_price,ds.by_post FROM 718shop_goods sg INNER JOIN (SELECT DISTINCT sg.goods_commonid FROM 718shop_goods sg) g ON g.goods_commonid=sg.goods_commonid LEFT JOIN (SELECT 718shop_daddress.address_id,718shop_storage.by_post FROM 718shop_daddress LEFT JOIN 718shop_storage ON 718shop_daddress.storage_id = 718shop_storage.storage_id) ds ON ds.address_id = sg.deliverer_id LEFT JOIN (SELECT sgp.goods_id,min(price) AS price FROM 718shop_goods_promotion sgp WHERE CASE sgp.promotion_type WHEN 30 THEN sgp.member_levels<=".$member_grade." ELSE 1 END ".$xinren_str." GROUP BY sgp.goods_id) d ON d.goods_id=sg.goods_id LEFT JOIN 718shop_buy_deliver_goods sbdg ON sg.goods_id=sbdg.goods_id LEFT JOIN 718shop_goods_common sgc ON sg.goods_commonid=sgc.goods_commonid WHERE sg.goods_state=1 AND sg.p_ladder_id>0 AND (sbdg.ziti_id=".$ziti_id." OR sbdg.ziti_id IS NULL) AND sg.goods_verify=1 AND sg.is_deleted=0 ORDER BY goods_promotion_price LIMIT 99999) e GROUP BY goods_commonid) a ORDER BY".$sortorder." limit 10 offset ".$offset;

        $goods_list = Model()->query($sql);
        if($goods_list && is_array($goods_list)){
            foreach($goods_list as $key=>$goods){
                //通过goods_id在活动表中查询最低价格以及标签，最多三个
                // $sql = "SELECT sgp.goods_id,sgp.promotion_type,sgp.price FROM 718shop_goods_promotion sgp WHERE sgp.goods_id=".$goods['goods_id']." AND sgp.promotion_type !=50 AND CASE sgp.promotion_type WHEN 30 THEN sgp.member_levels<=".$member_grade." ELSE 1 END GROUP BY sgp.promotion_type ORDER BY sgp.price ASC LIMIT 3";
                $sql = "SELECT a.* FROM ((SELECT sgp.promotion_type,sgp.goods_id,sgp.price,sgp.goods_promotion_id FROM 718shop_goods_promotion sgp WHERE sgp.goods_id=".$goods['goods_id']."  ".$xinren_str." AND sgp.promotion_type !=30) UNION (SELECT sgp1.promotion_type,sgp1.goods_id,sgp1.price,sgp1.goods_promotion_id FROM 718shop_goods_promotion sgp1 WHERE sgp1.goods_id=".$goods['goods_id']." AND sgp1.promotion_type=30 AND sgp1.member_levels<=".$member_grade." ORDER BY sgp1.price LIMIT 1)) a ORDER BY a.price ASC,a.goods_promotion_id ASC LIMIT 3";
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
                $goods_list[$key]['member_grade'] = $member_grade;
            }
        }

        if (count($goods_list)<=10 && $_POST['num_page'] != 0) {
            $list['end'] = 0;
        } else {
            $list['end'] = 1;
        }
        if ($goods_list) {
            $list['xs_goods_list'] = $goods_list;
            die(json_encode(array('code' => '100', 'message' => 'succ', 'data' => $list), 320));
        } else {
            die(json_encode(array('code' => '200', 'message' => 'fail', 'data' => []), 320));
        }
    }
    public function deliver_timeOp()
    {
        $time = $_GET['time'];
        $store_id = $_GET['store_id'];
        $model_mansong = Model('p_ladder');
        $model_mansong_rule = Model('p_ladder_rule');
        $condition = array();
        $condition['store_id'] = intval($store_id);
        $condition['is_default'] = 1;
        $ladder_info = $model_mansong->getMansongInfo($condition);
        // var_dump($ladder_info);die;
        $mansong_id = intval($ladder_info['p_ladder_id']);
        $ladder_rule = $model_mansong_rule->getMansongRuleListByID($mansong_id);
        // var_dump($ladder_info);die;
        // echo date( "h:i ");
        foreach ($ladder_rule as $key => $value) {
            $time_dian[] = $value['time'];
        }
        // var_dump($time_dian);die;
        $a = date('H', time()) + date('s', time()) / 60;
        // $a=2.5;
        $chazhi = $time - $a;
        if ($chazhi < 2) {
            $res = array('code' => '200', 'message' => '配送时限太少', 'data' => $data);
            echo json_encode($res, 320);die;
        }
        $max = max($time_dian);
        if ($max >= $chazhi) {
            $count = count($time_dian);
            for ($i = 0; $i < $count; $i++) {
                $arr2[] = $chazhi - $time_dian[$i];
            }
            for ($i = 0; $i < $count; $i++) {
                if ($arr2[$i] <= 0) {
                    $time = $time_dian[$i];
                    break;
                }
            }
        } else {
            $time = $max;
        }
        foreach ($ladder_rule as $key => $value) {
            if ($time == $value['time']) {
                $data = $value;
            }
        }
        if (!empty($data)) {
            $res = array('code' => '100', 'message' => 'sucess', 'data' => $data);
            echo json_encode($res, 320);die;
        }

    }

}
