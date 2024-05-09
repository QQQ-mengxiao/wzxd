<?php
defined('In718Shop') or exit('Access Invalid!');

class cw_deliverControl extends BaseControl
{
    /**
     * 云仓传递运单号接口[POST]
     * @param string order_sn订单编号
     * @param string shipping_code运单号
     * @param string e_code快递公司编号
     * @param string e_name快递公司名称
     * @param string deliver_explain发货备注
     * @return array
     */
    public function deliverOp(){

        $order_sn           = $_POST['order_sn'];
        $shipping_code      = $_POST['shipping_code'];
        $e_code             = $_POST['e_code'];
        $e_name             = $_POST['e_name'];
        $deliver_explain    = $_POST['deliver_explain'];

        if(!$order_sn || !$shipping_code || !$e_code || !$e_name){
            die(json_encode([
                'code'  => '0',
                'msg'   => '发货失败[参数错误]'
            ]));
        }
        
        //验证订单状态
        $order_state = Model()->table('order')->getfby_order_sn($order_sn,'order_state');
        if($order_state != 20){
            die(json_encode([
                'code'  => '0',
                'msg'   => '发货失败[订单状态异常]'
            ]));
        }

        //拼接数据
        $order_info['order_sn']     = $order_sn;
        $order_info['order_id']     = Model()->table('order')->getfby_order_sn($order_sn,'order_id');

        $shipping_express_id                = $this->getShippingExpressId($e_code,$e_name);
        $post_data['deliver_explain']       = $deliver_explain;
        $post_data['shipping_code']         = $shipping_code;
        $post_data['shipping_express_id']   = $shipping_express_id;
        $post_data['e_code']                = $e_code;
        $post_data['e_name']                = $e_name;

        $logic_order = Logic('order');
        $result = $logic_order->changeOrderPostByCW($order_info, $post_data);
        if($result){
            die(json_encode([
                'code'  => '1',
                'msg'   => '发货成功',
            ]));
        }else{
            die(json_encode([
                'code'  =>  '0',
                'msg'   =>  '发货失败[数据更新失败]'.$result,
            ]));
        }

    }

    /**
     * 拼接发送数据
     * @param string $e_code
     * @param string $e_name
     * @return int $shipping_express_id
     */
    private function getShippingExpressId($e_code,$e_name){

        $shipping_express_id        = Model()->table('express')->getfby_e_code($e_code,'id');

        if(!$shipping_express_id){
            $express_data = [
                'e_name'            => $e_name,
                'e_code'            => $e_code,
                'e_letter'          => strtoupper(substr($e_code, 0, 1)),
                'e_url'             => 'http',
            ];
            $shipping_express_id    = Model()->table('express')->insert($express_data);
        }

        return $shipping_express_id;
    }
    

}
