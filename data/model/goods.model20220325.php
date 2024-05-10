<?php
/**
 * 商品管理 
 */
defined('In718Shop') or exit('Access Invalid!');
//include 'imgcompress.php';

class goodsModel extends Model{
    public function __construct(){
        parent::__construct('goods');
    }

    const STATE1 = 1;       // 出售中
    const STATE0 = 0;       // 下架
    const STATE10 = 10;     // 违规
    const VERIFY1 = 1;      // 审核通过
    const VERIFY0 = 0;      // 审核失败
    const VERIFY10 = 10;    // 等待审核
    const STATE2 = 1;       // 参与会员价
    /**
     * 新增商品数据
     *
     * @param array $insert 数据
     * @param string $table 表名
     */
    public function addGoods($insert) {
        $result = $this->table('goods')->insert($insert);
        // var_dump($result);die;
        if ($result) {
            $this->_dGoodsCache($result);
            $this->_dGoodsCommonCache($insert['goods_commonid']);
            $this->_dGoodsSpecCache($insert['goods_commonid']);
        }
        return $result;
    }

    /**
     * 新增商品公共数据
     *
     * @param array $insert 数据
     * @param string $table 表名
     */
    public function addGoodsCommon($insert) {
        return $this->table('goods_common')->insert($insert);
    }

        /**
     * 新增商品跨境电子口岸数据
     *
     * @param array $insert 数据
     * @param string $table 表名
     */
    public function addGoodsKuajingD($insert) {

            return $this->table('goods_kuajing_d')->insert($insert);
    }

    /**
     * 新增多条商品数据
     *
     * @param unknown $insert
     */
    public function addGoodsImagesAll($insert) {
        $result = $this->table('goods_images')->insertAll($insert);
        if ($result) {
            foreach ($insert as $val) {
                $this->_dGoodsImageCache($val['goods_commonid'] . '|' . $val['color_id']);
            }
        }
        return $result;
    }
     /**
     * 商品SKU列表
     *
     * @param array $condition 条件
     * @param string $field 字段
     * @param string $group 分组
     * @param string $order 排序
     * @param int $limit 限制
     * @param int $page 分页
     * @param boolean $lock 是否锁定
     * @return array 二维数组
     */
    public function getGoodsList3($condition, $field = '*', $group = '',$order = '', $limit = 0, $page = 0, $lock = false,  $offset = 0,$i=0,$sortkey=2,$postorder,$ngoods_sign) {
        $condition = $this->_getRecursiveClass($condition);
        $condition1 = ' where 1';
        foreach ($condition as $key => $value) {
            if($key == 'goods_price' ){
                if($value[1][1] >0){
                    $condition1 .= ' AND '.$key.' '.$value[0].' '.$value[1][0] .' AND '.$value[1][1];
                }
            }elseif($key == 'goods_name' ){
                $keyword = $value[1];
            }else{
                 $condition1 .= ' AND '.$key.' = ' .$value;
            }
        }
        // if($ngoods_sign){
        //     $condition2 = " AND sgc.is_new=1 ";
        // }
        $sortorder = "(CASE WHEN a.goods_storage=0 THEN 1 ELSE 0 END),a.tempsort,a.goods_storage DESC,a.goods_id DESC";
        if($sortkey==2){
            if($postorder == 'false'){
                $sortorder = "(CASE WHEN a.goods_storage=0 THEN 1 ELSE 0 END),a.tempsort,a.goods_storage DESC,a.goods_id DESC";
            }else{
                $sortorder = "a.goods_storage,a.tempsort DESC,a.goods_id DESC";
            }
        }elseif($sortkey==1){
            if($postorder =='false'){
                $sortorder = "a.sale_num DESC,a.tempsort,a.sale_num,a.goods_storage DESC,a.goods_id DESC";
            }else{
                $sortorder = "a.sale_num ASC,a.tempsort DESC,a.goods_id DESC";
            }
        }elseif($sortkey==3){
            if($postorder=='false'){
                $sortorder = "goods_fi_price DESC,a.tempsort,a.goods_id DESC";
            }else{
                $sortorder = "goods_fi_price ASC,a.tempsort,a.goods_id DESC";
            }
        }
        if($ngoods_sign){
            $sql = "SELECT * FROM(SELECT sg.goods_commonid, sg.goods_id, sg.goods_name, sg.goods_jingle, sg.gc_id, sg.store_id, sg.store_name, min(sg.goods_price) AS goods_price, sg.goods_promotion_price, ( CASE WHEN sg.is_group_ladder = 7 THEN min(sg.goods_promotion_price) ELSE min(sg.goods_price) END ) AS goods_fi_price, sg.goods_promotion_type, sg.goods_marketprice, sum(sg.goods_storage) AS goods_storage, sg.goods_image, sg.goods_freight, sg.goods_salenum, sg.goods_presalenum, ( sg.goods_salenum + sg.goods_presalenum ) AS sale_num, sg.color_id, sg.evaluation_good_star, sg.evaluation_count, sg.is_virtual, sg.is_fcode, sg.is_appoint, sg.is_presell, sg.have_gift, sg.is_mode, sg.is_group_ladder,1 as tempsort FROM 718shop_goods sg LEFT JOIN 718shop_goods_common sgc ON sg.goods_commonid = sgc.goods_commonid WHERE sgc.is_new = 1 AND ( NOT ( sg.is_group_ladder = 5 AND goods_id IN ( SELECT goods_id FROM 718shop_buy_deliver_goods WHERE buy_deliver_id IN (9))) " . $i . ")GROUP BY goods_commonid)a ORDER BY ".$sortorder." limit  10 offset ".$offset;
            $goods_list = Model()->query($sql);
            // print_r($sql);die;
            return $goods_list;
        }
        if($keyword){
            $sql = "SELECT goods_commonid, goods_id, goods_name, goods_jingle, gc_id, store_id, store_name, goods_price, goods_promotion_price, is_group_ladder, ( CASE WHEN is_group_ladder = 4 THEN goods_promotion_price WHEN is_group_ladder = 7 THEN goods_promotion_price ELSE goods_price END ) AS goods_fi_price, goods_promotion_type, goods_marketprice, goods_storage, goods_image, goods_freight, goods_salenum, goods_presalenum, sale_num, color_id, evaluation_good_star, evaluation_count, is_virtual, is_fcode, is_appoint, is_presell, have_gift, is_mode, tempsort,hui_discount FROM ((SELECT sg.goods_commonid,sg.goods_id,sg.goods_name,sg.goods_jingle,sg.gc_id,sg.store_id,sg.store_name,min(sg.goods_price) as goods_price,sg.goods_promotion_price,sg.goods_promotion_type,sg.goods_marketprice,sum(sg.goods_storage) as goods_storage,sg.goods_image,sg.goods_freight,sg.goods_salenum,sg.goods_presalenum,(sg.goods_salenum+sg.goods_presalenum) as sale_num,sg.color_id,sg.evaluation_good_star,sg.evaluation_count,sg.is_virtual,sg.is_fcode,sg.is_appoint,sg.is_presell,sg.have_gift,sg.is_mode,sg.is_group_ladder,1 AS tempsort,sg.hui_discount FROM 718shop_goods sg INNER JOIN (SELECT sgc.gc_id FROM 718shop_goods_class sgc WHERE sgc.gc_name LIKE '".$keyword."' AND sgc.gc_parent_id> 0) c ON c.gc_id=sg.gc_id WHERE sg.goods_state=1 AND sg.goods_verify=1 AND sg.is_deleted=0 ".$i." GROUP BY sg.goods_commonid) UNION (SELECT sg.goods_commonid,sg.goods_id,sg.goods_name,sg.goods_jingle,sg.gc_id,sg.store_id,sg.store_name,min(sg.goods_price) as goods_price,sg.goods_promotion_price,sg.goods_promotion_type,sg.goods_marketprice,sum(sg.goods_storage) as goods_storage,sg.goods_image,sg.goods_freight,sg.goods_salenum,sg.goods_presalenum,(sg.goods_salenum+sg.goods_presalenum) as sale_num,sg.color_id,sg.evaluation_good_star,sg.evaluation_count,sg.is_virtual,sg.is_fcode,sg.is_appoint,sg.is_presell,sg.have_gift,sg.is_mode,sg.is_group_ladder,2 AS tempsort,sg.hui_discount FROM 718shop_goods sg INNER JOIN (SELECT DISTINCT sg.goods_commonid FROM 718shop_goods sg WHERE sg.goods_name LIKE '".$keyword."') g ON g.goods_commonid=sg.goods_commonid WHERE sg.goods_state=1 AND sg.goods_verify=1 AND sg.is_deleted=0 ".$i." GROUP BY sg.goods_commonid) UNION (SELECT sg.goods_commonid,sg.goods_id,sg.goods_name,sg.goods_jingle,sg.gc_id,sg.store_id,sg.store_name,min(sg.goods_price) as goods_price,sg.goods_promotion_price,sg.goods_promotion_type,sg.goods_marketprice,sum(sg.goods_storage) as goods_storage,sg.goods_image,sg.goods_freight,sg.goods_salenum,sg.goods_presalenum,(sg.goods_salenum+sg.goods_presalenum) as sale_num,sg.color_id,sg.evaluation_good_star,sg.evaluation_count,sg.is_virtual,sg.is_fcode,sg.is_appoint,sg.is_presell,sg.have_gift,sg.is_mode,sg.is_group_ladder,3 AS tempsort,sg.hui_discount FROM 718shop_goods sg INNER JOIN (SELECT sgc.gc_id FROM 718shop_goods_class sgc WHERE sgc.gc_name LIKE '".$keyword."' AND sgc.gc_parent_id=0) c ON c.gc_id=sg.gc_id_1 WHERE sg.goods_state=1 AND sg.goods_verify=1 AND sg.is_deleted=0 ".$i." GROUP BY sg.goods_commonid)) a GROUP BY a.goods_commonid ORDER BY ".$sortorder." limit  10 offset ".$offset;
        }else{
            // $sql="SELECT ".$field." FROM `718shop_goods` ".$condition1." GROUP BY goods_commonid ORDER BY ".$order." limit  10 offset ".$offset;
            $sql="SELECT * FROM (SELECT ".$field.",(CASE WHEN is_group_ladder = 4 THEN goods_promotion_price WHEN is_group_ladder= 7 THEN goods_promotion_price ELSE goods_price END) AS goods_fi_price,1 as tempsort,(goods_salenum+goods_presalenum) as sale_num FROM `718shop_goods` sg ".$condition1.$i." GROUP BY goods_commonid)a ORDER BY ".$sortorder." limit  10 offset ".$offset;
        }
        $temp_goods=Model()->query($sql);
        // print_r($sql);die;
        return $temp_goods;
    }
        /**
     * 二维数组根据某个字段排序
     * @param array $array 要排序的数组
     * @param string $keys   要排序的键字段
     * @param string $sort  排序类型  SORT_ASC     SORT_DESC 
     * @return array 排序后的数组
     */
    function arraySort($array, $keys, $sort = SORT_DESC) {
        $keysValue = [];
        foreach ($array as $k => $v) {
            $keysValue[$k] = $v[$keys];
        }
        array_multisort($keysValue, $sort, $array);
        return $array;
    }
    /**
     * 二维数组按照指定键值去重
     * @param $arr 需要去重的二维数组
     * @param $key 需要去重所根据的索引
     * @return mixed
    */
    function assoc_unique($arr, $key)
    {
        $tmp_arr = array();
        foreach($arr as $k => $v) {
            if(in_array($v[$key],$tmp_arr)) {  //搜索$v[$key]是否在$tmp_arr数组中存在，若存在返回true
                unset($arr[$k]);
            } else {
                $tmp_arr[] = $v[$key];
            }
        }
        sort($arr); //sort函数对数组进行排序
        return $arr;
    }
    /**
     * 商品SKU列表
     *
     * @param array $condition 条件
     * @param string $field 字段
     * @param string $group 分组
     * @param string $order 排序
     * @param int $limit 限制
     * @param int $page 分页
     * @param boolean $lock 是否锁定
     * @return array 二维数组
     */
    public function getGoodsList($condition, $field = '*', $group = '',$order = '', $limit = 0, $page = 0, $lock = false, $count = 0) {
        $condition = $this->_getRecursiveClass($condition);
       /*return $this->table('goods')->field($field)->where($condition)->order($order)->limit($limit)->page($page, $count)->select();*/
        // return $this->table('goods')->field($field)->where($condition)->order($order)->limit($limit)->page($page, $count)->lock($lock)->select();
        return $this->table('goods')->field($field)->where($condition)->group($group)->order($order)->limit($limit)->page($page, $count)->lock($lock)->select();
    }
    public function getGoodsList1($condition, $field = '*', $group = '',$order = '', $limit = 0, $page = 0, $lock = false, $count = 0) {
        $condition = $this->_getRecursiveClass($condition);
      
        return $this->table('goods')->field($field)->where($condition)->group($group)->order($order)->limit($limit)->page($page, $count)->lock($lock)->select();
    }
    
