<?php defined('In718Shop') or exit('Access Invalid!');?>

<div class="tabmenu">
  <?php include template('layout/submenu');?>
</div>
<form method="get" action="index.php">
  <table class="search-form">
    <input type="hidden" name="act" value="store_goods_offline" />
    <input type="hidden" name="op" value="index" />
    <input type="hidden" name="type" value="lock_up" />
    <tr>
      <!-- <td>&nbsp;</td>
      <th><?php echo $lang['store_goods_index_store_goods_class'];?></th>
      <td class="w160"><select name="stc_id" class="w150">
          <option value="0"><?php echo $lang['nc_please_choose'];?></option>
          <?php if(is_array($output['store_goods_class']) && !empty($output['store_goods_class'])){?>
          <?php foreach ($output['store_goods_class'] as $val) {?>
          <option value="<?php echo $val['stc_id']; ?>" <?php if ($_GET['stc_id'] == $val['stc_id']){ echo 'selected=selected';}?>><?php echo $val['stc_name']; ?></option>
          <?php if (is_array($val['child']) && count($val['child'])>0){?>
          <?php foreach ($val['child'] as $child_val){?>
          <option value="<?php echo $child_val['stc_id']; ?>" <?php if ($_GET['stc_id'] == $child_val['stc_id']){ echo 'selected=selected';}?>>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $child_val['stc_name']; ?></option>
          <?php }?>
          <?php }?>
          <?php }?>
          <?php }?>
        </select></td> -->
         <th class="w15">关联发货人</th>
       <td class="w150">
         <select id="address_id" name="address_id" class="w120">
          <option value="0">请选择...</option>
        <?php if(!empty($output['fahuo_list']) && is_array($output['fahuo_list'])){ ?>
          <?php foreach($output['fahuo_list'] as $k => $v){ ?>
       
              <option value="<?php echo $v['address_id'];?>" <?php if ($_GET['address_id'] ==$v['address_id']) {?>selected="selected"<?php }?>><?php echo $v['seller_name']; ?></option>
            
          <?php }?>
        <?php }?>
        </select>
       </td>
      <th class="w150">
        <select name="search_type" class="w140">
          <option value="0" <?php if ($_GET['type'] == 0) {?>selected="selected"<?php }?>><?php echo $lang['store_goods_index_goods_name'];?></option>
          <option value="1" <?php if ($_GET['type'] == 1) {?>selected="selected"<?php }?>><?php echo $lang['store_goods_index_goods_no'];?></option>
          <option value="2" <?php if ($_GET['type'] == 2) {?>selected="selected"<?php }?>>平台货号</option>
        </select>
      </th>
      <td class="w160"><input type="text" class="text" name="keyword" value="<?php echo $_GET['keyword']; ?>"/></td>
      <td class="tc w70"><label class="submit-border"><input type="submit" class="submit" value="<?php echo $lang['nc_search'];?>" /></label></td>
      <td class="tc w70"><a class="ncsc-btn ncsc-btn-green" target="_blank"
                                  href="index.php?<?php echo $_SERVER['QUERY_STRING']; ?>&op=export"><span>导出Excel</span></a>
      </td>
    </tr>
    <tr>
       <th>分类</th>
          <td id="searchgc_td" colspan="3"></td><input type="hidden" id="choose_gcid" name="choose_gcid" value="0"/>
    </tr>
  </table>
