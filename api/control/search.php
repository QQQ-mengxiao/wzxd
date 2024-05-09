<?php
defined('In718Shop') or exit('Access Invalid!');
class searchControl extends BaseControl{

    public function indexOp()
    {
        //全部商品对应cate_id为0
        if ($_POST['keyword'] === '全部商品') {
            unset($_POST['cate_id']);
            unset($_POST['keyword']);
            $allgoods = 1;
        }
        $num            = $_POST['num_page']?$_POST['num_page']:1;
        $sortkey        = $_POST['key'];
        $postorder      = $_POST['order'];
        $ziti_id        = $_POST['ziti_id']?$_POST['ziti_id']:3;
        $keyword        = $_POST['keyword'];
        $cate_id        = $_POST['cate_id'];
        $ngoods_sign    = $_POST['ngoods_sign'];
        $offset         = ($num-1)*10;
        $member_id      = $_POST['member_id'];
        //包邮专区
        if ($_POST['keyword'] === '邮寄商品') {
            unset($_POST['cate_id']);
            unset($_POST['keyword']);
            $by_post = 1;
        }

        $member_grade   = $member_id?(Model('member')->getGrade($member_id))+1:-1;//用户等级
        $member_info    = Model()->table('member')->where(array('member_id' => $member_id, 'is_xinren' => 1))->find();
        if ($member_info) { //是新人
            $xinren_str = "";
        } else { //非新人
            $xinren_str = " AND sgp.promotion_type != 50 ";
        }

        $model_goods = Model('goods');
        $goods_list = $model_goods->getSearchGoodsList($keyword, $cate_id, $ziti_id,  $offset, $sortkey, $postorder, $ngoods_sign, $allgoods,$member_grade,$xinren_str,$by_post);

        if($goods_list && is_array($goods_list)){
            foreach($goods_list as $key=>$goods){
                //通过goods_id在活动表中查询最低价格以及标签，最多三个
                // $sql = "SELECT a.*FROM ((SELECT goods_id,promotion_type,price FROM 718shop_goods_promotion WHERE goods_id=".$goods['goods_id']." AND promotion_type !=30 GROUP BY promotion_type) UNION (SELECT goods_id,promotion_type,price FROM 718shop_goods_promotion WHERE goods_id=".$goods['goods_id']." AND promotion_type=30 ORDER BY price ASC LIMIT 1)) a ORDER BY a.price ASC LIMIT 3";
                $sql = "SELECT a.* FROM ((SELECT sgp.promotion_type,sgp.goods_id,sgp.price,sgp.goods_promotion_id,sgp.promotion_type_id FROM 718shop_goods_promotion sgp WHERE sgp.goods_id=".$goods['goods_id']."  ".$xinren_str." AND sgp.promotion_type !=30) UNION (SELECT sgp1.promotion_type,sgp1.goods_id,sgp1.price,sgp1.goods_promotion_id,sgp1.promotion_type_id FROM 718shop_goods_promotion sgp1 WHERE sgp1.goods_id=".$goods['goods_id']." AND sgp1.promotion_type=30 AND sgp1.member_levels<=".$member_grade." ORDER BY sgp1.price LIMIT 1)) a GROUP BY a.promotion_type ORDER BY a.price ASC,a.goods_promotion_id ASC LIMIT 3";
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
                    $goods_list[$key]['goods_promotion_price'] = $goods['goods_price'];
                    $goods_list[$key]['promotion_type'] = 0;
                }
                //增加新人活动商品id
                // if (is_array($goods_promotion_info)) {
                //     foreach ($goods_promotion_info as $key => $promotion_info) {
                //         if ($promotion_info['promotion_type']==50) {
                //            $goods_list[$key]['xinren_goods_id'] = $promotion_info['promotion_type_id'];
                //         }
                //     }
                // }
                $goods_list[$key]['promotion_length'] = count($goods_promotion_info);//长度
                $goods_list[$key]['goods_image'] = cthumb($goods['goods_image']);
                $goods_list[$key]['member_grade'] = $member_grade;
                if($goods_list[$key]['goods_promotion_price'] == $goods['goods_price'] && $goods_list[$key]['goods_promotion_price']>0){
                    $goods_list[$key]['goods_price'] = $goods['goods_marketprice'];
                }
            }
        }
        if (count($goods_list)<=10 && count($goods_list)>0 && $num != 0) {
            $list['end'] = 0;
            $list['goods_data'] = $goods_list;
        } else {
            $list['end'] = 1;
            $list['goods_data'] = [];
        }

        $res = array('code' => '100', 'message' => 'success', 'data' => $list);
        echo json_encode($res, 320);
    }
}
