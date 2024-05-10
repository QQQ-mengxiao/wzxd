<?php
/**
 * 税率计算 
 *
 */
defined('In718Shop') or exit('Access Invalid!');

class ctax_hsModel extends Model{
    public function __construct(){
        parent::__construct('ctax_hs');
    }
//unitPrice      商品单价
//dutiableValue  完税价格
//consumptionTax  消费税
//valueAddedTax  增值税
//tariff         关税
//rate 			率 
//coefficient 系数    

public function getgoodsTax($unitPrice,$consumptionTax_rate,$valueAddedTax_rate) {

$dutiableValue = $unitPrice;    //目前包邮，不考虑运费

$tariff_rate = 0;
$coefficient = 0.7;

//$dutiableValue = 99;
//$consumptionTax_rate = 0.3;
//$valueAddedTax_rate = 0.17;

//完税价格
//$dutiableValue


//计算关税
$tariff =   $dutiableValue * $tariff_rate;

//计算消费税
$consumptionTax_pre = (($dutiableValue + $tariff)/(1-$consumptionTax_rate))*$consumptionTax_rate;
$consumptionTax = $consumptionTax_pre*$coefficient;

//计算增值税
$valueAddedTax_pre = ($dutiableValue + $tariff + $consumptionTax_pre)*$valueAddedTax_rate;
$valueAddedTax = $valueAddedTax_pre*$coefficient;

//单件总税金
$tax = $tariff + $consumptionTax + $valueAddedTax;

$tax_all = array();
//单件税金
$tax_all['tax'] = $tax;

//消费税
$tax_all['consumption_tax'] = $consumptionTax;

//增值税
$tax_all['vat'] = $valueAddedTax;

return $tax_all;  
}
    

/**
*计算综合税率
*@unitPrice      商品单价
*@dutiableValue  完税价格
*@consumptionTax  消费税
*@valueAddedTax  增值税
*@tariff         关税
*@rate 			率 
*@coefficient 系数  
**/
  
public function getgoodsTax_Rate($consumptionTax_rate,$valueAddedTax_rate) {

$dutiableValue = 1;    //目前包邮，不考虑运费

//关税默认为0
$tariff_rate = 0;
$coefficient = 0.7;

//$dutiableValue = 99;
//$consumptionTax_rate = 0.3;
//$valueAddedTax_rate = 0.17;

//完税价格
//$dutiableValue


//计算关税
$tariff =   $dutiableValue * $tariff_rate;

//计算消费税
$consumptionTax_pre = (($dutiableValue + $tariff)/(1-$consumptionTax_rate))*$consumptionTax_rate;
$consumptionTax = $consumptionTax_pre*$coefficient;

//计算增值税
$valueAddedTax_pre = ($dutiableValue + $tariff + $consumptionTax_pre)*$valueAddedTax_rate;
$valueAddedTax = $valueAddedTax_pre*$coefficient;

//单件总税金
$tax = $tariff + $consumptionTax + $valueAddedTax;

//综合税率
$tax_rate =  round($tax/$dutiableValue,4);

return $tax_rate;  
}


   
	
}
