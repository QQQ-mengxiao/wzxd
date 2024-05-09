<?php defined('In718Shop') or exit('Access Invalid!');?>

<div class="tabmenu">
  <?php include template('layout/submenu');?>
  <a href="javascript:void(0)" class="ncsc-btn ncsc-btn-green" nc_type="dialog" dialog_title="<?php echo $lang['store_daddress_new_address'];?>" dialog_id="my_address_add"  uri="index.php?act=store_deliver_set&op=daddress_add" dialog_width="550" title="<?php echo $lang['store_daddress_new_address'];?>"><?php echo $lang['store_daddress_new_address'];?></a></div>
<div><form method="get" action="index.php">
  <table class="search-form">
    <input type="hidden" name="act" value="store_deliver_set" />
    <input type="hidden" name="op" value="daddress_list" />
      <th class="w15">联系人</th>
       <td class="w150">
          <select name="address_id" id="menu1" class="select1" onchange="getMenuByajax()">
          <option value="0">请选择...</option>
        <?php if(!empty($output['fahuo_list']) && is_array($output['fahuo_list'])){ ?>
          <?php foreach($output['fahuo_list'] as $v){ ?>
       
              <option value="<?php echo $v['address_id'];?>" <?php if ($_GET['address_id'] ==$v['address_id']) {?>selected="selected"<?php }?>><?php echo $v['seller_name']; ?></option>
            
          <?php }?>
        <?php }?>
        </select>
       </td>
      <td class="tc w60"><label class="submit-border"><input type="submit" class="submit" value="<?php echo $lang['nc_search'];?>" /></label></td>
    </tr>
   
  </table>
</form></div>
<table class="ncsc-default-table" >
  <thead>
    <tr>
      <th class="w70">是否默认</th>
      <th class="w90"><?php echo $lang['store_daddress_receiver_name'];?></th>
      <th class="tl"><?php echo $lang['store_daddress_deliver_address'];?></th>
      <th class="w150">发货仓库</th>
      <th class="w150">商品数量（在售/仓库）</th>
      <th class="w150"><?php echo $lang['store_daddress_phone'];?></th>
      <th class="w110"><?php echo $lang['nc_handle'];?></th>
    </tr>
  </thead>
  <tbody>
    <?php if(!empty($output['address_list']) && is_array($output['address_list'])){?>
    <?php foreach($output['address_list'] as $key=>$address){?>
    <tr class="bd-line">
      <td>
        <label for="is_default_<?php echo $address['address_id'];?>"><input type="radio" id="is_default_<?php echo $address['address_id'];?>" name="is_default" <?php if ($address['is_default'] == 1) echo 'checked';?> value="<?php echo $address['address_id'];?>">
        <?php echo $lang['store_daddress_default'];?></label>
      </td>
      <td><?php echo $address['seller_name'];?></td>
      <td class="tl"><?php echo $address['area_info'];?>&nbsp;<?php echo $address['address'];?></td>
      <td><span class="tel"><?php echo $address['storage_name'];?></span> <br/>
      <td><span class="tel"><?php echo $address['goods_online']."/".$address['goods_offline'];?></span> <br/>
      <td><span class="tel"><?php echo $address['telphone'];?></span> <br/>
      <td class="nscs-table-handle"><span><a href="javascript:void(0);" dialog_id="my_address_edit" dialog_width="640" dialog_title="<?php echo $lang['store_daddress_edit_address'];?>" nc_type="dialog" uri="index.php?act=store_deliver_set&op=daddress_add&address_id=<?php echo $address['address_id'];?>" class="btn-blue"><i class="icon-edit"></i>
        <p><?php echo $lang['nc_edit'];?></p>
        </a></span><span> <a href="javascript:void(0)" onclick="ajax_get_confirm('<?php echo $lang['nc_ensure_del'];?>', 'index.php?act=store_deliver_set&op=daddress_del&address_id=<?php echo $address['address_id'];?>');" class="btn-red"><i class="icon-trash"></i>
        <p><?php echo $lang['nc_del'];?></p>
        </a></span></td>
    </tr>
    <?php }?>
    <?php }else{?>
    <tr>
      <td colspan="20" class="norecord"><div class="warning-option"><i class="icon-warning-sign"></i><span><?php echo $lang['no_record'];?></span></div></td>
    </tr>
    <?php }?>
  </tbody>
    <tfoot>
    <tr>
        <td colspan="20">
            <div class="pagination"><?php echo $output['show_page']; ?></div>
        </td>
    </tr>
    </tfoot>
</table>
<script src="<?php echo RESOURCE_SITE_URL;?>/js/common_select.js"></script> 
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/select2/select2.min.css"/>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/select2/select2.full.min.js"></script>
<script type="text/javascript">
  //页面加载完成后初始化select2控件
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

$(function (){
	$('input[name="is_default"]').on('click',function(){
		$.get('index.php?act=store_deliver_set&op=daddress_default_set&address_id='+$(this).val(),function(result){})
	});
});
</script> 
