<?php defined('In718Shop') or exit('Access Invalid!');?>
<script src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.ajaxContent.pack.js"></script>
<script src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/i18n/zh-CN.js"></script>
<script src="<?php echo RESOURCE_SITE_URL;?>/js/common_select.js"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/fileupload/jquery.iframe-transport.js" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/fileupload/jquery.ui.widget.js" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/fileupload/jquery.fileupload.js" charset="utf-8"></script>
<script src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.poshytip.min.js"></script>
<script src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.mousewheel.js"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.charCount.js"></script>
<!--[if lt IE 8]>
  <script src="<?php echo RESOURCE_SITE_URL;?>/js/json2.js"></script>
<![endif]-->
<script src="<?php echo SHOP_RESOURCE_SITE_URL;?>/js/store_goods_add.step2.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/themes/ui-lightness/jquery.ui.css"  />
<style type="text/css">
#fixedNavBar { filter:progid:DXImageTransform.Microsoft.gradient(enabled='true',startColorstr='#CCFFFFFF', endColorstr='#CCFFFFFF');background:rgba(255,255,255,0.8); width: 90px; margin-left: 510px; border-radius: 4px; position: fixed; z-index: 999; top: 172px; left: 50%;}
#fixedNavBar h3 { font-size: 12px; line-height: 24px; text-align: center; margin-top: 4px;}
#fixedNavBar ul { width: 80px; margin: 0 auto 5px auto;}
#fixedNavBar li { margin-top: 5px;}
#fixedNavBar li a { font-family: Arial, Helvetica, sans-serif; font-size: 12px; line-height: 20px; background-color: #F5F5F5; color: #999; text-align: center; display: block;  height: 20px; border-radius: 10px;}
#fixedNavBar li a:hover { color: #FFF; text-decoration: none; background-color: #27a9e3;}
</style>

<div id="fixedNavBar">
<h3>页面导航</h3>
  <ul>
    <li><a id="demo1Btn" href="#demo1" class="demoBtn">电商企业信息</a></li>
    <li><a id="demo2Btn" href="#demo2" class="demoBtn">发货人信息</a></li>
    <li><a id="demo3Btn" href="#demo3" class="demoBtn">代理企业信息</a></li>
  </ul>
</div>

<div class="item-publish">
  <form method="post" id="goods_form" action="<?php echo urlShop('store_kuajing', 'D_canshu_save');?>">
    <input type="hidden" name="form_submit" value="ok" />
    <input type="hidden" name="commonid" value="<?php echo $output['goods']['goods_commonid'];?>" />
    <input type="hidden" name="type_id" value="<?php echo $output['goods_class']['type_id'];?>" />
    <input type="hidden" name="ref_url" value="<?php echo $_GET['ref_url'] ? $_GET['ref_url'] : getReferer();?>" />
    <div class="ncsc-form-goods">
      <h3 id="demo1">电商企业信息</h3>

<!--      <dl>-->
<!--        <dt><i class="required">*</i>电商企业编号</dt>-->
<!--        <dd>-->
<!--          <input name="bh_companycode" type="text" class="text w400" value="--><?php //echo $output['companycode']; ?><!--" />-->
<!--          <span></span>-->
<!--          <p class="hint"></p>-->
<!--        </dd>-->
<!--      </dl>-->
<!---->
<!--      <dl>-->
<!--        <dt><i class="required">*</i>电商企业key</dt>-->
<!--        <dd>-->
<!--          <input name="bh_emallyikey" type="text" class="text w400" value="--><?php //echo $output['emallyikey']; ?><!--" />-->
<!--          <span></span>-->
<!--          <p class="hint"></p>-->
<!--        </dd>-->
<!--      </dl>-->

      <dl>
        <dt><i class="required">*</i>电商代码（检）</dt>
        <dd>
          <input name="bh_eccode" type="text" class="text w400" value="<?php echo $output['eccode']; ?>" />
          <span></span>
          <p class="hint"></p>
        </dd>
      </dl>

      <dl>
        <dt><i class="required">*</i>电商代码（关）</dt>
        <dd>
          <input name="bh_cbecode" type="text" class="text w400" value="<?php echo $output['cbecode']; ?>" />
          <span></span>
          <p class="hint"></p>
        </dd>
      </dl>

      <dl>
        <dt><i class="required">*</i>电商名称（检）</dt>
        <dd>
          <input name="bh_ecname" type="text" class="text w400" value="<?php echo $output['ecname']; ?>" />
          <span></span>
          <p class="hint"></p>
        </dd>
      </dl>

      <dl>
        <dt><i class="required">*</i>电商名称（关）</dt>
        <dd>
          <input name="bh_cbename" type="text" class="text w400" value="<?php echo $output['cbename']; ?>" />
          <span></span>
          <p class="hint"></p>
        </dd>
      </dl>

