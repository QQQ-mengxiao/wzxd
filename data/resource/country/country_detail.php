<?php if(!empty($output['code_banner']['code_info']['pic'])) { ?>
  <?php if(!$output['code_banner']['code_info']['show']==0){?>

      <div>
    <img src="<?php  echo UPLOAD_SITE_URL.'/'.$output['code_banner']['code_info']['pic'];?>"  float="left" alt="<?php echo $output['code_banner']['code_info']['title']; ?>" style="vertical-align:center;border:0;width:100%;height:469px">
      
    </div>
  <?php } ?>
  <?php } ?>

<div class="wraper">
<div class="list-box" style="margin-top:10px;">

<?php if (!empty($output['code_recommend_list']['code_info']) && is_array($output['code_recommend_list']['code_info'])) {
                    $j = 0;
                    $num = count($output['code_recommend_list']['code_info']);
                    ?>
<?php foreach ($output['code_recommend_list']['code_info'] as $key => $val) {
                      $i = 0;
                      $j++;
                      
                    ?>
      <div class="list-tit-box3" id="TabZone<?php echo $j;?>">
        <div class="list-tit-box3-back wra">
            <ul class="list-tit list-tit-4 cls" style="height: 50px;">
<?php foreach ($output['code_recommend_list']['code_info'] as $k1 => $v1) {

                    $i++;
                    ?>
              <li <?php echo $i==$j ? "class=\"on\"":'';?> style="width:<?php echo round(100/($num*1.1),2).'%';?>;">
                <a href="#TabZone<?php echo $i;?>">
              
                  <span><?php echo $v1['recommend']['name'];?></span>
                </a>
              </li>
              <?php }?>
            </ul>
            </div>
      

                  
      <div class="wra list-item-box blue">
      <ul class="list-item-4 cls">
                

                <?php if(!empty($val['goods_list']) && is_array($val['goods_list'])) { ?>
                <?php foreach($val['goods_list'] as $k => $v){ ?>
                <li>
                  <div class="item-intr">
                    <div class="item-img">
                      <a href="<?php echo urlShop('goods','index',array('goods_id'=> $v['goods_id'])); ?>"  target="_blank">
                              <img src="<?php echo strpos($v['goods_pic'],'http')===0 ? $v['goods_pic']:UPLOAD_SITE_URL."/".$v['goods_pic'];?>" style="width:241px; height:241px;">
                          </a>
                    </div>
                    <div class="item-tit-box">
                    <p class="item-tit" title="<?php echo $v['goods_name']; ?>"><?php echo $v['goods_name']; ?></p>
                    </div>
                    <div class="item-pri">
                      
                      <span class="pri"><?php echo ncPriceFormatForList($v['goods_price']); ?></span>
                      <a href="<?php echo urlShop('goods','index',array('goods_id'=> $v['goods_id'])); ?>" target="_blank">
                        <i class="shopping-cart1 act-icon"></i>
                      </a>
                    </div>
                  </div>
                </li>
                <?php }?>
                <?php }?>
      </ul>
    </div>
    </div>
<?php }?>
<?php }?>
   </div>
 </div>