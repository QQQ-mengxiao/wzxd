
<?php defined('In718Shop') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3>HS编辑</h3>
      <ul class="tab-base">
        <li><a href="index.php?act=kuajing_hs&op=start" ><span>管理</span></a></li>
        <li><a href="index.php?act=kuajing_hs&op=add"><span><?php echo $lang['nc_new'];?></span></a></li>
		<li><a href="JavaScript:void(0);" class="current"><span><?php echo $lang['nc_edit'];?></span></a></li>
      </ul>
    </div>
  </div>
  <div class="fixed-empty"></div>
  <form id="grade_form_abc" name="grade_form_abc" method="post" action="index.php?act=kuajing_hs&op=edit_hs">
    <input type="hidden" name="hs_id" value="ok" />
	<input type="hidden" name="id" value="<?php echo $output['hs']['id'];?>" />
    <table class="table tb-type2">
      <tbody>
        <tr class="noborder">
          <td colspan="2" class="required"><label class="validation" for="hs">hs:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform"><input type="text" value="<?php echo $output['hs']['hs']?>" id="hs" name="hs" class="txt"></td>
          <td class="vatop tips"></td>
        </tr>
       <tr class="noborder">
          <td colspan="2" class="required"><label class="validation" for="xiaofei_rate">消费税率:</label></td>
        </tr>
       <?php print_r($output['hs_11']);?>
        <tr class="noborder">
          <td class="vatop rowform">
		  <input type="text" value="<?php echo $output['hs']['xiaofei_rate'];?>" id="xiaofei_rate" name="xiaofei_rate" class="txt" maxlength="6"></td>
          <td class="vatop tips"></td>
        </tr>
        <tr class="noborder">
          <td colspan="2" class="required"><label class="validation" for="zengzhi_rate">增值税率:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform"><input type="text" value="<?php echo $output['hs']['zengzhi_rate'];?>" id="zengzhi_rate" name="zengzhi_rate" class="txt" maxlength="6"></td>
          <td class="vatop tips"></td>
        </tr>
       
      </tbody>
      <tfoot>
        <tr class="tfoot">
          <td colspan="15"><a href="JavaScript:void(0);" class="btn" id="submitBtn2" type="submit"><span><?php echo $lang['nc_submit'];?></span></a></td>
        </tr>
      </tfoot>
    </table>
  </form>
</div>
<script type="text/javascript">
$(function(){$("#submitBtn2").click(function(){
    if($("#grade_form_abc").valid()){
       $("#grade_form_abc").submit();
	}
	});
});
$(document).ready(function(){
    $('#grade_form_abc').validate({
		errorPlacement: function(error, element){
			error.appendTo(element.parent().parent().prev().find('td:first'));
        },

        rules : {
			hs:{
				required : true,
                minlength : 10
//                maxlength : 10
			},
		    xiaofei_rate:{
				required : true,
                min : 0.0000,
                max : 0.9999
			},
			zengzhi_rate:{
				required : true,
                min : 0.0000,
                max : 0.9999
			}
        },
        messages : {
            hs: {
                required : '<?php echo '请输入hs码';?>',
		        minlength: '<?php echo '输入的hs码至少为10位';?>'
//                maxlength: '<?php //echo '输入的hs码必须为10位纯数字';?>//'

            },
			xiaofei_rate: {
                required : '<?php echo '请输入消费税';?>',
                max :'<?php echo '输入的消费税必须为大于0小于1的小数';?>'

            },
			zengzhi_rate: {
                required : '<?php echo '请输入增值税';?>',
			    max :'<?php echo '输入的增值税必须为大于0小于1的小数';?>'

            }

        },
			
    });
		
});

</script>

