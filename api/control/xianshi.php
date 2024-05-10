<?php
defined('In718Shop') or exit('Access Invalid!');

class xianshiControl extends BaseControl
{

    //模型对象
    private $_model_search;
    //每页显示商品评价数
    const PAGESIZE = 10;
    //页数
    const PAGENUM = 1;

    /**
     * 商品列表
     */
    public function goods_listOp()
    {
        $member_id      = $_POST['member_id'];
        $member_grade   = $member_id?(Model('member')->getGrade($member_id))+1:-1;//用户等级
        $member_info    = Model()->table('member')->where(array('member_id' => $member_id, 'is_xinren' => 1))->find();
        $postorder      = $_POST['order'];
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

        $sortorder = "(CASE WHEN sg.goods_storage = 0 THEN 1 ELSE 0 END),goods_sort,goods_id DESC";
        if($sortkey==2){
            if($postorder == 'false'){
                $sortorder = "(CASE WHEN sg.goods_storage = 0 THEN 1 ELSE 0 END),goods_sort,goods_id DESC";
            }else{
                $sortorder = "( CASE WHEN sg.goods_storage = 0 THEN 1 ELSE 0 END ),goods_sort DESC,goods_id DESC";
            }
        }elseif($sortkey==1){
            if($postorder =='false'){
                $sortorder = "( CASE WHEN sg.goods_storage = 0 THEN 1 ELSE 0 END ),sale_num DESC,goods_id DESC";
            }else{
                $sortorder = "( CASE WHEN sg.goods_storage = 0 THEN 1 ELSE 0 END ),sale_num ASC,goods_id DESC";
            }
        }elseif($sortkey==3){
            if($postorder=='false'){
                $sortorder = "( CASE WHEN sg.goods_storage = 0 THEN 1 ELSE 0 END ),goods_promotion_price DESC,goods_id DESC";
            }else{
                $sortorder = "( CASE WHEN sg.goods_storage = 0 THEN 1 ELSE 0 END ),goods_promotion_price ASC,goods_id DESC";
            }
        }

        $sql = "SELECT sg.goods_commonid,spxg.goods_id,IFNULL(min(sgp.price),sg.goods_price) AS goods_promotion_price,sg.goods_price,sg.goods_name,sg.goods_image,sg.goods_storage,(sg.goods_salenum+sg.goods_presalenum) AS sale_num,spxg.goods_sort,sgc.is_vip_price,ds.by_post FROM 718shop_p_xianshi_goods spxg LEFT JOIN 718shop_goods_promotion sgp ON spxg.goods_id=sgp.goods_id LEFT JOIN 718shop_goods sg ON spxg.goods_id=sg.goods_id LEFT JOIN (SELECT 718shop_daddress.address_id,718shop_storage.by_post FROM 718shop_daddress LEFT JOIN 718shop_storage ON 718shop_daddress.storage_id = 718shop_storage.storage_id) ds ON ds.address_id = sg.deliverer_id LEFT JOIN 718shop_goods_common sgc ON sg.goods_commonid=sgc.goods_commonid LEFT JOIN 718shop_buy_deliver_goods sbdg ON sg.goods_id=sbdg.goods_id LEFT JOIN 718shop_p_xianshi spx ON spxg.xianshi_id = spx.xianshi_id WHERE UNIX_TIMESTAMP() BETWEEN spxg.start_time AND spxg.end_time AND spx.xianshi_type = 1 AND sg.goods_state=1 AND (sbdg.ziti_id=".$ziti_id." OR sbdg.ziti_id IS NULL) AND sg.goods_verify=1 AND sg.is_deleted=0 ".$xinren_str." AND CASE sgp.promotion_type WHEN 30 THEN sgp.member_levels<=".$member_grade." ELSE 1 END GROUP BY spxg.goods_id ORDER BY ".$sortorder." limit 10 offset ".$offset;
        $goods_list = Model()->query($sql);
        if($goods_list && is_array($goods_list)){
            foreach($goods_list as $key=>$goods){
                //通过goods_id在活动表中查询最低价格以及标签，最多三个
                $sql = "SELECT a.* FROM ((SELECT sgp.promotion_type,sgp.goods_id,sgp.price,sgp.goods_promotion_id FROM 718shop_goods_promotion sgp WHERE sgp.goods_id=".$goods['goods_id']."  ".$xinren_str." AND sgp.promotion_type !=30) UNION (SELECT sgp1.promotion_type,sgp1.goods_id,sgp1.price,sgp1.goods_promotion_id FROM 718shop_goods_promotion sgp1 WHERE sgp1.goods_id=".$goods['goods_id']." AND sgp1.promotion_type=30 AND sgp1.member_levels<=".$member_grade." ORDER BY sgp1.price LIMIT 1)) a ORDER BY a.price ASC,a.goods_promotion_id ASC LIMIT 3";
                // print_r($sql);
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
                $goods_list[$key]['image_url_240'] = cthumb($goods['goods_image']);
                $goods_list[$key]['member_grade'] = $member_grade;
                $goods_list[$key]['mx'] = 'ceshi';
            }
        }

        if ((count($goods_list)<=10 && $_POST['num_page'] != 0) || $_POST['num_page']==1) {
            $list['end'] = 0;
        } else {
            $list['end'] = 1;
        }
        
        if ($goods_list) {
            $list['xs_goods_list'] = $goods_list;
            die(json_encode(array('code' => '1', 'message' => 'succ', 'data' => $list), 320));
        } else {
            die(json_encode(array('code' => '-1', 'message' => 'fail', 'data' => []), 320));
        }
    }

}