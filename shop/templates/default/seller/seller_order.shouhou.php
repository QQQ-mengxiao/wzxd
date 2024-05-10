<div class="eject_con"><!-- onsubmit="ajaxpost('confirm_order_form','','','onerror')" -->
  <div id="warning"></div>
  <?php if ($output['order_info']) {?>
  <form action="index.php?act=store_order&op=change_state&state_type=shouhou&order_id=<?php echo $output['order_info']['order_id']; ?>" method="post" id="receive_order_form" onsubmit="ajaxpost('receive_order_form','','','')" >
    <input type="hidden" name="form_submit" value="ok" />
    <h5 class="orange" style="text-align: center;">您是否确定更改以下订单的状态为待发货?</h5>
    <dl>
      <dt>订单编号：</dt>
      <dd><?php echo trim($_GET['order_sn']); ?>
        <p class="hint" style="line-height:22px;margin-top: 0;">请注意：请核对好订单再点击“确认”。</p>
      </dd>
    </dl>
    <div class="bottom">
      <label class="submit-border">
        <input type="submit" class="submit" id="confirm_yes" value="确定" />
      </label> </div>
  </form>
  <?php } else { ?>
  <p style="line-height:80px;text-align:center">该订单并不存在，请检查参数是否正确!</p>
  <?php } ?>
</div>
