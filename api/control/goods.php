<?php
defined('In718Shop') or exit('Access Invalid!');
include 'imgcompress.php';
class goodsControl extends BaseControl
{

    //模型对象
    private $_model_search;
    //每页显示商品评价数
    const PAGESIZE = 10;

    public function imgcom($goods_detail)
    {
    
        if (is_array($goods_detail['goods_image'])) {
            foreach ($goods_detail['goods_image'] as $key => $image_info) {
                foreach ($image_info as $k => $image_url) {
                    $image_name = explode('/', $image_url); //$image_name[count($image_name)-1]是图片名字
                    $image_name_detail = explode('_', $image_name[count($image_name) - 1]); //$image_name_detail[0]是4，$image_name_detail[1]是图片名字06641346819965691
                    $suffix = explode('.', $image_name_detail[count($image_name_detail) - 1]); //$suffix[count($suffix)-1]是图片后缀
                    $new_image = UPLOAD_SITE_URL . '/' . ATTACH_GOODS . '/4/' . $image_name_detail[0] . '_' . $image_name_detail[1] . '_00.' . $suffix[count($suffix) - 1]; //print_r($new_image);echo '---';//echo UPLOAD_SITE_URL;
                    if (!file_exists($new_image)) {
                        if ($image_name_detail[0] == 4) {
                            $source = UPLOAD_SITE_URL . '/' . ATTACH_GOODS . '/4/' . $image_name_detail[0] . '_' . $image_name_detail[1] . '_1280.' . $suffix[count($suffix) - 1];
                        } else {
                            $source = UPLOAD_SITE_URL . '/' . ATTACH_GOODS . '/4/' . 4 . '_' . $image_name_detail[0] . '_1280.' . $suffix[count($suffix) - 1];
                        }

                        $dst_img = $new_image;
                        $percent = 1; #原图压缩，不缩放
                        $imgcompress = new imgcompress(); //echo $source;die;
                        // $image_com = $imgcompress->mximage($source, $percent=1,$dst_img);
                    }
                    $goods_detail['goods_image'][$key][$k] = UPLOAD_SITE_URL . '/' . ATTACH_GOODS . '/4/' . $image_name_detail[0] . '_' . $image_name_detail[1] . '_1280.' . $suffix[count($suffix) - 1];
                }
            }
        }
        return $goods_detail;
    }

