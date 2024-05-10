<?php defined('In718Shop') or exit('Access Invalid!');?>

<div class="tabmenu">
  <?php include template('layout/submenu');?>
  <a href="javascript:void(0)" class="ncsc-btn ncsc-btn-green" nc_type="dialog" dialog_title="<?php echo '图片新增';?>" dialog_id="my_goods_brand_apply" dialog_width="480" uri="index.php?act=store_sharepic&op=sharepic_add">图片新增</a></div>
<table class="search-form">
  <form method="get">
    <input type="hidden" name="act" value="store_sharepic">
    <input type="hidden" name="op" value="brand_list">
    <tr>
      <td>&nbsp;</td>
      <th>图片名称</th>
      <td class="w160"><input type="text" class="text" name="brand_name" value="<?php echo $_GET['brand_name']; ?>"/></td>
      <td class="w70 tc"><label class="submit-border"><input type="submit" class="submit" value="<?php echo $lang['nc_search'];?>" /></label></td>
    </tr>
  </form>
</table>
<table class="ncsc-default-table">
  <thead>
    <tr>
      <th class="w70">是否默认</th>
      <th class="w150">背景图片</th>
      <th>图片名称</th>
   
      <th class="w100"><?php echo $lang['nc_handle'];?></th>
    </tr>
  </thead>
  <tbody>
    <?php if (!empty($output['sharepic_list'])) { ?>
    <?php foreach($output['sharepic_list'] as $val) { ?>
    <tr class="bd-line">
      <td>
        <label for="is_default_<?php echo $val['sharepic_id'];?>"><input type="radio" id="is_default_<?php echo $val['sharepic_id'];?>" name="is_default" <?php if ($val['sharepic_recommend'] == 1) echo 'checked';?> value="<?php echo $val['sharepic_id'];?>">
        <?php echo '默认';?></label>
      </td>
      <td><img src="<?php echo UPLOAD_SITE_URL.'/'.ATTACH_SHAREPIC.'/'.$val['share_pic'];?>" onload="javascript:DrawImage(this,88,44);" /></td>
      <td><?php echo $val['sharepic_name']; ?></td>
      <td class="nscs-table-handle"><?php if ($val['brand_apply'] == 0) { ?>
     <span><a href="javascript:void(0)" class="btn-blue" nc_type="dialog" dialog_title="<?php echo '图片编辑';?>" style="top:160px" dialog_id="my_goods_brand_edit" dialog_width="480" uri="index.php?act=store_sharepic&op=sharepic_add&sharepic_id=<?php echo $val['sharepic_id']; ?>"><i class="icon-edit"></i><p><?php echo $lang['nc_edit'];?></p></a></span>
        <span><a href="javascript:void(0)" class="btn-red" onclick="ajax_get_confirm('<?php echo $lang['nc_ensure_del'];?>', 'index.php?act=store_sharepic&op=drop_sharepic&sharepic_id=<?php echo $val['sharepic_id']; ?>');"><i class="icon-trash"></i><p><?php echo $lang['nc_del'];?></p></a></span><?php } ?></td>
    </tr>
    <?php } ?>
    <?php } else { ?>
    <tr>
      <td colspan="20" class="norecord"><div class="warning-option"><i class="icon-warning-sign"></i><span><?php echo $lang['no_record'];?></span></div></td>
    </tr>
    <?php } ?>
  </tbody>
  <tfoot>
    <?php if (!empty($output['sharepic_list'])) { ?>
    <tr>
      <td colspan="20"><div class="pagination"><?php echo $output['show_page']; ?></div></td>
    </tr>
    <?php } ?>
  </tfoot>
</table>
<script src="<?php echo RESOURCE_SITE_URL;?>/js/common_select.js"></script> 
<script>
$(function (){
  $('input[name="is_default"]').on('click',function(){
    $.get('index.php?act=store_sharepic&op=default_set&address_id='+$(this).val(),function(result){})
  });
});
</script> 
