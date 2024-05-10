<?php defined('In718Shop') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3>一卡通管理</h3>
      <ul class="tab-base">
        <li><a href="index.php?act=member_card&op=index" ><span>管理</span></a></li>
        <li><a href="JavaScript:void(0);" class="current"><span>新增</span></a></li>
        <li><a href="index.php?act=member_card&op=batch_add" ><span>批量导入</span></a></li>
      </ul>
    </div>
  </div>
  <div class="fixed-empty"></div>
  <div id="prompt">
    <div class="title"><h5>操作提示：已经绑定过的会员或卡号无法重复绑定</h5></div>
  </div>
  <form id="user_form" enctype="multipart/form-data" method="post">
    <input type="hidden" name="form_submit" value="ok" />
    <table class="table tb-type2">
      <tbody>
        <tr class="noborder">
          <td colspan="2" class="required"><label class="validation" for="member_id">会员ID:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform"><input type="text" value="" name="member_id" id="member_id" class="txt"></td>
          <td class="vatop tips"></td>
        </tr>
        <tr>
          <td colspan="2" class="required"> <select name="search_field_name" >
      <option <?php if($output['search_field_name']=='cardno'){ ?>selected='selected'<?php } ?> value="cardno">一卡通卡号</option>
      <option <?php if($output['search_field_name']=='personalId'){ ?>selected='selected'<?php } ?> value="personalId">工号</option>
    </select></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform"><input type="text" id="<?php echo $output['search_field_value'];?>" value="<?php echo $output['search_field_value'];?>" name="search_field_value" class="txt"></td>
          <td class="vatop tips"></td>
        </tr>
<!--        <tr>
          <td colspan="2" class="required"><label class="" for="status">绑定状态（请填写：1 确认绑定 0 不绑定）:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform"><input type="text" value="" id="status" name="status" class="txt"></td>
          <td class="vatop tips"></td>
        </tr>  -->
      </tbody>
      <tfoot>
        <tr class="tfoot">
          <td colspan="15"><a href="JavaScript:void(0);" class="btn" id="submitBtn"><span>保存</span></a></td>
        </tr>
      </tfoot>
    </table>
  </form>
</div>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/jquery.ui.js"></script>
<link href="<?php echo RESOURCE_SITE_URL;?>/js/jquery.Jcrop/jquery.Jcrop.min.css" rel="stylesheet" type="text/css" id="cssfile2" />
<script type="text/javascript">
$(function(){
	//按钮先执行验证再提交表单
	$("#submitBtn").click(function(){
    if($("#user_form").valid()){
      $("#user_form").submit();
	  }
	});
  $('#user_form').validate({
    errorPlacement: function(error, element){
			error.appendTo(element.parent().parent().prev().find('td:first'));
    },
    rules : {
			member_id: {
			  required : true,
				minlength: 1,
				maxlength: 11,
				remote   : {                //验证id是否已存在
            url :'index.php?act=member_card&op=ajax&branch=check_member_id',
            type:'get',
            data:{
              user_name : function(){ return $('#member_id').val(); },
            }
        }
			},
      cardno: {
				required : true,
        maxlength: 6,
        minlength: 6,    
				remote   : {         //验证cardNo是否已存在
            url :'index.php?act=member_card&op=ajax&branch=check_cardno',
            type:'get',
            data:{
              user_name : function(){ return $('#cardno').val();  },
            }
        }
      },
    },
    messages : {
			member_id: {
			  required : '会员ID不能为空',
				maxlength: '会员ID长度应在1-11位之间',
				minlength: '会员ID长度应在1-11位之间',
				remote   : '会员ID已存在，勿重复绑定'
			},
      cardno: {
				required : '一卡通卡号不能为空',
        maxlength: '卡号长度应为6位字符',
        minlength: '卡号长度应为6位字符',         
				remote : '卡号已存在，勿重复绑定'
      }
    }
  });
});
</script>
