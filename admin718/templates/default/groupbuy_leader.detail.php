<?php defined('In718Shop') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3>团长管理</h3>
      <ul class="tab-base">
        <li><a href="index.php?act=groupbuy_leader&op=groupbuy_leader_list"><span>团长列表</span></a></li>
        <li><a href="JavaScript:void(0);" class="current"><span>团长详情</span></a></li>
      </ul>
    </div>
  </div>

  <table class="table tb-type2 order">
    <tbody>
      <tr>
        <td>
          <ul>
            <li style="width:20%">
              <strong>团长ID:</strong><?php echo $output['groupbuy_leader_info']['groupbuy_leader_id'];?>
            </li>
            <li style="width:20%">
              <strong>微信昵称:</strong><?php echo $output['groupbuy_leader_info']['wx_nickname'];?>
            </li>
            <li style="width:20%">
              <strong>添加时间:</strong><?php echo date('Y-m-d H:i:s',$output['groupbuy_leader_info']['add_time']);?>
            </li>
            <li style="width:20%">
              <strong style="vertical-align: top;">身份证正面照片:</strong>
              <a href="<?php echo $output['groupbuy_leader_info']['id_photo_front'];?>" class="nyroModal" rel="gal">
                <img style="max-width:50px;max-height:50px" class="show_image" src="<?php echo $output['groupbuy_leader_info']['id_photo_front'];?>">
              </a>
            </li>
            <li style="width:20%">
              <strong style="vertical-align: top;">身份证反面照片:</strong>
              <a href="<?php echo $output['groupbuy_leader_info']['id_photo_back'];?>" class="nyroModal" rel="gal">
                <img style="max-width:50px;max-height:50px" class="show_image" src="<?php echo $output['groupbuy_leader_info']['id_photo_back'];?>">
              </a>
            </li>
          </ul>
        </td>
      </tr>

      <tr>
        <td><table class="table tb-type2 ziti_address ">
            <tbody>
              <tr>
                <th>自提点列表</th>
              </tr>
              <tr>
                <th class="align-center">自提点ID</th>
                <th class="align-center">自提点名称</th>
                <th class="align-center">自提点照片</th>
                <th class="align-center">自提点地址</th>
                <th class="align-center">电话</th>
                <th class="align-center">营业时间</th>
                <th class="align-center">是否有营业执照</th>
                <th class="align-center">申请时间</th>
                <th class="align-center">状态</th>
              </tr>
              <?php foreach($output['groupbuy_leader_ziti_address_list'] as $ziti_address){?>
                <tr>
                  <td class="w20 align-center"><?php echo $ziti_address['address_id'];?></td>
                  <td class="w120 align-center"><?php echo $ziti_address['seller_name'];?></td>
                  <td class="w60 align-center">
                    <a href="<?php echo UPLOAD_SITE_URL . '/' . DIR_UPLOAD_ZITI . '/' . $output['groupbuy_leader_info']['groupbuy_leader_id'] . '/' . $ziti_address['ziti_photo'];?>" class="nyroModal" rel="gal">
                    <img style="max-width:50px;max-height:50px" class="show_image" src="<?php echo UPLOAD_SITE_URL . '/' . DIR_UPLOAD_ZITI . '/' . $output['groupbuy_leader_info']['groupbuy_leader_id'] . '/' . $ziti_address['ziti_photo'];?>">
                    </a>
                  </td>
                  <td class="w100 align-center"><?php echo $ziti_address['area_info'];?><br><?php echo $ziti_address['address'];?></td>
                  <td class="w70 align-center"><?php echo $ziti_address['phone_num'];?></td>
                  <td class="w98 align-center"><?php echo date('Y-m-d H:i:s',$ziti_address['open_time_start']).'~'.date('Y-m-d H:i:s',$ziti_address['open_time_end']);?></td>
                  <td class="w30 align-center"><?php echo $ziti_address['have_license']?'有':'无';?></td>
                  <td class="w100 align-center"><?php echo date('Y-m-d H:i:s',$ziti_address['add_time']);?></td>
                  <td class="w50 align-center">
                    <?php switch ($ziti_address['state']) {
                    case 0:
                      ?>
                      <a href="index.php?act=groupbuy_leader&op=ziti_address_review_info&address_id=<?php echo $ziti_address['address_id']; ?>">待审核</a>
                      <?php
                      break;
                    case 1:
                      echo '营业中';
                      break;
                    case 2:
                      echo '歇业';
                      break;
                    case 3:
                      echo '关闭';
                      break;
                    case 4:
                      echo '审核失败';
                      break;
                    default:
                      break;
                    }?>
                  </td>
                </tr>
              <?php }?>
            </tbody>
          </table>
        </td>
      </tr>
      
      <tr>
        <td><table class="table tb-type2 assistant ">
            <tbody>
              <tr>
                <th>团长助手列表</th>
              </tr>
              <tr>
                <th class="align-center">团长助手ID</th>
                <th class="align-center">登录名</th>
                <th class="align-center">姓名</th>
                <th class="align-center">电话</th>
                <th class="align-center">添加时间</th>
                <th class="align-center">备注</th>
                <th class="align-center">状态</th>
              </tr>
              <?php foreach($output['groupbuy_leader_assistant_list'] as $assistant){?>
                <tr>
                  <td class="w96 align-center"><?php echo $assistant['gl_assistant_id'];?></td>
                  <td class="w96 align-center"><?php echo $assistant['username'];?></td>
                  <td class="w96 align-center"><?php echo $assistant['name'];?></td>
                  <td class="w96 align-center"><?php echo $assistant['phone_number'];?></td>
                  <td class="w96 align-center"><?php echo date('Y-m-d H:i:s',$assistant['add_time']);?></td>
                  <td class="w96 align-center"><?php echo $assistant['remark'];?></td>
                  <td class="w96 align-center">
                    <?php switch($assistant['state']){
                      case 0:
                        echo '禁用';
                        break;
                      case 1:
                        echo '启用';
                        break;
                      default:
                        break;
                    }?>
                  </td>
                </tr>
              <?php }?>
            </tbody>
          </table>
        </td>
      </tr>

    </tbody>
    
    <tfoot>
      <tr class="tfoot">
        <td><a href="JavaScript:void(0);" class="btn" onclick="history.go(-1)"><span><?php echo $lang['nc_back'];?></span></a></td>
      </tr>
    </tfoot>
  </table>
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
$(function(){
  $('.nyroModal').nyroModal();
	regionInit("region");
});
</script>