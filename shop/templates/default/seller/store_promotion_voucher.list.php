<?php defined('In718Shop') or exit('Access Invalid!');?>

<div class="tabmenu">
  <?php include template('layout/submenu');?>

  <a class="ncsc-btn ncsc-btn-green" href="<?php echo urlShop('store_promotion_voucher', 'mansong_add');?>"><i class="icon-plus-sign"></i>添加活动</a>

</div>
<?php if ($output['isOwnShop']) { ?>
<div class="alert alert-block mt10">
  <ul>
    <li>1、<?php echo $lang['mansong_explain1'];?></li>
  </ul>
</div>
<?php } else { ?>
<div class="alert alert-block mt10">
  <strong>在此先设置满赠代金券活动中的代金券和每个代金券发放的数量</strong>
  <ul>
    <li>1、代金券到期则代金券发放失败</li>
    <li>2、<strong style="color: red">可以进行操作查看</strong>。</li>
  </ul>
</div>
<?php } ?>

<form method="get">
  <table class="search-form">
    <input type="hidden" name="act" value="store_promotion_voucher" />
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
      <th class="w30"></th>
      <th class="tl"><?php echo $lang['mansong_name'];?></th>
      <th class="tl">订单满额度</th>
      <th class="w180">添加时间</th>
      <th class="w180">备注</th>
      <!-- <th class="w90"><?php echo $lang['nc_state'];?></th> -->
      <th class="w100"><?php echo $lang['nc_handle'];?></th>
    </tr>
  </thead>
  <tbody>
    <?php foreach($output['list'] as $key=>$val){?>
    <tr class="bd-line">
      <td></td>
      <td class="tl"><dl class="goods-name"><dt><?php echo $val['p_name'];?></dt></dl></td>
       <td class="tl"><dl class="goods-name"><dt>￥<?php echo $val['order_limit'];?></dt></dl></td>
     <td class="goods-time"><?php echo date("Y-m-d H:i",$val['add_time']);?></td>
     <td class="goods-time"><?php echo $val['remark'];?></td>
      <td class="nscs-table-handle"><span><a href="index.php?act=store_promotion_voucher&op=mansong_detail&mansong_id=<?php echo $val['p_voucher_id'];?>" class="btn-blue"><i class="icon-th-list"></i>
        <p><?php echo $lang['nc_detail'];?></p>
        </a></span> <span><a nctype="btn_mansong_del" data-mansong-id="<?php echo $val['p_voucher_id'];?>" href="javascript:return void(0)" class="btn-red"><i class="icon-trash"></i>
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
<script type="text/javascript">
    $(document).ready(function(){
        $('[nctype="btn_mansong_del"]').on('click', function() {
            if(confirm('<?php echo $lang['nc_ensure_cancel'];?>')) {
                var action = "<?php echo urlShop('store_promotion_voucher', 'mansong_del');?>";
                var mansong_id = $(this).attr('data-mansong-id');
                $('#submit_form').attr('action', action);
                $('#mansong_id').val(mansong_id);
                ajaxpost('submit_form', '', '', 'onerror');
            }
        });
    });
</script>