    public function indexOp()
    {
        $goods_id = intval($_POST['goods_id']);
        $member_id = intval($_POST['member_id']);
        // 商品详细信息
        $model_goods = Model('goods');
        $goods_detail = $model_goods->getGoodsDetail($goods_id);
        $goods_commonid = $goods_detail['goods_info']['goods_commonid'];
        $goods_spec_value = Model()->table('goods_common')->getfby_goods_commonid($goods_commonid, 'spec_value');

        //活动叠加
        $member_grade = $member_id ? (Model('member')->getGrade($member_id))+1 : -1; //用户等级
        $member_info = Model()->table('member')->where(array('member_id' => $member_id, 'is_xinren' => 1))->find();
        if ($member_info) { //是新人
            $xinren_str = "";
            $xinren_str1 = "";
        } else { //非新人
            $xinren_str = " AND sgp.promotion_type != 50 ";
            $xinren_str1 = " WHERE promotion_type !=50 ";
        }

        $sql = "SELECT sgp.goods_id,sgp.price,sgp.promotion_type,sgp.upper_limit,sgp.end_time FROM 718shop_goods_promotion sgp WHERE sgp.goods_id=" . $goods_id . " AND CASE sgp.promotion_type WHEN 30 THEN sgp.member_levels<= " . $member_grade . " ELSE 1 END " . $xinren_str . " ORDER BY sgp.price ASC,sgp.goods_promotion_id ASC LIMIT 1";
        $goods_poromotion_info = Model()->query($sql);
        $goods_detail['goods_info']['promotion_type'] = $goods_poromotion_info[0]['promotion_type'] ? $goods_poromotion_info[0]['promotion_type'] : 0; //活动类型
        $goods_detail['goods_info']['goods_promotion_price'] = $goods_poromotion_info[0]['price'] ? $goods_poromotion_info[0]['price'] : $goods_detail['goods_info']['goods_price']; //活动价格
        $goods_detail['goods_info']['promotion_price'] = $goods_poromotion_info[0]['price'] ? $goods_poromotion_info[0]['price'] : $goods_detail['goods_info']['goods_price']; //活动价格
        $goods_detail['goods_info']['upper_limit'] = $goods_poromotion_info[0]['upper_limit'] ? $goods_poromotion_info[0]['upper_limit'] : 0; //购买上限，0不限制
        $goods_detail['goods_info']['end_time'] = $goods_poromotion_info[0]['end_time'] ? $goods_poromotion_info[0]['end_time'] : 0; //结束时间
        $goods_detail['goods_info']['member_grade'] = $member_grade; //会员等级
         //会员折扣率计算
        if ($goods_poromotion_info[0]['promotion_type']  == 30){
            $discount = number_format($goods_poromotion_info[0]['price'] / $goods_detail['goods_info']['goods_price'],2) * 10;
            $goods_detail['goods_info']['hui_discount'] = $discount.'折';
        }
        //划线价格优化
        if(!$goods_poromotion_info[0]['price'] || $goods_detail['goods_info']['goods_price']==$goods_poromotion_info[0]['price']){
            $goods_detail['goods_info']['goods_price'] = $goods_detail['goods_info']['goods_marketprice'];
        }

        //04072156mx暂时处理
        $goods_detail = $this->imgcom($goods_detail);

        $goods_info = $goods_detail['goods_info'];
        $goods_info['goods_image'] = cthumb($goods_info['goods_image']);

        if ($goods_info['goods_state'] != 1 || $goods_info['goods_verify'] != 1) {
            $message = 'fail';
            $res = array('code' => '200', 'message' => $message, 'data' => $goods_info);
            echo json_encode($res, 320);exit();
        }
        if (empty($goods_info)) {
            $message = 'fail';
            $res = array('code' => '200', 'message' => $message, 'data' => $goods_info);
            echo json_encode($res, 320);exit();
        }
        $rs = $model_goods->getGoodsList(array('goods_commonid' => $goods_info['goods_commonid']));
        $count = 0;
        foreach ($rs as $v) {
            $count += $v['goods_salenum'];
        }
        $goods_info['goods_salenum'] = $count;
        $goods_info['goods_presalenum'] = $goods_info['goods_presalenum'] + $count;
        if (!is_array($goods_detail['goods_image'][0])) {
            $goods_detail['goods_image'][0] = explode(",", $goods_detail['goods_image'][0]);
        }

        if (!empty($goods_info['deliverer_id'])) {
            $deliverer = Model('daddress')->where(array('address_id' => $goods_info['deliverer_id']))->find();
        } else {
            $deliverer = [];
        }

        //判断是否有可领取的品类券或者商品券
        $where = array();
        $where['voucher_t_state'] = 1;
        $where['voucher_t_end_date'] = array('gt', time());
        $where['voucher_t_type'] = array('gt', 0);
        $recommend_voucher = Model('voucher')->getVoucherTemplateList($where, $field = '*', 0, 0, 'voucher_t_recommend desc,voucher_t_id desc');
        foreach ($recommend_voucher as $key => $value) {
            $recommend_voucher[$key]['voucher_t_start_date'] = date("Y-m-d H:i", $value['voucher_t_start_date']);
            $recommend_voucher[$key]['voucher_t_end_date'] = date("Y-m-d H:i", $value['voucher_t_end_date']);
            $condition = array();
            $voucher_type = Model('voucher_type');
            $condition['voucher_tid'] = $value['voucher_t_id'];
            $type_info = $voucher_type->getvouchertypeInfo($condition);
            if ($type_info['type'] == 1) {
                $goodsclass_arr = explode(',', $type_info['goodsclass_id']);
                $goods_gc_idinfo= $model_goods->getGoodsInfoByID($goods_id);
             if($_POST['test']==1) {
                var_dump( $goodsclass_arr);die;
              } 
                if ($type_info['is_use'] == 1) {
                    if (!in_array($goods_gc_idinfo['gc_id_3'], $goodsclass_arr) || empty($goods_gc_idinfo)) {
                        unset($recommend_voucher[$key]);
                    }
                } else {
                    if (in_array($goods_gc_idinfo['gc_id_3'], $goodsclass_arr) || empty($goods_gc_idinfo)) {
                        unset($recommend_voucher[$key]);
                    }
                }

            } else {
                $goods_arr = explode(',', $type_info['goods_id']);

                if ($type_info['is_use'] == 1) {
                    if (!in_array($goods_id, $goods_arr)) {
                        unset($recommend_voucher[$key]);
                    }
                } else {
                    if (in_array($goods_id, $goods_arr)) {
                        unset($recommend_voucher[$key]);
                    }
                }
            }
            if ($value['voucher_t_display'] != 0) {
                unset($recommend_voucher[$key]);
            }
        }
       
        if (!empty($recommend_voucher) && count($recommend_voucher) > 0 && $goods_detail['goods_info']['promotion_type']!=10) {
            $goods_info['is_voucher'] = 1;
        } else {
            $goods_info['is_voucher'] = 0;
        }
           // 

        //抛出已购买人信息
        $model_order = Model('order');
        $sales = $model_order->getOrderAndOrderGoodsSalesRecordList(array('order_goods.goods_id' => $goods_id), 'order_goods.*, order.buyer_name, order.add_time', 10);
        $buy_numbers_list = array();
        foreach ($sales as $key => $value) {
            $buy_numbers = Model('member')->where(array('member_id' => $value['buyer_id']))->field('member_name,member_avatar')->select();
            $buy_numbers_list[$value['buyer_id']]['member_avatar'] = UPLOAD_SITE_URL . DS . ATTACH_AVATAR . DS . $buy_numbers[0]['member_avatar'];
            $buy_numbers_list[$value['buyer_id']]['member_name'] = mb_substr($buy_numbers[0]['member_name'], 0, 1, 'utf-8') . "***" . mb_substr($buy_numbers[0]['member_name'], -1, 1, 'utf-8');
        }
        // $buy_numbers_list=array_slice($buy_numbers_list,0,3);//只取三个
        $buy_numbers_list = array_values($buy_numbers_list);
        $goods_detail['buy_numbers_list'] = $buy_numbers_list;

        //小程序码
        $payment_code = 'wxpay_jsapi';
        $condition = array();
        $condition['payment_code'] = $payment_code;
        $payment_info = Model()->table('mb_payment')->where($condition)->find();
        $appletcode = $payment_info['appletcode'];
        $goods_detail['appletcode'] = UPLOAD_SITE_URL . DS . ATTACH_MOBILE . '/appletcode/' . $appletcode;

        $goods_info = array_merge($goods_info, $deliverer);

        $model_store_plate = Model('store_plate');
        if ($goods_info['plateid_top'] != 0) {
            $plated_top = $model_store_plate->getStorePlateInfoByID($goods_info['plateid_top']);
            $goods_info['mobile_body'] = $plated_top['plate_content'] . $goods_info['mobile_body'];
            //$goods_info['plate_top'] = $plated_top['plate_content'];
        }
        if ($goods_info['plateid_bottom'] != 0) {
            $plate_bottom = $model_store_plate->getStorePlateInfoByID($goods_info['plateid_bottom']);
            $goods_info['mobile_body'] = $goods_info['mobile_body'] . $plate_bottom['plate_content'];
            //$goods_info['plate_bottom'] = $plate_bottom['plate_content'];
        }
        //ip替换
        $goods_info['mobile_body'] = str_replace('219.157.200.55', '117.159.3.227', $goods_info['mobile_body']);

        //规格
        $speclist = [];
        $spec = implode(',', array_keys(array_values(unserialize($goods_spec_value))[0]));
        if ($spec) {
            $spec_str = "AND sg.color_id IN (" . $spec . ")";
            $spec_arr = array_values(unserialize($goods_spec_value))[0];
            $sql_spec = "SELECT sg.goods_id AS id,sg.goods_price,sg.goods_marketprice,sg.goods_commonid,sg.goods_image,sg.color_id,sg.goods_storage,min(sgp.price) AS goods_promotion_price FROM 718shop_goods sg LEFT JOIN 718shop_goods_common sgc ON sg.goods_commonid=sgc.goods_commonid LEFT JOIN (SELECT goods_id,price,member_levels,promotion_type FROM 718shop_goods_promotion ".$xinren_str1.") sgp ON sg.goods_id=sgp.goods_id WHERE sg.goods_commonid=(SELECT goods_commonid FROM 718shop_goods WHERE goods_verify=1 AND goods_state=1 AND is_deleted=0 AND goods_id=" . $goods_id . " LIMIT 1) " . $spec_str . " AND CASE sgp.promotion_type WHEN 30 THEN sgp.member_levels<= " . $member_grade . " ELSE 1 END GROUP BY id";
            $spec_list = Model()->query($sql_spec);
            if ($spec_list) {
                foreach ($spec_list as $key => $specInfo) {
                    $spec_list[$key]['goods_image'] = cthumb($specInfo['goods_image']);
                    $spec_list[$key]['goods_price'] = $specInfo['goods_price'];
                    $spec_list[$key]['specname'] = $spec_arr[$specInfo['color_id']];
                    if ($specInfo['color_id'] == $goods_info['color_id']) {
                        $spec_list[$key]['is_select'] = true;
                    } else {
                        $spec_list[$key]['is_select'] = false;
                    }
                    $spec_list[$key]['goods_promotion_price'] = $specInfo['goods_promotion_price']?$specInfo['goods_promotion_price']:$specInfo['goods_price'];
                    $sql_type = "SELECT sgp.promotion_type FROM 718shop_goods_promotion sgp WHERE sgp.goods_id=" . $specInfo['id'] . " AND sgp.price=" . $specInfo['goods_promotion_price'] . " AND CASE sgp.promotion_type WHEN 30 THEN sgp.member_levels<= " . $member_grade . " ELSE 1 END " . $xinren_str . " order by sgp.goods_promotion_id asc limit 1";
                    $spec_promotion_type = Model()->query($sql_type)[0]['promotion_type'];
                    $spec_list[$key]['promotion_type'] = $spec_promotion_type?$spec_promotion_type:0;
                     if($spec_list[$key]['promotion_type']<30){
                        $model_goodspromotion=Model('goods_promotion'); 
                        $arr_goodspromotion=array();
                        $arr_goodspromotion['goods_id']=$specInfo['id'];
                        $arr_goodspromotion['promotion_type']=$spec_list[$key]['promotion_type'];
                        $info=$model_goodspromotion->getgoods_promotionInfo($arr_goodspromotion);
                        $spec_list[$key]['upper_limit']=$info['upper_limit'];
                    }else{
                        $spec_list[$key]['upper_limit']=0;
                    }
                    $spec_list[$key]['promotion_name'] = $spec_promotion_type?promotion_typeName($spec_promotion_type):'';
                    if($specInfo['goods_promotion_price']==$specInfo['goods_price'] || !$specInfo['goods_promotion_price']){
                        $spec_list[$key]['goods_price'] = $specInfo['goods_marketprice'];
                    }
                }
                $sp_id = Model('spec_value')->getfby_sp_value_id($goods_info['color_id'], 'sp_id');
                $sp_name = Model('spec')->getfby_sp_id($sp_id, 'sp_name');
                $spec_info[] = [
                    'childCurGoods' => $spec_list,
                    'name' => $sp_name,
                ];
                $speclist[] = $spec_info;
            }
        }

        $goods_info['speclist'] = $speclist[0];
        //会员价
        $goods_commond_info = Model()->table('goods_common')->field('is_vip_price')->where(array('goods_commonid' => $goods_info['goods_commonid']))->find();
        $is_vip_price = $goods_commond_info['is_vip_price'];
        $goods_detail['goods_info']['is_vip_price'] = $is_vip_price; 
        
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
        

        $goods_detail['goods_info'] = $goods_info;
        if ($goods_detail) {
            $message = 'success';
            $res = array('code' => '100', 'message' => $message, 'data' => $goods_detail);
            echo json_encode($res, 320);
        } else {
            $message = 'fail';
            $res = array('code' => '200', 'message' => $message, 'data' => $goods_detail);
            echo json_encode($res, 320);
        }

    }

