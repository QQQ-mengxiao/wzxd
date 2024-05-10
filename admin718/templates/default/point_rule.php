<?php defined('In718Shop') or exit('Access Invalid!');?>
<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3>会员级别</h3>
      <ul class="tab-base">
        <li><a href="index.php?act=member_grade&op=index"><span>级别管理</span></a></li>
        <li><a href="index.php?act=member_grade&op=gui_add" class="current" ><span>积分规则设置</span></a></li>
      </ul>
    </div>
  <div class="fixed-empty"></div>
  <form method="post" name="settingForm" id="settingForm">
    <input type="hidden" name="form_submit" value="ok" />
    <table class="table tb-type2">
      <tbody>
        <tr>
          <td class="" colspan="2"><table class="table tb-type2 nomargin">
              <thead>
                <tr class="space">
                  <th colspan="16">积分获取规则如下:</th>
                </tr>
              </thead>
              <tbody>
                <tr style="background: rgb(255, 255, 255);">
                  <td colspan="2" class="required"><label>规则描述：</label></td>
                </tr>
                <tr class="noborder" style="background: rgb(255, 255, 255);">
                <td class="vatop rowform"><textarea name="pointsdesc" rows="20" class="tarea" style="width: 700px; height: 200px;"><?php echo $output['points_rule']['value'];?></textarea></td>
                <!-- <td class="vatop tips">描述信息将显示在经验值明细相关页，会员和管理员都可见</td> -->
            </tr>
      </tbody>
      <tfoot>
        <tr class="tfoot">
          <td colspan="2" ><a href="JavaScript:void(0);" class="btn" id="submitBtn"><span><?php echo $lang['nc_submit'];?></span></a></td>
        </tr>
      </tfoot>
    </table>
  </form>
</div>
<script>
$(function(){
  $("#submitBtn").click(function(){
    $("#settingForm").submit();
  });
});
</script> 
