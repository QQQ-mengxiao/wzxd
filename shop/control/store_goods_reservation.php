<?php
/**
 * 商品管理
 *
 **/


defined('In718Shop') or exit ('Access Invalid!');
class store_goods_reservationControl extends BaseSellerControl {
    public function __construct() {
        parent::__construct ();
        Language::read ('member_store_goods_index');
    }
    public function indexOp() {
        $this->store_goods_reservationOp();
    }

    /**
     * 预约的商品列表
     */
    public function store_goods_reservationOp() {
        $model_goods = Model('goods');

        $where = array();
        $where['store_id'] = $_SESSION['store_id'];
        if (trim($_GET['add_time_from']) != '' || trim($_GET['add_time_to']) != '') {
            $add_time_from = strtotime(trim($_GET['add_time_from']));
            $add_time_to = strtotime(trim($_GET['add_time_to']));
            if ($add_time_from !== false || $add_time_to !== false) {
                $condition['purchase_time'] = array('purchase_time',array($add_time_from,$add_time_to));
                // var_dump($condition['purchase_time']);die;
            }
        }
        if ($_GET['good_name']) {
            $condition['good_name'] = array('like', '%,' . intval($_GET['good_name']) . ',%');
        }
        $goods_list = $model_goods->getGoodsReservationList($condition);
        foreach ($goods_list as $key => $value) {
            if ($value['status']=='10') {
                $goods_list[$key]['status_zh']='未处理';
            }else{
                $goods_list[$key]['status_zh']='已处理';
            }
        }
        Tpl::output('show_page', $model_goods->showpage());
        Tpl::output('goods_list', $goods_list);
        Tpl::showpage('store_goods_list.reservation');
    }


        /*
     * 处理预约请求
     */
    public function reservation_dealOp() {
        Language::read('member_store_index');
        $reservation_id = intval($_GET['reservation_id']);
        //获取预约详细信息
        $reservation_info = $this->get_reservation_info($reservation_id);
        if ($reservation_info['status']=='20') {
            showDialog('预约信息已处理', 'reload', 'error');
        }
        $update_reservation_info=Model('goods_reservation')->where(array('id'=>$reservation_info['id']))->update(array('status'=>'20'));
        if ($update_reservation_info) {
            showDialog('预约信息处理成功', 'reload', 'succ');
        }
    }


        /*
     * 获取预约信息
     */
    private function get_reservation_info($reservation_id) {
        if(empty($reservation_id)) {
            showMessage(Language::get('para_error'),'','html','error');
        }
        $model_goods = Model('goods');
        $reservation_info = $model_goods->getoneReservation($reservation_id);
        return $reservation_info;
    }

}
