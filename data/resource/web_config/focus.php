<?php defined('In718Shop') or exit('Access Invalid!');?>

  <ul id="fullScreenSlides" class="full-screen-slides">
          <?php if (is_array($output['code_screen_list']['code_info']) && !empty($output['code_screen_list']['code_info'])) { ?>
          <?php foreach ($output['code_screen_list']['code_info'] as $key => $val) { ?>
          <?php if (is_array($val) && $val['ap_id'] > 0) { ?>
          <li ap_id="<?php echo $val['ap_id'];?>" color="<?php echo $val['color'];?>" style="background: <?php echo $val['color'];?> url('<?php echo UPLOAD_SITE_URL.'/'.$val['pic_img'];?>') no-repeat center top">
            <a href="<?php echo $val['pic_url'];?>" target="_blank" title="<?php echo $val['pic_name'];?>">&nbsp;</a></li>
          <?php }else { ?>
          <li style="background: <?php echo $val['color'];?> url('<?php echo UPLOAD_SITE_URL.'/'.$val['pic_img'];?>') no-repeat center top">
            <a href="<?php echo $val['pic_url'];?>" target="_blank" title="<?php echo $val['pic_name'];?>">&nbsp;</a></li>
          <?php } ?>
          <?php } ?>
          <?php } ?>

  </ul>
  <div class="jfocus-trigeminybox">
    <!-- 首页第一张图片链接修改 -->
  <!-- <a class="limited_time" title="限时打折" href="http://www.banliego.com/shop/?act=search&keyword=%E7%A4%BC%E7%9B%92%E8%A3%85" target="_blank"> -->
    <a class="limited_time" title="限时打折" href="http://www.banliego.com/shop/?act=search&keyword=%E6%B4%8B%E9%85%92%E5%BF%85%E6%8A%A2
" target="_blank">
        <div class="clock-wrap">
         <!--<div class="clock">
		 href="/index.php?act=search&keyword=%E7%B2%BE%E9%80%89%E4%BD%8E%E4%BB%B7"
            <div class="clock-h" id="ClockHours" style="-webkit-transform: rotate(93deg);"></div>
            <div class="clock-m"></div>
            <div class="clock-s"></div>
          </div>-->
      </div>
    </a>
  <div class="jfocus-trigeminy"> 
    <ul>
          <?php if (is_array($output['code_focus_list']['code_info']) && !empty($output['code_focus_list']['code_info'])) { ?>
          <?php foreach ($output['code_focus_list']['code_info'] as $key => $val) { ?>
          <li>
              <?php if (is_array($val['pic_list']) && $val['pic_list'][1]['ap_id'] > 0) { ?>
              <?php foreach($val['pic_list'] as $k => $v) { ?>
            <a ap_id="<?php echo $v['ap_id'];?>" href="<?php echo $v['pic_url'];?>" target="_blank" title="<?php echo $v['pic_name'];?>">
                <img src="<?php echo UPLOAD_SITE_URL;?>/shop/common/loading.gif" rel="lazy" data-url="<?php echo UPLOAD_SITE_URL.'/'.$v['pic_img'];?>" alt="<?php echo $v['pic_name'];?>"></a>
              <?php } ?>
              <?php }else { ?>
              <?php foreach($val['pic_list'] as $k => $v) { ?>
            <a href="<?php echo $v['pic_url'];?>" target="_blank" title="<?php echo $v['pic_name'];?>">
                <img src="<?php echo UPLOAD_SITE_URL;?>/shop/common/loading.gif" rel="lazy" data-url="<?php echo UPLOAD_SITE_URL.'/'.$v['pic_img'];?>" alt="<?php echo $v['pic_name'];?>"></a>
              <?php } ?>
              <?php } ?>
          </li>
          <?php } ?>
          <?php } ?>
    </ul>
  </div>
  </div>
<script type="text/javascript">
	update_screen_focus();
</script>