<!--      <h3 id="demo2">发货人信息</h3>-->
<!--      <dl>-->
<!--        <dt><i class="required">*</i>发货人名称</dt>-->
<!--        <dd>-->
<!--          <input name="bh_shipper" type="text" class="text w400" value="--><?php //echo $output['shipper']; ?><!--" />-->
<!--          <span></span>-->
<!--          <p class="hint"></p>-->
<!--        </dd>-->
<!--      </dl>      -->
<!--      -->
<!--      <dl>-->
<!--        <dt><i class="required">*</i>发货人地址</dt>-->
<!--        <dd>-->
<!--          <input name="bh_shipperaddress" type="text" class="text w400" value="--><?php //echo $output['shipperaddress']; ?><!--" />-->
<!--          <span></span>-->
<!--          <p class="hint"></p>-->
<!--        </dd>-->
<!--      </dl>   -->
<!--      -->
<!--      <dl>-->
<!--        <dt><i class="required">*</i>发货人电话</dt>-->
<!--        <dd>-->
<!--          <input name="bh_shippertelephone" type="text" class="text w400" value="--><?php //echo $output['shippertelephone']; ?><!--" />-->
<!--          <span></span>-->
<!--          <p class="hint"></p>-->
<!--        </dd>-->
<!--      </dl>-->
<!---->
<!--      <dl>-->
<!--        <dt><i class="required">*</i>发货人所在国（检）</dt>-->
<!--        <dd>-->
<!--          <input name="bh_shippercountryciq" type="text" class="text w400" value="--><?php //echo $output['shippercountryciq']; ?><!--" />-->
<!--          <span></span>-->
<!--          <p class="hint"></p>-->
<!--        </dd>-->
<!--      </dl>-->
<!---->
<!--      <dl>-->
<!--        <dt><i class="required">*</i>发货人所在国（关）</dt>-->
<!--        <dd>-->
<!--          <input name="bh_shippercountrycus" type="text" class="text w400" value="--><?php //echo $output['shippercountrycus']; ?><!--" />-->
<!--          <span></span>-->
<!--          <p class="hint"></p>-->
<!--        </dd>-->
<!--      </dl>    -->
<!---->
<!---->
<!--      <h3 id="demo3">代理企业信息</h3>-->
<!--      <dl>-->
<!--        <dt><i class="required">*</i>代理企业代码（检）</dt>-->
<!--        <dd>-->
<!--          <input name="bh_agentcodeciq" type="text" class="text w400" value="--><?php //echo $output['agentcodeciq']; ?><!--" />-->
<!--          <span></span>-->
<!--          <p class="hint"></p>-->
<!--        </dd>-->
<!--      </dl>  -->
<!---->
<!---->
<!--      <dl>-->
<!--        <dt><i class="required">*</i>代理企业代码（关）</dt>-->
<!--        <dd>-->
<!--          <input name="bh_agentcodecus" type="text" class="text w400" value="--><?php //echo $output['agentcodecus']; ?><!--" />-->
<!--          <span></span>-->
<!--          <p class="hint"></p>-->
<!--        </dd>-->
<!--      </dl>-->
<!---->
<!---->
<!--      <dl>-->
<!--        <dt><i class="required">*</i>代理企业名称（检）</dt>-->
<!--        <dd>-->
<!--          <input name="bh_agentnameciq" type="text" class="text w400" value="--><?php //echo $output['agentnameciq']; ?><!--" />-->
<!--          <span></span>-->
<!--          <p class="hint"></p>-->
<!--        </dd>-->
<!--      </dl>  -->
<!---->
<!--      <dl>-->
<!--        <dt><i class="required">*</i>代理企业名称（关）</dt>-->
<!--        <dd>-->
<!--          <input name="bh_agentnamecus" type="text" class="text w400" value="--><?php //echo $output['agentnamecus']; ?><!--" />-->
<!--          <span></span>-->
<!--          <p class="hint"></p>-->
<!--        </dd>-->
<!--      </dl>  -->


    </div>
    <div class="bottom tc hr32">
      <label class="submit-border">
        <input type="submit" class="submit" value="提交" />
      </label>
    </div>
  </form>
