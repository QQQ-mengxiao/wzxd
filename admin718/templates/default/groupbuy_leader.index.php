<?php defined('In718Shop') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3>团长管理</h3>
      <ul class="tab-base">
        <li><a href="JavaScript:void(0);" class="current"><span>团长列表</span></a></li>
      </ul>
    </div>
  </div>
  <div class="fixed-empty"></div>
  <form method="get" name="formSearch" id="formSearch">
    <input type="hidden" value="groupbuy_leader" name="act">
    <input type="hidden" value="groupbuy_leader_list" name="op">
    <table class="tb-type1 noborder search">
      <tbody>
        <tr>
          <th>&nbsp;&nbsp;&nbsp;&nbsp;团长ID&nbsp;&nbsp;&nbsp;&nbsp;</th>  
          <td><input type="text" value="<?php echo $_GET['search_groupbuy_leader_id'];?>" name="search_groupbuy_leader_id" class="txt"></td>

          <th>&nbsp;&nbsp;&nbsp;&nbsp;团长微信昵称&nbsp;&nbsp;&nbsp;&nbsp;</th>  
          <td><input type="text" value="<?php echo $_GET['search_wx_nickname'];?>" name="search_wx_nickname" class="txt"></td>

          <th>&nbsp;&nbsp;&nbsp;&nbsp;添加时间&nbsp;&nbsp;&nbsp;&nbsp;</th>
          <td>
            <input class="txt date" type="text" value="<?php echo $_GET['start_add_time'];?>" id="start_add_time" name="start_add_time">
            ~
            <input class="txt date" type="text" value="<?php echo $_GET['end_add_time'];?>" id="end_add_time" name="end_add_time"/>
          </td>

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
          <th rowspan="2" class="align-center"></th>
          <th class="align-center">团长ID</th>
          <th class="align-center">微信昵称</th>
          <th class="align-center">添加时间</th>
          <th class="align-center">自提点总数</th>
          <th class="align-center">团长助手个数</th>
          <th class="align-center">操作</th>
        </tr>
      <tbody>
        <?php if(!empty($output['groupbuy_leader_list']) && is_array($output['groupbuy_leader_list'])){ ?>
          <?php foreach($output['groupbuy_leader_list'] as $k => $v){ ?>
          <tr class="hover member">
            <!-- <td class="w24"><input type="checkbox" name='del_id[]' value="<?php echo $v['member_id']; ?>" class="checkitem"></td> -->
            <td class="w48 picture"><div class="size-44x44"><span class="thumb size-44x44"><i></i><img src="<?php if ($v['wx_avatar'] != ''){ echo UPLOAD_SITE_URL.DS.ATTACH_TZAVATAR.DS.$v['wx_avatar'];}else { echo UPLOAD_SITE_URL.'/'.ATTACH_COMMON.DS.C('default_user_portrait');}?>?<?php echo microtime();?>"  onload="javascript:DrawImage(this,44,44);"/></span></div></td>
            <td class="align-center"><p class="name"><strong><?php echo $v['groupbuy_leader_id']; ?></strong></p></td>
            <td class="align-center"><?php echo $v['wx_nickname']; ?></td>
            <td class="align-center"><?php echo date('Y-m-d H:i:s',$v['add_time']); ?></td>
            <td class="align-center"><?php echo $v['ziti_address_count']; ?></td>
            <td class="align-center"><?php echo $v['assistant_count'];?></td>

            <td class="align-center">
              <a href="index.php?act=groupbuy_leader&op=groupbuy_leader_info&groupbuy_leader_id=<?php echo $v['groupbuy_leader_id']; ?>">查看详情</a>
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
            <td colspan="10">
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
<script type="text/javascript">
$('#start_add_time').datetimepicker({dateFormat: 'yy-mm-dd',timeFormat: 'hh:mm:ss'});
$('#end_add_time').datetimepicker({dateFormat: 'yy-mm-dd',timeFormat: 'hh:mm:ss'});
$(function(){
  $('#ncsubmit').click(function(){
    $('#formSearch').submit();
  });	
});
$('a[nc_type="inline_edit"]').click(function(){
  var i_id    = $(this).attr('fieldid');
  var i_val   = ($(this).attr('fieldvalue'))== 1 ? 3 : 1;
  var i_name  = $(this).attr('fieldname');
  if(i_val == 1){
    var flag = confirm('你确定开启该自提点吗？');
  }
  if(i_val == 3){
    var flag = confirm('你确定关闭该自提点吗？');
  }
  if(!flag){
    return;
  }

  $.get('index.php?act=groupbuy_leader&op=ajaxState',{id:i_id,value:i_val},function(data){
  if(data == 1)
    {
      if(i_val == 3){
        $('a[fieldid="'+i_id+'"][fieldname="'+i_name+'"]').attr({'class':('enabled','disabled'),'title':('开启'),'fieldvalue':i_val});
      }else{
        $('a[fieldid="'+i_id+'"][fieldname="'+i_name+'"]').attr({'class':('disabled','enabled'),'title':('关闭'),'fieldvalue':i_val});
      }
    }else{
      alert(data);
    }
  });
});
</script>
