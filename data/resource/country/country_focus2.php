<?php defined('In718Shop') or exit('Access Invalid!');?>


<?php if (is_array($output['code_screen_list']['code_info']) && !empty($output['code_screen_list']['code_info'])) { ?>
<?php foreach ($output['code_screen_list']['code_info'] as $key => $val) { ?>
<?php //if (is_array($val) && $val['ap_id'] > 0) { ?>
<div class="map">
        <img src="<?php echo UPLOAD_SITE_URL.'/'.$val['pic_img'];?>" alt="" width="1190" height="537" usemap="#Map">
        <map name="Map">
            <area shape="rect" coords="989,362,1107,446" href="http://www.zosc.com">
            <area shape="rect" coords="897,282,1017,376" href="http://www.zosc.com">
            <area shape="rect" coords="907,139,1013,229" href="http://www.zosc.com">
            <area shape="rect" coords="840,40,956,133" href="http://www.zosc.com">
            <area shape="rect" coords="532,277,652,375" href="http://www.zosc.com">
            <area shape="rect" coords="513,98,616,180" href="http://www.zosc.com">
            <area shape="rect" coords="442,14,558,93" href="http://www.zosc.com">
        </map>
    </div>
    <?php } ?>
    <?php } ?>
    <?php //} ?>

<script type="text/javascript">
  //update_screen_focus();
</script>