<?php defined('In718Shop') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3>团长助手管理</h3>
      <ul class="tab-base">
        <li><a href="JavaScript:void(0);" class="current"><span>团长助手列表</span></a></li>
      </ul>
    </div>
  </div>
  <div class="fixed-empty"></div>
  <form method="get" name="formSearch" id="formSearch">
    <input type="hidden" value="groupbuy_leader" name="act">
    <input type="hidden" value="groupbuy_leader_assistant_list" name="op">
    <table class="tb-type1 noborder search">
      <tbody>

        <tr>
          <th>&nbsp;&nbsp;&nbsp;&nbsp;团长ID&nbsp;&nbsp;&nbsp;&nbsp;</th>  
          <td><input type="text" value="<?php echo $_GET['search_groupbuy_leader_id'];?>" name="search_groupbuy_leader_id" class="txt"></td>

          <th>&nbsp;&nbsp;&nbsp;&nbsp;团长微信昵称&nbsp;&nbsp;&nbsp;&nbsp;</th>  
          <td><input type="text" value="<?php echo $_GET['search_wx_nickname'];?>" name="search_wx_nickname" class="txt"></td>

          <th>&nbsp;&nbsp;&nbsp;&nbsp;助手账号&nbsp;&nbsp;&nbsp;&nbsp;</th>  
          <td><input type="text" value="<?php echo $_GET['search_username'];?>" name="search_username" class="txt"></td>

          <th>&nbsp;&nbsp;&nbsp;&nbsp;助手姓名&nbsp;&nbsp;&nbsp;&nbsp;</th>  
          <td><input type="text" value="<?php echo $_GET['search_name'];?>" name="search_name" class="txt"></td>
        </tr>

        <tr>
          <th>&nbsp;&nbsp;&nbsp;&nbsp;助手电话&nbsp;&nbsp;&nbsp;&nbsp;</th>  
          <td><input type="text" value="<?php echo $_GET['search_phone_number'];?>" name="search_phone_number" class="txt"></td>

          <th>&nbsp;&nbsp;&nbsp;&nbsp;添加时间&nbsp;&nbsp;&nbsp;&nbsp;</th>
          <td colspan="3">
            <input class="txt date" type="text" value="<?php echo $_GET['start_add_time'];?>" id="start_add_time" name="start_add_time">
            ~
            <input class="txt date" type="text" value="<?php echo $_GET['end_add_time'];?>" id="end_add_time" name="end_add_time"/>
          </td>
		  
		      <th>&nbsp;&nbsp;&nbsp;&nbsp;状态&nbsp;&nbsp;&nbsp;&nbsp;</th>  
          <td>
            <select name="state">
              <option <?php if($_GET['state']==''){?>selected='selected'<?php }?> value="">全部</option>
              <option <?php if($_GET['state']=='0'){?>selected='selected'<?php }?> value="0">禁用</option>
              <option <?php if($_GET['state']=='1'){?>selected='selected'<?php }?> value="1">启用</option>
              <option <?php if($_GET['state']=='2'){?>selected='selected'<?php }?> value="2">已删除</option>
            </select>
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
          <!-- <th rowspan="2" class="align-center"></th> -->
          <th class="align-center">团长助手ID</th>
          <th class="align-center">助手账号</th>
          <th class="align-center">姓名</th>
          <th class="align-center">电话</th>
          <th class="align-center">所属团长</th>
          <th class="align-center">添加时间</th>
          <th class="align-center">备注</th>
          <th class="align-center">状态</th>
        </tr>
      <tbody>
        <?php if(!empty($output['assistant_list']) && is_array($output['assistant_list'])){ ?>
        <?php foreach($output['assistant_list'] as $k => $v){ ?>
        <tr class="hover member">
          <!-- <td class="w24"><input type="checkbox" name='del_id[]' value="<?php echo $v['address_id']; ?>" class="checkitem"></td> -->
          <td class="align-center"><p class="name"><strong><?php echo $v['gl_assistant_id']; ?></strong></p></td>
          <td class="align-center"><?php echo $v['username']; ?></td>
          <td class="align-center"><?php echo $v['name']; ?></td>
          <td class="align-center"><?php echo $v['phone_number']; ?></td>
          <td class="align-center">ID:<?php echo $v['groupbuy_leader_id'];?><br><?php echo $v['wx_nickname'];?></td>
          <td class="align-center"><?php echo date('Y-m-d H:i:s',$v['add_time']); ?></td>
          <td class="w96 align-center"><?php echo $v['remark'];?></td>
          <td class="w96 align-center">
            <?php switch($v['state']){
              case 0:
                echo '禁用';
                break;
              case 1:
                echo '启用';
                break;
              case 2:
                echo '已删除';
                break;
              default:
                break;
            }?>
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
        <?php if(!empty($output['assistant_list']) && is_array($output['assistant_list'])){ ?>
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
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.nyroModal/custom.min.js" charset="utf-8"></script>
<link href="<?php echo RESOURCE_SITE_URL;?>/js/jquery.nyroModal/styles/nyroModal.css" rel="stylesheet" type="text/css" id="cssfile2" />
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
$(function(){
  $('.nyroModal').nyroModal();
	regionInit("region");
});
</script>