    /*
     * 商品评论 查询好评中评差评 列表1：好评 2：中评 3：差评
     */
    public function commentsOp()
    {
        $page_num = self::PAGESIZE;
        if (isset($_POST['num_page'])) {
            $num = intval($_POST['num_page'] * $page_num);
        } else {
            $num = $page_num;
        }
        $goods_id = intval($_POST['goods_id']);
        $goods_commmonid = Model()->table('goods')->getfby_goods_id($goods_id,'goods_commonid');

        $get_comments = $this->_get_comments1($goods_commmonid, $_POST['type'], $num);

        foreach ($get_comments as $key => $value) {
            if (empty($value['geval_image'])) {
                $get_comments[$key]['geval_image'] = null;
            }
            $geval_goodsimage_url = cthumb($value['geval_goodsimage'], 60);
            $get_comments[$key]['geval_goodsimage'] = $geval_goodsimage_url;
            $model = Model();
            $buyer_id = $model->table('order')->where(array('order_id' => $value['geval_orderid']))->field('buyer_id')->find();
            $comments_member = $model->table('member')->where(array('member_id' => $buyer_id['buyer_id']))->field('member_name,member_avatar')->find();
            $get_comments[$key]['comments_member_name'] = $comments_member['member_name'];

            if (empty($comments_member['member_avatar'])) {
                $get_comments[$key]['comments_member_avatar'] = UPLOAD_SITE_URL . '/' . ATTACH_COMMON . DS . C('default_user_portrait');
            } else {
                $get_comments[$key]['comments_member_avatar'] = UPLOAD_SITE_URL . DS . ATTACH_AVATAR . DS . $comments_member['member_avatar'];
            }
        }
        $goods_evaluate_info = Model('evaluate_goods')->getEvaluateGoodsInfoByCommonidID($goods_commmonid);
        $pagecount = intval($goods_evaluate_info['all'] / $page_num) + 1;
        $list['comments_data'] = $get_comments;
        $list['pageCount'] = $pagecount;
        $list['goods_evaluate_info'] = $goods_evaluate_info;

        if ($list) {
            if ($num > $goods_evaluate_info['all']) {
                $message = '没有更多待加载数据';
            } else {
                $message = 'sucess';
            }
            $res = array('code' => '100', 'message' => $message, 'data' => $list);
            echo json_encode($res, 320);
        } else {
            $message = 'fail';
            $res = array('code' => '200', 'message' => $message, 'data' => $list);
            echo json_encode($res, 320);
        }
    }


