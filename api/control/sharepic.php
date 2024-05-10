<?php
defined('In718Shop') or exit('Access Invalid!');
class sharepicControl extends BaseControl{

      /*签到
     */
    public function indexOp(){
         $goods_id=$_GET['goods_id'];
         $pic_url=UPLOAD_SITE_URL.'/'.ATTACH_PATH.'/shareimgs/'. $goods_id.'.jpg';
        
        if(getimagesize($pic_url)){
            // var_dump($pic_url);die;
              $data['pic_url']=$pic_url;
            $message='sucess';
            $res = array('code'=>'100' , 'message'=>$message,'data'=>$data);
            echo json_encode($res,320);
        // }else{
        //    $data['pic_url']=UPLOAD_SITE_URL.'/'.ATTACH_PATH.'/shareimgs/default.jpg';
        //     $message='sucess';
        //     $res = array('code'=>'100' , 'message'=>$message,'data'=>$data);
        //     echo json_encode($res,320);

        }
    }

  




}