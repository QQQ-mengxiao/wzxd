<?php 
class test7Control extends BaseSellerControl {
    public function __construct() {
        parent::__construct ();
        Language::read ('member_store_goods_index');
    }
    public function indexOp() {
        $this->insertOp();
    }


   

public function insertOp() {

 		$model = Model('member_common');
        for($i=1143;$i<=2240;$i++){
        $data = array(
		'member_id'=>$i,

		);

        $model->insert($data);
    }
echo 'abc';
}

}
?>