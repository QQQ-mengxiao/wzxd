<?php defined('In718Shop') or exit('Access Invalid!');?>

<div class="tabmenu">
  <?php include template('layout/submenu');?>
  <a href="javascript:void(0)" class="ncsc-btn ncsc-btn-green" nc_type="dialog" dialog_title="新增配货方式" dialog_id="my_address_add"  uri="index.php?act=pei_setting&op=pei_add" dialog_width="1310"  title="新增配货方式">新增配货方式</a></div>
<div></div>
<table class="ncsc-default-table" >
  <thead>
    <tr>
      <th class="w90">配货方式</th>
      <th class="tl">关联发货人</th>
      <!-- <th class="tl">备注</th> -->
      <th class="w110">操作</th>
    </tr>
  </thead>
  <tbody>
    <?php if(!empty($output['pei_list']) && is_array($output['pei_list'])){?>
    <?php foreach($output['pei_list'] as $key=>$pei){?>
    <tr class="bd-line">
      <td><?php echo $pei['p_name'];?></td>
      <td class="tl"><?php echo $pei['deliever_id'];?></td>
      <!-- <td class="tl"><?php echo $pei['note'];?></td> -->
      <td class="nscs-table-handle"><span><a href="javascript:void(0);" dialog_id="my_address_edit" dialog_width="1310" dialog_title="编辑配货方式" nc_type="dialog" uri="index.php?act=pei_setting&op=pei_add&pei_id=<?php echo $pei['id'];?>" class="btn-blue"><i class="icon-edit"></i>
        <p>编辑</p>
        </a></span><span> <a href="javascript:void(0)" onclick="ajax_get_confirm('确定删除？', 'index.php?act=pei_setting&op=pei_del&pei_id=<?php echo $pei['id'];?>');" class="btn-red"><i class="icon-trash"></i>
        <p>删除</p>
        </a></span></td>
    </tr>
    <?php }?>
    <?php }else{?>
    <tr>
      <td colspan="20" class="norecord"><div class="warning-option"><i class="icon-warning-sign"></i><span>没有符合条件的记录</span></div></td>
    </tr>
    <?php }?>
  </tbody>
  <tfoot>
    <tr>
      <td colspan="20">&nbsp;</td>
    </tr>
  </tfoot>
</table>
<script src="<?php echo RESOURCE_SITE_URL;?>/js/common_select.js"></script> 
<script>

/*$(function (){
	$('input[name="is_default"]').on('click',function(){
		$.get('index.php?act=pei_setting&op=daddress_default_set&address_id='+$(this).val(),function(result){})
	});
});*/
</script> 
