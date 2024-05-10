<?php defined('In718Shop') or exit('Access Invalid!');?>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/jquery.ui.js"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/i18n/zh-CN.js" charset="utf-8"></script>
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/themes/ui-lightness/jquery.ui.css"/>
<div class="page">
  <!-- 页面导航 -->
  <div class="fixed-bar">
    <div class="item-title">
      <h3><?php echo $lang['nc_voucher_price_manage'];?></h3>
      <ul class="tab-base">
        <?php   foreach($output['menu'] as $menu) {  if($menu['menu_key'] == $output['menu_key']) { ?>
          <li><a href="JavaScript:void(0);" class="current"><span><?php echo $menu['menu_name'];?></span></a></li>
        <?php }  else { ?>
          <li><a href="<?php echo $menu['menu_url'];?>" ><span><?php echo $menu['menu_name'];?></span></a></li>
        <?php  } }  ?>
      </ul>
      </ul>
    </div>
  </div>
  <!--  搜索 -->
  <div class="fixed-empty"></div>
  <form method="get" name="formSearch">
    <input type="hidden" name="act" value="voucher">
    <input type="hidden" name="op" value="donate">
    <table class="tb-type1 noborder search">
      <tbody>
      <tr>
        <th><label for="store_name"><?php echo '用户名类型';?></label></th>
          <td><select name="search_field_name" >
                  <option <?php if($_GET['search_field_name'] == 'donate'){ ?>selected='selected'<?php } ?> value="donate"><?php echo '转赠'?></option>
                  <option <?php if($_GET['search_field_name'] == 'accept'){ ?>selected='selected'<?php } ?> value="accept"><?php echo '接收'?></option>
              </select></td>
          <td><input type="text" value="<?php echo $_GET['search_field_value'];?>" name="search_field_value" class="txt"></td>
        <th><label for="store_name"><?php echo '转赠时间';?></label></th>
        <td><input type="text" id="sdate" name="sdate" class="txt date" value="<?php echo $_GET['sdate'];?>" >~<input type="text" id="edate" name="edate" class="txt date" value="<?php echo $_GET['edate'];?>" ></td>
        <td><a href="javascript:document.formSearch.submit();" class="btn-search " title="<?php echo $lang['nc_query'];?>">&nbsp;</a></td>
      </tr>
      </tbody>
    </table>
  </form>
  <!-- 帮助 -->
  <table class="table tb-type2" id="prompt">
    <tbody>
    <tr class="space odd">
      <th colspan="12" class="nobg"><div class="title">
          <h5><?php echo $lang['nc_prompts'];?></h5>
          <span class="arrow"></span></div></th>
    </tr>
    <tr>
      <td><ul><li><?php echo $lang['admin_voucher_template_list_tip'];?></li></ul></td>
    </tr>
    </tbody>
  </table>
  <!-- 列表 -->
  <?php //var_dump($output['list'])?>
  <form id="list_form" method="post" action="index.php?act=voucher&op=donateop">
    <input type="hidden" id="log_id" name="log_id" value="" />
    <table class="table tb-type2">
      <thead>
      <tr class="thead">
        <th class="w24"></th>
        <th class="w70">代金券编码</th>
        <th class="w130">转赠者</th>
        <th class="w70">接收者</th>
        <th class="w100">面额（元）</th>
        <th class="w120">转赠时间</th>
        <th style="display: none" class="align-center"><?php echo $lang['nc_handle'];?></th>
      </tr>
      </thead>
      <tbody>
      <?php if(!empty($output['list']) && is_array($output['list'])){ ?>
        <?php foreach($output['list'] as $key=> $val) { ?>
          <tr class="hover">
            <td><input type="checkbox" name='voucher_price_checkbox' value="<?php echo $val['log_id'];?>" class="checkitem"></td>
            <?php //var_dump($val)?>
            <td class="w70">
              <dt><?php echo $val['voucher_code'];?>
            </td>
            <td class="w70"><?php echo $val['donate_name']; ?></td>
            <td class="w70"><?php echo $val['accept_name']; ?></td>
            <td class="w80"><?php echo $val['voucher_price'];?></td>
            <td class="w60"><?php echo date("Y-m-d H:m:s",$val['add_time']);?></td>
            <td style="display: none" class="w96 align-center">
              <a href="JavaScript:void(0);" onclick="submit_delete('<?php echo $val['log_id'];?>')"><?php echo $lang['nc_del'];?></a>
            </td>
          </tr>
        <?php }?>
      <?php }else { ?>
        <tr class="no_data">
          <td colspan="16"><?php echo $lang['nc_no_record'];?></td>
        </tr>
      <?php } ?>
      </tbody>
      <?php if(!empty($output['list']) && is_array($output['list'])){ ?>
        <tfoot>
        <tr class="tfoot">
          <td><?php if(!empty($output['list']) && is_array($output['list'])){ ?>
              <input type="checkbox" class="checkall" id="checkall_1">
            <?php } ?></td>
          <td colspan="16">
            <label for="checkallBottom"><?php echo $lang['nc_select_all']; ?></label>&nbsp;&nbsp;
            <a style="display: none" href="JavaScript:void(0);" class="btn" onclick="submit_delete_batch()"><span><?php echo $lang['nc_del']?></span></a>
            <div class="pagination"> <?php echo $output['show_page'];?> </div></td>
        </tr>
        </tfoot>
      <?php } ?>
    </table>
  </form>
</div>

<script type="text/javascript">
  $(function(){
    $('#sdate').datepicker({dateFormat: 'yy-mm-dd'});
    $('#edate').datepicker({dateFormat: 'yy-mm-dd'});
  });
  function submit_delete_batch(){
    /* 获取选中的项 */
    var items = '';
    $('.checkitem:checked').each(function(){
      items += this.value + ',';
    });
    if(items != '') {
      items = items.substr(0, (items.length - 1));
      submit_delete(items);
    }
  }
  function submit_delete(log_id){
    if(confirm('<?php echo $lang['nc_ensure_del'];?>')) {
      $('#list_form').attr('method','post');
      $('#log_id').val(log_id);
      $('#list_form').submit();
    }
  }
</script>