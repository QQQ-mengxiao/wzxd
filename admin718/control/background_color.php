<?php
/**
 * 团长管理
 */

defined('In718Shop') or exit('Access Invalid!');

class background_colorControl extends SystemControl{
	public function __construct(){
		parent::__construct();
	}

	/**
	 * 列表
	 */
	public function background_color_listOp(){

		$model_background_color = Model('background_color');

		$background_color_list = $model_background_color->getAllBackgroundColorList();
		
		Tpl::output('background_color_list',$background_color_list);
		Tpl::output('page',$model_background_color->showpage());

		Tpl::showpage('background_color.index');
	}

	/**
	 * 编辑颜色
	 */
	public function ajaxEditColorOp(){
		$model_background_color = Model('background_color');

        if (chksubmit()) {

            $id = $_POST['id'];
            $color = $_POST['color'];

			if(!$id){
            	showDialog('参数错误', 'reload');
			}

            $update = array();
            $update['color'] = $color;

			$result = $model_background_color->editBackgroundColor(array('color'=>$color), array('id'=>$id));

			if($result){
				$this->log('背景色编辑成功，id：'.$id.'，颜色编辑为：'.$color);
            	showDialog('背景色编辑成功', 'reload', 'succ');
			}else{
				showDialog('背景色编辑失败', 'reload');
			}
        }

		$background_color = $model_background_color->getColorById($_GET['id']);

        Tpl::output('id', $_GET['id']);
        Tpl::output('color', $background_color['color']);
        Tpl::showpage('background_color.edit_color', 'null_layout');
	}

}
