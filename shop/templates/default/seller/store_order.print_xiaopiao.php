<div class="eject_con">
<div id="warning"></div>
<?php if ($output['order_info']) {?>

  <form id="changeform" method="post" action="index.php?act=store_order&op=print_xiaopiao&order_id=<?php echo $output['order_info']['order_id']; ?>">
    <input type="hidden" name="form_submit" value="ok" />
    <dl>
      <dt><?php echo $lang['store_order_buyer_with'].$lang['nc_colon'];?></dt>
      <dd><?php echo $output['order_info']['buyer_name']; ?></dd>
    </dl>
    <dl>
      <dt><?php echo $lang['store_order_sn'].$lang['nc_colon'];?></dt>
      <dd><span class="num"><?php echo $output['order_info']['order_sn']; ?></span></dd>
    </dl>
    <dl>
      <dt><?php echo '打印机'.$lang['nc_colon'];?></dt>
      <dd>
        <select id="dayin_id" name="dayin_id" class="w300">
        <?php if(!empty($output['dayin_info']) && is_array($output['dayin_info'])){ ?>
          <?php foreach($output['dayin_info'] as $k => $v){ ?>
       
              <option value="<?php echo $v['dayin_id'];?>" ><?php echo $v['dayin_name'].'&nbsp/&nbsp;'.$v['dayin_sn']; ?></option>
            
          <?php }?>
        <?php }?>
        </select>
      </dd>
    </dl>
    <dl class="bottom">
      <dt>&nbsp;</dt>
      <dd>
        <input type="submit" class="submit" id="confirm_button" value="<?php echo $lang['nc_ok'];?>" />
      </dd>
    </dl>
  </form>
<?php } else { ?>
<p style="line-height:80px;text-align:center">该订单并不存在，请检查参数是否正确!</p>
<?php } ?>
</div>
