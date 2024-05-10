<?php
/**
 *一卡通接口操作
 */
defined('In718Shop') or exit('Access Invalid!');

const URL = 'ykt.zhonghaokeji.cn:8085/YuCartoon/thirdController/';
const DATA = array(
    "compandName" => "新零售",
    "companyId" => "16",
    "consumeType" => "90",
);

class card_newModel extends Model
{

    public function __construct()
    {
        parent::__construct('card');
    }

    // post请求
    public function posturl($url, $data)
    {
        $data = http_build_query($data);
        $headerArray = array('Content-Type: application/x-www-form-urlencoded');
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headerArray);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }

    public function getMemberCardInfobygh($gonghao)
    {
        $url = URL . 'getMemberInfoByPersonalID.do';
        $api_encrypt = api_encrypt(["personalID" => $gonghao]);
        $res = $this->posturl($url, ['data' => $api_encrypt]);
        $res = json_decode($res, 320);
        if ($res['state'] == 0) {
            return $res['data']['mealCard'];
        } else {
            return false;
        }
    }

    public function updateCardBaseAndConsume($data)
    {
        $data = array_merge(DATA, $data);
        $url = '';
        if ($data['consumerMoney'] > 0) {
            $url = URL . 'yktBalancePay.do';
        } elseif ($data['consumerMoney'] < 0) {
            $url = URL . 'yktBalanceRefund.do';
        } else {
            return false;
        }
        $res = $this->posturl($url, ['data' => api_encrypt($data)]);
        // print_r(json_decode($res, 320));die;
        return json_decode($res, 320);
    }

    public function updateHCConsumeExchange($data)
    {
        $data = array_merge(DATA, $data);
        $url = '';
        if ($data['consumerMoney'] > 0) {
            $url = URL . 'yktExchangeBalancePay.do';
        } elseif ($data['consumerMoney'] < 0) {
            $url = URL . 'yktExchangeBalanceRefund.do';
        } else {
            return false;
        }
        $res = $this->posturl($url, ['data' => api_encrypt($data)]);
        // print_r(json_decode($res, 320));die;
        return json_decode($res, 320);
    }
}
