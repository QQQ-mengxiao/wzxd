<?php
/**
 * 短信验证码  
 *
 */
defined('In718Shop') or exit('Access Invalid!');

class sms_captchaModel extends Model{

    public function __construct() {
        parent::__construct();
    }

    /**
     * 增加短信记录
     *
     * @param
     * @return int
     */
    public function addSms($data) {
        $captcha_id = $this->table('sms_captcha')->insert($data);
        return $captcha_id;
    }

    /**
     * 查询单条记录
     *
     * @param
     * @return array
     */
    public function getSmsInfo($condition) {
        $result = $this->table('sms_captcha')->where($condition)->order('captcha_id desc')->find();
        return $result;
    }

    /**
     * 查询记录
     *
     * @param
     * @return array
     */
    public function getSmsList($condition = array(), $page = '', $limit = '', $order = 'captcha_id desc') {
        $result = $this->table('sms_captcha')->where($condition)->page($page)->limit($limit)->order($order)->select();
        return $result;
    }

    /**
     * 查询条数
     */
    public function getSmsCount($condition = array())
    {
        $count = $this->table('sms_captcha')->where($condition)->count();
        return $count;
    }
}
