<?php defined('In718Shop') or exit('Access Invalid!');?>

<div class="page">
  <form method="post" name="form1" id="form1" action="<?php echo urlAdmin('groupbuy_leader', 'ajaxBreakState');?>">
    <input type="hidden" name="form_submit" value="ok" />
    <input type="hidden" value="<?php echo $output["address_id"];?>" name="address_id">
    <table class="table tb-type2 nobdb">
      <tbody>
        <tr class="noborder">
          <td colspan="2" class="required"><label for="xie_state">是否同意歇业:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform">
            <li>
              <input type="radio" value="1" name="state" id="state0">
              <label for="state0">同意</label>
            </li>
            <li>
              <input type="radio" checked="checked" value="2" name="state" id="state1">
              <label for="state1">拒绝</label>
            </li>
            </td>
        </tr>
      </tbody>
      <tfoot>
        <tr class="tfoot">
          <td colspan="2"><a href="javascript:void(0);" class="btn" nctype="btn_submit"><span><?php echo $lang['nc_submit'];?></span></a></td>
        </tr>
      </tfoot>
    </table>
  </form>
</div>
<style type="text/css">
  .page {
	padding-top: 0px;
	margin-top: 0px;
}
</style>
<script>
$(function(){
    $('a[nctype="btn_submit"]').click(function(){
        ajaxpost('form1', '', '', 'onerror');
    });
});
</script>
