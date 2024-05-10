<?php defined('In718Shop') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3>背景色管理</h3>
      <ul class="tab-base">
        <li><a href="JavaScript:void(0);" class="current"><span>模块列表</span></a></li>
      </ul>
    </div>
  </div>
  <div class="fixed-empty"></div>
  <form method="post" id="form_member">
    <input type="hidden" name="form_submit" value="ok" />
    <table class="table tb-type2 nobdb">
      <thead>
        <tr class="thead">
          <th class="align-center" style="width: 150px;">模块名称</th>
          <th class="align-center">模块背景色</th>
          <th class="align-center">操作</th>
        </tr>
      <tbody>
        <?php if(!empty($output['background_color_list']) && is_array($output['background_color_list'])){ ?>
          <?php foreach($output['background_color_list'] as $k => $v){ ?>
            <tr class="hover member">
              <td class="align-center">
                <?php 
                  switch ($v['type']){
                    case 1:
                      echo '首页';
                      break;
                    case 2:
                      echo '秒杀';
                      break;
                    case 3:
                      echo '折扣';
                      break;
                    default:
                      break;
                  }
                ?>
              </td>
              <td class="align-center">
                <div style="width: 50px;height: 20px;background-color: <?php echo $v['color'];?>;margin: 0px auto;"></div>
                <?php echo $v['color'];?>
              </td>

              <td class="align-center">
                <a href="javascript:void(0);" onclick="ajaxEditColor(<?php echo $v['id'];?>);">编辑</a>
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
        <?php if(!empty($output['background_color_list']) && is_array($output['background_color_list'])){ ?>
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
<script type="text/javascript">
$(function(){
  $('#ncsubmit').click(function(){
    $('#formSearch').submit();
  });	
});
function ajaxEditColor(id) {
    _uri = "<?php echo ADMIN_SITE_URL;?>/index.php?act=background_color&op=ajaxEditColor&id=" + id;
    CUR_DIALOG = ajax_form('ajaxEditColor', '编辑', _uri, 350);
}
</script>
