<?php defined('In718Shop') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
        <h3><?php echo '弹窗设置';?></h3>
      <ul class="tab-base">
          <li><a href="index.php?act=mb_special1&op=special_list"><span><?php echo $lang['nc_manage'];?></span></a></li>
         <!--  <li><a href="index.php?act=mb_special1&op=popup&item_id=320"><span><?php echo '弹窗';?></span></a></li>
          <li><a href="JavaScript:void(0);" class="current"><span><?php echo '弹窗开关';?></span></a></li> -->
      </ul>
    </div>
  </div>
  <div class="fixed-empty"></div>
  <form method="post" name="form_popupverify">
    <input type="hidden" name="form_submit" value="ok" />
    <table class="table tb-type2">
      <tbody>
        <tr class="noborder">
          <td colspan="2" class="required"><label><?php echo '是否开启弹窗'?>:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform onoff"><label for="rewrite_enabled"  class="cb-enable <?php if($output['item_usable'] == '1'){ ?>selected<?php } ?>" title="<?php echo $lang['nc_yes'];?>"><span><?php echo $lang['nc_yes'];?></span></label>
            <label for="rewrite_disabled" class="cb-disable <?php if($output['item_usable'] == '0'){ ?>selected<?php } ?>" title="<?php echo $lang['nc_no'];?>"><span><?php echo $lang['nc_no'];?></span></label>
            <input id="rewrite_enabled" name="item_usable" <?php if($output['item_usable'] == '1'){ ?>checked="checked"<?php } ?> value="1" type="radio">
            <input id="rewrite_disabled" name="item_usable" <?php if($output['item_usable'] == '0'){ ?>checked="checked"<?php } ?> value="0" type="radio"></td>
          <td class="vatop tips">
            <?php echo $lang['open_rewrite_tips'];?></td>
        </tr>
      </tbody>
      <tfoot>
        <tr class="tfoot">
          <td colspan="2" ><a href="JavaScript:void(0);" class="btn" onclick="document.form_popupverify.submit()"><span><?php echo $lang['nc_submit'];?></span></a></td>
        </tr>
      </tfoot>
    </table>
  </form>
</div>