<?php 
class test6Control extends BaseSellerControl {
    public function __construct() {
        parent::__construct ();
        Language::read ('member_store_goods_index');
    }
    public function indexOp() {
        $this->rateOp();
    }


//dutiableValue  完税价格
//consumptionTax  消费税
//valueAddedTax  增值税
//tariff         关税
//rate 率 
//coefficient 系数    

public function rateOp($dutiableValue,$consumptionTax_rate,$valueAddedTax_rate) {

set_time_limit(20000);
ini_set('memory_limit','-1');
// by www.phpddt.com
require_once './PHPExcel.php';
require_once './PHPExcel/IOFactory.php';
require_once './PHPExcel/Reader/Excel5.php';


}

}
?>