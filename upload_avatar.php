
<?php  
$base_path = "./data/upload/shop/avatar/"; //存放目录  
if(!is_dir($base_path)){  
    mkdir($base_path,0777,true);  
}  
$target_path = $base_path . basename ( $_FILES ['attach'] ['name'] );  
if (move_uploaded_file ( $_FILES ['attach'] ['tmp_name'], $target_path )) {  
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
