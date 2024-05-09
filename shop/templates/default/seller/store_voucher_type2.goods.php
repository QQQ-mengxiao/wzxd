<?php defined('In718Shop') or exit('Access Invalid!');?>
<?php if(!empty($output['goods_list']) && is_array($output['goods_list'])){?>
<ul>
    <!-- <a class="ncsc-btn-mini ncsc-btn-green" onclick="add()" id="add" url="<?php echo urlShop('store_voucher_type', 'vouchertype_goods_add_all');?>">批量添加</a>
    <a href="JavaScript:void(0);" class="ncsc-btn-mini" onClick="checkAll()"><i class="icon-check"></i>全选</a>
    <a href="JavaScript:void(0);" class="ncsc-btn-mini" onClick="uncheckAll()"><i class="icon-check-empty"></i>取消</a> -->
</ul>
<ul class="goods-list">
    <hr style="border-style: ridge;">
  <?php foreach($output['goods_list'] as $key=>$val){?>
  <li>
    <div class="goods-thumb"> <a href="<?php echo urlShop('goods', 'index', array('goods_id' => $val['goods_id']));?>" target="_blank"><img src="<?php echo thumb($val, 240);?>"/></a></div>
    <dl class="goods-info">
        <input id="C<?php echo $val['goods_id']; ?>" name="id[]"  value="<?php echo $val['goods_id']; ?>" type="checkbox" class="checkbox"/>
      <dt><a href="<?php echo urlShop('goods', 'index', array('goods_id' => $val['goods_id']));?>" target="_blank"><?php echo $val['goods_name'];?></a> </dt>
      <dd>销售价格：<?php echo $lang['currency'].$val['goods_price'];?>
    </dl>
    <a nctype="btn_add_xianshi_goods" data-goods-id="<?php echo $val['goods_id'];?>" data-goods-name="<?php echo $val['goods_name'];?>" data-goods-img="<?php echo thumb($val, 240);?>" data-goods-price="<?php echo $val['goods_price'];?>" href="javascript:void(0);" class="ncsc-btn-mini">选择商品</a> </li>
  <?php } ?>
</ul>
<div class="pagination"><?php echo $output['show_page']; ?></div>
<?php } else { ?>
<div><?php echo $lang['no_record'];?></div>
<?php } ?>
<div id="dialog_add_xianshi_goods" style="display:none;">
  <input id="dialog_goods_id" type="hidden">
  <input id="dialog_input_goods_price" type="hidden">
  <div class="selected-goods-info">
    <div class="goods-thumb"><img id="dialog_goods_img" src="" alt=""></div>
    <dl class="goods-info" style="width: 350px">
      <dt id="dialog_goods_name"></dt>
      <dd>销售价格：<?php echo $lang['currency']; ?><span id="dialog_goods_price"></span></dd>
      
    </dl>
  </div>

    <div class="bottom pt10 pb10"><a id="btn_submit" class="submit" href="javascript:void(0);">提交</a></div>
  </div>

<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/common.js"></script>
<script>
    function add() {
        if($('input[type=checkbox]:checked').length == 0){    //没有选择
            showDialog('请选择需要操作的记录！');
            return false;
        }
        var id = '';
        $('input[type=checkbox]:checked').each(function(){
            id += $(this).val() + ',';
        });
        var tid = <?php echo $output['tid']?>;
        ajax_form('ajax_plate', '批量添加秒杀商品', $('#add').attr('url')+'&goods_id=' + id + '&tid=' + tid, '600');
    }
    // 全选
    function checkAll() {
        $('#batchClass').hide();
        $('input[type="checkbox"]').each(function () {
            $(this).attr('checked', true);
        });
    }

    // 取消
    function uncheckAll() {
        $('#batchClass').hide();
        $('input[type="checkbox"]').each(function () {
            $(this).attr('checked', false);
        });
    }
</script>