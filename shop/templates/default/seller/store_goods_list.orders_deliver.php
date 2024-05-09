<div class="eject_con">
  <div id="warning" class="alert alert-error"></div>
  <form method="post" action="<?php echo urlShop('store_deliver', 'orders_deliver');?>" id="plate_form">
    <input type="hidden" name="form_submit" value="ok" />
    <input type="hidden" name="order_id" value="<?php echo $_GET['order_id']; ?>" />
      <?php if($output['order_list']){ ?>
        订单号：
        <?php foreach($output['order_list'] as $order){ ?>
          <tr>
            <?php echo $order['order_sn'];?>
          </tr>
        <?php }?>
        确认发货？
      <?php }?>
    <div class="bottom">
      <label class="submit-border"><input type="submit" class="submit" value="发货"/></label>
    </div>
  </form>
</div>
<script>
$(function(){
    $('#plate_form').submit(function(){
        ajaxpost('plate_form', '', '', 'onerror');
        return false;
    });
});
</script>