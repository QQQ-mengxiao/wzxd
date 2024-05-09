<?php defined('In718Shop') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3>我的模块设置</h3>
    </div>
  </div>
  <div class="fixed-empty"></div>
  <form method="post" name="form_mine" id="form_mine">
    <input type="hidden" name="form_submit" value="ok" />
    <table class="table tb-type2">
      <tbody>
	<!-- 400 电话 -->		
<tr>
          <td colspan="2" class="required"><label for="service_number">客服电话:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform"><input id="service_number" name="service_number" value="<?php echo $output['mine_setting']['service_number'];?>" class="txt" type="text" /></td>
          <td class="vatop tips"><span class="vatop rowform">显示在我的模块联系我们</span></td>
        </tr>
		<!-- 400 电话 -->	


        <tr>
          <td colspan="2" class="required"><label for="about_us">关于我们:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform"><textarea name="about_us" rows="6" class="tarea" id="about_us" ><?php echo $output['mine_setting']['about_us'];?></textarea></td>
          <td class="vatop tips"><span class="vatop rowform">显示在我的模块关于我们</span></td>
        </tr>
      </tbody>
      <tfoot id="submit-holder">
        <tr class="tfoot">
          <td colspan="2" ><a href="JavaScript:void(0);" class="btn" onclick="document.form_mine.submit()"><span><?php echo $lang['nc_submit'];?></span></a></td>
        </tr>
      </tfoot>
    </table>
  </form>
</div>
<script type="text/javascript">
// 模拟网站LOGO上传input type='file'样式
$(function(){
	$("#site_logo").change(function(){
		$("#textfield1").val($(this).val());
	});
	$("#member_logo").change(function(){
		$("#textfield2").val($(this).val());
	});
	$("#seller_center_logo").change(function(){
		$("#textfield3").val($(this).val());
	});
	//v 3-b1 1
	$("#site_mobile_logo").change(function(){
		$("#textfield8").val($(this).val());
	});
	$("#site_logowx").change(function(){
		$("#textfield5").val($(this).val());
	});
// 上传图片类型
$('input[class="type-file-file"]').change(function(){
	var filepatd=$(this).val();
	var extStart=filepatd.lastIndexOf(".");
	var ext=filepatd.substring(extStart,filepatd.lengtd).toUpperCase();		
		if(ext!=".PNG"&&ext!=".GIF"&&ext!=".JPG"&&ext!=".JPEG"){
			alert("<?php echo $lang['default_img_wrong'];?>");
				$(this).attr('value','');
			return false;
		}
	});
$('#time_zone').attr('value','<?php echo $output['list_setting']['time_zone'];?>');	
});
</script>
