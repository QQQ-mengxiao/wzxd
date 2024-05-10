<?php defined('In718Shop') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3>通知栏管理</h3>
      <ul class="tab-base">
        <li><a href="index.php?act=noticeboard&op=noticeboard" ><span><?php echo $lang['nc_manage'];?></span></a></li>
        <li><a href="index.php?act=noticeboard&op=noticeboard_add" ><span><?php echo $lang['nc_new'];?></span></a></li>
        <li><a href="JavaScript:void(0);" class="current"><span><?php echo $lang['nc_edit'];?></span></a></li>
      </ul>
    </div>
  </div>
  <div class="fixed-empty"></div>
  <form id="noticeboard_form" method="post">
    <input type="hidden" name="form_submit" value="ok" />
    <input type="hidden" name="nav_id" value="<?php echo $output['noticeboard_array']['no_id'];?>" />
    <table class="table tb-type2">
      <tbody>
        <tr>
          <td colspan="2" class="required"><label class="validation" for="nav_title">标题:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform"><input type="text" value="<?php echo $output['noticeboard_array']['no_title'];?>" name="nav_title" id="nav_title" class="txt"></td>
          <td class="vatop tips"></td>
        </tr>
        <tr>
          <td colspan="2" class="required"><label class="validation" for="nav_content">通知内容:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform"><input type="text" value="<?php echo $output['noticeboard_array']['no_content'];?>" name="nav_content" id="" class="txt"></td>
          <td class="vatop tips"></td>
        </tr>
        <tr>
          <td colspan="2" class="required"><label for="nav_url">跳转链接:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform"><input type="text" value="<?php echo $output['noticeboard_array']['no_url'];?>" name="nav_url" id="nav_url" class="txt"></td>
          <td class="vatop tips"></td>
        </tr>
        
        <tr>
          <td colspan="2" class="required"><label>
            <label>是否展示:</label>
            </label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform onoff"><label for="nav_new_open1" class="cb-enable <?php if($output['noticeboard_array']['is_open'] == '1'){ ?>selected<?php } ?>" ><span><?php echo $lang['nc_yes'];?></span></label>
            <label for="nav_new_open0" class="cb-disable <?php if($output['noticeboard_array']['is_open'] == '0'){ ?>selected<?php } ?>" ><span><?php echo $lang['nc_no'];?></span></label>
            <input id="nav_new_open1" name="nav_new_open" <?php if($output['noticeboard_array']['is_open'] == '1'){ ?>checked="checked"<?php } ?>  value="1" type="radio">
            <input id="nav_new_open0" name="nav_new_open" <?php if($output['noticeboard_array']['is_open'] == '0'){ ?>checked="checked"<?php } ?> value="0" type="radio"></td>
          <td class="vatop tips"></td>
        </tr>
      </tbody>
      <tfoot>
        <tr class="tfoot">
          <td colspan="15"><a href="JavaScript:void(0);" class="btn" id="submitBtn"><span><?php echo $lang['nc_submit'];?></span></a></td>
        </tr>
      </tfoot>
    </table>
  </form>
</div>
<script>
//按钮先执行验证再提交表单
$(function(){$("#submitBtn").click(function(){
    if($("#noticeboard_form").valid()){
     $("#noticeboard_form").submit();
	}
	});
});
//
$(document).ready(function(){
	$('#noticeboard_form').validate({
        errorPlacement: function(error, element){
			error.appendTo(element.parent().parent().prev().find('td:first'));
        },
        rules : {
            nav_title : {
                required : true
            },
            nav_sort:{
               number   : true
            }
        },
        messages : {
            nav_title : {
                required : '<?php echo $lang['noticeboard_add_partner_null'];?>'
            },
            nav_sort  : {
                number   : '<?php echo $lang['noticeboard_add_sort_int'];?>'
            }
        }
    });
	
	<?php if($output['noticeboard_array']['nav_type'] == '1'){ ?>
	showType('goods_class');
	<?php } ?>
	<?php if($output['noticeboard_array']['nav_type'] == '2'){ ?>
	showType('article_class');
	<?php } ?>
	<?php if($output['noticeboard_array']['nav_type'] == '3'){ ?>
	showType('activity');
	<?php } ?>
});

function showType(type){
	$('#goods_class_id').css('display','none');
	$('#article_class_id').css('display','none');
	$('#activity_id').css('display','none');
	if(type == 'diy'){
		$('#nav_url').attr('disabled',false);
	}else{
		$('#nav_url').attr('disabled',true);
		$('#'+type+'_id').show();	
	}
}
</script>