    private function _get_comments1($goods_commmonid, $type, $page)
    {
        $goods_id_arr = Model()->table('goods')->field('goods_id')->where(['goods_commonid'=>$goods_commmonid])->select();
        $condition = array();
        $condition['geval_goodsid'] = array('in', array_column($goods_id_arr,'goods_id'));
        switch ($type) {
            case '1':
                $condition['geval_scores'] = array('in', '5,4');
                break;
            case '2':
                $condition['geval_scores'] = array('in', '3,2');
                break;
            case '3':
                $condition['geval_scores'] = array('in', '1');
                break;
            default:
                break;
        }
        //查询商品评分信息
        $model_evaluate_goods = Model("evaluate_goods");
        $goodsevallist = $model_evaluate_goods->getEvaluateGoodsList($condition, $page);
        return $goodsevallist;
    }

    /* 我的——评价图片访问的绝对路径
     */
    public function evaluate_url($evaluate_image, $goods_id)
    {
        // define('EVALUATE_IMAGES_XCX',UPLOAD_SITE_URL.'/upload/xcx/evaluate/');
        $evaluate_url = UPLOAD_SITE_URL . '/xcx/evaluate' . '/' . $goods_id . '/' . $evaluate_image;
        return $evaluate_url;
    }
    /**
     * 商品评价概述
     */
    public function comments_listOp()
    {
        $goods_id = intval($_POST['goods_id']);

        // 商品详细信息
        $model_goods = Model('goods');
        $goods_info = $model_goods->getGoodsInfoByID($goods_id, '*');
        // 验证商品是否存在
        if (empty($goods_info)) {
            $message = 'fail';
            $res = array('code' => '200', 'message' => $message, 'data' => null);
            echo json_encode($res, 320);
        }
        //评价信息
        $goods_evaluate_info = Model('evaluate_goods')->getEvaluateGoodsInfoByGoodsID($goods_id);
        if ($goods_evaluate_info) {
            $message = 'success';
            $res = array('code' => '100', 'message' => $message, 'data' => $goods_evaluate_info);
            echo json_encode($res, 320);
        } else {
            $message = 'fail';
            $res = array('code' => '200', 'message' => $message, 'data' => $goods_evaluate_info);
            echo json_encode($res, 320);
        }
    }

