<?php defined('In718Shop') or exit('Access Invalid!');?>
<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3>会员卡管理</h3>
      <ul class="tab-base">
      	<li><a href="index.php?act=vip_card&op=index"><span>会员卡列表</span></a></li>
        <li><a href="JavaScript:void(0);" class="current"><span>新增会员卡</span></a></li>
      </ul>
    </div>
  </div>
  <div class="fixed-empty"></div>
    <div class="fixed-empty"></div>
    <form id="points_form" method="post" name="formvip">
        <input type="hidden" name="form_submit" value="ok" />
        <table class="table tb-type2 nobdb">
            <tbody>
            <tr class="noborder">
                <td colspan="2" class="required"><label class="validation">会员卡前缀:</label></td>
            </tr>
            <tr class="noborder">
                <td class="vatop rowform"><input type="text" id="vip_card_prefix" name="vip_card_prefix" class="txt"></td>
            </tr>
            <tr class="noborder">
                <td colspan="2"><label class="validation">会员卡等级:</label></td>
            </tr>
            <tr class="noborder">
                <td>
                    <select name="vip_card_grade">
                        <option value="0" <?php if ($_POST['vip_card_grade'] == '0'){echo 'selected=selected';}?>><?php echo $output['member_grade'][0]['level_name'];?></option>
                        <option value="1" <?php if ($_POST['vip_card_grade'] == '1'){echo 'selected=selected';}?>><?php echo $output['member_grade'][1]['level_name'];?></option>
                        <option value="2" <?php if ($_POST['vip_card_grade'] == '2'){echo 'selected=selected';}?>><?php echo $output['member_grade'][2]['level_name'];?></option>
                        <option value="3" <?php if ($_POST['vip_card_grade'] == '3'){echo 'selected=selected';}?>><?php echo $output['member_grade'][3]['level_name'];?></option>
                    </select>
                </td>
            </tr>
            <tr class="noborder">
                <td colspan="2" class="required"><label class="validation">会员卡张数:</label></td>
            </tr>
            <tr class="noborder">
                <td class="vatop rowform"><input type="text" id="count" name="count" class="txt"></td>
            </tr>
            </tbody>
            <tfoot>
            <tr class="tfoot">
                <td colspan="2" ><a href="JavaScript:void(0);" class="btn" onclick="document.formvip.submit()"><span><?php echo $lang['nc_submit'];?></span></a></td>
            </tr>
            </tfoot>
        </table>
    </form>
</div>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/jquery.ui.js"></script> 
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/i18n/zh-CN.js" charset="utf-8"></script>
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/themes/ui-lightness/jquery.ui.css"  />
<script language="javascript">
    $(function(){
        $('#points_form').validate({
            rules : {
                vip_card_prefix: {
                    required : true
                },
                count   : {
                    required : true,
                    min : 1
                }
            },
            messages : {
                vip_card_prefix: {
                    required : '请输入会员卡号前缀'
                },
                count  : {
                    required : '请输入会员卡张数',
                    min : '会员卡张数至少为1张'
                }
            }
        });
    });
</script>
