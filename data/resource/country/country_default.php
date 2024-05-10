<!--Floor-->
    <div class="cpart <?php echo $output['style_name'];?> clearfix" id="<?php echo $web_id.'floor';?>">
        <div class="left leadpic">
        <?php if (is_array($output['code_adv']['code_info']) && !empty($output['code_adv']['code_info'])) { ?>
                  <?php foreach ($output['code_adv']['code_info'] as $key => $val) { ?>
                      <?php if (is_array($val) && !empty($val)) { ?>
                      <?php $pic_url=$val['pic_url'];?>
            <a href="<?php echo $val['pic_url'];?>" target="_blank">
                <img class="lazy" src="<?php echo UPLOAD_SITE_URL;?>/shop/common/loading.gif" rel='lazy' data-url="<?php echo UPLOAD_SITE_URL.'/'.$val['pic_img'];?>" alt="<?php echo $val['pic_name'];?>" width="270" height="594" style="display: inline;">
                <span>进入<br>国家馆&gt;&gt;</span>
            </a>
            <?php } ?>
                  <?php } ?>
                  <?php } ?>
        </div>
        <div class="right tabarea">
            <div class="tabtitle">
             <?php if (!empty($output['code_recommend_list']['code_info']) && is_array($output['code_recommend_list']['code_info'])) {
                    $i = 0;
                    ?>
                  <?php foreach ($output['code_recommend_list']['code_info'] as $key => $val) {
                    $i++;
                    ?>
                    <a href="javascript:void(0)" <?php echo $i==1 ? "class=\"current\"":'';?>><?php echo $val['recommend']['name'];?></a>
                  <?php } ?>
                  <?php } ?>
                  <a href='#' onclick='window.location.href="<?php echo $pic_url;?>";return false' target="_blank">更多</a>
            </div>
            <div class="tabcont">


                <div class="plist current">
            <?php if (!empty($output['code_recommend_list']['code_info']) && is_array($output['code_recommend_list']['code_info'])) {
                    $i = 0;
                    ?>
                  <?php foreach ($output['code_recommend_list']['code_info'] as $key => $val) {
                    $i++;
                    ?>
                          <?php if(!empty($val['goods_list']) && is_array($val['goods_list'])) { ?>                    

<ul class="clearfix" data-categoryid="1733" data-limit="8">

                          <?php foreach($val['goods_list'] as $k => $v){ ?>
            <li class="left" data-productid="23370 ">
            <a href="<?php echo urlShop('goods','index',array('goods_id'=> $v['goods_id'])); ?>" title="<?php echo $v['goods_name']; ?>" target="_blank">
                <img src="<?php echo UPLOAD_SITE_URL;?>/shop/common/loading.gif" rel='lazy' data-url="<?php echo strpos($v['goods_pic'],'http')===0 ? $v['goods_pic']:UPLOAD_SITE_URL."/".$v['goods_pic'];?>" class="thumb lazy" width="188" height="188" style="display: block;">
                <span class="pname"><?php echo $v['goods_name']; ?></span>
                                <span class="pprice">

                                            <span class="price" data-item-price="419.0000"><?php echo ncPriceFormatForList($v['goods_price']); ?></span>                    
                    <del>                                                                                        €57.45                                                    </del>
                </span>
            </a>
        </li>
        <?php } ?>

            
    </ul>

                </div>
                <div class="plist">
                    <?php }}}?>



                </div>
            </div>
        </div>
    </div>