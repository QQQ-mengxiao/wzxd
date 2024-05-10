<?php defined('In718Shop') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3>团长管理</h3>
      <ul class="tab-base">
        <li><a href="index.php?act=groupbuy_leader&op=groupbuy_leader_list" ><span>管理</span></a></li>
        <li><a href="JavaScript:void(0);" class="current"><span>自提点详情</span></a></li>
      </ul>
    </div>
  </div>
  <div class="fixed-empty"></div>
  <form id="groupbuy_leader_form" enctype="multipart/form-data" method="post">
    <input type="hidden" name="form_submit" value="ok" />
    <input type="hidden" name="address_id" value="<?php echo $output['ziti_address_info']['address_id'];?>" />
    <input type="hidden" name="groupbuy_leader_id" value="<?php echo $output['ziti_address_info']['groupbuy_leader_id'];?>" />
    <input type="hidden" name="ziti_area_info" value="<?php echo $output['ziti_address_info']['area_info'];?>" />
    <table class="table tb-type2">
      <tbody>
        <tr>
          <td class="required"><label for="seller_name">自提点名称:</label></td>
          <td class="vatop rowform" style="width: 90%;"><input type="text" id="seller_name" name="seller_name" class="txt" value="<?php echo $output['ziti_address_info']['seller_name'];?>"></td>
        </tr>
        
        <tr>
          <td class="required"><label for="area_info">自提点地址:</label></td>
          <td id="ziti_area_info"><?php echo $output['ziti_address_info']['area_info'];?>
            <input type="button" value="编辑" style="background-color: #F5F5F5; width: 60px; height: 32px; border: solid 1px #E7E7E7; cursor: pointer" class="edit_region" />
          </td>
          <td mx="class" class="vatop rowform" style="display: none;">
            <span id="region" class="w400">
              <input type="hidden" value="" name="province_id" id="province_id">
              <input type="hidden" value="" name="city_id" id="city_id">
              <input type="hidden" value="" name="area_id" id="area_id" class="area_ids" />
              <input type="hidden" value="" name="area_info" id="area_info" class="area_names" />
              <select mx='region' style="display:none;">
              </select>
            </span>
          </td>
        </tr>

        <tr>
          <td class="required"><label for="address">街道地址:</label></td>
          <td class="vatop rowform"><input type="text" id="address" name="address" class="txt" value="<?php echo $output['ziti_address_info']['address'];?>"></td>
        </tr>

        <tr>
          <td class="required"><label for="phone_num">手机号:</label></td>
          <td class="vatop rowform"><input type="text" id="phone_num" name="phone_num" class="txt" value="<?php echo $output['ziti_address_info']['phone_num'];?>"></td>
        </tr>

        <tr>
          <td class="required">营业时间:</td>
          <td class="vatop rowform">
            <input type="text" id="open_time_start" name="open_time_start" class="txt" style="width: 50px;" value="<?php echo $output['ziti_address_info']['open_time_start'];?>">~
            <input type="text" id="open_time_end" name="open_time_end" class="txt" style="width: 50px;" value="<?php echo $output['ziti_address_info']['open_time_end'];?>">
          </td>
        </tr>

        <tr>
          <td class="required">自提点状态:</td>
          <td class="vatop rowform">
            <li>
              <input type="radio" <?php if($output['ziti_address_info']['state'] == 0){ ?>checked="checked"<?php } ?> value="0" name="state" id="state0">
              <label for="state0">待审核</label>
            </li>
            <li>
              <input type="radio" <?php if($output['ziti_address_info']['state'] == 1){ ?>checked="checked"<?php } ?> value="1" name="state" id="state1">
              <label for="state1">正常营业</label>
            </li>
            <li>
              <input type="radio" <?php if($output['ziti_address_info']['state'] == 2){ ?>checked="checked"<?php } ?> value="2" name="state" id="state2">
              <label for="state2">歇业</label>
            </li>
            <li>
              <input type="radio" <?php if($output['ziti_address_info']['state'] == 3){ ?>checked="checked"<?php } ?> value="3" name="state" id="state3">
              <label for="state3">关闭</label>
            </li>
          </td>
        </tr>

        <?php if($output['ziti_address_info']['state'] == 2){ ?>
          <tr>
            <td class="required">歇业时间:</td>
            <td class="vatop rowform">
              <strong><?php echo date('Y-m-d H:i:s',$output['ziti_address_info']['xie_time_start']);?></strong>
              &nbsp;&nbsp;&nbsp;~&nbsp;&nbsp;&nbsp;
              <strong><?php echo date('Y-m-d H:i:s',$output['ziti_address_info']['xie_time_end']);?></strong>
            </td>
          </tr>
        <?php }?>

        <tr>
          <td class="required">申请时间:</td>
          <td class="vatop rowform">
            <strong><?php echo date('Y-m-d H:i:s',$output['ziti_address_info']['add_time']);?></strong>
          </td>
        </tr>

        <tr>
          <td class="required">营业执照:</td>
          <td class="vatop rowform">
            <?php echo $output['ziti_address_info']['have_license']?'有':'无'?>
          </td>
        </tr>

        <tr>
          <td class="required">自提点照片:</td>
          <td class="vatop rowform">
            <a href="<?php echo UPLOAD_SITE_URL.'/'.DIR_UPLOAD_ZITI.'/'.$output['ziti_address_info']['groupbuy_leader_id'].'/'.$output['ziti_address_info']['ziti_photo'];?>" class="nyroModal" rel="gal">
            <img style="max-width:200px;max-height:200px" class="show_image" src="<?php echo UPLOAD_SITE_URL.'/'.DIR_UPLOAD_ZITI.'/'.$output['ziti_address_info']['groupbuy_leader_id'].'/'.$output['ziti_address_info']['ziti_photo'];?>"></a>
          </td>
        </tr>

        <tr>
          <td class="required">身份证正面照片:</td>
          <td class="vatop rowform">            
            <a href="<?php echo UPLOAD_SITE_URL.'/'.DIR_UPLOAD_GLID_FRONT.'/'.$output['ziti_address_info']['groupbuy_leader_id'].'/'.$output['ziti_address_info']['id_photo_front'];?>" class="nyroModal" rel="gal">
            <img style="max-width:200px;max-height:200px" class="show_image" src="<?php echo UPLOAD_SITE_URL.'/'.DIR_UPLOAD_GLID_FRONT.'/'.$output['ziti_address_info']['groupbuy_leader_id'].'/'.$output['ziti_address_info']['id_photo_front'];?>"></a>
          </td>
        </tr>

        <tr>
          <td class="required">身份证反面照片:</td>
          <td class="vatop rowform">
            <a href="<?php echo UPLOAD_SITE_URL.'/'.DIR_UPLOAD_GLID_BACK.'/'.$output['ziti_address_info']['groupbuy_leader_id'].'/'.$output['ziti_address_info']['id_photo_back'];?>" class="nyroModal" rel="gal">
            <img style="max-width:200px;max-height:200px" class="show_image" src="<?php echo UPLOAD_SITE_URL.'/'.DIR_UPLOAD_GLID_BACK.'/'.$output['ziti_address_info']['groupbuy_leader_id'].'/'.$output['ziti_address_info']['id_photo_back'];?>"></a>
          </td>
        </tr>

      <tfoot>
        <tr class="tfoot">
          <td colspan="15"><a href="JavaScript:void(0);" class="btn" id="submitBtn"><span><?php echo $lang['nc_submit'];?></span></a></td>
        </tr>
      </tfoot>
    </table>
  </form>
