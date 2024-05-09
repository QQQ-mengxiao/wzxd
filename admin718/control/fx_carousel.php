<?php
/**
 * 手机端 轮播图上传 首页轮播图
 *
 *
 *
 ** */

defined('In718Shop') or exit('Access Invalid!');
class fx_carouselControl extends SystemControl{
	public function __construct(){
		parent::__construct();
		Language::read('setting');
	}

	/**
	 * 分销轮播图片上传
	 */
	public function fx_carouselOp() {
	    $size = 3;//上传显示图片总数
	    $i = 1;
	    $info['pic'] = array();
	    $model_setting = Model('setting');
	    $code_info = $model_setting->getRowSetting('fx_carousel_pic');
	    if(!empty($code_info['value'])) {
	        $info = unserialize($code_info['value']);
	    }
	    if (chksubmit()) {
    	    for ($i;$i <= $size;$i++) {
    	        $file = 'pic'.$i;
    	        $info['pic'][$i] = $_POST['show_pic'.$i];
        		if (!empty($_FILES[$file]['name'])) {//上传图片
        		    $filename_tmparr = explode('.', $_FILES[$file]['name']);
        		    $ext = end($filename_tmparr);
        		    $file_name = 'fx_carousel_'.$i.'.'.$ext;
        			$upload = new UploadFile();
        			$upload->set('default_dir',ATTACH_COMMON);
        			$upload->set('file_name',$file_name);
        			$result = $upload->upfile($file);
        			if ($result) {
        			    $info['pic'][$i] = $file_name;
        			}
        		}
    	    }
    	    $code_info = serialize($info);
    	    $update_array = array();
    	    $update_array['fx_carousel_pic'] = $code_info;
    	    $result = $model_setting->updateSetting($update_array);
    	    showMessage(Language::get('nc_common_save_succ'),'index.php?act=fx_carousel&op=fx_carousel');
	    }
		Tpl::output('size',$size);
		Tpl::output('pic',$info['pic']);
		Tpl::showpage('fx_carousel');
	}
	/**
	 * 上传图片
	 */
	public function upload_picOp() {
	    $data = array();
		if (!empty($_FILES['fileupload']['name'])) {//上传图片
		    $fprefix = 'help_store';
			$upload = new UploadFile();
			$upload->set('default_dir',ATTACH_ARTICLE);
			$upload->set('fprefix',$fprefix);
			$upload->upfile('fileupload');
		    $model_upload = Model('upload');
		    $file_name = $upload->file_name;
		    $insert_array = array();
		    $insert_array['file_name'] = $file_name;
		    $insert_array['file_size'] = $_FILES['fileupload']['size'];
		    $insert_array['upload_time'] = time();
		    $insert_array['item_id'] = intval($_GET['item_id']);
		    $insert_array['upload_type'] = '2';
		    $result = $model_upload->add($insert_array);
			if ($result) {
			    $data['file_id'] = $result;
			    $data['file_name'] = $file_name;
			}
		}
	    echo json_encode($data);exit;
	}

	/**
	 * 删除图片
	 */
	public function del_picOp() {
		$condition = array();
		$condition['upload_id'] = intval($_GET['file_id']);
	    $model_help = Model('help');
	    $state = $model_help->delHelpPic($condition);
		if ($state) {
		    echo 'true';exit;
		} else {
		    echo 'false';exit;
		}
	}
}
