<?php defined('In718Shop') or exit('Access Invalid!');?>

<div class="tabmenu">
  <?php include template('layout/submenu');?>

  <a class="ncsc-btn ncsc-btn-green" href="<?php echo urlShop('store_ladder_price', 'mansong_add');?>"><i class="icon-plus-sign"></i>添加活动</a>

</div>
<?php if ($output['isOwnShop']) { ?>
<div class="alert alert-block mt10">
  <ul>
    <li>1、<?php echo $lang['mansong_explain1'];?></li>
  </ul>
</div>
<?php } else { ?>
<div class="alert alert-block mt10">
  <strong>在此设置每个配送时间段对应的订单折扣</strong>
  <ul>
    <li>1、设置的折扣时间段均为整数，例如折扣填8就是8折</li>
    <li>2、<strong style="color: red">可以进行操作查看</strong>。</li>
  </ul>
</div>
<?php } ?>

<form method="get">
  <table class="search-form">
    <input type="hidden" name="act" value="store_ladder_price" />
    <input type="hidden" name="op" value="mansong_list" />
    <tr>
      <td>&nbsp;</td>
      <th class="w110"><?php echo $lang['mansong_name'];?></th>
      <td class="w160"><input type="text" class="text w150" name="mansong_name" value="<?php echo $_GET['mansong_name'];?>"/></td>
      <td class="w70 tc"><label class="submit-border"><input type="submit" class="submit" value="<?php echo $lang['nc_search'];?>" /></label></td>
    </tr>
  </table>
</form>
<table class="ncsc-default-table">
  <?php if(!empty($output['list']) && is_array($output['list'])){?>
  <thead>
    <tr>
    <th class="w180">是否使用</th>
      <th class="w180"><?php echo $lang['mansong_name'];?></th>
      <th class="w180">添加时间</th>
      <th class="w180">备注</th>
      <!-- <th class="w90"><?php echo $lang['nc_state'];?></th> -->
      <th class="w100"><?php echo $lang['nc_handle'];?></th>
    </tr>
  </thead>
  <tbody>
    <?php foreach($output['list'] as $key=>$val){?>
    <tr class="bd-line">
     <td>
        <label for="is_default_<?php echo $val['p_ladder_id'];?>"><input type="radio" id="is_default_<?php echo $val['p_ladder_id'];?>" name="is_default" <?php if ($val['is_default'] == 1) echo 'checked';?> value="<?php echo $val['p_ladder_id'];?>">启用</label>
      </td>
            <td class="goods-name"><?php echo $val['p_name'];?></td>
     <td class="goods-time"><?php echo date("Y-m-d H:i",$val['add_time']);?></td>
     <td class="goods-time"><?php echo $val['remark'];?></td>
      <td class="nscs-table-handle"><span><a href="index.php?act=store_ladder_price&op=mansong_detail&mansong_id=<?php echo $val['p_ladder_id'];?>" class="btn-blue"><i class="icon-th-list"></i>
        <p><?php echo $lang['nc_detail'];?></p>
        </a></span> <span><a nctype="btn_mansong_del" data-mansong-id="<?php echo $val['p_ladder_id'];?>" href="javascript:return void(0)" class="btn-red"><i class="icon-trash"></i>
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
  <?php if(!empty($output['list']) && is_array($output['list'])){?>
  <tfoot>
    <tr>
      <td colspan="20"><div class="pagination"><?php echo $output['show_page']; ?></div></td>
    </tr>
  </tfoot>
  <?php } ?>
</table>
<form id="submit_form" action="" method="post" >
  <input type="hidden" id="mansong_id" name="mansong_id" value="">
</form>
<script src="<?php echo RESOURCE_SITE_URL;?>/js/common_select.js"></script> 
<script type="text/javascript">
    $(document).ready(function(){
        $('[nctype="btn_mansong_del"]').on('click', function() {
            if(confirm('<?php echo $lang['nc_ensure_cancel'];?>')) {
                var action = "<?php echo urlShop('store_ladder_price', 'mansong_del');?>";
                var mansong_id = $(this).attr('data-mansong-id');
                $('#submit_form').attr('action', action);
                $('#mansong_id').val(mansong_id);
                ajaxpost('submit_form', '', '', 'onerror');
            }
        });
    });
    $(function (){
  $('input[name="is_default"]').on('click',function(){
    $.get('index.php?act=store_ladder_price&op=default_set&p_ladder_id='+$(this).val(),function(result){})
  });
});
</script>