    /**
     * 获取指定分类指定店铺下的随机商品列表
     *
     * @param int $gcId 一级分类ID
     * @param int $storeId 店铺ID
     * @param int $notEqualGoodsId 此商品ID除外
     * @param int $size 列表最大长度
     *
     * @return array|null
     */
    public function getGoodsGcStoreRandList($gcId, $storeId, $notEqualGoodsId = 0, $size = 4)
    {
        $where = array(
            'store_id' => (int) $storeId,
            'gc_id_1' => (int) $gcId,
            'goods_state' => 1,
        );

        if ($notEqualGoodsId > 0) {
            $where['goods_id'] = array('neq', (int) $notEqualGoodsId);
        }

        return $this->table('goods')
            ->where($where)
            ->order('rand()')
            ->limit($size)
            ->select();
    }

    /**
     * 出售中的商品SKU列表（只显示不同颜色的商品，前台商品索引，店铺也商品列表等使用）
     * @param array $condition
     * @param string $field
     * @param string $order
     * @param number $page
     * @return array
     */
    public function getGoodsListByColorDistinct($condition, $field = '*', $order = 'goods_id asc', $page = 0) {
        $condition['goods_state']   = self::STATE1;
        $condition['goods_verify']  = self::VERIFY1;
        $condition['is_deleted']  = 0;
        $condition = $this->_getRecursiveClass($condition);
	//去掉多规格颜色显示 
        //$field = "CONCAT(goods_commonid,',',color_id) as nc_distinct ," . $field;
        //$count = $this->getGoodsOnlineCount($condition,"distinct CONCAT(goods_commonid,',',color_id)");
	$field = "CONCAT(goods_commonid) as nc_distinct ," . $field;
	$count = $this->getGoodsOnlineCount($condition,"distinct CONCAT(goods_commonid)");
        $goods_list = array();
        if ($count != 0) {
            $goods_list = $this->getGoodsOnlineList($condition, $field, $page, $order, 0, 'nc_distinct', false, $count);
        }
        return $goods_list;
    }
	
	    /**
     * 出售中的商品SKU列表（只显示不同颜色的商品，前台商品索引，店铺也商品列表等使用）
     * @param array $condition
     * @param string $field
     * @param string $order
     * @param number $page
     * @return array
     */
    public function getGoodsListByColorDistinct1($condition, $field = '*', $order = 'goods_id asc', $offset= 0,$i=0,$sortkey=2,$postorder,$ngoods_sign) {
        $condition['goods_state']   = self::STATE1;
        $condition['goods_verify']  = self::VERIFY1;
        $condition['is_deleted']  = 0;
        $condition = $this->_getRecursiveClass($condition);
    //去掉多规格颜色显示 
        //$field = "CONCAT(goods_commonid,',',color_id) as nc_distinct ," . $field;
        //$count = $this->getGoodsOnlineCount($condition,"distinct CONCAT(goods_commonid,',',color_id)");
    $field = "CONCAT(goods_commonid) as nc_distinct ," . $field;
    $count = $this->getGoodsOnlineCount($condition,"distinct CONCAT(goods_commonid)");
        $goods_list = array();
        // if ($count != 0) {
           $goods_list = $this->getGoodsOnlineList1($condition, $field, $page, $order, 0, 'nc_distinct', false, $offset,$i,$sortkey,$postorder,$ngoods_sign);
        // }
        return $goods_list;
    }

    public function getGoodsListByNoColorDistinct($condition, $field = '*', $order = 'goods_id asc', $page = 0) {
        $condition['goods_state']   = self::STATE1;
        $condition['goods_verify']  = self::VERIFY1;
        $condition['is_deleted']  = 0;
        $condition = $this->_getRecursiveClass($condition);
        $goods_list = $this->getGoodsOnlineList($condition, $field, $page, $order, 0);
        return $goods_list;
    }


    /**
     * 在售商品SKU列表
     *
     * @param array $condition 条件
     * @param string $field 字段
     * @param string $group 分组
     * @param string $order 排序
     * @param int $limit 限制
     * @param int $page 分页
     * @param boolean $lock 是否锁定
     * @return array
     */
    public function getGeneralGoodsList($condition, $field = '*', $page = 0, $order = 'goods_id desc') {
        $condition['is_virtual']    = 0;
        $condition['is_fcode']      = 0;
        $condition['is_presell']    = 0;
        return $this->getGoodsList($condition, $field, '', $order, 0, $page, false, 0);
    }

    /**
     * 在售商品SKU列表
     *
     * @param array $condition 条件
     * @param string $field 字段
     * @param string $group 分组
     * @param string $order 排序
     * @param int $limit 限制
     * @param int $page 分页
     * @param boolean $lock 是否锁定
     * @return array
     */
    public function getGoodsOnlineList($condition, $field = '*', $page = 0, $order = 'goods_id desc', $limit = 0, $group = '', $lock = false, $count = 0) {
        $condition['goods_state']   = self::STATE1;
        $condition['goods_verify']  = self::VERIFY1;
        $condition['is_deleted']  = 0;
        return $this->getGoodsList($condition, $field, $group, $order, $limit, $page, $lock, $count);
    }
    /**
     * 在售商品SKU列表
     *
     * @param array $condition 条件
     * @param string $field 字段
     * @param string $group 分组
     * @param string $order 排序
     * @param int $limit 限制
     * @param int $page 分页
     * @param boolean $lock 是否锁定
     * @return array
     */
    public function getGoodsOnlineList1($condition, $field = '*', $page = 0, $order = 'goods_id desc', $limit = 0, $group = '', $lock = false, $offset = 0,$i=0,$sortkey=2,$postorder=false,$ngoods_sign) {
        $condition['goods_state']   = self::STATE1;
        $condition['goods_verify']  = self::VERIFY1;
        $condition['is_deleted']  = 0;
         return $this->getGoodsList3($condition, $field, $group, $order, $limit, $page, $lock, $offset,$i,$sortkey,$postorder,$ngoods_sign);
    }

