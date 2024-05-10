<?php defined('In718Shop') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3><?php echo $lang['member_index_manage']?></h3>
      <ul class="tab-base">
        <li><a href="index.php?act=member&op=member" ><span><?php echo $lang['nc_manage']?></span></a></li>
        <li><a href="index.php?act=member&op=member_add" ><span><?php echo $lang['nc_new']?></span></a></li>
        <li><a href="JavaScript:void(0);" class="current"><span>审核</span></a></li>
      </ul>
    </div>
  </div>
  <div class="fixed-empty"></div>
  <form id="user_form" enctype="multipart/form-data" method="post">
    <input type="hidden" name="form_submit" value="ok" />
    <input type="hidden" name="member_id" value="<?php echo $output['member_array']['member_id'];?>" />
    <input type="hidden" name="old_member_avatar" value="<?php echo $output['member_array']['member_avatar'];?>" />
    <input type="hidden" name="member_name" value="<?php echo $output['member_array']['member_name'];?>" />
    <table class="table tb-type2">
      <tbody>
        <tr class="noborder">
          <td colspan="2" class="required"><label>用户名字:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform"><?php echo $output['member_array']['member_name'];?></td>
          <td class="vatop tips"></td>
        </tr>
         <tr>
          <td colspan="2" class="required"><label for="member_passwd">真实姓名:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform"><?php echo $output['member_array']['member_truename'];?></td>
<!--         <td class="vatop tips">真实姓名.....</td>-->
        </tr>
        <tr>
          <td colspan="2" class="required"><label for="member_passwd">身份证号:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform"><?php echo $output['member_array']['ID_card'];?></td>
<!--         <td class="vatop tips">dakjdlkajkajdl</td>-->
        </tr>
        <tr>
          <td colspan="2" class="required"><label class="" for="member_email">所在地区:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform"><?php echo $output['member_array']['member_areainfo'];?></td>
<!--          <td class="vatop tips">会员所在地区</td>-->
       <!--  </tr>
        <tr>
          <td colspan="2" class="required">真实头像</td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform">
          <a href="<?php echo UPLOAD_SITE_URL.'/'.DIR_UPLOAD_IDCARD.'/'.$output['member_array']['member_trueavatar'];?>" class="nyroModal" rel="gal">
            <img width="64" height="64" class="show_image" src="<?php echo UPLOAD_SITE_URL.'/'.DIR_UPLOAD_IDCARD.'/'.$output['member_array']['member_trueavatar'];?>"></a>
            </td> -->
<!--          <td class="vatop tips">会员照片</td>-->
        </tr>
        <tr>
          <td colspan="2" class="required">身份证正面照</td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform">
          <a href="<?php echo UPLOAD_SITE_URL.'/'.DIR_UPLOAD_IDCARD.'/'.$output['member_array']['ID_card_photo'];?>" class="nyroModal" rel="gal">
            <img width="64" height="64" class="show_image" src="<?php echo UPLOAD_SITE_URL.'/'.DIR_UPLOAD_IDCARD.'/'.$output['member_array']['ID_card_photo'];?>"></a>
            </td>
<!--          <td class="vatop tips">身份证照片</td>-->
        </tr>
       <!--  <tr>
          <td colspan="2" class="required">身份证反面照</td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform">
          <a href="<?php echo UPLOAD_SITE_URL.'/'.DIR_UPLOAD_IDCARD.'/'.$output['member_array']['ID_card_sidephoto'];?>" class="nyroModal" rel="gal">
            <img width="64" height="64" class="show_image" src="<?php echo UPLOAD_SITE_URL.'/'.DIR_UPLOAD_IDCARD.'/'.$output['member_array']['ID_card_sidephoto'];?>"></a>
            </td>
          <td class="vatop tips">身份证照片</td>
        </tr> -->
       <!--  一些审核按钮 -->
        <tr>
          <td colspan="2" class="required"><label>审核结果:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform onoff">
            <label for="member_verify_1" class="cb-enable <?php if($output['member_array']['member_verify'] == '1'){ ?>selected<?php } ?>" ><span>通过</span></label>
            <label for="member_verify_2" class="cb-disable <?php if($output['member_array']['member_verify'] == '0'){ ?>selected<?php } ?>" ><span>不通过</span></label>
            <input id="member_verify_1" name="member_verify" <?php if($output['member_array']['member_verify'] == '1'){ ?>checked="checked"<?php } ?>  value="1" type="radio">
            <input id="member_verify_2" name="member_verify" <?php if($output['member_array']['member_verify'] == '0'){ ?>checked="checked"<?php } ?> value="0" type="radio"></td>
      </tbody>
      <tfoot>
        <tr class="tfoot">
          <td colspan="15"><a href="JavaScript:void(0);" class="btn" id="submitBtn"><span><?php echo $lang['nc_submit'];?></span></a></td>
        </tr>
      </tfoot>
    </table>
  </form>
</div>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/dialog/dialog.js" id="dialog_js" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/jquery.ui.js"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/ajaxfileupload/ajaxfileupload.js"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.Jcrop/jquery.Jcrop.js"></script>
<link href="<?php echo RESOURCE_SITE_URL;?>/js/jquery.Jcrop/jquery.Jcrop.min.css" rel="stylesheet" type="text/css" id="cssfile2" />
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/common_select.js" charset="utf-8"></script> 
<script type="text/javascript">
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
            member_passwd: {
                maxlength: 20,
                minlength: 6
            },
            member_email   : {
                required : false,
                email : true,
        remote   : {
                    url :'index.php?act=member&op=ajax&branch=check_email',
                    type:'get',
                    data:{
                        user_name : function(){
                            return $('#member_email').val();
                        },
                        member_id : '<?php echo $output['member_array']['member_id'];?>'
                    }
                }
            }
        },
        messages : {
            member_passwd : {
                maxlength: '<?php echo $lang['member_edit_password_tip']?>',
                minlength: '<?php echo $lang['member_edit_password_tip']?>'
            },
            member_email  : {
                required : '<?php echo $lang['member_edit_email_null']?>',
                email   : '<?php echo $lang['member_edit_valid_email']?>',
        remote : '<?php echo $lang['member_edit_email_exists']?>'
            }
        }
    });
</script>
  <script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.nyroModal/custom.min.js" charset="utf-8"></script>
<link href="<?php echo RESOURCE_SITE_URL;?>/js/jquery.nyroModal/styles/nyroModal.css" rel="stylesheet" type="text/css" id="cssfile2" />
<script type="text/javascript">
$(function(){
    $('.nyroModal').nyroModal();
  $("#submitBtn").click(function(){
    if($("#post_form").valid()){
     $("#post_form").submit();
    }
  });
    $('#post_form').validate({
    errorPlacement: function(error, element){
      error.appendTo(element.parent().parent().prev().find('td:first'));
        },
        rules : {
            admin_message : {
                required   : true
            }
        },
        messages : {
            admin_message  : {
                required   : '<?php echo $lang['refund_message_null'];?>'
            }
        }
    });
});
</script>


