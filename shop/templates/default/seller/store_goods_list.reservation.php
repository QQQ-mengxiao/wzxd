<?php defined('In718Shop') or exit('Access Invalid!');?>

<div class="tabmenu">
  <?php include template('layout/submenu');?>
</div>
<form method="get" action="index.php">
  <table class="search-form">
    <input type="hidden" name='act' value='store_goods_reservation' />
    <tr>
      <td>&nbsp;</td>
      <th><?php echo $lang['complain_datetime'];?></th>
      <td class="w240"><input name="add_time_from" id="add_time_from" type="text" class="text w70" value="<?php echo $_GET['add_time_from']; ?>" /><label class="add-on"><i class="icon-calendar"></i></label> &#8211; <input name="add_time_to" id="add_time_to" type="text" class="text w70" value="<?php echo $_GET['add_time_to']; ?>" /><label class="add-on"><i class="icon-calendar"></i></label></td>
<!--       <td class="w160"><input type="text" class="text" name="key" value="<?php echo trim($_GET['key']); ?>" /></td> -->
      <td class="w70 tc"><label class="submit-border"><input type="submit" class="submit" value="<?php echo $lang['nc_search'];?>" /></label></td>
    </tr>
  </table>
</form>
<table class="ncsc-default-table">
  <thead>
    <tr>
      <th class="w10"></th>
      <th class="w80 tl">预约id</th>
      <th class="tl" colspan="2">预约商品</th>
      <th class="tl">预约采购量</th>
      <th class="tl">预约采购价</th>
      <th class="tl">预约单位</th>
      <th class="tl">发货人</th>
      <th class="tl">发货人电话</th>
      <th class="tl">预约时间</th>
      <th class="tl">处理状态</th>
      <th class="tl">处理预约</th>
    </tr>
  </thead>
  <tbody>
    <?php if (count($output['goods_list'])>0) { ?>
    <?php foreach($output['goods_list'] as $val) {
        $goods = $output['goods_list'];?>
    <tr class="bd-line">
      <td></td>
      <td class="w80 tl"><?php echo $val['id'];?></td>
      <td class="tl" colspan="2"><?php echo $val['goods_name'];?></td>
       <td class="tl"><?php echo $val['purchase_price'];?></td>
       <td class="tl"><?php echo $val['purchase_quantity'];?></td> 
       <td class="tl"><?php echo $val['purchase_unit'];?></td> 
       <td class="tl"><?php echo $val['seller_name'];?></td> 
       <td class="tl"><?php echo $val['telphone'];?></td> 
      <td class="tl"><?php echo date("Y-m-d H:i:s",$val['purchase_time']);?></td>
      <td class="tl"><?php echo $val['status_zh'];?></td> 

<!--       <td class="nscs-table-handle"><span><a href="index.php?act=store_goods_reservation&op=reservation_deal&reservation_id=<?php echo $val['id'];?>" class="btn-orange"> -->

        <td><a href="javascript:void(0)" onclick="ajax_get_confirm('','<?php echo urlShop('store_goods_reservation', 'reservation_deal', array('reservation_id' => $val['id']));?>')" class="ncsc-btn"><?php echo 处理;?></a></td>
        </a></span>
        </td>
    </tr>
    <?php }?>
    <?php } else { ?>
    <tr>
      <td colspan="20" class="norecord"><div class="warning-option"><i class="icon-warning-sign"></i><span>查询不到数据</span></div></td>
    </tr>
    <?php } ?>
  </tbody>
  <tfoot>
    <?php if (count($output['goods_list'])>0) { ?>
    <tr>
      <td colspan="20"><div class="pagination"><?php echo $output['show_page'];?></div></td>
    </tr>
    <?php } ?>
  </tfoot>
</table>
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/themes/ui-lightness/jquery.ui.css"  />
<script src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/i18n/zh-CN.js" charset="utf-8"></script>
<script>
  $(function(){
      $('#add_time_from').datepicker({dateFormat: 'yy-mm-dd'});
      $('#add_time_to').datepicker({dateFormat: 'yy-mm-dd'});
  });
</script>
<script src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.poshytip.min.js"></script>
<script src="<?php echo SHOP_RESOURCE_SITE_URL;?>/js/store_goods_list.js"></script> 
<script>
$(function(){
    //Ajax提示
    $('.tip').poshytip({
        className: 'tip-yellowsimple',
        showTimeout: 1,
        alignTo: 'target',
        alignX: 'center',
        alignY: 'top',
        offsetY: 5,
        allowTipHover: false
    });
});
</script>
