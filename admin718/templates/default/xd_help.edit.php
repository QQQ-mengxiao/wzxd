<?php defined('In718Shop') or exit('Access Invalid!');?>
<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3>小店帮助</h3>
      <ul class="tab-base">
        <li><a href="index.php?act=xd_help&op=xd_help"><span><?php echo '帮助内容';?></span></a></li>
        <li><a href="JavaScript:void(0);" class="current"><span><?php echo '编辑内容';?></span></a></li>
      </ul>
    </div>
  </div>
  <div class="fixed-empty"></div>
  <form id="post_form" method="post" name="form1">
    <input type="hidden" name="form_submit" value="ok" />
    <table class="table tb-type2">
      <tbody>
      	<tr class="noborder">
          <td colspan="2" class="required"><label class="validation" for="help_title">帮助标题:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform"><input id="help_title" name="help_title" value="<?php echo $output['help']['help_title']?>" class="txt" type="text"></td>
          <td class="vatop tips"></td>
        </tr>
        <tr>
          <td colspan="2" class="required"><label class="validation">帮助内容:</label></td>
        </tr>
        <tr class="noborder">
          <td colspan="2" class="vatop rowform"><?php showEditor('content',$output['help']['help_content']);?></td>
        </tr>
      </tbody>
      <tfoot>
        <tr class="tfoot">
          <td colspan="15" ><a href="JavaScript:void(0);" class="btn" id="submitBtn"><span><?php echo $lang['nc_submit'];?></span></a></td>
        </tr>
      </tfoot>
    </table>
  </form>
</div>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/fileupload/jquery.iframe-transport.js" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/fileupload/jquery.ui.widget.js" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/fileupload/jquery.fileupload.js" charset="utf-8"></script>
<script>
var UPLOAD_ARTICLE_URL = "<?php echo UPLOAD_SITE_URL.'/'.ATTACH_ARTICLE.'/'; ?>";
//按钮先执行验证再提交表单
$(function(){
	$("#submitBtn").click(function(){
        if($("#post_form").valid()){
            $("#post_form").submit();
    	}
	});
	$("#post_form").validate({
		errorPlacement: function(error, element){
			error.appendTo(element.parent().parent().prev().find('td:first'));
        },
        rules : {
            help_title : {
                required : true
            },
			content : {
                required   : true
            }
        },
        messages : {
            help_title : {
                required : "类型名称不能为空"
            },
            content : {
                required : "帮助内容不能为空"
            }
        }
	});
});

function insert_editor(file_name){
	KE.appendHtml('content', '<img src="'+UPLOAD_ARTICLE_URL+ file_name + '">');
}

</script>