    /**
     * 出售中的普通商品列表，即不包括虚拟商品、F码商品、预售商品
     */
    public function getGoodsListForPromotion($condition, $field = '*', $page = 0, $type = '') {
        switch ($type) {
            case 'xianshi':
                $condition['is_virtual']    = 0;
                $condition['is_fcode']      = 0;
                $condition['is_presell']    = 0;
                $condition['goods_state']   = self::STATE1;
                $condition['goods_verify']  = self::VERIFY1;
                break;
            case 'xinren':
            case 'bundling':
            case 'combo':
            case 'buy_deliver':
                $condition['is_virtual']    = 0;
                $condition['is_fcode']      = 0;
                $condition['is_presell']    = 0;
                $condition['goods_state']   = self::STATE1;
                $condition['goods_verify']  = self::VERIFY1;
                break;
            case 'gift':
                $condition['is_virtual']    = 0;
                break;
            case 'groupbuy':
                    $condition['is_virtual']    = 0;
                    $condition['is_fcode']      = 0;
                    $condition['is_presell']    = 0;
                    $condition['goods_state']   = self::STATE1;
                    $condition['goods_verify']  = self::VERIFY1;
            default:
                break;
        }
        //  $condition['is_group_ladder']    = 0;
         if ($type == 'buy_deliver') {
             $condition['is_group_ladder'] = array('in','0,5');
         }
		 $condition['is_deleted']    = 0;
        return $this->getGoodsList($condition, $field, '', '', 0, $page);
    }
      /**
     * 出售中的普通商品列表，即不包括虚拟商品、F码商品、预售商品
     */
    public function getGoodsListForPromotion2($condition, $field = '*', $page = 0, $type = '') {
        switch ($type) {
            case 'xianshi':
            case 'xinren':
            case 'bundling':
            case 'combo':
            case 'buy_deliver':
                $condition['is_virtual']    = 0;
                $condition['is_fcode']      = 0;
                $condition['is_presell']    = 0;
                $condition['goods_state']   = self::STATE1;
                $condition['goods_verify']  = self::VERIFY1;
                break;
            case 'gift':
                $condition['is_virtual']    = 0;
                break;
            case 'groupbuy':
                    $condition['is_virtual']    = 0;
                    $condition['is_fcode']      = 0;
                    $condition['is_presell']    = 0;
                    $condition['goods_state']   = self::STATE1;
                    $condition['goods_verify']  = self::VERIFY1;
            default:
                break;
        }
         if ($type == 'buy_deliver') {
             $condition['is_group_ladder'] = array('in','0,1,2,4,5');
         }
         // var_dump($condition);die;
         $condition['is_deleted']    = 0;
        return $this->getGoodsList($condition, $field, '', '', 0, $page);
    }
    /**
     * 商品列表 卖家中心使用
     *
     * @param array $condition 条件
     * @param array $field 字段
     * @param string $page 分页
     * @param string $order 排序
     * @return array
     */
    public function getGoodsCommonList($condition, $field = '*', $page = 10, $order = 'goods_commonid desc') {
        $condition = $this->_getRecursiveClass($condition);
        return $this->table('goods_common')->field($field)->where($condition)->order($order)->page($page)->select();
    }
    //shang商品信息导出用
     public function getGoodsCommonList2($condition, $field = '*', $page = 2000, $order = 'goods_commonid desc') {
        $condition = $this->_getRecursiveClass($condition);
        return $this->table('goods')->field($field)->where($condition)->order($order)->page($page)->select();
    }
     //库存为0商品信息
     public function getGoodsCommonList3($condition, $field = '*', $page = 10, $order = 'goods_commonid desc') {
        $condition = $this->_getRecursiveClass($condition);
       /* print_r($condition);
        echo $order;die;*/
        return $this->table('goods')->field($field)->where($condition)->order($order)->page($page)->select();
    }
    /**
     * 在线预约商品
     *
     * @param array $condition 条件
     * @param array $field 字段
     * @param string $page 分页
     * @param string $order 排序
     * @return array
     */
    public function getGoodsReservationList($condition, $page = 10, $order = "id
     desc") {
        $model = Model();
        $field = 'member.member_mobile,member.member_name,goods_reservation.*';
        $on = 'member.member_id=goods_reservation.user_id';
        $model->table('member,goods_reservation')->field($field);
        $goods_reservation=$model->join('inner')->on($on)->where($condition)->order($order)->page($page)->select();
        
        foreach ($goods_reservation as $key => $value) {
            $model_daddress = Model('daddress');
            $address = $model_daddress->getAddressInfo(array('address_id'=>$value['address_id']));
            $goods_reservation[$key]['seller_name']=$address['seller_name'];
            $goods_reservation[$key]['telphone']=$address['telphone'];
        }

        return $goods_reservation;
    }

            /**
     * 获取预约单个的商品
     *
     * @param array $reservation_id 预约id 
     * @param array $field 字段
     */
    public function getoneReservation($reservation_id) {
        $model = Model();
        $field = 'member.member_mobile,member.member_name,goods_reservation.*';
        $on = 'member.member_id=goods_reservation.user_id';
        $model->table('member,goods_reservation')->field($field);
        $goods_reservation_detail=$model->join('inner')->on($on)->where(array('goods_reservation.id'=>$reservation_id))->find();
        return $goods_reservation_detail;
    }
    /**
     * 出售中的商品列表 卖家中心使用
     *
     * @param array $condition 条件
     * @param array $field 字段
     * @param string $page 分页
     * @param string $order 排序
     * @return array
     */
    public function getGoodsCommonOnlineList($condition, $field = '*', $page = 10, $order = "goods_commonid desc") {
        $condition['goods_state']   = self::STATE1;
        $condition['goods_verify']  = self::VERIFY1;
        return $this->getGoodsCommonList($condition, $field, $page, $order);
    }
     //商品导出
    public function getGoodsCommonOnlineList2($condition, $field = '*', $page = 2000, $order = "goods_commonid desc") {
        $condition['goods_state']   = self::STATE1;
        $condition['goods_verify']  = self::VERIFY1;
        return $this->getGoodsCommonList2($condition, $field, $page, $order);
    }
     //商品库存为0
    public function getGoodsCommonOnlineList3($condition, $field = '*', $page = 10, $order = "goods_commonid desc") {
        $condition['goods_state']   = self::STATE1;
        $condition['goods_verify']  = self::VERIFY1;
        $condition['goods_storage'] = 0;
        return $this->getGoodsCommonList3($condition, $field, $page, $order);
    }
    /**
     * 出售中的普通商品列表，即不包括虚拟商品、F码商品、预售商品
     */
    public function getGoodsCommonListForPromotion($condition, $field = '*', $page = 10, $type) {
        if ($type == 'groupbuy') {
            $condition['is_virtual']    = 0;
            $condition['is_fcode']      = 0;
            $condition['is_presell']    = 0;
            $condition['goods_state']   = self::STATE1;
            $condition['goods_verify']  = self::VERIFY1;
        }
        return $this->getGoodsCommonList($condition, $field, $page);
    }

    /**
     * 出售中的未参加促销的虚拟商品列表
     */
    public function getGoodsCommonListForVrPromotion($condition, $field = '*', $page = 10) {
        $condition['is_virtual']    = 1;
        $condition['is_fcode']      = 0;
        $condition['is_presell']    = 0;
        $condition['goods_state']   = self::STATE1;
        $condition['goods_verify']  = self::VERIFY1;

        return $this->getGoodsCommonList($condition, $field, $page);
    }

    /**
     * 仓库中的商品列表 卖家中心使用
     *
     * @param array $condition 条件
     * @param array $field 字段
     * @param string $page 分页
     * @param string $order 排序
     * @return array
     */
    public function getGoodsCommonOfflineList($condition, $field = '*', $page = 10, $order = "goods_commonid desc") {
        $condition['goods_state']   = self::STATE0;
        $condition['goods_verify']  = self::VERIFY1;
        return $this->getGoodsCommonList($condition, $field, $page, $order);
    }
    /**
     * 仓库中的商品列表 卖家中心使用
     *
     * @param array $condition 条件
     * @param array $field 字段
     * @param string $page 分页
     * @param string $order 排序
     * @return array
     */
    public function getGoodsCommonOfflineList1($condition, $field = '*', $page = 2000, $order = "goods_commonid desc") {
        $condition['goods_state']   = self::STATE0;
        $condition['goods_verify']  = self::VERIFY1;
        return $this->getGoodsCommonList2($condition, $field, $page, $order);
    }

    /**
     * 违规的商品列表 卖家中心使用
     *
     * @param array $condition 条件
     * @param array $field 字段
     * @param string $page 分页
     * @param string $order 排序
     * @return array
     */
    public function getGoodsCommonLockUpList($condition, $field = '*', $page = 10, $order = "goods_commonid desc") {
        $condition['goods_state']   = self::STATE10;
        $condition['goods_verify']  = self::VERIFY1;
        return $this->getGoodsCommonList($condition, $field, $page, $order);
    }
    /**
     * 违规的商品导出
     *
     * @param array $condition 条件
     * @param array $field 字段
     * @param string $page 分页
     * @param string $order 排序
     * @return array
     */
    public function getGoodsCommonLockUpList1($condition, $field = '*', $page = 2000, $order = "goods_commonid desc") {
        $condition['goods_state']   = self::STATE10;
        $condition['goods_verify']  = self::VERIFY1;
        return $this->getGoodsCommonList2($condition, $field, $page, $order);
    }