</form>
<table class="ncsc-default-table">
  <thead>
    <tr nc_type="table_header">
      <th class="w30"></th>
      <th class="w50"></th>
      <th><?php echo $lang['store_goods_index_goods_name'];?></th>
      <th class="w180"><?php echo $lang['store_goods_index_close_reason'];?></th>
      <th class="w100"><?php echo $lang['store_goods_index_price'];?></th>
      <th class="w100"><?php echo $lang['store_goods_index_stock'];?></th>
      <th class="w100"><?php echo $lang['nc_handle'];?></th>
    </tr>
    <?php  if (!empty($output['goods_list'])) { ?>
    <tr>
      <td class="tc"><input type="checkbox" id="all" class="checkall"/></td>
      <td colspan="10"><label for="all"><?php echo $lang['nc_select_all'];?></label>
        <a href="javascript:void(0);" class="ncsc-btn-mini" nc_type="batchbutton" uri="<?php echo urlShop('store_goods_online', 'drop_goods');?>" name="commonid" confirm="<?php echo $lang['nc_ensure_del'];?>"><i class="icon-trash"></i><?php echo $lang['nc_del'];?></a>
    </tr>
    <?php } ?>
  </thead>
  <tbody>
    <?php if (!empty($output['goods_list'])) { ?>
    <?php foreach ($output['goods_list'] as $val) { ?>
    <tr>
      <th class="tc"><input type="checkbox" class="checkitem tc" value="<?php echo $val['goods_commonid']; ?>"/></th>
      <th colspan="20">平台货号：<?php echo $val['goods_commonid'];?>
          <?php if($val['is_cw'] == 1){?>
          &nbsp;&nbsp;<i class="icon-cloud blue"></i>
        <?php }?></th>
    </tr>
    <tr>
      <td class="trigger"><i class="icon-plus-sign" nctype="ajaxGoodsList" data-comminid="<?php echo $val['goods_commonid'];?>"></i></td>
      <td><div class="pic-thumb">
        <a href="<?php echo urlShop('goods', 'index', array('goods_id' => $output['storage_array'][$val['goods_commonid']]['goods_id']));?>" target="_blank"><img src="<?php echo thumb($val, 60);?>"/></a></div></td>
      <td class="tl"><dl class="goods-name">
          <dt style="max-width: 450px !important;">
            <?php if ($val['is_virtual'] ==1) {?>
            <span class="type-virtual" title="虚拟兑换商品">虚拟</span>
            <?php }?>
            <?php if ($val['is_fcode'] ==1) {?>
            <span class="type-fcode" title="F码优先购买商品">F码</span>
            <?php }?>
            <?php if ($val['is_presell'] ==1) {?>
            <span class="type-presell" title="预先发售商品">预售</span>
            <?php }?>
            <?php if ($val['is_appoint'] ==1) {?>
            <span class="type-appoint" title="预约销售提示商品">预约</span>
            <?php }?>
            <a href="<?php echo urlShop('goods', 'index', array('goods_id' => $output['storage_array'][$val['goods_commonid']]['goods_id']));?>" target="_blank"><?php echo $val['goods_name']; ?></a></dt>
          <dd><?php echo $lang['store_goods_index_goods_no'].$lang['nc_colon'];?><?php echo $val['goods_serial'];?></dd>
          <dd class="serve"> <span class="<?php if ($val['goods_commend'] == 1) { echo 'open';}?>" title="店铺推荐商品"><i class="commend">荐</i></span> <span class="<?php if ($val['mobile_body'] != '') { echo 'open';}?>" title="手机端商品详情"><i class="icon-tablet"></i></span> <span class="" title="商品页面二维码"><i class="icon-qrcode"></i>
            <div class="QRcode"><a target="_blank" href="<?php echo goodsQRCode(array('goods_id' => $output['storage_array'][$val['goods_commonid']]['goods_id'], 'store_id' => $_SESSION['store_id']));?>">下载标签</a>
              <p><img src="<?php echo goodsQRCode(array('goods_id' => $output['storage_array'][$val['goods_commonid']]['goods_id'], 'store_id' => $_SESSION['store_id']));?>"/></p>
            </div>
            </span> </dd>
        </dl></td>
      <td><?php echo $val['goods_stateremark'];?></td>
      <td><span><?php echo $lang['currency'].$val['goods_price']; ?></span></td>
      <td><span><?php echo $output['storage_array'][$val['goods_commonid']]['sum'].$lang['piece']; ?></span></td>
      <td class="nscs-table-handle"><span><a href="<?php echo urlShop('store_goods_online', 'edit_goods', array('commonid' => $val['goods_commonid']));?>" class="btn-blue"><i class="icon-edit"></i><p><?php echo $lang['nc_edit'];?></p></a></span>
        <span><a href="javascript:void(0)" onclick="ajax_get_confirm('<?php echo $lang['nc_ensure_del'];?>', '<?php echo urlShop('store_goods_online', 'drop_goods', array('commonid' => $val['goods_commonid']));?>');" class="btn-red"><i class="icon-trash"></i><p><?php echo $lang['nc_del'];?></p></a></span></td>
    </tr>
    <tr style="display:none;"><td colspan="20"><div class="ncsc-goods-sku ps-container"></div></td></tr>
    <?php } ?>
    <?php } else { ?>
    <tr>
      <td colspan="20" class="norecord"><div class="warning-option"><i class="icon-warning-sign"></i><span><?php echo $lang['no_record'];?></span></div></td>
    </tr>
    <?php } ?>
  </tbody>
    <?php  if (!empty($output['goods_list'])) { ?>
  <tfoot>
    <tr>
      <th class="tc"><input type="checkbox" id="all2" class="checkall"/></th>
      <th colspan="10"><label for="all2"><?php echo $lang['nc_select_all'];?></label>
        <a href="javascript:void(0);" class="ncsc-btn-mini" nc_type="batchbutton" uri="<?php echo urlShop('store_goods_online', 'drop_goods');?>" name="commonid" confirm="<?php echo $lang['nc_ensure_del'];?>"><i class="icon-trash"></i><?php echo $lang['nc_del'];?></a> 
    </tr>
    <tr>
      <td colspan="20"><div class="pagination"> <?php echo $output['show_page']; ?> </div></td>
    </tr>
  </tfoot>
  <?php } ?>
</table>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/common_select.js" charset="utf-8"></script> 
<script type="text/javascript" src="<?php echo SHOP_RESOURCE_SITE_URL;?>/js/store_goods_list.js" charset="utf-8"></script> 
<script>
  $(function(){
    //商品分类
    init_gcselect(<?php echo $output['gc_choose_json'];?>,<?php echo $output['gc_json']?>);
  });
<?php if($output['seller_group_id']==46){?>
  $('#seller_center_left_menu li:nth-of-type(4)').attr('style','display:none');
  $('.ncsc-nav dl:nth-of-type(2) dd ul li:nth-of-type(4)').attr('style','display:none');
  $('#quicklink_list dl dd:nth-of-type(4)').attr('style','display:none');
<?php }?>
</script>