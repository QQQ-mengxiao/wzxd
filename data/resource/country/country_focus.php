<?php defined('In718Shop') or exit('Access Invalid!');?>
<?php if (is_array($output['code_screen_list']['code_info']) && !empty($output['code_screen_list']['code_info'])) { ?>
<?php $x=0;?>
<?php foreach ($output['code_screen_list']['code_info'] as $key => $val) { ?>
<?php if ($x==0) {?>

<div class="ncp-category" >
<div  class="ncp-ad">
<img src="<?php echo UPLOAD_SITE_URL.'/'.$val['pic_img'];?>" >
</div>
    <div class="ncp-brand">
    <?php } ?>
    <?php $x++;} ?>
    <?php } ?>

<ul>
<?php if (is_array($output['code_focus_list']['code_info']) && !empty($output['code_focus_list']['code_info'])) { ?>
          <?php foreach ($output['code_focus_list']['code_info'] as $key => $val) { ?>
              <?php foreach($val['pic_list'] as $k => $v) { ?>
            <li><a href="<?php echo $v['pic_url'];?>" target="_blank" title="<?php echo $v['pic_name'];?>">
                <img src="<?php echo UPLOAD_SITE_URL.'/'.$v['pic_img'];?>" alt="<?php echo $v['pic_name'];?>"></a></li>
              <?php } ?>
              <?php } ?>
          <?php } ?>
</ul>

    </div>
  </div>