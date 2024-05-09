<?php defined('In718Shop') or exit('Access Invalid!');?>

<div class="page">
  <form method="post" name="form1" id="form1" action="<?php echo urlAdmin('evaluate', 'ajaxVoucher');?>">
    <input type="hidden" name="form_submit" value="ok" />
    <input type="hidden" value="<?php echo $output["geval_id"];?>" name="geval_id">
    <table class="table tb-type2 nobdb">
      <tbody>
        <tr class="noborder">
          <td colspan="2" class="required"><label for="xie_state">请选择代金券:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform">
            <select name="voucher_t_id" class="querySelect">
              <?php foreach($output['voucher_list'] as $v){?>
                <option value=<?php echo $v['voucher_t_id'];?>><?php echo $v['voucher_t_title'];?></option>
              <?php }?>
            </select>  
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