    /**
     * 商品分享
     */
    public function goods_shareOp()
    {
        $goods_id = intval($_POST['goods_id']);
        // 分享获取商品详细信息
        $model_goods = Model('goods');
        $goods_detail = $model_goods->getGoodsDetail($goods_id);

        $goods_info = $goods_detail['goods_info'];
        $goods_info['goods_image'] = cthumb($goods_info['goods_image'], 60);
        if ($goods_info) {
            $message = 'success';
            $res = array('code' => '100', 'message' => $message, 'data' => $goods_info);
            echo json_encode($res, 320);
        } else {
            $message = 'fail';
            $res = array('code' => '200', 'message' => $message, 'data' => $goods_info);
            echo json_encode($res, 320);
        }
    }

    //商品分类只显示二级分类。
    public function josn_goods_classOp()
    {
        /**
         * 实例化商品分类模型
         */
        $model_class = Model('goods_class');
        $goods_class = $model_class->getGoodsClassListByParentId(intval($_POST['gc_id']));
        foreach ($goods_class as $key => $value) {
            $model = Model();
            $gc_thumb = $model->table('mb_category')->where('gc_id', $value['gc_id'])->field('gc_thumb')->find();
            $goods_class[$key]['gc_thumb'] = UPLOAD_SITE_URL . '/' . ATTACH_MOBILE . '/category' . '/' . $gc_thumb['gc_thumb'];
            // if($value['gc_id']==1794){
            //     unset($goods_class[$key]);
            // }
        }
        if ($goods_class) {
            $message = 'success';
            $res = array('code' => '100', 'message' => $message, 'data' => $goods_class);
            echo json_encode($res, 320);
        } else {
            $message = 'fail';
            $res = array('code' => '200', 'message' => $message, 'data' => $goods_class);
            echo json_encode($res, 320);
        }
    }
    //商品分类只显示三级分类。
    public function josn_goods_class1Op()
    {
        /**
         * 实例化商品分类模型
         */
        $model_class = Model('goods_class');
        $goods_class = $model_class->getGoodsClassListByParentId(intval($_POST['gc_id']));
        $goods_class3 = array();
        $arr2 = array();
        if (!empty($_POST['gc_id'])) {
            foreach ($goods_class as $key => $value) {
                $goods_class_class3 = $model_class->getGoodsClassListByParentId(intval($value['gc_id']));
                $goods_class3[] = $goods_class_class3;
            }
            foreach ($goods_class3 as $value) {
                foreach ($value as $v) {
                    $arr2[] = $v;
                }
            }
        } else {
            $arr2 = $goods_class;
        }

        //将三维数组转成二维数组结束
        foreach ($arr2 as $key => $value) {
            $model = Model();
            $gc_thumb = $model->table('mb_category')->where(array('gc_id' => $value['gc_id']))->field('gc_thumb')->find();
            $arr2[$key]['gc_thumb'] = UPLOAD_SITE_URL . '/' . ATTACH_MOBILE . '/category' . '/' . $gc_thumb['gc_thumb'];
        }
        if ($arr2) {
            $message = 'success';
            $res = array('code' => '100', 'message' => $message, 'data' => $arr2);
            echo json_encode($res, 320);
        } else {
            $message = 'fail';
            $res = array('code' => '200', 'message' => $message, 'data' => $arr2);
            echo json_encode($res, 320);
        }
    }

