<div class="eject_con"><!-- onsubmit="ajaxpost('confirm_order_form','','','onerror')" -->
  <div id="warning"></div>
  <?php if ($output['vouchertype_info']) {?>
  <form action="index.php?act=store_voucher_type&op=use_set&t_id=<?php echo $output['vouchertype_info']['voucher_tid']; ?>&is_use=<?php echo $output['vouchertype_info']['is_use']; ?>" method="post" id="voucher_type_form" onsubmit="ajaxpost('voucher_type_form','','','')" >
    <input type="hidden" name="form_submit" value="ok" />
    <h5 class="orange" style="text-align: center;">您是否确已经更改包含状态?</h5>
    <dl>
      <dt>代金券编号</dt>
      <dd><?php echo trim($output['vouchertype_info']['voucher_tid']); ?>
      </dd>
    </dl>
    <div class="bottom">
      <label class="submit-border">
        <input type="submit" class="submit" id="confirm_yes" value="确定" />
      </label> </div>
  </form>
  <?php } else { ?>
  <p style="line-height:80px;text-align:center">该代金券并不存在，请检查参数是否正确!</p>
  <?php } ?>
</div>
