<?php defined('In718Shop') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3>通知栏管理</h3>
      <ul class="tab-base">
        <li><a href="JavaScript:void(0);" class="current"><span><?php echo $lang['nc_manage'];?></span></a></li>
        <li><a href="index.php?act=noticeboard&op=noticeboard_add" ><span><?php echo $lang['nc_new'];?></span></a></li>
      </ul>
    </div>
  </div>
  <form method='post' id="form_no">
    <input type="hidden" name="form_submit" value="ok" />
    <table class="table tb-type2">
      <thead>
        <tr class="space">
          <th colspan="15"><?php echo $lang['noticeboard_index_no'];?><?php echo $lang['nc_list'];?></th>
        </tr>
        <tr class="thead">
          <th>&nbsp;</th>
          <th class="align-center">标题</th>
          <th class="align-center">通知内容</th>
          <th class="align-center">跳转链接</th>
          <th class="align-center">是否展示</th>
          <th class="align-center"><?php echo $lang['nc_handle'];?></th>
        </tr>
      </thead>
      <tbody>
        <?php if(!empty($output['noticeboard_list']) && is_array($output['noticeboard_list'])){ ?>
        <?php foreach($output['noticeboard_list'] as $k => $v){ ?>
        <tr class="hover">
          <td class="w24"><input type="checkbox" name="del_id[]" value="<?php echo $v['no_id'];?>" class="checkitem"></td>
          <td class="w150 align-center"><?php echo $v['no_title'];?></td>
          <td class="w150 align-center"><?php echo $v['no_content'];?></td>
          <td class="w150 align-center"><?php echo $v['no_url'];?></td>
          <td class="w150 align-center"><?php echo $v['is_open'];?></td>
          <td class="w72 align-center"><a href="index.php?act=noticeboard&op=noticeboard_edit&no_id=<?php echo $v['no_id'];?>"><?php echo $lang['nc_edit'];?></a> | <a href="javascript:if(confirm('<?php echo $lang['nc_ensure_del'];?>'))window.location = 'index.php?act=noticeboard&op=noticeboard_del&no_id=<?php echo $v['no_id'];?>';"><?php echo $lang['nc_del'];?></a></td>
        </tr>
        <?php } ?>
        <?php }else { ?>
        <tr class="no_data">
          <td colspan="15"><?php echo $lang['nc_no_record'];?></td>
        </tr>
        <?php } ?>
      </tbody>
      <tfoot>
        <?php if(!empty($output['noticeboard_list']) && is_array($output['noticeboard_list'])){ ?>
        <tr class="tfoot">
          <td><input type="checkbox" class="checkall" id="checkallBottom"></td>
          <td colspan="16"><label for="checkallBottom"><?php echo $lang['nc_select_all']; ?></label>
            &nbsp;&nbsp;<a href="JavaScript:void(0);" class="btn" onclick="if(confirm('<?php echo $lang['nc_ensure_del'];?>')){$('#form_no').submit();}"><span><?php echo $lang['nc_del'];?></span></a>
            <div class="pagination"> <?php echo $output['page'];?> </div></td>
        </tr>
        <?php } ?>
      </tfoot>
    </table>
  </form>
</div>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.edit.js" charset="utf-8"></script>