</div>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/dialog/dialog.js" id="dialog_js" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.nyroModal/custom.min.js" charset="utf-8"></script>
<link href="<?php echo RESOURCE_SITE_URL;?>/js/jquery.nyroModal/styles/nyroModal.css" rel="stylesheet" type="text/css" id="cssfile2" />
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/jquery.ui.js"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/ajaxfileupload/ajaxfileupload.js"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.Jcrop/jquery.Jcrop.js"></script>
<link href="<?php echo RESOURCE_SITE_URL;?>/js/jquery.Jcrop/jquery.Jcrop.min.css" rel="stylesheet" type="text/css" id="cssfile2" />
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/common_select.js" charset="utf-8"></script> 
<script type="text/javascript">
$('input[class="edit_region"]').click(function(){
  $('#ziti_area_info').css('display','none');
  $('td[mx="class"]').css('display','inherit');
  $('select[mx="region"]').css('display','inherit');
});
//裁剪图片后返回接收函数
function call_back(picname){
	$('#member_avatar').val(picname);
	$('#view_img').attr('src','<?php echo UPLOAD_SITE_URL.'/'.ATTACH_AVATAR;?>/'+picname+'?'+Math.random());
}
$("#submitBtn").click(function(){
  $("#groupbuy_leader_form").submit();
});
$(function(){
  $('.nyroModal').nyroModal();
	regionInit("region");
});
</script> 
