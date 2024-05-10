<?php defined('In718Shop') or exit('Access Invalid!');?>

<div class="tabmenu">
  <?php include template('layout/submenu');?>
    <a href="<?php echo urlShop('dayin_setting', 'add');?>"  class="ncsc-btn ncsc-btn-green" title="添加打印机"><i class="icon-plus-sign"></i>添加打印机</a>
</div>
<table class="search-form">
  <form method="get">
    <input type="hidden" name="act" value="dayin_setting">
    <input type="hidden" name="op" value="dayin_list">
    <tr>
      <td>&nbsp;</td>
      <th style="width: 100px;">打印机编码：</th>
      <td class="w160"><input class="text" type="text" name="dayin_sn" value="<?php echo $_GET['dayin_sn'];?>"/></td>
      <th style="width: 100px;">打印机名称：</th>
      <td class="w160"><input class="text" type="text" name="dayin_name" value="<?php echo $_GET['dayin_name'];?>"/></td>
      <td class="w70 tc"><label class="submit-border"><input type="submit" class="submit" value="查询" /></label></td>
    </tr>
  </form>
</table>
<form id="form_grade" method='post' name="">
<input type="hidden" name="dayin_id" id="dayin_id" value="ok" />
<table class="ncsc-default-table">
  <thead>
    <tr>
      <th class="w10"></th>
      <th class="w150">打印机编码</th>
      <th class="w300">打印机名称</th>
      <th class="w180">注册账号名</th>
      <th class="w150">用户账号密钥(UKEY)</th>
      <th class="w150">密钥</th>
      <!-- <th class="w100">业务类型</th> -->
      <!-- <th class="w100">流量卡手机号</th> -->
      <!-- <th class="w90"><font color="red">状态</font></th> -->
     <!--  <th class="w200">备注</th> -->
      <th class="w100">操作</th>
    </tr>
  </thead>
  <tbody>
    <?php if(!empty($output['result']) && is_array($output['result'])){ ?>
      <?php foreach($output['result'] as $k => $v){ ?>
        <tr class="bd-line">
          <td></td>
        <!-- <td><input type="checkbox" name='check_id[]' value="<?php echo $v['id'];?>" class="checkitem"></td> -->
          <td class="align-center"><?php echo $v['dayin_sn'];?></td>
          <td class="align-center"><?php echo $v['dayin_name'];?></td>
          <td class="align-center"><?php echo $v['dayin_user'];?></td>
          <td class="align-center"><?php echo $v['ukey'];?></td>
          <td class="align-center"><?php echo $v['dayin_key'];?></td>
          <!-- <td class="align-center"><?php if($v['order_type']=='2'){echo '团购价';}elseif($v['order_type']=='1'){echo '阶梯价';}elseif($v['order_type']=='3'){echo '新人专享价';}elseif($v['order_type']=='4'){echo '限时秒杀';}elseif($v['order_type']=='5'){echo '即买即送';}else{echo '无活动';}?></td> -->
          <!-- <td class="align-center"><?php echo $v['mobile'];?></td> -->
          <!-- <td class="align-center"><font color="red"><?php echo $v['tax_rate'];?></font></td> -->
          <!-- <td class="align-center"><?php echo $v['note'];?></td> -->
          <td class="w100 align-center">
        <a href="index.php?act=dayin_setting&op=edit&dayin_id=<?php echo $v['dayin_id'];?> ">编辑</a> |<a href="javascript:submit_delete(<?php echo $v['dayin_id'];?>)">删除</a>
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
    <tr>
      <td colspan="20"><div class="pagination"><?php echo $output['show_page']; ?></div></td>
    </tr>
  </tfoot>
</table>
</form>
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
function submit_delete(dayin_id){
    if(confirm('您确定要删除吗?')) {
        $('#form_grade').attr('method','post');
        $('#form_grade').attr('action','index.php?act=dayin_setting&op=del');
        $('#dayin_id').val(dayin_id);
        $('#form_grade').submit();
    }
}
</script>

