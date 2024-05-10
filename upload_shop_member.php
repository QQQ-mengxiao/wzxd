
<?php  

$member_id = $_POST['member_id'];//正式的环境使用的POST方式提交
$base_path = "./data/upload/shop/member/$member_id/"; //存放目录  

if(!is_dir($base_path)){  
    mkdir($base_path,0777,true);  
}  
$target_path = $base_path . basename ( $_FILES ['attach'] ['name'] );  


if (move_uploaded_file ( $_FILES ['attach'] ['tmp_name'], $target_path )) {


    $name_file = basename($_FILES ['attach'] ['name']);
    $name_file_pre = explode('.',$name_file);
    if($name_file_pre[1]=="jpg"){
    	$name_file_no_jpg = basename($_FILES ['attach'] ['name'],".jpg");
    	//copy("./data/upload/shop/member/$member_id/$name_file","./data/upload/shop/member/$member_id/'$name_file_no_jpg'_1026.jpg");

    	//$name_file = basename($path);
    	//$name_file_no_jpg = basename($path,".jpg");
    	$name_file_no_jpg_1024 = $name_file_no_jpg."_1024.jpg";
    	$name_file_no_jpg_240 = $name_file_no_jpg."_240.jpg";
    	copy("./data/upload/shop/member/$member_id/$name_file","./data/upload/shop/member/$member_id/$name_file_no_jpg_1024");
    	copy("./data/upload/shop/member/$member_id/$name_file","./data/upload/shop/member/$member_id/$name_file_no_jpg_240");
	}
	elseif($name_file_pre[1]=="jpeg"){
    	$name_file_no_jpg = basename($_FILES ['attach'] ['name'],".jpeg");
    	$name_file_no_jpg_1024 = $name_file_no_jpg."_1024.jpeg";
    	$name_file_no_jpg_240 = $name_file_no_jpg."_240.jpeg";
    	copy("./data/upload/shop/member/$member_id/$name_file","./data/upload/shop/member/$member_id/$name_file_no_jpg_1024");
    	copy("./data/upload/shop/member/$member_id/$name_file","./data/upload/shop/member/$member_id/$name_file_no_jpg_240");
	}
       
    $array = array (  
            "status" => true,  
            "msg" => $_FILES ['attach'] ['name']   
    );  
    echo json_encode ( $array );  
} else {  
    $array = array (  
            "status" => false,  
            "msg" => "There was an error uploading the file, please try again!" . $_FILES ['attach'] ['error']   
    );  
    echo json_encode ( $array );  
}  
?>  
