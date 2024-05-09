<?php defined('In718Shop') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3>签约信息</h3>
      <ul class="tab-base">
        <li><a href="JavaScript:void(0);" class="current"><span><?php echo '所有记录';?></span></a></li>
      </ul>
    </div>
  </div>
  <div class="fixed-empty"></div>
  <form method="get" action="index.php" name="formSearch" id="formSearch">
    <input type="hidden" name="act" value="signing" />
    <input type="hidden" name="op" value="signing_all" />
    <table class="tb-type1 noborder search">
      <tbody>
        <tr>
        <th><select name="type">
            <option value="goods_name" <?php if($_GET['type'] == 'goods_name'){?>selected<?php }?>><?php echo '商品名称'; ?></option>
            <option value="buyer_name" <?php if($_GET['type'] == 'buyer_name'){?>selected<?php }?>>会员名称</option>
          </select></th>
        <td><input type="text" class="text" name="key" value="<?php echo trim($_GET['key']); ?>" /></td>
          <th><label for="add_time_from"><?php echo '申请时间';?></label></th>
          <td><input class="txt date" type="text" value="<?php echo $_GET['add_time_from'];?>" id="add_time_from" name="add_time_from">
            <label for="add_time_to">~</label>
            <input class="txt date" type="text" value="<?php echo $_GET['add_time_to'];?>" id="add_time_to" name="add_time_to"/></td>
          <td><a href="javascript:void(0);" id="ncsubmit" class="btn-search " title="<?php echo $lang['nc_query'];?>">&nbsp;</a>
            </td>
        </tr>
      </tbody>
    </table>
  </form>
  <table class="table tb-type2 nobdb">
    <div style="text-align:right;"><a class="btns" target="_blank" href="index.php?<?php echo $_SERVER['QUERY_STRING'];?>&op=export_step1"><span>导出Excel</span></a></div>
    <thead>
      <tr class="thead">
        <th class="align-center">编号</th>
        <th class="align-center">签约人</th>
        <th class="align-center">店铺名称</th>
        <th class="align-center">采购商品</th>
        <th class="align-center">申请时间</th>
        <th class="align-center">采购单位</th>
        <th class="align-center">采购数量</th>
        <th class="align-center">意向价格</th>
        <th class="align-center">供应商</th>
         <th class="align-center">供应商电话</th>
         <th class="align-center">供应商地址</th>
        <th class="align-center"><?php echo $lang['nc_handle'];?></th>
      </tr>
    </thead>
    <tbody>
      <?php if (is_array($output['signing_list']) && !empty($output['signing_list'])) { ?>
      <?php foreach ($output['signing_list'] as $key => $val) { ?>
      <tr class="bd-line" >
        <td class="align-center"><?php echo $val['id'];?></td>
        <td class="align-center"><?php echo $val['member_name'];?></td>
        <td class="align-center"><?php echo $val['store_name']; ?></td>
        <td class="align-center"><?php echo $val['goods_name']; ?></td>
        <td class="align-center"><?php echo date('Y-m-d H:i:s',$val['purchase_time']);?></td>
        <td class="align-center"><?php echo $val['purchase_unit'];?></td>
        <td class="align-center"><?php echo $val['purchase_quantity'];?></td>
        <td class="align-center"><?php echo $val['purchase_price'];?></td>
        <td class="align-center"><?php echo $val['seller_name'];?></td>
         <td class="align-center"><?php echo $val['telphone'];?></td>
        <td class="align-center"><?php echo $val['area_info'];?></td>
      </tr>
      <?php } ?>
    </tbody>
    <?php } else { ?>
    <tbody>
      <tr class="no_data">
        <td colspan="20"><?php echo $lang['no_record'];?></td>
      </tr>
    </tbody>
    <?php } ?>
      <?php if (is_array($output['signing_list']) && !empty($output['signing_list'])) { ?>
    <tfoot>
      <tr>
        <td colspan="20"><div class="pagination"><?php echo $output['show_page']; ?></div></td>
      </tr>
    </tfoot>
    <?php } ?>
  </table>
</div>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/jquery.ui.js"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/i18n/zh-CN.js" charset="utf-8"></script>
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/themes/ui-lightness/jquery.ui.css"  />
<script type="text/javascript">
$(function(){
    $('#add_time_from').datepicker({dateFormat: 'yy-mm-dd'});
    $('#add_time_to').datepicker({dateFormat: 'yy-mm-dd'});
    $('#ncsubmit').click(function(){
    	$('#formSearch').submit();
    });
});
</script>
