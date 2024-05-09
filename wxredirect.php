
<?php  

$code = $_REQUEST['code'];

//print_r($code);


if(!empty($code)){

	//echo "$code";
	//echo "</br>";

    echo "<script language=''javascript'>";
    echo "window.location='http://m.zosc.com/zosc/member/wxapi.shtml?code=$code'";
    echo "</script>";
}


?>  
