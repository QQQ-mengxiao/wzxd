<?php defined('In718Shop') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3>设置2</h3>
      <ul class="tab-base">
        <li><a href="JavaScript:void(0);" class="current"><span>管理</span></a></li>
        <li><a href="index.php?act=kuajing_payment&op=add" ><span><?php echo $lang['nc_new'];?></span></a></li>
      </ul>
    </div>
  </div>
  <div class="fixed-empty"></div>
  
  <form method="post" name="formSearch">
    <table class="tb-type1 noborder search">
      <tbody>
        <tr>
          <th><label for="like_sg_name"><?php echo $lang['store_grade_name'];?>名称</label></th>
          <td><input type="text" value="<?php echo $output['like_sg_name'];?>" name="like_sg_name" id="like_sg_name" class="txt"></td>
          <td><a href="javascript:document.formSearch.submit();" class="btn-search " title="<?php echo $lang['nc_query']; ?>">&nbsp;</a>
            <?php if($output['like_sg_name'] != ''){?>
            <a class="btns " href="index.php?act=kuajing_payment&op=index" title="<?php echo $lang['cancel_search'];?>"><span>取消检索<?php echo $lang['cancel_search'];?></span></a>
            <?php }?></td>
        </tr>
      </tbody>
    </table>
  </form>

  <form id="form_grade" method='post' name="">
    <input type="hidden" name="payment_id" id="payment_id" value="ok" />
    <table class="table tb-type2">
      <thead>
        <tr class="thead">
        <th class="w24">&nbsp;</th>
          <th class="align-center">序号</th>
          <th class="align-center">支付企业名称</th>
          <th class="align-center">海关代码</th>
          <th class="align-center">国检代码</th>
          <th class="align-center">操作</th>
        </tr>
      </thead>
      <tbody>
        <?php if(!empty($output['result']) && is_array($output['result'])){ ?>
        
        <?php $no = 0;?>
        <?php foreach($output['result'] as $k => $v){ ?>
        <?php $no++;?>
        <tr class="hover">
        <td><input type="checkbox" name='check_id[]' value="<?php echo $v['payment_id'];?>" class="checkitem"></td>
          <td class="align-center"><?php echo $no;?></td>
          <td class="align-center"><?php echo $v['payment_name'];?></td>
          <td class="align-center"><?php echo $v['code_guan'];?></td>
          <td class="align-center"><?php echo $v['code_jian'];?></td>
          <td class="w270 align-center">
		    <a href="index.php?act=kuajing_payment&op=edit&payment_id=<?php echo $v['payment_id'];?> "><?php echo $lang['nc_edit'];?></a> |<a href="javascript:submit_delete(<?php echo $v['payment_id'];?>)">
           <?php echo $lang['nc_del'];?></a>
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
            &nbsp;&nbsp;<a href="JavaScript:void(0);" class="btn"  onclick="submit_delete_batch()"; ><span><?php echo $lang['nc_del'];?></span></a><div class="pagination"> <?php echo $output['page'];?> </div></td><!--gai-->
        </tr>
      </tfoot>
    </table>
  </form>
</div>
<script type="text/javascript">

//xinzeng
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
    else {
        alert('<?php echo $lang['nc_please_select_item'];?>');
    }
}
function submit_delete(payment_id){
    if(confirm('<?php echo $lang['nc_ensure_del'];?>')) {
        $('#form_grade').attr('method','post');
        $('#form_grade').attr('action','index.php?act=kuajing_payment&op=del');
        $('#payment_id').val(payment_id);
        $('#form_grade').submit();
    }
}
</script>