</div>
<script type="text/javascript">
var SITEURL = "<?php echo SHOP_SITE_URL; ?>";
var DEFAULT_GOODS_IMAGE = "<?php echo thumb(array(), 60);?>";
var SHOP_RESOURCE_SITE_URL = "<?php echo SHOP_RESOURCE_SITE_URL;?>";

$(function(){
	//电脑端手机端tab切换
	$(".tabs").tabs();
	
    $.validator.addMethod('checkPrice', function(value,element){
    	_g_price = parseFloat($('input[name="g_price"]').val());
        _g_marketprice = parseFloat($('input[name="g_marketprice"]').val());
        if (_g_price > _g_marketprice) {
            return false;
        }else {
            return true;
        }
    }, '<i class="icon-exclamation-sign"></i>商品价格不能高于市场价格');
	jQuery.validator.addMethod("checkFCodePrefix", function(value, element) {       
		return this.optional(element) || /^[a-zA-Z]+$/.test(value);       
	},'<i class="icon-exclamation-sign"></i>请填写不多于5位的英文字母');  
    $('#goods_form').validate({
        errorPlacement: function(error, element){
            $(element).nextAll('span').append(error);
        },
        <?php if ($output['edit_goods_sign']) {?>
        submitHandler:function(form){
            ajaxpost('goods_form', '', '', 'onerror');
        },
        <?php }?>
        rules : {
            g_name : {
                required    : true,
                minlength   : 3,
                maxlength   : 50
            },
            g_jingle : {
                maxlength   : 140
            },
            g_price : {
                required    : true,
                number      : true,
                min         : 0.01,
                max         : 9999999,
                checkPrice  : true
            },
            g_marketprice : {
                required    : true,
                number      : true,
                min         : 0.01,
                max         : 9999999
            },
            g_costprice : {
                number      : true,
                min         : 0.00,
                max         : 9999999
            },
            g_storage  : {
                required    : true,
                digits      : true,
                min         : 0,
                max         : 999999999
            },
            image_path : {
                required    : true
            },
            g_vindate : {
                required    : function() {if ($("#is_gv_1").prop("checked")) {return true;} else {return false;}}
            },
			g_vlimit : {
				required	: function() {if ($("#is_gv_1").prop("checked")) {return true;} else {return false;}},
				range		: [1,10]
			},
			g_fccount : {
				<?php if (!$output['edit_goods_sign']) {?>required	: function() {if ($("#is_fc_1").prop("checked")) {return true;} else {return false;}},<?php }?>
				range		: [1,100]
			},
			g_fcprefix : {
				<?php if (!$output['edit_goods_sign']) {?>required	: function() {if ($("#is_fc_1").prop("checked")) {return true;} else {return false;}},<?php }?>
				checkFCodePrefix : true,
				rangelength	: [3,5]
			},
			g_saledate : {
				required	: function () {if ($('#is_appoint_1').prop("checked")) {return true;} else {return false;}}
			},
			g_deliverdate : {
				required	: function () {if ($('#is_presell_1').prop("checked")) {return true;} else {return false;}}
			}
        },
        messages : {
            g_name  : {
                required    : '<i class="icon-exclamation-sign"></i><?php echo $lang['store_goods_index_goods_name_null'];?>',
                minlength   : '<i class="icon-exclamation-sign"></i><?php echo $lang['store_goods_index_goods_name_help'];?>',
                maxlength   : '<i class="icon-exclamation-sign"></i><?php echo $lang['store_goods_index_goods_name_help'];?>'
            },
            g_jingle : {
                maxlength   : '<i class="icon-exclamation-sign"></i>商品卖点不能超过140个字符'
            },
            g_price : {
                required    : '<i class="icon-exclamation-sign"></i><?php echo $lang['store_goods_index_store_price_null'];?>',
                number      : '<i class="icon-exclamation-sign"></i><?php echo $lang['store_goods_index_store_price_error'];?>',
                min         : '<i class="icon-exclamation-sign"></i><?php echo $lang['store_goods_index_store_price_interval'];?>',
                max         : '<i class="icon-exclamation-sign"></i><?php echo $lang['store_goods_index_store_price_interval'];?>'
            },
            g_marketprice : {
                required    : '<i class="icon-exclamation-sign"></i>请填写市场价',
                number      : '<i class="icon-exclamation-sign"></i>请填写正确的价格',
                min         : '<i class="icon-exclamation-sign"></i>请填写0.01~9999999之间的数字',
                max         : '<i class="icon-exclamation-sign"></i>请填写0.01~9999999之间的数字'
            },
            g_costprice : {
                number      : '<i class="icon-exclamation-sign"></i>请填写正确的价格',
                min         : '<i class="icon-exclamation-sign"></i>请填写0.00~9999999之间的数字',
                max         : '<i class="icon-exclamation-sign"></i>请填写0.00~9999999之间的数字'
            },
            g_storage : {
                required    : '<i class="icon-exclamation-sign"></i><?php echo $lang['store_goods_index_goods_stock_null'];?>',
                digits      : '<i class="icon-exclamation-sign"></i><?php echo $lang['store_goods_index_goods_stock_error'];?>',
                min         : '<i class="icon-exclamation-sign"></i><?php echo $lang['store_goods_index_goods_stock_checking'];?>',
                max         : '<i class="icon-exclamation-sign"></i><?php echo $lang['store_goods_index_goods_stock_checking'];?>'
            },
            image_path : {
                required    : '<i class="icon-exclamation-sign"></i>请设置商品主图'
            },
            g_vindate : {
                required    : '<i class="icon-exclamation-sign"></i>请选择有效期'
            },
			g_vlimit : {
				required	: '<i class="icon-exclamation-sign"></i>请填写1~10之间的数字',
				range		: '<i class="icon-exclamation-sign"></i>请填写1~10之间的数字'
			},
			g_fccount : {
				required	: '<i class="icon-exclamation-sign"></i>请填写1~100之间的数字',
				range		: '<i class="icon-exclamation-sign"></i>请填写1~100之间的数字'
			},
			g_fcprefix : {
				required	: '<i class="icon-exclamation-sign"></i>请填写3~5位的英文字母',
				rangelength	: '<i class="icon-exclamation-sign"></i>请填写3~5位的英文字母'
			},
			g_saledate : {
				required	: '<i class="icon-exclamation-sign"></i>请选择有效期'
			},
			g_deliverdate : {
				required	: '<i class="icon-exclamation-sign"></i>请选择有效期'
			}
        }
    });
    <?php if (isset($output['goods'])) {?>
	setTimeout("setArea(<?php echo $output['goods']['areaid_1'];?>, <?php echo $output['goods']['areaid_2'];?>)", 1000);
	<?php }?>
});
// 按规格存储规格值数据
var spec_group_checked = [<?php for ($i=0; $i<$output['sign_i']; $i++){if($i+1 == $output['sign_i']){echo "''";}else{echo "'',";}}?>];
var str = '';
var V = new Array();