    /**
     * 等待审核或审核失败的商品列表 卖家中心使用
     *
     * @param array $condition 条件
     * @param array $field 字段
     * @param string $page 分页
     * @param string $order 排序
     * @return array
     */
    public function getGoodsCommonWaitVerifyList($condition, $field = '*', $page = 10, $order = "goods_commonid desc") {
        if (!isset($condition['goods_verify'])) {
            $condition['goods_verify']  = array('neq', self::VERIFY1);
        }
        return $this->getGoodsCommonList($condition, $field, $page, $order);
    }

    /**
     * 等待审核或审核失败的商品导出
     *
     * @param array $condition 条件
     * @param array $field 字段
     * @param string $page 分页
     * @param string $order 排序
     * @return array
     */
    public function getGoodsCommonWaitVerifyList1($condition, $field = '*', $page = 2000, $order = "goods_commonid desc") {
        if (!isset($condition['goods_verify'])) {
            $condition['goods_verify']  = array('neq', self::VERIFY1);
        }
        return $this->getGoodsCommonList2($condition, $field, $page, $order);
    }

    /**
     * 查询商品SUK及其店铺信息
     *
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getGoodsStoreList($condition, $field = '*') {
        $condition = $this->_getRecursiveClass($condition);
        return $this->table('goods,store')->field($field)->join('inner')->on('goods.store_id = store.store_id')->where($condition)->select();
    }
	
	/**
     * 查询推荐商品(随机排序)
     * @param int $store_id 店铺
     * @param int $limit 限制
     * @return array
     */
    public function getGoodsCommendList($store_id, $limit = 5) {
            $goods_commend_list = $this->getGoodsOnlineList(array('store_id' => $store_id, 'goods_commend' => 1), 'goods_id,goods_name,goods_jingle,goods_image,store_id,goods_promotion_price', 0, 'rand()', $limit, 'goods_commonid');
            if (!empty($goods_id_list)) {
                $tmp = array();
                foreach ($goods_id_list as $v) {
                    $tmp[] = $v['goods_id'];
                }
                $goods_commend_list = $this->getGoodsOnlineList(array('goods_id' => array('in',$tmp)), 'goods_id,goods_name,goods_jingle,goods_image,store_id,goods_promotion_price', 0, 'rand()', $limit);
            }
        return $goods_commend_list;
    }

    /**
     * 计算商品库存
     *
     * @param array $goods_list
     * @return array|boolean
     */
    public function calculateStorage($goods_list) {
        // 计算库存
        if (!empty($goods_list)) {
            $goodsid_array = array();
            foreach ($goods_list as $value) {
                $goodscommonid_array[] = $value['goods_commonid'];
            }
            $goods_storage = $this->getGoodsList(array('goods_commonid' => array('in', $goodscommonid_array),'is_deleted'=>0), 'goods_storage,goods_commonid,goods_id,goods_storage_alarm');
            $storage_array = array();
            foreach ($goods_storage as $val) {
                if ($val['goods_storage_alarm'] != 0 && $val['goods_storage'] <= $val['goods_storage_alarm']) {
                    $storage_array[$val['goods_commonid']]['alarm'] = true;
                }
                $storage_array[$val['goods_commonid']]['sum'] += $val['goods_storage'];
                $storage_array[$val['goods_commonid']]['goods_id'] = $val['goods_id'];
            }
            return $storage_array;
        } else {
            return false;
        }
    }

    /**
     * 更新商品SUK数据
     *
     * @param array $update 更新数据
     * @param array $condition 条件
     * @return boolean
     */
    public function editGoods($update, $condition) {
        $goods_list = $this->getGoodsList($condition, 'goods_id');
        if (empty($goods_list)) {
            return true;
        }
        $goodsid_array = array();
        foreach ($goods_list as $value) {
            $goodsid_array[] = $value['goods_id'];
        }
        return $this->editGoodsById($update, $goodsid_array);
    }

    /**
     * 更新商品SUK数据
     * @param array $update
     * @param int|array $goodsid_array
     * @return boolean|unknown
     */
    public function editGoodsById($update, $goodsid_array) {
        if (empty($goodsid_array)) {
            return true;
        }
        $condition['goods_id'] = array('in', $goodsid_array);
        $update['goods_edittime'] = TIMESTAMP;
        // var_dump($update);
        $result = $this->table('goods')->where($condition)->update($update);
        if ($result) {
            foreach ((array)$goodsid_array as $value) {
                $this->_dGoodsCache($value);
            }
        }
        return $result;
    }

    /**
     * 更新商品促销价 (需要验证抢购和限时折扣是否进行)
     *
     * @param array $update 更新数据
     * @param array $condition 条件
     * @return boolean
     */
    public function editGoodsPromotionPrice($condition) {
        $goods_list = $this->getGoodsList($condition, 'goods_id,goods_commonid');
        $goods_array = array();
        foreach ($goods_list as $val) {
            $goods_array[$val['goods_commonid']][$val['goods_id']] = $val;
        }
        $model_groupbuy = Model('groupbuy');
        $model_xianshigoods = Model('p_xianshi_goods');
        foreach ($goods_array as $key => $val) {
            // 查询抢购时候进行
            // $groupbuy = $model_groupbuy->getGroupbuyOnlineInfo(array('goods_commonid' => $key));
            $groupbuy = $model_groupbuy->getGroupbuyOnlineInfo(array('goods_id' => $key));
            if (!empty($groupbuy)) {
                // 更新价格
                // $this->editGoods(array('goods_promotion_price' => $groupbuy['groupbuy_price'], 'goods_promotion_type' => 1), array('goods_commonid' => $key));
                 $this->editGoods(array('goods_promotion_price' => $groupbuy['groupbuy_price'], 'goods_promotion_type' => 1), array('goods_id' => $key));
                continue;
            }
            foreach ($val as $k => $v) {
                // 查询限时折扣时候进行
                $xianshigoods = $model_xianshigoods->getXianshiGoodsInfo(array('goods_id' => $k, 'start_time' => array('lt', TIMESTAMP), 'end_time' => array('gt', TIMESTAMP)));
                if (!empty($xianshigoods)) {
                    // 更新价格
                    $this->editGoodsById(array('goods_promotion_price' => $xianshigoods['xianshi_price'], 'goods_promotion_type' => 2,'is_group_ladder'=>4,'commis_rate'=>$xianshigoods['commis_rate']), $k);
                    continue;
                }
             $goods_info = $this->getGoodsInfo(array('goods_id'=>$k));
             // var_dump($K);die;
             $is_group_ladder= $goods_info['is_group_ladder'];
             if( $is_group_ladder==4){
               $is_group_ladder=0;
             }
                // 没有促销使用原价
              // var_dump($k);die;
                $commis_rate = Model()->table('goods_common')->getfby_goods_commonid($v['goods_commonid'],'commis_rate');
                $this->editGoodsById(array('goods_promotion_price' => array('exp', 'goods_price'), 'goods_promotion_type' => 0,'is_group_ladder'=>$is_group_ladder,'commis_rate'=>$commis_rate), $k);
            }
        }
        return true;
    }

    /**
     * 更新商品数据
     * @param array $update 更新数据
     * @param array $condition 条件
     * @return boolean
     */
    public function editGoodsCommon($update, $condition) {
        $common_list = $this->getGoodsCommonList($condition, 'goods_commonid', 0);
        if (empty($common_list)) {
            return false;
        }
        $commonid_array = array();
        foreach ($common_list as $val) {
            $commonid_array[] = $val['goods_commonid'];
        }
        return $this->editGoodsCommonById($update, $commonid_array);
    }

      /**
     * 更新跨境商品数据 电子口岸
     * @param array $update
     * @param int|array $commonid_array
     * @return boolean|unknown
     */
    public function editGoodsKuajingById($update, $kuajingID) {
        $condition['id'] = $kuajingID;
        $result = $this->table('goods_kuajing_d')->where($condition)->update($update);

        return $result;
    }
  

    /**
     * 更新商品数据
     * @param array $update
     * @param int|array $commonid_array
     * @return boolean|unknown
     */
    public function editGoodsCommonById($update, $commonid_array) {
        if (empty($commonid_array)) {
            return true;
        }
        $condition['goods_commonid'] = array('in', $commonid_array);
        $result = $this->table('goods_common')->where($condition)->update($update);
        if ($result) {
            foreach ((array)$commonid_array as $val) {
                $this->_dGoodsCommonCache($val);
            }
        }
        return $result;
    }

    /**
     * 锁定商品
     * @param unknown $condition
     * @return boolean
     */
    public function editGoodsCommonLock($condition) {
        $update = array('goods_lock' => 1);
        return $this->editGoodsCommon($update, $condition);
    }

     /**
     * 解锁商品
     * @param unknown $condition
     * @return boolean
     */
    public function editGoodsCommonUnlock($condition) {
        $update = array('goods_lock' => 0);
        return $this->editGoodsCommon($update, $condition);
    }

