<?php defined('In718Shop') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3><?php echo '手机端页面';?></h3>
      <ul class="tab-base">
        <li><a href="JavaScript:void(0);" class="current"><span><?php echo $lang['nc_manage'];?></span></a></li>
        <!-- <li><a href="index.php?act=mb_special1&op=popup&item_id=320"><span><?php echo '弹窗';?></span></a></li>
          <li><a href="index.php?act=mb_special1&op=popup_set&item_id=320"><span><?php echo '弹窗开关';?></span></a></li> -->
<!--        <li><a href="index.php?act=mb_category&op=mb_category_add" ><span>--><?php //echo $lang['nc_new'];?><!--</span></a></li>-->
      </ul>
    </div>
  </div>
  <div class="fixed-empty"></div>
  <form method="get" name="formSearch">
    <input type="hidden" value="mb_special1" name="act">
    <input type="hidden" value="special_list" name="op">
    <table class="tb-type1 noborder search">
      <tbody>
         <tr>
          <th>页面名称</th>
          <td><input type="text" value="<?php echo $output['search_special_desc']?>" name="search_special_desc" class="txt"></td>
          <td><a href="javascript:document.formSearch.submit();" class="btn-search tooltip" title="<?php echo $lang['nc_query'];?>">&nbsp;</a>
            <?php if($output['search_special_desc'] != ''){?>
            <a href="index.php?act=mb_special1&op=special_list" class="btns tooltip" title="<?php echo $lang['nc_cancel_search'];?>"><span>撤销检索</span></a>
            <?php }?>
            <a href="JavaScript:void(0);" onclick="window.location.href='index.php?act=mb_special1&op=special_list'" class="btns tooltip" title="<?php echo $lang['tp_all']; ?>"><span>全部</span></a> 
        </tr>
      </tbody>
    </table>
  </form>
  <form method='post' id="form_link">
    <input type="hidden" name="form_submit" value="ok" />
    <table class="table tb-type2 nobdb">
      <thead>
        <tr class="thead">
          <th style="width: 65px"><?php echo '页面ID';?></th>
          <th><?php echo '页面名称';?></th>
          <th class="align-center"><?php echo $lang['nc_handle'];?></th>
        </tr>
      </thead>
      <tbody>
        <?php if(!empty($output['list']) && is_array($output['list'])){ ?>
        <?php foreach($output['list'] as $k => $v){ ?>
        <tr class="hover edit">
          <td><?php  echo $v['special_id'];?></td>
          <td><?php echo $v['special_desc'];?></td>
          <td class="w96 align-center"><a href="index.php?act=mb_special1&op=special_edit&special_id=<?php echo $v['special_id'];?>"><?php echo $lang['nc_edit'];?></a></td>
        </tr>
        <?php } ?>
        <?php }else { ?>
        <tr class="no_data">
          <td colspan="10"><?php echo $lang['nc_no_record'];?></td>
        </tr>
        <?php } ?>
         <!-- <tr style="background: none repeat scroll 0% 0% rgb(255, 255, 255);">
          <td colspan="20"><a id="btn_add_mb_special1" href="javascript:;" class="btn-add marginleft">添加页面</a></td>
        </tr> -->
      </tbody>
       <tfoot>
        <?php if(!empty($output['list']) && is_array($output['list'])){ ?>
        <tr class="tfoot" id="dataFuncs">
         <!--  <td><input type="checkbox" class="checkall" id="checkallBottom"></td> -->
          <td colspan="16" id="batchAction"><label for="checkallBottom"></label>
            &nbsp;&nbsp; <a href="JavaScript:void(0);" class="btn" onclick="if(confirm('<?php echo $lang['nc_ensure_del'];?>')){$('#form_tp').submit();}"></a>
            <div class="pagination"> <?php echo $output['page'];?> </div></td>
        </tr>
      </tfoot>
       <?php } ?>
    </table>
  </form>
</div>
<form id="del_form" action="<?php echo urlAdmin('mb_special1', 'special_del');?>" method="post">
  <input type="hidden" id="del_special_id" name="special_id">
</form>
<div id="dialog_add_mb_special1" style="display:none;">
  <form id="add_form" method="post" action="<?php echo urlAdmin('mb_special1', 'special_save');?>">
    <table class="table tb-type2">
      <tbody>
        <tr class="noborder">
          <td colspan="2" class="required"><label class="validation" for="special_desc">专题描述<?php echo $lang['nc_colon'];?></label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform"><input type="text" value="" name="special_desc" class="txt"></td>
          <td class="vatop tips">专题描述，最多20个字</td>
        </tr>
      </tbody>
      <tfoot>
        <tr>
          <td colspan="2"><a id="submit" href="javascript:void(0)" class="btn"><span><?php echo $lang['nc_submit'];?></span></a></td>
        </tr>
      </tfoot>
    </table>
  </form>
</div>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/jquery.ui.js"></script> 
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/dialog/dialog.js" id="dialog_js" charset="utf-8"></script> 
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.edit.js"></script> 
<script type="text/javascript">
    $(document).ready(function(){
        //添加专题
        $('#btn_add_mb_special1').on('click', function() {
            $('#dialog_add_mb_special1').nc_show_dialog({title: '添加专题'});
        });

        //提交
        $("#submit").click(function(){
            $("#add_form").submit();
        });

        $('#add_form').validate({
            errorPlacement: function(error, element){
                error.appendTo(element.parents('tr').prev().find('td:first'));
            },
            rules : {
                special_desc : {
                    required : true,
                    maxlength : 20
                }
            },
            messages : {
                special_desc : {
                    required : "专题描述不能为空",
                    maxlength : "专题描述最多20个字" 
                }
            }
        });

        //删除专题
        $('[nctype="btn_del"]').on('click', function() {
            if(confirm('确认删除?')) {
                $('#del_special_id').val($(this).attr('data-special-id'));
                $('#del_form').submit();
            }
        });

        //编辑专题描述
        $('span[nc_type="edit_special_desc"]').inline_edit({act: 'mb_special1',op: 'update_special_desc'});
    });
</script> 