<?php for ($i=0; $i<$output['sign_i']; $i++){?>
var spec_group_checked_<?php echo $i;?> = new Array();
<?php }?>

$(function(){
	$('dl[nctype="spec_group_dl"]').on('click', 'span[nctype="input_checkbox"] > input[type="checkbox"]',function(){
		into_array();
		goods_stock_set();
	});

	// 提交后不没有填写的价格或库存的库存配置设为默认价格和0
	// 库存配置隐藏式 里面的input加上disable属性
	$('input[type="submit"]').click(function(){
		$('input[data_type="price"]').each(function(){
			if($(this).val() == ''){
				$(this).val($('input[name="g_price"]').val());
			}
		});
		$('input[data_type="stock"]').each(function(){
			if($(this).val() == ''){
				$(this).val('0');
			}
		});
		$('input[data_type="alarm"]').each(function(){
			if($(this).val() == ''){
				$(this).val('0');
			}
		});
		if($('dl[nc_type="spec_dl"]').css('display') == 'none'){
			$('dl[nc_type="spec_dl"]').find('input').attr('disabled','disabled');
		}
	});
	
});

// 将选中的规格放入数组
function into_array(){
<?php for ($i=0; $i<$output['sign_i']; $i++){?>
		
		spec_group_checked_<?php echo $i;?> = new Array();
		$('dl[nc_type="spec_group_dl_<?php echo $i;?>"]').find('input[type="checkbox"]:checked').each(function(){
			i = $(this).attr('nc_type');
			v = $(this).val();
			c = null;
			if ($(this).parents('dl:first').attr('spec_img') == 't') {
				c = 1;
			}
			spec_group_checked_<?php echo $i;?>[spec_group_checked_<?php echo $i;?>.length] = [v,i,c];
		});

		spec_group_checked[<?php echo $i;?>] = spec_group_checked_<?php echo $i;?>;

<?php }?>
}

