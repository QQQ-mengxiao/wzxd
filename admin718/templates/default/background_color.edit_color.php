<?php defined('In718Shop') or exit('Access Invalid!');?>

<div class="page">
  <form method="post" name="form11" id="form11" action="<?php echo urlAdmin('background_color', 'ajaxEditColor');?>">
    <input type="hidden" name="form_submit" value="ok" />
    <input type="hidden" value="<?php echo $output["id"];?>" name="id">
    <table class="table tb-type2 nobdb">
      <tbody>
        <tr class="noborder">
          <td colspan="2" class="required">点击选择颜色:</td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform">
          <input type="text" class="text w100" name="color" value="<?php echo $output['color'];?>" nctype="spec_color" />
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
<script src="<?php echo RESOURCE_SITE_URL;?>/js/colorpicker/evol.colorpicker.min.js"></script>
<link href="<?php echo RESOURCE_SITE_URL;?>/js/colorpicker/evol.colorpicker.css" rel="stylesheet" type="text/css">
<script>
$(function(){
    $('a[nctype="btn_submit"]').click(function(){
        ajaxpost('form11', '', '', 'onerror');
    });
});
    // 颜色选择器
    $('input[nctype="spec_color"]').colorpicker({showOn:'both'}).removeAttr('nctype');

</script>