    //社区服务轮播图商品分类跳转接口只显示三级分类。
    public function josn_goods_class2Op()
    {
        $arr2 = array();
        /**
         * 实例化商品分类模型
         */
        $model_class = Model('goods_class');
        $goods_class = $model_class->getGoodsClassListByParentId(intval($_POST['gc_id']));
        $goods_class3 = array();
        $arr2 = array();
        if (!empty($_POST['gc_id'])) {
            foreach ($goods_class as $key => $value) {
                $goods_class_class3 = $model_class->getGoodsClassListByParentId(intval($value['gc_id']));

                $goods_class3[] = $goods_class_class3;
            }
            foreach ($goods_class3 as $value) {
                foreach ($value as $v) {
                    $arr2[] = $v;
                }
            }
        } else {
            $arr21 = $goods_class;
        }
        foreach ($arr21 as $key => $value) {
            if (strstr($value['gc_name'], '社区')) {
                $arr2[] = $value;
            }
        }
        //将三维数组转成二维数组结束
        foreach ($arr2 as $key => $value) {
            $model = Model();
            $gc_thumb = $model->table('mb_category')->where(array('gc_id' => $value['gc_id']))->field('gc_thumb')->find();
            $arr2[$key]['gc_thumb'] = UPLOAD_SITE_URL . '/' . ATTACH_MOBILE . '/category' . '/' . $gc_thumb['gc_thumb'];
        }
        if ($arr2) {
            $message = 'success';
            $res = array('code' => '100', 'message' => $message, 'data' => $arr2);
            echo json_encode($res, 320);
        } else {
            $message = 'fail';
            $res = array('code' => '200', 'message' => $message, 'data' => $arr2);
            echo json_encode($res, 320);
        }
    }
    //商品分类搜索
    public function class_searchOp()
    {
        /**
         * 实例化商品分类模型
         */

        $gc_name = $_POST['gc_name'];
        $model = Model();
        $goods_class = $model->table('goods_class')->select();
        $class_search = array();
        foreach ($goods_class as $key => $value) {
            if (strstr($value['gc_name'], $gc_name)) {
                $class_search[] = $goods_class[$key];
            }
        }

        foreach ($class_search as $key => $value) {
            $model = Model();
            $gc_thumb = $model->table('mb_category')->where('gc_id', $value['gc_id'])->field('gc_thumb')->find();
            $class_search[$key]['gc_thumb'] = UPLOAD_SITE_URL . '/' . ATTACH_MOBILE . '/category' . '/' . $gc_thumb['gc_thumb'];
        }
        if ($class_search) {
            $message = 'sucess';
            $res = array('code' => '100', 'message' => $message, 'data' => $class_search);
            echo json_encode($res, 320);
        } else {
            $message = 'fail';
            $res = array('code' => '200', 'message' => $message, 'data' => $class_search);
            echo json_encode($res, 320);
        }
    }
// 查看全部分类
    // public function goods_classOp()
    // {
    //     $model_class = Model('goods_class');
    //     $goods_class = $model_class->get_all_category();
    //     $model_channel = Model('web_channel');
    //     $goods_channel = $model_channel->getChannelList(array('channel_show'=>'1'));
    //     //多频道开始
    //     foreach ($goods_class as $key => $value) {
    //         foreach ($goods_channel as $k=> $v) {
    //              if($value['gc_id']==$v['gc_id']){
    //                  $goods_class[$value['gc_id']]['channel_gc_id'] =$v['gc_id'];
    //                  $goods_class[$value['gc_id']]['channel_id'] =$v['channel_id'];
    //                  }
    //             if(!empty($value['class2'])&&is_array($value['class2'])){
    //                 foreach ($value['class2'] as $kk=> $vv) {
    //                           if($vv['gc_id']==$v['gc_id']){
    //                          $goods_class[$value['gc_id']]['class2'][$vv['gc_id']]['channel_gc_id'] =$v['gc_id'];
    //                          $goods_class[$value['gc_id']]['class2'][$vv['gc_id']]['channel_id'] =$v['channel_id'];
    //                          }
    //                 }
    //             }
    //         }
    //     }
    //     if ($goods_class) {
    //         $message='sucess';
    //         $res = array('code'=>'100' , 'message'=>$message,'data'=>$goods_class);
    //          echo json_encode($res,320);
    //     } else {
    //         $message='fail';
    //         $res = array('code'=>'200' , 'message'=>$message,'data'=>$goods_class);
    //          echo json_encode($res,320);
    //     }
    // }

