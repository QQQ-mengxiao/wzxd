
<?php
 $host = "10.10.9.21";//20190130修改
        $username = "sa";//"lg&zosc";
        $userpwd = "lgchen@718";
    // $host = "39.98.82.154";//20190130修改
    //     $username = "sa";//"lg&zosc";
    //     $userpwd = "hnZZzl(!@#)jksp@2018";
        // $conn=new PDO("odbc:Driver={waterrun};Server=$host;Database=$dbname",$username,$userpwd);
        try{
            $conn=new PDO("dblib:host=$host;dbname=sysdb",$username,$userpwd);
        // $conn=odbc_connect("Driver={SQL Server};Server=$host;Database=$dbname",$username,$userpwd);
            if($conn){
                echo'888999';
            }
        }
        catch(Exception $e){
            echo'666';
        }
?>
 