<?php defined('In718Shop') or exit('Access Invalid!');?>
<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3>会员卡管理</h3>
      <ul class="tab-base">
      	<li><a href="JavaScript:void(0);" class="current"><span>会员卡列表</span></a></li>
        <li><a href="index.php?act=vip_card&op=addvipcard"><span>新增会员卡</span></a></li>
      </ul>
    </div>
  </div>
  <div class="fixed-empty"></div>
  <form method="post" name="formVip" id="formVip">
    <input type="hidden" name="act" value="vip_card">
    <input type="hidden" name="op" value="index">
    <table class="tb-type1 noborder search">
      <tbody>
        <tr>
          <th><label>会员卡号</label></th>
          <td style="width: 160px;"><input type="text" name="vip_card_num" class="txt" value='<?php echo $_POST['vip_card_num'];?>'></td>
          <th><label>会员卡等级</label></th>
          <td>
                  <select name="vip_card_grade">
                  <option value="" <?php if (!$_POST['vip_card_grade']){echo 'selected=selected';}?>>所有</option>
                  <option value="0" <?php if ($_POST['vip_card_grade'] == '0'){echo 'selected=selected';}?>><?php echo $output['member_grade'][0]['level_name'];?></option>
                  <option value="1" <?php if ($_POST['vip_card_grade'] == '1'){echo 'selected=selected';}?>><?php echo $output['member_grade'][1]['level_name'];?></option>
                  <option value="2" <?php if ($_POST['vip_card_grade'] == '2'){echo 'selected=selected';}?>><?php echo $output['member_grade'][2]['level_name'];?></option>
                  <option value="3" <?php if ($_POST['vip_card_grade'] == '3'){echo 'selected=selected';}?>><?php echo $output['member_grade'][3]['level_name'];?></option>
              </select>
          </td>
          <th><label>是否激活</label></th>
          <td>
              <select name="is_used">
                  <option value="" <?php if (!$_POST['is_used']){echo 'selected=selected';}?>>所有</option>
                  <option value="0" <?php if ($_POST['is_used'] == '0'){echo 'selected=selected';}?>>未激活</option>
                  <option value="1" <?php if ($_POST['is_used'] == '1'){echo 'selected=selected';}?>>已激活</option>
              </select>
          </td>
          <th><label>使用人ID</label></th>
          <td><input type="text" name="used_member_id" class="txt" value='<?php echo $_POST['used_member_id'];?>'></td>
          <th><label>使用时间</label></th>
            <td>
                <input type="text" id="stime" name="stime" class="txt date" value="<?php echo $_POST['stime'];?>">
                <label>~</label>
                <input type="text" id="etime" name="etime" class="txt date" value="<?php echo $_POST['etime'];?>">
            </td>
            <td><a href="javascript:void(0);" id="submit" class="btn-search " title="<?php echo $lang['nc_query'];?>">&nbsp;</a>
        </tr>
      </tbody>
    </table>
  </form> <span id="excel"><a class="btns" href="index.php?act=vip_card&op=export"><span>导出Excel</span></a></span>
  <table class="table tb-type2">
    <thead>
      <tr class="thead">
        <th>序号</th>
        <th>会员卡号</th>
        <th class="align-center">会员卡等级</th>
        <th class="align-center">是否激活</th>
        <th class="align-center">用户ID</th>
        <th>激活时间</th>
        <th>操作</th>
      </tr>
    </thead>
    <tbody>
      <?php if(!empty($output['vip_card_list']) && is_array($output['vip_card_list'])){ ?>
      <?php foreach($output['vip_card_list'] as $k => $v){?>
      <tr class="hover">
        <td><?php echo $v['vip_card_id'];?></td>
        <td><?php echo $v['vip_card_num'];?></td>
        <td class="align-center"><?php echo $output['member_grade'][$v['vip_card_grade']]['level_name'];?></td>
        <td class="nowrap align-center"><?php echo $v['is_used']==0?'未激活':'已激活';?></td>
        <td class="align-center"><?php echo $v['used_member_id']; ?></td>
        <td><?php echo $v['use_time']?date('Y-m-d H:i:s',$v['use_time']):'-';?></td>
          <td>
              <?php if ($v['is_used']== 0): ?>
                  <a onclick="return confirm('确定删除？');" href="<?php echo urlAdmin('vip_card', 'del_vip_card', array('vip_card_id' => $v['vip_card_id'])); ?>" class="normal">删除</a>
              <?php endif; ?>
          </td>
      </tr>
      <?php } ?>
      <?php }else { ?>
      <tr class="no_data">
        <td colspan="15"><?php echo $lang['nc_no_record'];?></td>
      </tr>
      <?php } ?>
    </tbody>
    <tfoot>
      <tr class="tfoot">
        <td colspan="15"><div class="pagination"> <?php echo $output['show_page'];?> </div></td>
      </tr>
    </tfoot>
  </table>
</div>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/jquery.ui.js"></script> 
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/i18n/zh-CN.js" charset="utf-8"></script>
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/themes/ui-lightness/jquery.ui.css"  />
<script language="javascript">
$(function(){
	$('#stime').datepicker({dateFormat: 'yy-mm-dd'});
	$('#etime').datepicker({dateFormat: 'yy-mm-dd'});
    $('#submit').click(function(){
    	// $('input[name="op"]').val('index');
    	$('#formVip').submit();
    });
});
</script>
