<?php defined('In718Shop') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3>歇业审核</h3>
      <ul class="tab-base">
        <li><a href="JavaScript:void(0);" class="current"><span>待审核列表</span></a></li>
      </ul>
    </div>
  </div>
  <div class="fixed-empty"></div>
  <form method="get" name="formSearch" id="formSearch">
    <input type="hidden" value="groupbuy_leader" name="act">
    <input type="hidden" value="ziti_address_break_review" name="op">
    <table class="tb-type1 noborder search">
      <tbody>
        <tr>
          <th>&nbsp;&nbsp;&nbsp;&nbsp;自提点名称&nbsp;&nbsp;&nbsp;&nbsp;</th>  
          <td><input type="text" value="<?php echo $_GET['search_seller_name'];?>" name="search_seller_name" class="txt"></td>

          <th>&nbsp;&nbsp;&nbsp;&nbsp;申请时间&nbsp;&nbsp;&nbsp;&nbsp;</th>
          <td>
            <input class="txt date" type="text" value="<?php echo $_GET['start_add_time'];?>" id="start_add_time" name="start_add_time">
            ~
            <input class="txt date" type="text" value="<?php echo $_GET['end_add_time'];?>" id="end_add_time" name="end_add_time"/>
          </td>
		  
		  <th>&nbsp;&nbsp;&nbsp;&nbsp;审核状态&nbsp;&nbsp;&nbsp;&nbsp;</th>  
          <td>
            <select name="xie_state">
              <option <?php if($_GET['xie_state']==''){?>selected='selected'<?php }?> value="">全部</option>
              <option <?php if($_GET['xie_state']=='3'){?>selected='selected'<?php }?> value="3">待审核</option>
              <option <?php if($_GET['xie_state']=='2'){?>selected='selected'<?php }?> value="2">审核失败</option>
            </select>
          </td>
          
          <!-- <th>&nbsp;&nbsp;&nbsp;&nbsp;营业执照&nbsp;&nbsp;&nbsp;&nbsp;</th>  
          <td>
            <select name="have_license">
              <option <?php if($_GET['have_license']==''){?>selected='selected'<?php }?> value="">全部</option>
              <option <?php if($_GET['have_license']=='0'){?>selected='selected'<?php }?> value="0">无</option>
              <option <?php if($_GET['have_license']=='1'){?>selected='selected'<?php }?> value="1">有</option>
            </select>
          </td> -->

          <td>
            &nbsp;&nbsp;&nbsp;&nbsp;<a href="javascript:void(0);" id="ncsubmit" class="btn-search"></a>&nbsp;&nbsp;&nbsp;&nbsp;
          </td>

        </tr>
      </tbody>
    </table>
  </form>
  </table> 
  <form method="post" id="form_member">
    <input type="hidden" name="form_submit" value="ok" />
    <table class="table tb-type2 nobdb">
      <thead>
        <tr class="thead">
          <!-- <th>&nbsp;</th> -->
          <!-- <th rowspan="2" class="align-center"></th> -->
          <th class="align-center">自提点ID</th>
          <th class="align-center">自提点名称</th>
          <th class="align-center">自提点地址</th>
          <th class="align-center">团长</th>
          <th class="align-center">电话</th>
          <th class="align-center">自提点申请时间</th>
          <th class="align-center">歇业时间</th>
          <th class="align-center">歇业原因</th>
          <th class="align-center">审核状态</th>
          <th class="align-center">操作</th>
        </tr>
      <tbody>
        <?php if(!empty($output['groupbuy_leader_list']) && is_array($output['groupbuy_leader_list'])){ ?>
        <?php foreach($output['groupbuy_leader_list'] as $k => $v){ ?>
        <tr class="hover member">
          <!-- <td class="w24"><input type="checkbox" name='del_id[]' value="<?php echo $v['address_id']; ?>" class="checkitem"></td> -->
          <!-- <td class="w48 picture"><div class="size-44x44"><span class="thumb size-44x44"><i></i><img src="<?php if ($v['wx_avatar'] != ''){ echo UPLOAD_SITE_URL.DS.ATTACH_TZAVATAR.DS.$v['wx_avatar'];}else { echo UPLOAD_SITE_URL.'/'.ATTACH_COMMON.DS.C('default_user_portrait');}?>?<?php echo microtime();?>"  onload="javascript:DrawImage(this,44,44);"/></span></div></td> -->
          <td class="align-center"><p class="name"><strong><?php echo $v['address_id']; ?></strong></p></td>
          <td class="align-center"><?php echo $v['seller_name']; ?></td>
          <td class="align-center"><?php echo $v['area_info']; ?><br><?php echo $v['address']; ?></td>
          <td class="align-center">团长ID:<?php echo $v['groupbuy_leader_id']; ?><br><?php echo $v['wx_nickname'];?></td>
          <td class="align-center"><?php echo $v['phone_num']; ?></td>
          <td class="align-center"><?php echo date('Y-m-d H:i:s',$v['add_time']); ?></td>
          <td class="align-center"><?php echo date('Y-m-d H:i:s',$v['xie_time_start']); ?><br>~<br><?php echo date('Y-m-d H:i:s',$v['xie_time_end']); ?></td>
          <td class="align-center"><?php echo $v['content']; ?></td>
          <td class="align-center"><?php echo $v['xie_state']==3?'待审核':'审核失败'; ?></td>

          <td class="align-center">
            <!-- <a href="" id="agree">同意</a> -->
            <a href="javascript:void(0);" onclick="break_review(<?php echo $v['address_id'];?>);">审核</a>
          </td>
        
        </tr>
        <?php } ?>
        <?php }else { ?>
        <tr class="no_data">
          <td colspan="11"><?php echo $lang['nc_no_record']?></td>
        </tr>
        <?php } ?>
      </tbody>
      <tfoot class="tfoot">
        <?php if(!empty($output['groupbuy_leader_list']) && is_array($output['groupbuy_leader_list'])){ ?>
          <tr>
            <td colspan="16">
              <div class="pagination"> <?php echo $output['page'];?></div>
            </td>
          </tr>
        <?php } ?>
      </tfoot>
    </table>
  </form>
</div>
<link  type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/time-add/jquery-ui-1.8.17.custom.css" rel="stylesheet" />
<link  type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/time-add/jquery-ui-timepicker-addon.css" rel="stylesheet" />
<script charset="utf-8" type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/time-add/jquery-ui-1.8.17.custom.min.js"></script>
<script charset="utf-8" type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/time-add/jquery-ui-timepicker-addon.js"></script>
<script charset="utf-8" type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/time-add/jquery-ui-timepicker-zh-CN.js"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/dialog/dialog.js" id="dialog_js" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/jquery.ui.js"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.mousewheel.js"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/common_select.js" charset="utf-8"></script>
<script type="text/javascript">
$('#start_add_time').datetimepicker({dateFormat: 'yy-mm-dd',timeFormat: 'hh:mm:ss'});
$('#end_add_time').datetimepicker({dateFormat: 'yy-mm-dd',timeFormat: 'hh:mm:ss'});
//自提点审核
function break_review(address_id) {
    _uri = "<?php echo ADMIN_SITE_URL;?>/index.php?act=groupbuy_leader&op=ajaxBreakState&address_id=" + address_id;
    CUR_DIALOG = ajax_form('ajaxBreakState', '审核', _uri, 350);
}
$(function(){
  $('#ncsubmit').click(function(){
    $('#formSearch').submit();
  });	
});

</script>
