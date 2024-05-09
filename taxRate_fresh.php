<?php


//连接到本地mysql数据库
    $myconn=mysql_connect("localhost","root","NCIEzih718zosc@182");
	//$myconn=mysql_connect("localhost","root","root");
    //选择test为操作库
    mysql_query("set names 'utf-8'"); // //这就是指定数据库字符集，一般放在连接数据库后面就系了
    //mysql_select_db("718shop_ceshi",$myconn);
    mysql_select_db("718shop",$myconn);
    
    $strSql="select * from 718shop_ctax_hs";
    
    //用mysql_query函数从user表里读取数据
    $result=mysql_query($strSql,$myconn);

	while ($row = mysql_fetch_array($result)) 
	{ 
		$hs = $row['hs'];
		$tax_rate = $row['tax_rate'];

		// print_r($hs);
		// print_r($tax_rate);
		$updateSql = "update 718shop_goods set goods_tax_rate=$tax_rate where goods_hs='$hs'";
		mysql_query($updateSql);
		$updateSql_common = "update 718shop_goods_common set goods_tax_rate=$tax_rate where goods_hs='$hs'";
		mysql_query($updateSql_common);
	} 
print_r($tax_rate); 



    //关闭对数据库的连接
    mysql_close($myconn);


?>