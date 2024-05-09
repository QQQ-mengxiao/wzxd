<?php

defined('In718Shop') or exit('Access Invalid!');
class background_colorControl extends BaseControl
{

    /**
     * 通过id获取背景色
     * @method get
     * @param $id 1首页,2秒杀,3折扣
     */
    public function getBackgroundColorByIdOp(){

        $id = $_GET['id'];

        if(!$id){

            die(json_encode(array('code' => '100', 'message' => '参数错误', 'data' => ''),320));

        }

        $model_background_color = Model('background_color');

        $background_color = $model_background_color->getColorById($id);

        die(json_encode(array('code' => '200', 'message' => '背景色获取成功', 'data' => $background_color['color']),320));

    }

}
