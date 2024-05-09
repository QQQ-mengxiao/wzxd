<?php defined('In718Shop') or exit('Access Invalid!');?>
<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3>经验值管理</h3>
      <ul class="tab-base">
      	<li><a href="JavaScript:void(0);" class="current"><span>管理</span></a></li>
          <li><a href="index.php?act=exppoints&op=index" ><span>经验值明细</span></a></li>
        <li><a href="index.php?act=exppoints&op=expsetting"><span>规则设置</span></a></li>
      </ul>
    </div>
  </div>
  <div class="fixed-empty"></div>
    <form id="exppoints_form" method="post" name="form1">
        <input type="hidden" name="form_submit" value="ok" />
        <table class="table tb-type2 nobdb">
            <tbody>
            <tr class="noborder">
                <td colspan="2" class="required"><label class="validation">会员ID：</label></td>
            </tr>
            <tr class="noborder">
                <td class="vatop rowform"><input type="text" name="member_id" id="member_id" class="txt" onchange="javascript:checkmember();">
                <td class="vatop tips"><?php echo $lang['member_index_name']?></td>
            </tr>
            <tr id="tr_memberinfo">
                <td colspan="2" style="font-weight:bold;" id="td_memberinfo"></td>
            </tr>
            <tr>
                <td colspan="2" class="required"><label>增减类型：</label></td>
            </tr>
            <tr class="noborder">
                <td class="vatop rowform"><select id="operatetype" name="operatetype">
                        <option value="1">增加</option>
                        <option value="2">减少</option>
                    </select></td>
                <td class="vatop tips"></td>
            </tr>
            <tr>
                <td colspan="2" class="required"><label class="validation">经验值：</label></td>
            </tr>
            <tr class="noborder">
                <td class="vatop rowform"><input type="text" id="exppointsnum" name="exppointsnum" class="txt"></td>
                <td class="vatop tips"><?php echo $lang['member_index_email']?></td>
            </tr>
            <tr>
                <td colspan="2" class="required"><label>描述：</label></td>
            </tr>
            <tr class="noborder">
                <td class="vatop rowform"><textarea name="pointsdesc" rows="6" class="tarea"></textarea></td>
                <td class="vatop tips">描述信息将显示在经验值明细相关页，会员和管理员都可见</td>
            </tr>
            </tbody>
            <tfoot>
            <tr class="tfoot">
                <td colspan="2" ><a href="JavaScript:void(0);" class="btn" onclick="document.form1.submit()"><span><?php echo $lang['nc_submit'];?></span></a></td>
            </tr>
            </tfoot>
        </table>
    </form>
</div>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/jquery.ui.js"></script> 
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/i18n/zh-CN.js" charset="utf-8"></script>
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/themes/ui-lightness/jquery.ui.css"  />
<script language="javascript">
    function checkmember(){
        var memberid = $.trim($("#member_id").val());
        if(memberid == ''){
            $("#member_id").val('0');
            alert('请输入会员ID');
            return false;
        }
        $.getJSON("index.php?act=exppoints&op=checkmember", {'id':memberid}, function(data){
            if (data)
            {
                $("#tr_memberinfo").show();
                var msg= "会员："+ data.name + "，当前经验值为：" + data.exppoints;
                $("#member_name").val(data.name);
                $("#member_id").val(data.id);
                $("#td_memberinfo").text(msg);
            }
            else
            {
                $("#member_name").val('');
                $("#member_id").val('0');
                alert("会员信息错误");
            }
        });
    }
    $(function(){
    $("#tr_memberinfo").hide();
    $('#points_form').validate({
        rules : {
            member_id: {
                required : true
            },
            exppointsnum   : {
                required : true,
                min : 1
            }
        },
        messages : {
            member_id : {
                required : '会员信息错误，请重新填写会员ID'
            },
            exppointsnum  : {
                required : '请添加经验值',
                min : '经验值必须大于0'
            }
        }
    });
    });
</script>