    /**
     * 更新商品信息
     *
     * @param array $condition
     * @param array $update1
     * @param array $update2
     * @return boolean
     */
    public function editProduces($condition, $update1, $update2 = array()) {
        $update2 = empty($update2) ? $update1 : $update2;
        $goods_array = $this->getGoodsCommonList($condition, 'goods_commonid', 0);
        if (empty($goods_array)) {
            return true;
        }
        $commonid_array = array();
        foreach ($goods_array as $val) {
            $commonid_array[] = $val['goods_commonid'];
        }
        $return1 = $this->editGoodsCommonById($update1, $commonid_array);
        $return2 = $this->editGoods($update2, array('goods_commonid' => array('in', $commonid_array)));
        if ($return1 && $return2) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 更新商品信息（审核失败）
     *
     * @param array $condition
     * @param array $update1
     * @param array $update2
     * @return boolean
     */
    public function editProducesVerifyFail($condition, $update1, $update2 = array()) {
        $result = $this->editProduces($condition, $update1, $update2);
        if ($result) {
            $commonlist = $this->getGoodsCommonList($condition, 'goods_commonid,store_id,goods_verifyremark', 0);
            foreach ($commonlist as $val) {
                $param = array();
                $param['common_id'] = $val['goods_commonid'];
                $param['remark']= $val['goods_verifyremark'];
                $this->_sendStoreMsg('goods_verify', $val['store_id'], $param);
            }
        }
    }

    /**
     * 更新未锁定商品信息
     *
     * @param array $condition
     * @param array $update1
     * @param array $update2
     * @return boolean
     */
    public function editProducesNoLock($condition, $update1, $update2 = array()) {
        $condition['goods_lock'] = 0;
        return $this->editProduces($condition, $update1, $update2);
    }
    /**
     * 商品批量参与会员折扣
     * @param array $condition 条件
     * @return boolean
     */
    public function editProducesvip($condition){
        $update = array('is_vip_price' => 1);
        return $this->editProducesNoLock1($condition, $update);
    }
     /**
     * 商品批量取消会员折扣
     * @param array $condition 条件
     * @return boolean
     */
    public function cancelProducesvip($condition){
        $update = array('is_vip_price' => 0);
        return $this->editProducesNoLock1($condition, $update);
    }
    /**
     * 更新未锁定商品信息（只是会员价设置用）
     *
     * @param array $condition
     * @param array $update1
     * @param array $update2
     * @return boolean
     */
    public function editProducesNoLock1($condition, $update1, $update2 = array()) {
        $condition['goods_lock'] = 0;
        return $this->editProduces2($condition, $update1, $update2);
    }
    /**
     * 更新商品信息（只是会员价设置专用）
     *
     * @param array $condition
     * @param array $update1
     * @param array $update2
     * @return boolean
     */
    public function editProduces2($condition, $update1, $update2 = array()) {
        $update2 = empty($update2) ? $update1 : $update2;
        $goods_array = $this->getGoodsCommonList($condition, 'goods_commonid', 0);
        if (empty($goods_array)) {
            return true;
        }
        $commonid_array = array();
        foreach ($goods_array as $val) {
            $commonid_array[] = $val['goods_commonid'];
        }
        $return1 = $this->editGoodsCommonById($update1, $commonid_array);
        // $return2 = $this->editGoods($update2, array('goods_commonid' => array('in', $commonid_array)));
        if ($return1 ) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * 商品下架
     * @param array $condition 条件
     * @return boolean
     */
    public function editProducesOffline($condition){
        $update['goods_state'] = self::STATE0;
        $update['is_new'] = 0;
        $update2['goods_state'] = self::STATE0;
        return $this->editProducesNoLock($condition, $update,$update2);
    }

    /**
     * 商品上架
     * @param array $condition 条件
     * @return boolean
     */
    public function editProducesOnline($condition){
        $update = array('goods_state' => self::STATE1);
        // 禁售商品、审核失败商品不能上架。
        $condition['goods_state'] = self::STATE0;
        $condition['goods_verify'] = array('neq', self::VERIFY0);
        // 修改预约商品状态
        $update['is_appoint'] = 0;
        return $this->editProduces($condition, $update);
    }
    /**
     * 新品上架
     */
    public function editProducesNewOnline($condition,$commonid){
        // 禁售商品、审核失败商品不能上架。
        $condition['goods_state'] = self::STATE0;
        $condition['goods_verify'] = array('neq', self::VERIFY0);
        // 修改预约商品状态
        $update['is_appoint'] = 0;
        //获取新品规则，更改goods_common表
        $model_setting = Model('setting');
        $goods_show_time = $model_setting->getRowSetting('goods_show_time');
        $goods_show_discount = $model_setting->getRowSetting('goods_show_discount');
        if (!$goods_show_discount['value']) {
            showDialog('新品折扣率为零，请确保新品折扣率填写正确！', '', 'error');
        }
        $update1['goods_puton_time'] = TIMESTAMP;
        $update1['goods_show_time'] = $goods_show_time['value'] * 3600;
        $update1['goods_new_discount'] = $goods_show_discount['value'];
        $update1['is_new'] = 1;
        $update1['goods_state'] = self::STATE1;
        $sql = "UPDATE 718shop_goods SET goods_initial_price = goods_price, goods_price = goods_price*".$goods_show_discount['value']."/100,is_group_ladder=7,goods_state=".self::STATE1." WHERE goods_commonid IN (".$commonid.")";
        // ob_start();
        // var_dump($commonid);
        // $result = ob_get_clean();
        // file_put_contents('C:\Users\Administrator\Desktop\abcd.txt', $return2);die;
        return $this->editProducesNew($condition, $update1,$sql);//1common2goods
    }
    /**
     * 更新商品新品信息
     */
    public function editProducesNew($condition, $update1, $sql) {
        $goods_array = $this->getGoodsCommonList($condition, 'goods_commonid', 0);
        if (empty($goods_array)) {
            return true;
        }
        $commonid_array = array();
        foreach ($goods_array as $val) {
            $commonid_array[] = $val['goods_commonid'];
        }
        $return1 = $this->editGoodsCommonById($update1, $commonid_array);
        $return2 = Model()->execute($sql);
        if ($return1 && $return2) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 违规下架
     *
     * @param array $update
     * @param array $condition
     * @return boolean
     */
    public function editProducesLockUp($update, $condition) {
        $update_param['goods_state'] = self::STATE10;
        $update = array_merge($update, $update_param);
        $return = $this->editProduces($condition, $update, $update_param);
        if ($return) {
            // 商品违规下架发送店铺消息
            $common_list = $this->getGoodsCommonList($condition, 'goods_commonid,store_id,goods_stateremark', 0);
            foreach ($common_list as $val) {
                $param = array();
                $param['remark'] = $val['goods_stateremark'];
                $param['common_id'] = $val['goods_commonid'];
                $this->_sendStoreMsg('goods_violation', $val['store_id'], $param);
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * 获取单条商品SKU信息
     *
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getGoodsInfo($condition, $field = '*') {
        return $this->table('goods')->field($field)->where($condition)->find();
    }

    /**
     * 获取单条商品SKU信息及其促销信息
     *
     * @param int $goods_id
     * @param string $field
     * @return array
     */
    public function getGoodsOnlineInfoForShare($goods_id) {
        $goods_info = $this->getGoodsOnlineInfoAndPromotionById($goods_id);
        if (empty($goods_info)) {
            return array();
        }
		//抢购
	    if (isset($goods_info['groupbuy_info'])) {
	        $goods_info['promotion_type'] = '抢购';
	        $goods_info['promotion_price'] = $goods_info['groupbuy_info']['groupbuy_price'];
	    }

	    if (isset($goods_info['xianshi_info'])) {
	        $goods_info['promotion_type'] = '限时折扣';
	        $goods_info['promotion_price'] = $goods_info['xianshi_info']['xianshi_price'];
	    }
		return $goods_info;
    }

    /**
     * 查询出售中的商品详细信息及其促销信息
     * @param int $goods_id
     * @return array
     */
    public function getGoodsOnlineInfoAndPromotionById($goods_id,$member_id=0) {
        $goods_info = $this->getGoodsInfoAndPromotionById($goods_id,$member_id);
        if (empty($goods_info) || $goods_info['goods_state'] != self::STATE1 || $goods_info['goods_verify'] != self::VERIFY1 || $goods_info['is_deleted'] != 0) {
            return array();
        }
        return $goods_info;
    }

    /**
     * 查询商品详细信息及其促销信息
     * @param int $goods_id
     * @return array
     */
    public function getGoodsInfoAndPromotionById($goods_id,$member_id) {
        $goods_info = $this->getGoodsInfoByID($goods_id);
        if (empty($goods_info)) {
            return array();
        }
        if(!empty($member_id)){
            $model_member = model('member');
            $member_info = $model_member->table('member')->where(array('member_id'=>$member_id))->find();
            if($member_info['is_xinren'] == 2){
                $goods_info['xinren_info'] = '';
            }else{
                //新人专享
                $goods_info['xinren_info'] = Model('p_xinren_goods')->getXinrenGoodsInfoByGoodsID($goods_info['goods_id']);
            }
        }else{
            //新人专享
            $goods_info['xinren_info'] = Model('p_xinren_goods')->getXinrenGoodsInfoByGoodsID($goods_info['goods_id']);
        }
        // $model_member = model('member');
        // $member_info = $model_member->table('member')->where(array('member_id'=>$member_id))->find();
        // if($member_info['is_xinren'] == 1){
        //     //新人专享
        //     $goods_info['xinren_info'] = Model('p_xinren_goods')->getXinrenGoodsInfoByGoodsID($goods_info['goods_id']);
        // }
        // //新人专享
        // $goods_info['xinren_info'] = Model('p_xinren_goods')->getXinrenGoodsInfoByGoodsID($goods_info['goods_id']);
        
        //抢购
        if (C('groupbuy_allow')) {
            // $goods_info['groupbuy_info'] = Model('groupbuy')->getGroupbuyInfoByGoodsCommonID($goods_info['goods_commonid']);
             $goods_info['groupbuy_info'] = Model('groupbuy')->
            getGroupbuyInfoByGoodsID($goods_info['goods_id']);
        }

        //限时折扣
        if (C('promotion_allow') && empty($goods_info['groupbuy_info'])) {
            $goods_info['xianshi_info'] = Model('p_xianshi_goods')->getXianshiGoodsInfoByGoodsID($goods_info['goods_id']);
        }
        return $goods_info;
    }

    /**
     * 查询出售中的商品列表及其促销信息
     * @param array $goodsid_array
     * @return array
     */
    public function getGoodsOnlineListAndPromotionByIdArray($goodsid_array) {
        if (empty($goodsid_array) || !is_array($goodsid_array)) return array();

        $goods_list = array();
        foreach ($goodsid_array as $goods_id) {
            $goods_info = $this->getGoodsOnlineInfoAndPromotionById($goods_id);
            if (!empty($goods_info)) $goods_list[] = $goods_info;
        }

        return $goods_list;
    }

    /**
     * 获取单条商品信息
     *
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getGoodeCommonInfo($condition, $field = '*') {
        return $this->table('goods_common')->field($field)->where($condition)->find();
    }
    
    /**181119 新添加
     * 获取单条商品信息
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getGoodeCommonInfo1($condition = array(), $field = '*') {
        return $this->table('goods_common')->field($field)->where($condition)->find();
    }


/**
     * 获取单条商品信息 --跨境
     *
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getGoodeKuajingInfo($condition, $field = '*') {
        return $this->table('goods_kuajing_d')->field($field)->where($condition)->find();
    }

    /**
     * 取得商品详细信息（优先查询缓存）
     * 如果未找到，则缓存所有字段
     * @param int $goods_commonid
     * @param string $fields 需要取得的缓存键值, 例如：'*','goods_name,store_name'
     * @return array
     */
    public function getGoodeCommonInfoByID($goods_commonid, $fields = '*') {
        $common_info = $this->_rGoodsCommonCache($goods_commonid, $fields);
        if (empty($common_info)) {
            $common_info = $this->getGoodeCommonInfo(array('goods_commonid'=>$goods_commonid));
            $this->_wGoodsCommonCache($goods_commonid, $common_info);
        }
        return $common_info;
    }




    /**
     * 获得商品SKU某字段的和
     *
     * @param array $condition
     * @param string $field
     * @return boolean
     */
    public function getGoodsSum($condition, $field) {
        return $this->table('goods')->where($condition)->sum($field);
    }

    /**
     * 获得商品SKU数量
     *
     * @param array $condition
     * @param string $field
     * @return int
     */
    public function getGoodsCount($condition) {
        return $this->table('goods')->where($condition)->count();
    }

    /**
     * 获得出售中商品SKU数量
     *
     * @param array $condition
     * @param string $field
     * @return int
     */
    public function getGoodsOnlineCount($condition, $field = '*') {
        $condition['goods_state']   = self::STATE1;
        $condition['goods_verify']  = self::VERIFY1;
        $condition['is_deleted']  = 0;
        return $this->table('goods')->where($condition)->group('')->count($field);
    }
    /**
     * 获得商品数量
     *
     * @param array $condition
     * @param string $field
     * @return int
     */
    public function getGoodsCommonCount($condition) {
        return $this->table('goods_common')->where($condition)->count();
    }

    /**
     * 出售中的商品数量
     *
     * @param array $condition
     * @return int
     */
    public function getGoodsCommonOnlineCount($condition) {
        $condition['goods_state']   = self::STATE1;
        $condition['goods_verify']  = self::VERIFY1;
        return $this->getGoodsCommonCount($condition);
    }

    /**
     * 仓库中的商品数量
     *
     * @param array $condition
     * @return int
     */
    public function getGoodsCommonOfflineCount($condition) {
        $condition['goods_state']   = self::STATE0;
        $condition['goods_verify']  = self::VERIFY1;
        return $this->getGoodsCommonCount($condition);
    }

    /**
     * 等待审核的商品数量
     *
     * @param array $condition
     * @return int
     */
    public function getGoodsCommonWaitVerifyCount($condition) {
        $condition['goods_verify']  = self::VERIFY10;
        return $this->getGoodsCommonCount($condition);
    }

    /**
     * 审核失败的商品数量
     *
     * @param array $condition
     * @return int
     */
    public function getGoodsCommonVerifyFailCount($condition) {
        $condition['goods_verify']  = self::VERIFY0;
        return $this->getGoodsCommonCount($condition);
    }

    /**
     * 违规下架的商品数量
     *
     * @param array $condition
     * @return int
     */
    public function getGoodsCommonLockUpCount($condition) {
        $condition['goods_state']   = self::STATE10;
        $condition['goods_verify']  = self::VERIFY1;
        return $this->getGoodsCommonCount($condition);
    }

    /**
     * 商品图片列表
     *
     * @param array $condition
     * @param array $order
     * @param string $field
     * @return array
     */
    public function getGoodsImageList($condition, $field = '*', $order = 'is_default desc,goods_image_sort asc') {
        $this->cls();
        return $this->table('goods_images')->field($field)->where($condition)->order($order)->select();
    }

    /**
     * 删除商品SKU信息
     *
     * @param array $condition
     * @return boolean
     */
    public function delGoods($condition) {
        $goods_list = $this->getGoodsList($condition, 'goods_id,goods_commonid,goods_kuajingD_id,store_id');
        if (!empty($goods_list)) {
            $goodsid_array = array();
            $kuajingid_array = array();
            // 删除商品二维码
            foreach ($goods_list as $val) {
                $goodsid_array[] = $val['goods_id'];
                $kuajingid_array[] = $val['goods_kuajingD_id'];
                @unlink(BASE_UPLOAD_PATH.DS.ATTACH_STORE.DS.$val['store_id'].DS.$val['goods_id'].'.png');
                // 删除商品缓存
                $this->_dGoodsCache($val['goods_id']);
                // 删除商品规格缓存
                $this->_dGoodsSpecCache($val['goods_commonid']);
            }
            // 删除属性关联表数据
            $this->table('goods_attr_index')->where(array('goods_id' => array('in', $goodsid_array)))->delete();
            // 删除优惠套装商品
            Model('p_bundling')->delBundlingGoods(array('goods_id' => array('in', $goodsid_array)));
            // 优惠套餐活动下架
            Model('p_bundling')->editBundlingCloseByGoodsIds(array('goods_id' => array('in', $goodsid_array)));
            // 推荐展位商品
            Model('p_booth')->delBoothGoods(array('goods_id' => array('in', $goodsid_array)));
            // 限时折扣
            Model('p_xianshi_goods')->delXianshiGoods(array('goods_id' => array('in', $goodsid_array)));
            //删除商品浏览记录
            Model('goods_browse')->delGoodsbrowse(array('goods_id' => array('in', $goodsid_array)));
            // 删除买家收藏表数据
            $this->table('favorites')->where(array('fav_id' => array('in', $goodsid_array), 'fav_type' => 'goods'))->delete();
            // 删除商品赠品
            Model('goods_gift')->delGoodsGift(array('goods_id' => array('in', $goodsid_array), 'gift_goodsid'=> array('in', $goodsid_array), '_op' => 'or'));
            // 删除推荐组合
            Model('goods_combo')->delGoodsCombo(array('goods_id' => array('in', $goodsid_array), 'combo_goodsid' => array('in', $goodsid_array), '_op' => 'or'));
            // 删除跨境电子口岸数据
            $this->table('goods_kuajing_d')->where(array('id' => array('in', $kuajingid_array)))->delete();
        }
        return $this->table('goods')->where($condition)->delete();
    }

    /**
     * 删除商品图片表信息
     *
     * @param array $condition
     * @return boolean
     */
    public function delGoodsImages($condition) {
        $image_list = $this->getGoodsImageList($condition, 'goods_commonid,color_id');
        if (empty($image_list)) {
            return true;
        }
        $result = $this->table('goods_images')->where($condition)->delete();
        if ($result) {
            foreach ($image_list as $val) {
                $this->_dGoodsImageCache($val['goods_commonid'] . '|' . $val['color_id']);
            }
        }
        return $result;
    }

    /**
     * 商品删除及相关信息
     *
     * @param   array $condition 列表条件
     * @return boolean
     */
    public function delGoodsAll($condition) {
        $goods_list = $this->getGoodsList($condition, 'goods_id,goods_commonid,store_id');
        if (empty($goods_list)) {
            return false;
        }
        $goodsid_array = array();
        $commonid_array = array();
       // $kuajingid_array = array();
        foreach ($goods_list as $val) {
            $goodsid_array[] = $val['goods_id'];
            $commonid_array[] = $val['goods_commonid'];
            // 商品公共缓存
            $this->_dGoodsCommonCache($val['goods_commonid']);
            // 商品规格缓存
            $this->_dGoodsSpecCache($val['goods_commonid']);
        }
        $commonid_array = array_unique($commonid_array);

        // 删除商品表数据
        $this->delGoods(array('goods_id' => array('in', $goodsid_array)));
        // 删除商品公共表数据
        $this->table('goods_common')->where(array('goods_commonid' => array('in', $commonid_array)))->delete();
        // 删除商品图片表数据
        $this->delGoodsImages(array('goods_commonid' => array('in', $commonid_array)));
        // 删除商品F码
        Model('goods_fcode')->delGoodsFCode(array('goods_commonid' => array('in', $commonid_array)));
        return true;
    }

    /*
     * 商品编辑，从多规格变为无规格提交时删除该商品的所有信息
     * ***
     */
    public function delGoodsonline($condition) {
        // 删除商品表数据
        $this->delGoods(array('goods_commonid' => $condition['goods_commonid']));
        // 删除商品公共表数据
        $this->table('goods_common')->where(array('goods_commonid' => $condition['goods_commonid']))->delete();
        // 删除商品图片表数据
        $this->delGoodsImages(array('goods_commonid' => $condition['goods_commonid']));
        // 删除商品F码
        Model('goods_fcode')->delGoodsFCode(array('goods_commonid' => $condition['goods_commonid']));
        return true;
    }

    /**
     * 删除未锁定商品
     * @param unknown $condition
     */
    public function delGoodsNoLock($condition) {
        $condition['goods_lock'] = 0;
        $common_array = $this->getGoodsCommonList($condition, 'goods_commonid', 0);
        $common_array = array_under_reset($common_array, 'goods_commonid');
        $commonid_array = array_keys($common_array);
        return $this->delGoodsAll(array('goods_commonid' => array('in', $commonid_array)));
    }

    /**
     * 发送店铺消息
     * @param string $code
     * @param int $store_id
     * @param array $param
     */
    private function _sendStoreMsg($code, $store_id, $param) {
        QueueClient::push('sendStoreMsg', array('code' => $code, 'store_id' => $store_id, 'param' => $param));
    }

     /**
      * 获得商品子分类的ID
      * @param array $condition
      * @return array
      */
    private function _getRecursiveClass($condition){
        if (isset($condition['gc_id']) && !is_array($condition['gc_id'])) {
            $gc_list = Model('goods_class')->getGoodsClassForCacheModel();
            if (!empty($gc_list[$condition['gc_id']])) {
                $gc_id[] = $condition['gc_id'];
                $gcchild_id = empty($gc_list[$condition['gc_id']]['child']) ? array() : explode(',', $gc_list[$condition['gc_id']]['child']);
                $gcchildchild_id = empty($gc_list[$condition['gc_id']]['childchild']) ? array() : explode(',', $gc_list[$condition['gc_id']]['childchild']);
                $gc_id = array_merge($gc_id, $gcchild_id, $gcchildchild_id);
                $condition['gc_id'] = array('in', $gc_id);
            }
        }
        return $condition;
    }

    /**
     * 由ID取得在售单个虚拟商品信息
     * @param unknown $goods_id
     * @param string $field 需要取得的缓存键值, 例如：'*','goods_name,store_name'
     * @return array
     */
    public function getVirtualGoodsOnlineInfoByID($goods_id) {
        $goods_info = $this->getGoodsInfoByID($goods_id,'*');
        return $goods_info['is_virtual'] == 1 && $goods_info['virtual_indate'] >= TIMESTAMP ? $goods_info : array();
    }

    /**
     * 取得商品详细信息（优先查询缓存）（在售）
     * 如果未找到，则缓存所有字段
     * @param int $goods_id
     * @param string $field 需要取得的缓存键值, 例如：'*','goods_name,store_name'
     * @return array
     */
    public function getGoodsOnlineInfoByID($goods_id, $field = '*') {
        if ($field != '*') {
            $field .= ',goods_state,goods_verify';
        }
        $goods_info = $this->getGoodsInfoByID($goods_id,trim($field,','));
        if ($goods_info['goods_state'] != self::STATE1 || $goods_info['goods_verify'] != self::VERIFY1) {
            $goods_info = array();
        }
        return $goods_info;
    }

    /**
     * 取得商品详细信息（优先查询缓存）
     * 如果未找到，则缓存所有字段
     * @param int $goods_id
     * @param string $fields 需要取得的缓存键值, 例如：'*','goods_name,store_name'
     * @return array
     */
    public function getGoodsInfoByID($goods_id, $fields = '*') {
        $goods_info = $this->_rGoodsCache($goods_id, $fields);
        if (empty($goods_info)) {
            $goods_info = $this->getGoodsInfo(array('goods_id'=>$goods_id));
            $this->_wGoodsCache($goods_id, $goods_info);
        }
        return $goods_info;
    }

    /**
     * 验证是否为普通商品
     * @param array $goods 商品数组
     * @return boolean
     */
    public function checkIsGeneral($goods) {
        if ($goods['is_virtual'] == 1 || $goods['is_fcode'] == 1 || $goods['is_presell'] == 1) {
            return false;
        }
        return true;
    }

    /**
     * 验证是否允许送赠品
     * @param unknown $goods
     * @return boolean
     */
    public function checkGoodsIfAllowGift($goods) {
        if ($goods['is_virtual'] == 1) {
            return false;
        }
        return true;
    }

    public function checkGoodsIfAllowCombo($goods) {
        if ($goods['is_virtual'] == 1 || $goods['is_fcode'] == 1 || $goods['is_presell'] == 1 || $goods['is_appoint'] == 1) {
            return false;
        }
        return true;
    }

    /**
     * 获得商品规格数组
     * @param unknown $common_id
     */
    public function getGoodsSpecListByCommonId($common_id) {
        $spec_list = $this->_rGoodsSpecCache($common_id);
        if (empty($spec_list)) {
            $spec_array = $this->getGoodsList(array('goods_commonid' => $common_id), 'goods_spec,goods_id,store_id,goods_image,color_id');
            $spec_list['spec'] = serialize($spec_array);
            $this->_wGoodsSpecCache($common_id, $spec_list);
        }
        $spec_array = unserialize($spec_list['spec']);
        return $spec_array;
    }

    /**
     * 获得商品图片数组
     * @param int $goods_id
     * @param array $condition
     */
    public function getGoodsImageByKey($key) {
        $image_list = $this->_rGoodsImageCache($key);
        if (empty($image_list)) {
            $array = explode('|', $key);
            list($common_id, $color_id) = $array;
            $image_more = $this->getGoodsImageList(array('goods_commonid' => $common_id, 'color_id' => $color_id), 'goods_image');
            $image_list['image'] = serialize($image_more);
            $this->_wGoodsImageCache($key, $image_list);
        }
        $image_more = unserialize($image_list['image']);
        return $image_more;
    }

    /**
     * 读取商品缓存
     * @param int $goods_id
     * @param string $fields
     * @return array
     */
    private function _rGoodsCache($goods_id, $fields) {
        return rcache($goods_id, 'goods', $fields);
    }

    /**
     * 写入商品缓存
     * @param int $goods_id
     * @param array $goods_info
     * @return boolean
     */
    private function _wGoodsCache($goods_id, $goods_info) {
        return wcache($goods_id, $goods_info, 'goods');
    }

    /**
     * 删除商品缓存
     * @param int $goods_id
     * @return boolean
     */
    private function _dGoodsCache($goods_id) {
        return dcache($goods_id, 'goods');
    }

    /**
     * 读取商品公共缓存
     * @param int $goods_commonid
     * @param string $fields
     * @return array
     */
    private function _rGoodsCommonCache($goods_commonid, $fields) {
        return rcache($goods_commonid, 'goods_common', $fields);
    }

    /**
     * 写入商品公共缓存
     * @param int $goods_commonid
     * @param array $common_info
     * @return boolean
     */
    private function _wGoodsCommonCache($goods_commonid, $common_info) {
        return wcache($goods_commonid, $common_info, 'goods_common');
    }

    /**
     * 删除商品公共缓存
     * @param int $goods_commonid
     * @return boolean
     */
    private function _dGoodsCommonCache($goods_commonid) {
        return dcache($goods_commonid, 'goods_common');
    }

    /**
     * 读取商品规格缓存
     * @param int $goods_commonid
     * @param string $fields
     * @return array
     */
    private function _rGoodsSpecCache($goods_commonid) {
        return rcache($goods_commonid, 'goods_spec');
    }

    /**
     * 写入商品规格缓存
     * @param int $goods_commonid
     * @param array $spec_list
     * @return boolean
     */
    private function _wGoodsSpecCache($goods_commonid, $spec_list) {
        return wcache($goods_commonid, $spec_list, 'goods_spec');
    }

    /**
     * 删除商品规格缓存
     * @param int $goods_commonid
     * @return boolean
     */
    private function _dGoodsSpecCache($goods_commonid) {
        return dcache($goods_commonid, 'goods_spec');
    }

    /**
     * 读取商品图片缓存
     * @param int $key ($goods_commonid .'|'. $color_id)
     * @param string $fields
     * @return array
     */
    private function _rGoodsImageCache($key) {
        return rcache($key, 'goods_image');
    }

    /**
     * 写入商品图片缓存
     * @param int $key ($goods_commonid .'|'. $color_id)
     * @param array $image_list
     * @return boolean
     */
    private function _wGoodsImageCache($key, $image_list) {
        return wcache($key, $image_list, 'goods_image');
    }

    /**
     * 删除商品图片缓存
     * @param int $key ($goods_commonid .'|'. $color_id)
     * @return boolean
     */
    private function _dGoodsImageCache($key) {
        return dcache($key, 'goods_image');
    }

    /**
     * 获取单条商品信息
     *
     * @param int $goods_id
     * @return array
     */
    public function getGoodsDetail($goods_id) {
        if($goods_id <= 0) {
            return null;
        }
        $result1 = $this->getGoodsInfoAndPromotionById($goods_id,'');
        
        if (empty($result1)) {
            return null;
        }
        $result2 = $this->getGoodeCommonInfoByID($result1['goods_commonid']);
        $goods_info = array_merge($result2, $result1);

        $goods_info['spec_value'] = unserialize($goods_info['spec_value']);
        $goods_info['spec_name'] = unserialize($goods_info['spec_name']);
        $goods_info['goods_spec'] = unserialize($goods_info['goods_spec']);
        $goods_info['goods_attr'] = unserialize($goods_info['goods_attr']);

        // 手机商品描述
        if ($goods_info['mobile_body'] != '') {
            $mobile_body_array = unserialize($goods_info['mobile_body']);
            if (is_array($mobile_body_array)) {
                $mobile_body = '';
                foreach ($mobile_body_array as $val) {
                    switch ($val['type']) {
                    	case 'text':
                    	    $mobile_body .= '<div>' . $val['value'] . '</div>';
                    	    break;
                    	case 'image':
                    	    $mobile_body .= '<img src="' . $val['value'] . '">';
                    	    break;
                    }
                }
                $goods_info['mobile_body'] = $mobile_body;
            }
        }

        // 查询所有规格商品
        $spec_array = $this->getGoodsSpecListByCommonId($goods_info['goods_commonid']);
        $spec_list = array();       // 各规格商品地址，js使用
        $spec_list_mobile = array();       // 各规格商品地址，js使用
        $spec_image = array();      // 各规格商品主图，规格颜色图片使用
        foreach ($spec_array as $key => $value) {
            $s_array = unserialize($value['goods_spec']);
            $tmp_array = array();
            if (!empty($s_array) && is_array($s_array)) {
                foreach ($s_array as $k => $v) {
                    $tmp_array[] = $k;
                }
            }
            sort($tmp_array);
            $spec_sign = implode('|', $tmp_array);
            $tpl_spec = array();
            $tpl_spec['sign'] = $spec_sign;
            $tpl_spec['url'] = urlShop('goods', 'index', array('goods_id' => $value['goods_id']));
            $spec_list[] = $tpl_spec;
            $spec_list_mobile[$spec_sign] = $value['goods_id'];
            $spec_image[$value['color_id']] = thumb($value, 60);
        }
        $spec_list = json_encode($spec_list);

        // 商品多图
        $image_more = $this->getGoodsImageByKey($goods_info['goods_commonid'] . '|' . $goods_info['color_id']);
        $goods_image = array();
        $goods_image_mobile = array();
        if (!empty($image_more)) {
            foreach ($image_more as $val) {
	//专用放大镜
                $goods_image[] = array(cthumb($val['goods_image'], 60, $goods_info['store_id']),cthumb($val['goods_image'], 360, $goods_info['store_id']),cthumb($val['goods_image'], 1280, $goods_info['store_id']));

				//$goods_image[] = array(UPLOAD_SITE_URL . '/' . ATTACH_GOODS . '/4/' . $val['goods_image'],UPLOAD_SITE_URL . '/' . ATTACH_GOODS . '/4/' . $val['goods_image'],UPLOAD_SITE_URL . '/' . ATTACH_GOODS . '/4/' . $val['goods_image']);

                $goods_image_mobile[] = cthumb($val['goods_image'], 360, $goods_info['store_id']);
            }
        } else {
            // $goods_image[] = "{ title : '', levelA : '".thumb($goods_info, 60)."', levelB : '".thumb($goods_info, 360)."', levelC : '".thumb($goods_info, 360)."', levelD : '".thumb($goods_info, 1280)."'}";
            //   $goods_image[] = thumb($goods_info, 96).",".thumb($goods_info, 360).",".thumb($goods_info, 360).",".thumb($goods_info, 1280);
              $goods_image[] = explode(',',thumb($goods_info, 96).",".thumb($goods_info, 360).",".thumb($goods_info, 360).",".thumb($goods_info, 1280));
			  
			  //$goods_image[] = UPLOAD_SITE_URL . '/' . ATTACH_GOODS . '/4/' . $goods_info['goods_image'].",".UPLOAD_SITE_URL . '/' . ATTACH_GOODS . '/4/' . $goods_info['goods_image'].",".UPLOAD_SITE_URL . '/' . ATTACH_GOODS . '/4/' . $goods_info['goods_image'].",".UPLOAD_SITE_URL . '/' . ATTACH_GOODS . '/4/' . $goods_info['goods_image'];
			 
            $goods_image_mobile[] = thumb($goods_info, 360);
        }

        //抢购
        if (!empty($goods_info['groupbuy_info'])) {
            $goods_info['promotion_type'] = 'groupbuy';
            $goods_info['title'] = '抢购';
            $goods_info['remark'] = $goods_info['groupbuy_info']['remark'];
            $goods_info['promotion_price'] = $goods_info['groupbuy_info']['groupbuy_price'];
            $goods_info['down_price'] = ncPriceFormat($goods_info['goods_price'] - $goods_info['groupbuy_info']['groupbuy_price']);
            $goods_info['upper_limit'] = $goods_info['groupbuy_info']['upper_limit'];
            unset($goods_info['groupbuy_info']);
        }

        //限时折扣
        if (!empty($goods_info['xianshi_info'])) {
            $goods_info['promotion_type'] = 'xianshi';
            $goods_info['title'] = $goods_info['xianshi_info']['xianshi_title'];
            $goods_info['remark'] = $goods_info['xianshi_info']['xianshi_title'];
            $goods_info['promotion_price'] = $goods_info['xianshi_info']['xianshi_price'];
            $goods_info['down_price'] = ncPriceFormat($goods_info['goods_price'] - $goods_info['xianshi_info']['xianshi_price']);
            $goods_info['lower_limit'] = $goods_info['xianshi_info']['lower_limit'];
            $goods_info['explain'] = $goods_info['xianshi_info']['xianshi_explain'];
			if($goods_info['xianshi_info']['start_time']<=TIMESTAMP && $goods_info['xianshi_info']['end_time']>=TIMESTAMP){
				$goods_info['xianshi_start_time'] = $goods_info['xianshi_info']['start_time'];
				$goods_info['xianshi_end_time'] = $goods_info['xianshi_info']['end_time'];
			}
            unset($goods_info['xianshi_info']);
        }

        // 验证是否允许送赠品
        if ($this->checkGoodsIfAllowGift($goods_info)) {
            $gift_array = Model('goods_gift')->getGoodsGiftListByGoodsId($goods_id);
            if (!empty($gift_array)) {
                $goods_info['have_gift'] = 'gift';
            }
        }

        // 加入购物车按钮
        $goods_info['cart'] = true;
        //虚拟、F码、预售不显示加入购物车
        if ($goods_info['is_virtual'] == 1 || $goods_info['is_fcode'] == 1 || $goods_info['is_presell'] == 1) {
            $goods_info['cart'] = false;
        }

        // 立即购买文字显示
        $goods_info['buynow_text'] = '立即购买';
        if ($goods_info['is_presell'] == 1) {
            $goods_info['buynow_text'] = '预售购买';
        } elseif ($goods_info['is_fcode'] == 1) {
            $goods_info['buynow_text'] = 'F码购买';
        }

        //满即送
        $mansong_info = ($goods_info['is_virtual'] == 1) ? array() : Model('p_mansong')->getMansongInfoByStoregcID($goods_info['store_id'],$goods_info['gc_id_1']); //gai
	$mansong_all = ($goods_info['is_virtual'] == 1) ? array() : Model('p_mansong')->getMansongInfoByStoregcID($goods_info['store_id'],'1'); //xinjia

        // 商品受关注次数加1
        $goods_info['goods_click'] = intval($goods_info['goods_click']) + 1;
        if (C('cache_open')) {
            $this->_wGoodsCache($goods_id, array('goods_click' => $goods_info['goods_click']));
            wcache('updateRedisDate', array($goods_id => $goods_info['goods_click']), 'goodsClick');
        } else {
            $this->editGoodsById(array('goods_click' => array('exp', 'goods_click + 1')), $goods_id);
        }
        $result = array();
        $result['goods_info'] = $goods_info;
        $result['spec_list'] = $spec_list;
        $result['spec_list_mobile'] = $spec_list_mobile;
        $result['spec_image'] = $spec_image;
        $result['goods_image'] = $goods_image;
        $result['goods_image_mobile'] = $goods_image_mobile;
        $result['mansong_info'] = $mansong_info;
	$result['mansong_all'] = $mansong_all;//xinjia
        $result['gift_array'] = $gift_array;
        return $result;
    }
	
	
	public function getMobileBodyByCommonID($goods_commonid, $fields = 'mobile_body') {
         $common_info =$this->_rGoodsCommonCache($goods_commonid, $fields);
         if (empty($common_info)) {
             $common_info =$this->getGoodeCommonInfo(array('goods_commonid'=>$goods_commonid));
            $this->_wGoodsCommonCache($goods_commonid, $common_info);
         }

                      
                    // 手机商品描述
         if ($common_info['mobile_body'] != ''){
             $mobile_body_array =unserialize($common_info['mobile_body']);
             if (is_array($mobile_body_array)){
                 $mobile_body = '';
                 foreach ($mobile_body_array as$val) {
                     switch ($val['type']) {
                        case 'text':
                            $mobile_body .='<div>' . $val['value'] . '</div>';
                            break;
                        case 'image':
                            $mobile_body .='<img src="' . $val['value'] . '">';
                            break;
                     }
                 }
                 $common_info['mobile_body'] =$mobile_body;
             }
         }
         return $common_info;
     }	
	
     public function getGoodsAllList($condition, $field = '*', $group = '',$order = '', $limit = 0, $page = 0, $lock = false, $count = 0){
         $condition = $this->_getRecursiveClass($condition);
         return $this->table('goods_common')->field($field)->where($condition)->order($order)->group($group)->limit($limit)->page($page, $count)->lock($lock)->select();
     }
}
