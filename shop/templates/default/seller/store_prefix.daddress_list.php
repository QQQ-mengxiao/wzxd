<?php defined('In718Shop') or exit('Access Invalid!');?>
<?php if(!empty($output['daddress_list']) && is_array($output['daddress_list'])){?>
<ul class="goods-list">
  <?php foreach($output['daddress_list'] as $key=>$val){?>
  <li>
      <input id="C<?php echo $val['address_id']; ?>" name="id[]"  value="<?php echo $val['address_id']; ?>" type="hidden"/>
    <dl class="goods-info">
      <dt><?php echo $val['seller_name'];?></dt>
    </dl>
    <a nctype="btn_add_prefix_daddress" data-daddress-id="<?php echo $val['address_id'];?>" data-daddress="<?php echo $val['seller_name'];?>" href="javascript:void(0);" class="ncsc-btn-mini">确认添加</a>
    </li>
  <?php } ?>
</ul>
<div class="pagination"><?php echo $output['show_page']; ?></div>
<?php } else { ?>
<div><?php echo $lang['no_record'];?></div>
<?php } ?>
<div id="dialog_add_prefix_goods" style="display:none;">
  <input id="dialog_goods_id" type="hidden">
  <input id="dialog_input_goods_price" type="hidden">
  <div class="selected-goods-info">
    <div class="goods-thumb"><img id="dialog_goods_img" src="" alt=""></div>
    <dl class="goods-info" style="width: 350px">
      <dt id="dialog_goods_name"></dt>
    </dl>
  </div>
  <div class="eject_con">
    <div class="bottom pt10 pb10"><a id="btn_submit" class="submit" href="javascript:void(0);">提交</a></div>
  </div>
</div>

<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/common.js"></script>