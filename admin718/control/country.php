<?php
/**
 * 前台模块编辑(首页)
 *
 *  */

defined('In718Shop') or exit('Access Invalid!');
class countryControl extends SystemControl{
	public function __construct(){
		parent::__construct();
		Language::read('web_config,recommend');
	}

	/**
	 * 板块列表
	 */
	public function web_configOp(){
		$model_country = Model('country');
		$style_array = $model_country->getCountryStyleList();//板块样式数组
		Tpl::output('style_array',$style_array);
		$web_list = $model_country->getWebList(array('web_page' => 'index'));
		Tpl::output('web_list',$web_list);
		Tpl::showpage('country.index');
	}

	/**
	 * 基本设置
	 */
	public function web_editOp(){
		$model_country = Model('country');
		$web_id = intval($_GET["web_id"]);
		if (chksubmit()){
			$web_array = array();
			$web_id = intval($_POST["web_id"]);
			$web_array['web_name'] = $_POST["web_name"];
			$web_array['style_name'] = $_POST["style_name"];
			$web_array['web_sort'] = intval($_POST["web_sort"]);
			$web_array['web_show'] = intval($_POST["web_show"]);
			$web_array['update_time'] = time();
			$model_country->updateWeb(array('web_id'=>$web_id),$web_array);
			$model_country->updateWebHtml($web_id,$web_array['style_name']);//更新前台显示的html内容
			$this->log(l('web_config_code_edit').'['.$_POST["web_name"].']',1);
			showMessage(Language::get('nc_common_save_succ'),'index.php?act=country&op=web_config');
		}
		$web_list = $model_country->getWebList(array('web_id'=>$web_id));
		Tpl::output('web_array',$web_list[0]);
		Tpl::showpage('country.edit');
	}

	/**
	 * 板块编辑
	 */
	public function code_editOp(){
		$model_country = Model('country');
		$web_id = intval($_GET["web_id"]);
		$code_list = $model_country->getCodeList(array('web_id'=>"$web_id"));
		if(is_array($code_list) && !empty($code_list)) {
			$model_class = Model('goods_class');
			$parent_goods_class = $model_class->getTreeClassList(2);//商品分类父类列表，只取到第二级
			if (is_array($parent_goods_class) && !empty($parent_goods_class)){
				foreach ($parent_goods_class as $k => $v){
					$parent_goods_class[$k]['gc_name'] = str_repeat("&nbsp;",$v['deep']*2).$v['gc_name'];
				}
			}
			Tpl::output('parent_goods_class',$parent_goods_class);

			$goods_class = $model_class->getTreeClassList(1);//第一级商品分类
			Tpl::output('goods_class',$goods_class);

			foreach ($code_list as $key => $val) {//将变量输出到页面
				$var_name = $val["var_name"];
				$code_info = $val["code_info"];
				$code_type = $val["code_type"];
				$val['code_info'] = $model_country->get_array($code_info,$code_type);
				Tpl::output('code_'.$var_name,$val);
			}
			$style_array = $model_country->getCountryStyleList();//样式数组
			Tpl::output('style_array',$style_array);
			$web_list = $model_country->getWebList(array('web_id'=>$web_id));
			Tpl::output('web_array',$web_list[0]);
			Tpl::showpage('country_code.edit');
		} else {
			showMessage(Language::get('nc_no_record'));
		}
	}

/**
	 * 详情页编辑
	 */
	public function country_detail_editOp(){
		$model_country = Model('country');
		$web_id = intval($_GET["web_id"]);
		$code_list = $model_country->getCodeList(array('web_id'=>"$web_id"));
		if(is_array($code_list) && !empty($code_list)) {
			$model_class = Model('goods_class');
			$parent_goods_class = $model_class->getTreeClassList(2);//商品分类父类列表，只取到第二级
			if (is_array($parent_goods_class) && !empty($parent_goods_class)){
				foreach ($parent_goods_class as $k => $v){
					$parent_goods_class[$k]['gc_name'] = str_repeat("&nbsp;",$v['deep']*2).$v['gc_name'];
				}
			}
			Tpl::output('parent_goods_class',$parent_goods_class);

			$goods_class = $model_class->getTreeClassList(1);//第一级商品分类
			Tpl::output('goods_class',$goods_class);

			foreach ($code_list as $key => $val) {//将变量输出到页面
				$var_name = $val["var_name"];
				$code_info = $val["country_info"];
				$code_type = $val["code_type"];
				$val['code_info'] = $model_country->get_array($code_info,$code_type);
				Tpl::output('code_'.$var_name,$val);
			}
			$style_array = $model_country->getCountryStyleList();//样式数组
			Tpl::output('style_array',$style_array);
			$web_list = $model_country->getWebList(array('web_id'=>$web_id));
			Tpl::output('web_array',$web_list[0]);
			Tpl::showpage('country_detail.edit');
		} else {
			showMessage(Language::get('nc_no_record'));
		}
	}

	/**
	 * 更新前台显示的html内容
	 */
	public function web_htmlOp(){
		$model_country = Model('country');
		$web_id = intval($_GET["web_id"]);
		$web_list = $model_country->getWebList(array('web_id'=>$web_id));
		$web_array = $web_list[0];
		if(!empty($web_array) && is_array($web_array)) {
			$model_country->updateWebHtml($web_id,$web_array['style_name']);
			showMessage(Language::get('nc_common_op_succ'),'index.php?act=country&op=web_config');
		} else {
			showMessage(Language::get('nc_common_op_fail'));
		}
	}

	/**
	 * 更新详情页html内容
	 */
	public function web_country_htmlOp(){
		$model_country = Model('country');
		$web_id = intval($_GET["web_id"]);
		$web_list = $model_country->getWebList(array('web_id'=>$web_id));
		$web_array = $web_list[0];
		if(!empty($web_array) && is_array($web_array)) {
			$model_country->updateWebCountryHtml($web_id,$web_array['style_name']);
			showMessage(Language::get('nc_common_op_succ'),'index.php?act=country&op=web_config');
		} else {
			showMessage(Language::get('nc_common_op_fail'));
		}
	}

}
