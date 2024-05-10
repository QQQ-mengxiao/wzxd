<?php defined('In718Shop') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3><?php echo $lang['nc_web_index'];?></h3>
      <ul class="tab-base">
        <li><a href="JavaScript:void(0);" class="current"><span><?php echo '国家馆';?></span></a></li>
        <li><a href="index.php?act=country_api&op=focus_edit"><span><?php echo '顶部图片';?></span></a></li>
        <li><a href="index.php?act=country_api&op=focus2_edit"><span><?php echo '底部图片';?></span></a></li>
      </ul>
    </div>
  </div>
  <div class="fixed-empty"></div>
  <table class="table tb-type2" id="prompt">
    <tbody>
      <tr class="space odd">
        <th colspan="12"><div class="title"><h5><?php echo $lang['nc_prompts'];?></h5><span class="arrow"></span></div></th>
      </tr>
      <tr>
        <td>
        <ul>
            <li><?php echo $lang['web_config_index_help1'];?></li>
            <li><?php echo $lang['web_config_index_help2'];?></li>
            <li><?php echo $lang['web_config_index_help3'];?></li>
          </ul></td>
      </tr>
    </tbody>
  </table>
    <table class="table tb-type2 nobdb">
      <thead>
        <tr class="thead">
          <th><?php echo $lang['nc_sort'];?></th>
          <th>国家馆名称</th>
          <th><?php echo $lang['web_config_style_name'];?></th>
          <th class="align-center"><?php echo $lang['web_config_update_time'];?></th>
          <th class="align-center"><?php echo $lang['nc_display'];?></th>
          <th class="align-center"><?php echo $lang['nc_handle'];?></th>
        </tr>
      </thead>
      <tbody>
        <?php if(!empty($output['web_list']) && is_array($output['web_list'])){ ?>
        <?php foreach($output['web_list'] as $k => $v){ ?>
        <tr class="hover">
          <td class="w48 sort"><?php echo $v['web_sort'];?></td>
          <td><?php echo $v['web_name'];?></td>
          <td><?php echo $output['style_array'][$v['style_name']];?></td>
          <td class="w150 align-center"><?php echo date('Y-m-d H:i:s',$v['update_time']);?></td>
          <td class="w150 align-center"><?php echo $v['web_show']==1 ? $lang['nc_yes'] : $lang['nc_no'];?></td>
          <td class="w150 align-center">
            <a href="index.php?act=country&op=web_edit&web_id=<?php echo $v['web_id'];?>">设置</a> | 
          	<a href="index.php?act=country&op=code_edit&web_id=<?php echo $v['web_id'];?>">编辑</a> |
            <a href="index.php?act=country&op=country_detail_edit&web_id=<?php echo $v['web_id'];?>">详情页</a>
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
        <?php if(!empty($output['web_list']) && is_array($output['web_list'])){ ?>
        <tr class="tfoot">
          <td colspan="16">
            <div class="pagination"> <?php echo $output['page'];?> </div></td>
        </tr>
        <?php } ?>
      </tfoot>
    </table>
</div>