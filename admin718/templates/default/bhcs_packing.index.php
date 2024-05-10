<?php defined('In718Shop') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3>设置</h3>
      <ul class="tab-base">
        <li><a href="JavaScript:void(0);" class="current"><span><?php echo $lang['manage'];?></span></a></li>
        <li><a href="index.php?act=store_grade&op=store_grade_add" ><span><?php echo $lang['nc_new'];?></span></a></li>
      </ul>
    </div>
  </div>
  <div class="fixed-empty"></div>
  <!--
  <form method="post" name="formSearch">
    <table class="tb-type1 noborder search">
      <tbody>
        <tr>
          <th><label for="like_sg_name"><?php echo $lang['store_grade_name'];?></label></th>
          <td><input type="text" value="<?php echo $output['like_sg_name'];?>" name="like_sg_name" id="like_sg_name" class="txt"></td>
          <td><a href="javascript:document.formSearch.submit();" class="btn-search " title="<?php echo $lang['nc_query']; ?>">&nbsp;</a>
            <?php if($output['like_sg_name'] != ''){?>
            <a class="btns " href="index.php?act=store_grade&op=store_grade" title="<?php echo $lang['cancel_search'];?>"><span><?php echo $lang['cancel_search'];?></span></a>
            <?php }?></td>
        </tr>
      </tbody>
    </table>
  </form>
  -->
  <form id="form_grade" method='post' name="">
    <input type="hidden" name="form_submit" value="ok" />
    <table class="table tb-type2">
      <thead>
        <tr class="thead">
        <th class="w24">&nbsp;</th>
          <th class="align-center">包装名称</th>
          <th class="align-center">包装种类（检）</th>
          <th class="align-center">包装种类（关）</th>
          <th class="align-center">操作</th>
        </tr>
      </thead>
      <tbody>
        <?php if(!empty($output['grade_list']) && is_array($output['grade_list'])){ ?>
        
        <?php foreach($output['grade_list'] as $k => $v){ ?>
        <tr class="hover">
        <td><input type="checkbox" name='check_sg_id[]' value="<?php echo $v['sg_id'];?>" class="checkitem"></td>
          <td class="align-center"><?php echo $v['package_name'];?></td>
          <td class="align-center"><?php echo $v['packagetypeciq'];?></td>
          <td class="align-center"><?php echo $v['packagetypecus'];?></td>
          <td class="w270 align-center"><a href="index.php?act=store_grade&op=store_grade_edit&sg_id=<?php echo $v['sg_id'];?>"><?php echo $lang['nc_edit'];?></a> |

            <a href="javascript:if(confirm('<?php echo $lang['problem_del'];?>'))window.location = 'index.php?act=store_grade&op=store_grade_del&sg_id=<?php echo $v['sg_id'];?>';"><?php echo $lang['nc_del'];?></a>
          </td>
        </tr>
        <?php } ?>
        <?php }else { ?>
        <tr class="no_data">
          <td colspan="10"><?php echo $lang['nc_no_record'];?></td>
        </tr>
        <?php } ?>
      </tbody>
      <tfoot>
        <tr class="tfoot">
          <td><input type="checkbox" class="checkall" id="checkallBottom"></td>
          <td colspan="15"><label for="checkallBottom"><?php echo $lang['nc_select_all']; ?></label>
            &nbsp;&nbsp;<a href="JavaScript:void(0);" class="btn" onclick="if(confirm('<?php echo $lang['problem_del'];?>')){$('#form_grade').submit();}"><span><?php echo $lang['nc_del'];?></span></a></td>
        </tr>
      </tfoot>
    </table>
  </form>
</div>
