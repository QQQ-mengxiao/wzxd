<?php defined('In718Shop') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3>设置</h3>
      <?php echo $output['top_link'];?>
    </div>
  </div>
  <div class="fixed-empty"></div>
  <form method="post" name="form1"  action="index.php?act=bhcs&op=edit_save_bhcs">
    <input type="hidden" name="form_submit" value="ok" />
    <table class="table tb-type2">
      <tbody>
        <tr class="noborder">
          <td colspan="2" class="required"><label for="site_name">电商平台代码（检）:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform"><input id="site_name" name="bh_ecpcodeciq" value="<?php echo $output['ecpcodeciq']; ?>" class="txt" type="text" /></td>
          <td class="vatop tips"><span class="vatop rowform"></span></td>
        </tr>
        
        <tr class="noborder">
          <td colspan="2" class="required"><label for="site_name">电商平台代码（关）:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform"><input id="site_name" name="bh_ecpcodecus" value="<?php echo $output['ecpcodecus']; ?>" class="txt" type="text" /></td>
          <td class="vatop tips"><span class="vatop rowform"></span></td>
        </tr>

        <tr class="noborder">
          <td colspan="2" class="required"><label for="site_name">电商平台名称（检）:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform"><input id="site_name" name="bh_ecpnameciq" value="<?php echo $output['ecpnameciq']; ?>" class="txt" type="text" /></td>
          <td class="vatop tips"><span class="vatop rowform"></span></td>
        </tr>

       <tr class="noborder">
          <td colspan="2" class="required"><label for="site_name">电商平台名称（关）:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform"><input id="site_name" name="bh_ecpnamecus" value="<?php echo $output['ecpnamecus']; ?>" class="txt" type="text" /></td>
          <td class="vatop tips"><span class="vatop rowform"></span></td>
        </tr>

       <tr class="noborder">
          <td colspan="2" class="required"><label for="site_name">接口版本:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform"><input id="site_name" name="bh_emallyi_version" value="<?php echo $output['emallyi_version']; ?>" class="txt" type="text" /></td>
          <td class="vatop tips"><span class="vatop rowform"></span></td>
        </tr>

       <tr class="noborder">
          <td colspan="2" class="required"><label for="site_name">企业编号:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform"><input id="site_name" name="bh_companycode" value="<?php echo $output['companycode']; ?>" class="txt" type="text" /></td>
          <td class="vatop tips"><span class="vatop rowform"></span></td>
        </tr>        


      </tbody>
<tfoot id="submit-holder">
        <tr class="tfoot">
          <td colspan="2" ><a href="JavaScript:void(0);" class="btn" onclick="document.form1.submit()"><span><?php echo $lang['nc_submit'];?></span></a></td>
        </tr>
      </tfoot>
      <!--
      <tfoot id="submit-holder">
        <tr class="tfoot">
          <td colspan="2" ><a href="JavaScript:void(0);" class="btn" onclick="document.form1.submit()"><span><?php echo $lang['nc_submit'];?></span></a></td>
        </tr>
      </tfoot>
      -->
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
