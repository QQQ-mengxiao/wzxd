<?php defined('In718Shop') or exit('Access Invalid!');?>

<div class="page">
  <form method="post" name="form1" id="form1" action="<?php echo urlAdmin('groupbuy_leader', 'ajaxEditZitiAddress');?>">
    <input type="hidden" name="form_submit" value="ok" />
    <input type="hidden" value="<?php echo $output["address_id"];?>" name="address_id">
    <input type="hidden" value="<?php echo $output["groupbuy_leader_id"];?>" name="ori_groupbuy_leader_id">
    <table class="table tb-type2 nobdb">
      <tbody>
        <tr class="noborder">
          <td class="required" style="width: 30%;">
            <label>当前团长:</label>
          </td>
          <td class="required">
            <label>
              ID:<?php echo $output["groupbuy_leader_id"]?><br>
              微信昵称:<?php echo $output["wx_nickname"]?>
            </label>
          </td>
        </tr>
        
        <tr class="noborder">
          <td class="required" style="width: 30%;"><label>编辑团长:</label></td>
          <td class="vatop rowform">
            <select style="width: 90%;" name="groupbuy_leader_id" id="menu1" class="select1" onchange="getMenuByajax()">
              <option value="0">解绑</option>
              <?php foreach($output['groupbuy_leader_list'] as $key=>$value){ ?>
                <option value="<?php echo $value['groupbuy_leader_id']; ?>"><?php echo $value['wx_nickname'].'(ID:'.$value['groupbuy_leader_id'].')'; ?></option>
              <?php } ?>
            </select>
          </td>
        </tr>
      </tbody>
      <tfoot>
        <tr class="tfoot">
          <td colspan="2"><a href="javascript:void(0);" class="btn" nctype="btn_submit1" onclick="ajaxpost('form1', '', '', 'onerror');"><span><?php echo $lang['nc_submit'];?></span></a></td>
        </tr>
      </tfoot>
    </table>
  </form>
</div>
<style type="text/css">
  .page {
	padding-top: 0px;
	margin-top: 0px;
  }
  .select2-dropdown{
    z-index: 9999;
  }
</style>

<script type="text/javascript">
  //页面加载完成后初始化select2控件
  $(function () {
      $("#menu1").select2();
  });
</script>