// 生成库存配置
function goods_stock_set(){
    //  店铺价格 商品库存改为只读
    $('input[name="g_price"]').attr('readonly','readonly').css('background','#E7E7E7 none');
    $('input[name="g_storage"]').attr('readonly','readonly').css('background','#E7E7E7 none');

    $('dl[nc_type="spec_dl"]').show();
    str = '<tr>';
    <?php recursionSpec(0,$output['sign_i']);?>
    if(str == '<tr>'){
        //  店铺价格 商品库存取消只读
        $('input[name="g_price"]').removeAttr('readonly').css('background','');
        $('input[name="g_storage"]').removeAttr('readonly').css('background','');
        $('dl[nc_type="spec_dl"]').hide();
    }else{
        $('tbody[nc_type="spec_table"]').empty().html(str)
            .find('input[nc_type]').each(function(){
                s = $(this).attr('nc_type');
                try{$(this).val(V[s]);}catch(ex){$(this).val('');};
                if ($(this).attr('data_type') == 'marketprice' && $(this).val() == '') {
                    $(this).val($('input[name="g_marketprice"]').val());
                }
                if ($(this).attr('data_type') == 'price' && $(this).val() == ''){
                    $(this).val($('input[name="g_price"]').val());
                }
                if ($(this).attr('data_type') == 'stock' && $(this).val() == ''){
                    $(this).val('0');
                }
                if ($(this).attr('data_type') == 'alarm' && $(this).val() == ''){
                    $(this).val('0');
                }
            }).end()
            .find('input[data_type="stock"]').change(function(){
                computeStock();    // 库存计算
            }).end()
            .find('input[data_type="price"]').change(function(){
                computePrice();     // 价格计算
            }).end()
            .find('input[nc_type]').change(function(){
                s = $(this).attr('nc_type');
                V[s] = $(this).val();
            });
    }
}

<?php 
/**
 * 
 * 
 *  生成需要的js循环。递归调用	PHP
 * 
 *  形式参考 （ 2个规格）
 *  $('input[type="checkbox"]').click(function(){
 *      str = '';
 *      for (var i=0; i<spec_group_checked[0].length; i++ ){
 *      td_1 = spec_group_checked[0][i];
 *          for (var j=0; j<spec_group_checked[1].length; j++){
 *              td_2 = spec_group_checked[1][j];
 *              str += '<tr><td>'+td_1[0]+'</td><td>'+td_2[0]+'</td><td><input type="text" /></td><td><input type="text" /></td><td><input type="text" /></td>';
 *          }
 *      }
 *      $('table[class="spec_table"] > tbody').empty().html(str);
 *  });
 */
