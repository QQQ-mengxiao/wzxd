<?php


//连接到本地mysql数据库
    $myconn=mysql_connect("localhost","root","NCIEzih718zosc@182");
    //选择test为操作库
    mysql_query("set names 'utf-8'"); // //这就是指定数据库字符集，一般放在连接数据库后面就系了
    //mysql_select_db("718shop_ceshi",$myconn);
    mysql_select_db("718shop",$myconn);
    
    $strSql="select xianshi_goods_id,goods_id,goods_price,xianshi_price from 718shop_p_xianshi_goods";
    
    //用mysql_query函数从user表里读取数据
    $result=mysql_query($strSql,$myconn);

	while ($row = mysql_fetch_row($result)) 
	{ 
		$xianshi_goods_id = $row[0];
		$goods_id = $row[1];
		$goods_price = $row[2];
		$xianshi_price = $row[3];

		$getSql = "select goods_tax from 718shop_goods where goods_id=$goods_id";
		$getResult=mysql_query($getSql,$myconn);
		while ($r = mysql_fetch_row($getResult)) {
		$goods_tax = $r[0];
		}

		$xianshi_goods_tax = round($goods_tax*$xianshi_price/$goods_price,2);


		$updateSql = "update 718shop_p_xianshi_goods set xianshi_goods_tax=$xianshi_goods_tax where xianshi_goods_id=$xianshi_goods_id";
		mysql_query($updateSql);
	} 
print_r($xianshi_goods_tax); 



    //关闭对数据库的连接
    mysql_close($myconn);


?>