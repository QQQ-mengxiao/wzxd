<?php defined('In718Shop') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3><?php echo $lang['admin_points_log_title'];?></h3>
      <ul class="tab-base">
      <li><a href="index.php?act=points&op=addpoints"><span><?php echo $lang['nc_manage']?></span></a></li>
      <li><a href="index.php?act=points&op=pointslog"><span><?php echo $lang['admin_points_log_title'];?></span></a></li>
          <li><a href="JavaScript:void(0);" class="current"><span>连续签到设置</span></a></li>
      </ul>
    </div>
  </div>
  <div class="fixed-empty"></div>
    <form id="points_form" method="post" name="form1">
        <input type="hidden" name="form_submit" value="ok" />
        <table class="table tb-type2">
            <thead>
            <tr class="thead">
                <th colspan="5">连续签到规则设置：</th>
            </tr>
            <tr class="thead">
                <th class="align-left" >连续签到天数</th>
                <th class="align-left">签到送积分值</th>
                <th class="align-left">签到送经验值</th>
            </tr>
            </thead>
            <tbody id="row_1">
                   <td class="align-left"><input type="text" name="day1" id="day1" value="日常积分" class="w60"/></td>
                <td class="align-left"><input type="text" name="points1" id="points1" value="<?php echo $output['serial_sign'][0]['points'];?>" class="w60"/></td>
                 <td class="align-left"><input type="text" name="exp1" id="exp1" value="<?php echo $output['serial_sign'][0]['exp'];?>" class="w60"/></td>

             </tr>
            <tr id="row_2">
                  <td class="align-left"><input type="text" name="day2" id="day2" value="<?php echo $output['serial_sign'][1]['days'];?>" class="w60"/></td>
                <td class="align-left"><input type="text" name="points2" id="points2" value="<?php echo $output['serial_sign'][1]['points'];?>" class="w60" nc_type="verify" data-param='{"name":"经验值","type":"int"}'/></td>
                 <td class="align-left"><input type="text" name="exp2" id="exp2" value="<?php echo $output['serial_sign'][1]['exp'];?>" class="w60"/></td>
            </tr>
             <tr id="row_3">
                 <td class="align-left"><input type="text" name="day3" id="day3" value="<?php echo $output['serial_sign'][2]['days'];?>" class="w60"/></td>
                <td class="align-left"><input type="text" name="points3" id="points3" value="<?php echo $output['serial_sign'][2]['points'];?>" class="w60" nc_type="verify" data-param='{"name":"经验值","type":"int"}'/></td>
                 <td class="align-left"><input type="text" name="exp3" id="exp3" value="<?php echo $output['serial_sign'][2]['exp'];?>" class="w60"/></td>
            </tr>
             <tr id="row_4">
                    <td class="align-left"><input type="text" name="day4" id="day4" value="<?php echo $output['serial_sign'][3]['days'];?>" class="w60"/></td>
                <td class="align-left"><input type="text" name="points4" id="points4" value="<?php echo $output['serial_sign'][3]['points'];?>" class="w60" nc_type="verify" data-param='{"name":"经验值","type":"int"}'/></td>
                 <td class="align-left"><input type="text" name="exp4" id="exp4" value="<?php echo $output['serial_sign'][3]['exp'];?>" class="w60"/></td>
            </tr>
              <tr id="row_5">
                   <td class="align-left"><input type="text" name="day5" id="day5" value="<?php echo $output['serial_sign'][4]['days'];?>" class="w60"/></td>
                <td class="align-left"><input type="text" name="points5" id="points5" value="<?php echo $output['serial_sign'][4]['points'];?>" class="w60" nc_type="verify" data-param='{"name":"经验值","type":"int"}'/></td>
                 <td class="align-left"><input type="text" name="exp5" id="exp5" value="<?php echo $output['serial_sign'][4]['exp'];?>" class="w60"/></td>
            </tr>
            </tbody>
            <tfoot>
            <tr>
                <td colspan="4"><a href="JavaScript:void(0);" class="btn" id="submitBtn"><span><?php echo $lang['nc_submit'];?></span></a></td>
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
    $('#submitBtn').click(function(){
        $('input[name="op"]').val('serialsign');$('#points_form').submit();
    });
});
</script>