function recursionSpec($len,$sign) {
    if($len < $sign){
        echo "for (var i_".$len."=0; i_".$len."<spec_group_checked[".$len."].length; i_".$len."++){td_".(intval($len)+1)." = spec_group_checked[".$len."][i_".$len."];\n";
        $len++;
        recursionSpec($len,$sign);
    }else{
        echo "var tmp_spec_td = new Array();\n";
        for($i=0; $i< $len; $i++){
            echo "tmp_spec_td[".($i)."] = td_".($i+1)."[1];\n";
        }
        echo "tmp_spec_td.sort(function(a,b){return a-b});\n";
        echo "var spec_bunch = 'i_';\n";
        for($i=0; $i< $len; $i++){
            echo "spec_bunch += tmp_spec_td[".($i)."];\n";
        }
        echo "str += '<input type=\"hidden\" name=\"spec['+spec_bunch+'][goods_id]\" nc_type=\"'+spec_bunch+'|id\" value=\"\" />';";
        for($i=0; $i< $len; $i++){
            echo "if (td_".($i+1)."[2] != null) { str += '<input type=\"hidden\" name=\"spec['+spec_bunch+'][color]\" value=\"'+td_".($i+1)."[1]+'\" />';}";
            echo "str +='<td><input type=\"hidden\" name=\"spec['+spec_bunch+'][sp_value]['+td_".($i+1)."[1]+']\" value=\"'+td_".($i+1)."[0]+'\" />'+td_".($i+1)."[0]+'</td>';\n";
        }
        echo "str +='<td><input class=\"text price\" type=\"text\" name=\"spec['+spec_bunch+'][marketprice]\" data_type=\"marketprice\" nc_type=\"'+spec_bunch+'|marketprice\" value=\"\" /><em class=\"add-on\"><i class=\"icon-renminbi\"></i></em></td><td><input class=\"text price\" type=\"text\" name=\"spec['+spec_bunch+'][price]\" data_type=\"price\" nc_type=\"'+spec_bunch+'|price\" value=\"\" /><em class=\"add-on\"><i class=\"icon-renminbi\"></i></em></td><td><input class=\"text stock\" type=\"text\" name=\"spec['+spec_bunch+'][stock]\" data_type=\"stock\" nc_type=\"'+spec_bunch+'|stock\" value=\"\" /></td><td><input class=\"text stock\" type=\"text\" name=\"spec['+spec_bunch+'][alarm]\" data_type=\"alarm\" nc_type=\"'+spec_bunch+'|alarm\" value=\"\" /></td><td><input class=\"text sku\" type=\"text\" name=\"spec['+spec_bunch+'][sku]\" nc_type=\"'+spec_bunch+'|sku\" value=\"\" /></td></tr>';\n";
        for($i=0; $i< $len; $i++){
            echo "}\n";
        }
    }
}

?>


<?php if (!empty($output['goods']) && $_GET['class_id'] <= 0 && !empty($output['sp_value']) && !empty($output['spec_checked']) && !empty($output['spec_list'])){?>
//  编辑商品时处理JS
$(function(){
	var E_SP = new Array();
	var E_SPV = new Array();
	<?php
	$string = '';
	foreach ($output['spec_checked'] as $v) {
		$string .= "E_SP[".$v['id']."] = '".$v['name']."';";
	}
	echo $string;
	echo "\n";
	$string = '';
	foreach ($output['sp_value'] as $k=>$v) {
		$string .= "E_SPV['{$k}'] = '{$v}';";
	}
	echo $string;
	?>
	V = E_SPV;
	$('dl[nc_type="spec_dl"]').show();
	$('dl[nctype="spec_group_dl"]').find('input[type="checkbox"]').each(function(){
		//  店铺价格 商品库存改为只读
		$('input[name="g_price"]').attr('readonly','readonly').css('background','#E7E7E7 none');
		$('input[name="g_storage"]').attr('readonly','readonly').css('background','#E7E7E7 none');
		s = $(this).attr('nc_type');
		if (!(typeof(E_SP[s]) == 'undefined')){
			$(this).attr('checked',true);
			v = $(this).parents('li').find('span[nctype="pv_name"]');
			if(E_SP[s] != ''){
				$(this).val(E_SP[s]);
				v.html('<input type="text" maxlength="20" value="'+E_SP[s]+'" />');
			}else{
				v.html('<input type="text" maxlength="20" value="'+v.html()+'" />');
			}
			change_img_name($(this));			// 修改相关的颜色名称
		}
	});

    into_array();	// 将选中的规格放入数组
    str = '<tr>';
    <?php recursionSpec(0,$output['sign_i']);?>
    if(str == '<tr>'){
        $('dl[nc_type="spec_dl"]').hide();
        $('input[name="g_price"]').removeAttr('readonly').css('background','');
        $('input[name="g_storage"]').removeAttr('readonly').css('background','');
    }else{
        $('tbody[nc_type="spec_table"]').empty().html(str)
            .find('input[nc_type]').each(function(){
                s = $(this).attr('nc_type');
                try{$(this).val(E_SPV[s]);}catch(ex){$(this).val('');};
            }).end()
            .find('input[data_type="stock"]').change(function(){
                computeStock();    // 库存计算
            }).end()
            .find('input[data_type="price"]').change(function(){
                computePrice();     // 价格计算
            }).end()
            .find('input[type="text"]').change(function(){
                s = $(this).attr('nc_type');
                V[s] = $(this).val();
            });
    }
});
<?php }?>
</script> 
<script src="<?php echo SHOP_RESOURCE_SITE_URL;?>/js/scrolld.js"></script>
<script type="text/javascript">$("[id*='Btn']").stop(true).on('click', function (e) {e.preventDefault();$(this).scrolld();})</script>
