<?php defined('In718Shop') or exit('Access Invalid!');?>

<div class="tabmenu">
  <?php include template('layout/submenu');?>
</div>
<div class="ncsc-form-default">
  <form id="add_form" action="<?php echo urlShop('store_account', 'account_edit_save');?>" method="post">
    <input name="seller_id" value="<?php echo $output['seller_info']['seller_id'];?>" type="hidden" />
    <dl>
      <dt><i class="required">*</i>卖家账号名<?php echo $lang['nc_colon'];?></dt>
      <dd> <?php echo $output['seller_info']['seller_name'];?> <span></span>
        <p class="hint"></p>
      </dd>
    </dl>
    <dl>
      <dt><i class="required">*</i>账号组<?php echo $lang['nc_colon'];?></dt>
      <dd><select name="group_id">
          <?php foreach($output['seller_group_list'] as $value) { ?>
          <option value="<?php echo $value['group_id'];?>" <?php echo $output['seller_info']['seller_group_id'] == $value['group_id']?'selected':'';?>><?php echo $value['group_name'];?></option>
          <?php } ?>
        </select>
        <span></span>
        <p class="hint"></p>
      </dd>
    </dl>
    
    <dl>
      <dt>关联发货人<?php echo $lang['nc_colon'];?></dt>
      <dd>
        <select class="w200" name="daddress_id" id="menu1" class="select1" onchange="getMenuByajax()">
          <option value="0">请选择...</option>
          <?php if(is_array($output['daddress_list']) && !empty($output['daddress_list'])){?>
            <?php foreach ($output['daddress_list'] as $val) {?>
              <option value="<?php echo $val['address_id']; ?>" <?php if ($output['seller_info']['address_id'] == $val['address_id']){ echo 'selected=selected';}?>><?php echo $val['seller_name']; ?></option>
            <?php }?>
          <?php }?>
        </select>
        <p style="color: red;">账号组选择为供货商发布商品专用时需要指定关联发货人</p>
      </dd>
    </dl>

    <div class="bottom">
      <label class="submit-border">
        <input type="submit" class="submit" value="<?php echo $lang['nc_submit'];?>">
      </label>
    </div>
  </form>
</div>

<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/common_select.js" charset="utf-8"></script> 
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/select2/select2.min.css"/>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/select2/select2.full.min.js"></script>

<script>
$(document).ready(function(){
    $('#add_form').validate({
        onkeyup: false,
        errorPlacement: function(error, element){
            element.nextAll('span').first().after(error);
        },
        rules: {
            group_id: {
                required: true
            }
        },
        messages: {
            group_id: {
                required: '<i class="icon-exclamation-sign"></i>请选择账号组'
            }
        }
    });
});

$(function () {
    $("#menu1").select2();
});
//下拉框
function getMenuByajax(){
    //获取menu1的编号
  menuVal = $('#menu1').val();
  //alert('menuVal='+menuVal)
  $("#belong").attr("value",menuVal);//给隐藏的sysNum字段赋值。
}
</script> 