    public function josn_class_detailOp()
    {
        Language::read('home_goods_class_index');
        $this->_model_search = Model('search');
        //显示左侧分类
        //默认分类，从而显示相应的属性和品牌
        $default_classid = intval($_POST['gc_id']);
        if (intval($_POST['gc_id']) > 0) {
            $goods_class_array = $this->_model_search->getLeftCategory(array($_POST['gc_id']));
        }

        if ($goods_class_array) {
            $message = 'sucess';
            $res = array('code' => '100', 'message' => $message, 'data' => $goods_class_array);
            echo json_encode($res, 320);
        } else {
            $message = 'fail';
            $res = array('code' => '200', 'message' => $message, 'data' => $goods_class_array);
            echo json_encode($res, 320);
        }
    }

    /**
     * 商品评论列表
    goods_id
    type=1 好评
    type=2 中评
    type=3 差评
     */
    public function goods_evaluate_infoOp()
    {
        $goods_id = intval($_POST['goods_id']);
        $type = intval($_POST['type']);
        $condition = array();
        $condition['geval_goodsid'] = $goods_id;
        switch ($type) {
            case '1':
                $condition['geval_scores'] = array('in', '5,4');
                Tpl::output('type', '1');
                break;
            case '2':
                $condition['geval_scores'] = array('in', '3,2');
                Tpl::output('type', '2');
                break;
            case '3':
                $condition['geval_scores'] = array('in', '1');
                Tpl::output('type', '3');
                break;
        }
        //查询商品评分信息
        $model_evaluate_goods = Model("evaluate_goods");
        $goodsevallist = $model_evaluate_goods->getEvaluateGoodsList($condition, $page);
        // var_dump($goodsevallist);die;
        $message = 'sucess';
        $res = array('code' => '100', 'message' => $message, 'data' => $goodsevallist);
        echo json_encode($res, 320);
    }
    /*商品预约*/
    public function goods_reservationOp()
    {
        // var_dump($_SESSION['member_id']);die;
        $member_id = $_POST['member_id']; //商品id
        $goods_id = $_POST['goods_id']; //商品id
        $store_id = $_POST['store_id']; //店铺id
        $purchase_price = $_POST['purchase_price']; //采购价格
        $purchase_quantity = $_POST['purchase_quantity']; //采购数量
        $purchase_unit = $_POST['purchase_unit']; //采购单位
        $model_goods = Model('goods');
        $goods_info = $model_goods->getGoodsInfoByID($goods_id, '*');
        if (empty($goods_info)) {
            echo $this->returnMsg(200, '商品不存在', null);exit();
        }
        //此处获取用户公司以及其他信息
        $model_member = Model('member');
        $member_info = $model_member->getMemberInfo(array('member_id' => $member_id));
        // if ($member_info['member_verify']!=='1') {
        //    $res = array('code'=>'300' , 'message'=>'请先进行实名认证','data'=>null);
        //      echo json_encode($res,320);exit();
        // }
        if ($member_info['member_verify'] == '0' || $member_info['member_verify'] == '10') {
            $res = array('code' => '300', 'message' => '请先进行实名认证', 'data' => null);
            echo json_encode($res, 320);exit();
        } else if ($member_info['member_verify'] == '20') {
            $res = array('code' => '400', 'message' => '实名认证审核中', 'data' => null);
            echo json_encode($res, 320);exit();
        }
        //此处获取商品以及其他信息诉讼
        $model_goods = Model('goods');
        $goods_info = $model_goods->getGoodsInfo(array('goods_id' => $goods_id));
        $data = array();
        $data['user_id'] = $member_id; //暂时无效
        $data['goods_id'] = $goods_id;
        $data['goods_name'] = $goods_info['goods_name'];
        $data['store_id'] = $goods_info['store_id'];
        $data['purchase_price'] = $purchase_price;
        $data['purchase_quantity'] = $purchase_quantity;
        $data['purchase_unit'] = $purchase_unit;
        $data['purchase_time'] = time();
        $data['address_id'] = $goods_info['deliverer_id'];
        $goods_reservation_info = Model('goods_reservation')->where(array('user_id' => $member_id, 'goods_id' => $goods_id, 'status' => '10'))->select();
        if (!empty($goods_reservation_info)) {
            $res = array('code' => '200', 'message' => '预约失败，您有待处理的预约信息，请耐心等待商家处理', 'data' => null);
            echo json_encode($res, 320);exit();
        }

        $goods_info = Model('goods_reservation')->insert($data);

        if ($goods_info) {
            $res = array('code' => '100', 'message' => '预约成功，请耐心等待商家与您联系', 'data' => null);
            echo json_encode($res, 320);
        } else {
            $res = array('code' => '200', 'message' => '预约失败，请联系管理员', 'data' => null);
            echo json_encode($res, 320);
        }
    }

    /*我的预约*/
    public function my_goods_reservationOp()
    {
        // $my_rs_goods=Model('mb_category')->where('gc_id',$value['gc_id'])->select();
        echo $this->returnMsg(0, '推广开启', 111);
        // $my_rs_goods = Model('goods_reservation')->where('user_id',$_SESSION['member_id'])->select();

    }

}
