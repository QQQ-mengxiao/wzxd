<?php defined('In718Shop') or exit('Access Invalid!');?>
<div class="tabmenu">
  <?php include template('layout/submenu');?>
  <a href="javascript:void(0)" class="ncsc-btn ncsc-btn-green" onclick="go('index.php?act=store_account&op=account_add');" title="添加账号">添加账号</a> </div>
<table class="ncsc-default-table">
  <thead>
    <tr><th class="w60"></th>
      <th class="tl">账号名</th>
      <th class="w200">账号组</th>
      <th class="w200">关联发货人</th>
      <th class="w80">账号状态</th>
      <th class="w100"><?php echo $lang['nc_handle'];?></th>
    </tr>
  </thead>
  <tbody>
    <?php if(!empty($output['seller_list']) && is_array($output['seller_list'])){?>
    <?php foreach($output['seller_list'] as $key => $value){?>
    <tr class="bd-line">
    <td></td>
      <td class="tl"><?php echo $value['seller_name'];?></td>
      <td><?php echo $output['seller_group_array'][$value['seller_group_id']]['group_name'];?></td>
      <td><?php echo $value['dseller_name'];?></td>
      <td>
        <a href="javascript:void(0)" onclick="edit(<?php echo $value['seller_id'];?>,'<?php echo $value['seller_name'];?>',<?php echo $value['is_use'];?>)">
          <img src="<?php echo $value['is_use']==1?SHOP_SITE_URL.'/templates/default/images/switch-on.png':SHOP_SITE_URL.'/templates/default/images/switch-off.png'?>" style="width:35px;height:35px"/>
        </a>
      </td>
      <td class="nscs-table-handle">
          <span><a href="<?php echo urlShop('store_account', 'account_edit', array('seller_id' => $value['seller_id']));?>" class="btn-blue"><i class="icon-edit"></i>
        <p><?php echo $lang['nc_edit'];?></p></a>
          </span><span><a nctype="btn_del_account" data-seller-id="<?php echo $value['seller_id'];?>" href="javascript:;" class="btn-red"><i class="icon-trash"></i>
        <p><?php echo $lang['nc_del'];?></p></a></span>
      </td>
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
      <td colspan="20"><div class="pagination"><?php echo $output['show_page']; ?></div></td>
    </tr>
  </tfoot>
</table>
<form id="del_form" method="post" action="<?php echo urlShop('store_account', 'account_del');?>">
  <input id="del_seller_id" name="seller_id" type="hidden" />
</form>
<script src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.poshytip.min.js"></script> 
<script src="<?php echo SHOP_RESOURCE_SITE_URL;?>/js/store_goods_list.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
        $('[nctype="btn_del_account"]').on('click', function() {
            var seller_id = $(this).attr('data-seller-id');
            if(confirm('确认删除？')) {
                $('#del_seller_id').val(seller_id);
                ajaxpost('del_form', '', '', 'onerror');
            }
        });
    });

    function edit(address_id,seller_name,is_use){
      var detail = "";
      if(is_use==1){
        detail = "禁用";
      }else{
        detail = "启用";
      }
      var choice = confirm("是否要"+detail+"账号【"+seller_name+"】？");
      if(choice){
        $.getJSON('index.php?act=store_account&op=edit_state&address_id='+address_id+'&is_use='+is_use+'&seller_name='+seller_name,function(data){
          if(data){
            if(data.result){
                alert('设置成功');
            }else{
                alert('设置失败');
            }
          }else{
              alert('err');
          }
        });
        window.location.reload();
      }else{
        return false;
      }
    }
</script> 
