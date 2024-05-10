<?php
/**
 * 生成分享图片
 *
 **/

defined('In718Shop') or exit ('Access Invalid!');
class create_imageControl{

    public function create_imageOp()
    {
    	$common_id = $_GET['commonid'];
    	//根据commonid获取所有goods图片、goods_id
    	$field = 'goods_id,goods_image';
    	$condition = array('goods_commonid'=>$common_id);
    	$goods_list = Model()->table('goods')->field($field)->where($condition)->select();
    	if (empty($goods_list) || !is_array($goods_list)) {
    		echo "请求异常";
    		exit;
    	}
        //$bg_path = '/home/wwwroot/default/wzxd/data/upload/shop/sharepic/4_07060091306346660.jpg';
        $bg_image_id = $_GET['bg_image_id'];
        //获取背景图片路径
        $bg_image_info = Model()->table('sharepic')->field('share_pic')->where(array('sharepic_recommend' => 1))->find();
        $bg_path = '/data/default/wzxd/data/upload/shop/sharepic/'.$bg_image_info['share_pic'];
        $echo_file = '/data/default/wzxd/data/upload/shop/shareimgs/';//生成新图片文件夹
        //图片2在背景图片上位置的左边距
        $image_x = $_GET['image_x'];
        //图片2在背景图片上位置的上边距
        $image_y = $_GET['image_y'];
        //图片宽度及高度
        $poster_w = $_GET['poster_x'];
        $poster_y = $_GET['poster_y'];
		foreach ($goods_list as $key => $goods_info) {
			$poster = '/data/default/wzxd/data/upload/shop/store/goods/4/'.$goods_info['goods_image'];
            $echo_path = $echo_file.$goods_info['goods_id'].'.jpg';
            $this->createPoster($bg_path, $poster, $image_x, $image_y, $poster_w, $poster_y, $echo_path, $is_base64 = 0);
		}
        showMessage("生成分享图片成功");
    	//获取背景路径，源图片路径，左边距位置、右边距位置、原图片宽度、高度、字体大小、字体边距、右边距
    	// $bg_path = ;//根据选择查找路径
    	// //生成新图片路径
    	// $echo_path = ;
    	// $poster = ;//根据common_id遍历所有商品

    	// $this->createPoster($bg_path, $poster, $x, $y, $poster_w, $poster_y, $echo_path, $is_base64 = 0);
    	// $this->imageAddText($bg_path, $text, $x, $y, $font_size, $echo_path);
    	// echo "success";
    }

    /**
     * 将两张图片合成一张
     * $bg_path    背景图地址
     * $poster     图片2
     * $x           图片2在背景图片上位置的左边距,单位：px （例：436）
     * $y           图片2在背景图片上位置的上边距,单位：px （例：1009）
     * $poster_w	图片2宽度,单位：px （例：200）
     * $poster_y		图片2高度,单位：px （例：300）
     * $echo_path   生成的新图片存放路径
     **/
    private function createPoster($bg_path, $poster, $x, $y, $poster_w, $poster_y, $echo_path, $is_base64 = 0){
        $background = imagecreatefromstring(file_get_contents($bg_path));
        $poster_res = imagecreatefromstring(file_get_contents($poster));
        imagecopyresampled($background, $poster_res, $x, $y, 0, 0, $poster_w, $poster_y, $poster_w, $poster_y);
        //输出到本地文件夹，返回生成图片的路径
        if(!is_dir(dirname($echo_path))){
            mkdir(dirname($echo_path), 0755, true);
            chown(dirname($echo_path), 'nobody');
            chgrp(dirname($echo_path), 'nobody');
        }

        if($is_base64){
            ob_start ();
            //imagepng展示出图片
            imagepng ($background);
            $imageData = ob_get_contents ();
            ob_end_clean ();
            //得到这个结果，可以直接用于前端的img标签显示
            $res = "data:image/jpeg;base64,". base64_encode ($imageData);
        }else{
            imagepng($background,$echo_path);
            $res = $echo_path;
        }
        imagedestroy($background);
        imagedestroy($poster_res);
        return $res;
    }
    /**
     * 给图片加文字
     * $bg_path     背景图地址
     * $text        要添加的文字
     * $x           文字在背景图片上位置的左边距,单位：px （例：436）
     * $y           文字在背景图片上位置的上边距,单位：px （例：1009）
     * $font_size   字体大小,单位：px （例：20）
     * $echo_path   生成的新图片存放路径
     **/
    private function imageAddText($bg_path, $text, $x, $y, $font_size, $echo_path){
        $background = imagecreatefromstring(file_get_contents($bg_path));
        $font = "D:\phpStudy\WWW\images\simkai.ttf"; //字体在服务器上的绝对路径
        $black = imagecolorallocate($background, 255, 0, 0);//字体颜色黑色

        $str = mb_convert_encoding($text, "html-entities", "utf-8");

        imagefttext($background, $font_size, 0, $x, $y, $black, $font, $text);
        imagepng($background,$echo_path);
        imagedestroy($background);
        return $echo_path;
    }

}
