<?php 
class test5Control extends BaseSellerControl {
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

$tariff_rate = 0;
$coefficient = 0.7;

$dutiableValue = 99;
//$consumptionTax_rate = 0.3;
//$valueAddedTax_rate = 0.17;

//完税价格
//$dutiableValue


//计算关税
$tariff = 	$dutiableValue * $tariff_rate;

//计算消费税
$consumptionTax_pre = (($dutiableValue + $tariff)/(1-$consumptionTax_rate))*$consumptionTax_rate;
$consumptionTax = $consumptionTax_pre*$coefficient;

//计算增值税
$valueAddedTax_pre = ($dutiableValue + $tariff + $consumptionTax_pre)*$valueAddedTax_rate;
$valueAddedTax = $valueAddedTax_pre*$coefficient;

$tax = $tariff + $consumptionTax + $valueAddedTax;

echo $tax;	
}

}
?>