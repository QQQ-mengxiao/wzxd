<?php defined('In718Shop') or exit('Access Invalid!');?>
<div class="tabmenu">
  <?php include template('layout/submenu');?>
  <a class="ncsc-btn ncsc-btn-green" href="<?php echo urlShop('store_prefix', 'prefix_add');?>"><i class="icon-plus-sign"></i><?php echo "添加前缀";?></a>
</div>

<div class="alert alert-block mt10">
  <ul>
    <li>1、点击添加活动按钮可以添加前缀词条，点击管理按钮可以对前缀词条内的商品进行管理</li>
    <li>2、点击删除按钮可以删除前缀词条</li>
 </ul>
</div>

<form method="get">
  <table class="search-form">
    <input type="hidden" name="act" value="store_prefix" />
    <input type="hidden" name="op" value="prefix_list" />
    <tr>
      <td>&nbsp;</td>
      <th class="w110"><?php echo "活动名称";?></th>
      <td class="w160"><input type="text" class="text w150" name="prefix_name" value="<?php echo $_GET['prefix_name'];?>"/></td>
      <td class="w70 tc"><label class="submit-border"><input type="submit" class="submit" value="<?php echo $lang['nc_search'];?>" /></label></td>
    </tr>
  </table>
</form>
<table class="ncsc-default-table">
  <thead>
    <tr>
      <th class="w30"></th>
      <th class="tl"><?php echo "前缀名称";?></th>
      <th class="tl"><?php echo "备注";?></th>
      <th class="w150"><?php echo $lang['nc_handle'];?></th>
    </tr>
  </thead>
  <?php if(!empty($output['prefix_list']) && is_array($output['prefix_list'])){?>
  <?php foreach($output['prefix_list'] as $key=>$val){?>
  <tbody id="prefix_list">
    <tr class="bd-line">
      <td></td>
      <td class="tl"><dl class="goods-name">
          <dt><?php echo $val['prefix_name'];?></dt>
        </dl></td>
      <td class="tl"><?php echo $val['prefix_explain'];?></td>
      <td class="nscs-table-handle tr">
          <span>
              <?php if(!empty($output['prefix_name'])){?>
                <a href="index.php?act=store_prefix&op=prefix_edit&prefix_id=<?php echo $val['prefix_id'];?>&prefix_name=<?php echo $output['prefix_name'];?>" class="btn-blue">
                  <i class="icon-edit"></i>
                  <p><?php echo $lang['nc_edit'];?></p>
                </a>
              <?php }else{?>
              <a href="index.php?act=store_prefix&op=prefix_edit&prefix_id=<?php echo $val['prefix_id'];?>" class="btn-blue">
                  <i class="icon-edit"></i>
                  <p><?php echo $lang['nc_edit'];?></p>
              </a>
              <?php }?>
          </span>
          <span>
              <a href="index.php?act=store_prefix&op=prefix_manage&prefix_id=<?php echo $val['prefix_id'];?>" class="btn-green">
                  <i class="icon-cog"></i>
                  <p><?php echo $lang['nc_manage'];?></p>
              </a>
          </span>
          <span>
              <a href="javascript:;" nctype="btn_del_prefix" data-prefix-id=<?php echo $val['prefix_id'];?> class="btn-red">
                  <i class="icon-trash"></i>
                  <p><?php echo $lang['nc_delete'];?></p>
              </a>
          </span>
      </td>
  </tr>
  <?php }?>
  <?php }else{?>
  <tr id="prefix_list_norecord">
      <td class="norecord" colspan="20"><div class="warning-option"><i class="icon-warning-sign"></i><span><?php echo $lang['no_record'];?></span></div></td>
  </tr>
  <?php }?>
  </tbody>
  <tfoot>
    <?php if(!empty($output['prefix_list']) && is_array($output['prefix_list'])){?>
    <tr>
      <td colspan="20"><div class="pagination"><?php echo $output['show_page']; ?></div></td>
    </tr>
    <?php } ?>
  </tfoot>
</table>
<form id="submit_form" action="" method="post" >
  <input type="hidden" id="prefix_id" name="prefix_id" value="">
</form>
<script type="text/javascript">
    $(document).ready(function(){
        $('[nctype="btn_del_prefix"]').on('click', function() {
            if(confirm('<?php echo $lang['nc_ensure_del'];?>')) {
                var action = "<?php echo urlShop('store_prefix', 'prefix_del');?>";
                var prefix_id = $(this).attr('data-prefix-id');
                $('#submit_form').attr('action', action);
                $('#prefix_id').val(prefix_id);
                ajaxpost('submit_form', '', '', 'onerror');
            }
        });
    });
</script>
