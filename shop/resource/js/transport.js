$(function(){
	//定义售卖区域主体模板、头模板、单行显示模板
	TransTpl = ''; RuleHead = ''; RuleCell = '';	
	TransTpl += "<div class=\"ncsu-trans-type\" data-delivery=\"TRANSTYPE\">\n";
	TransTpl += "<div class=\"entity\">\n";
	TransTpl += "<div class=\"tbl-except\">\n";
	TransTpl += "<\/div>\n";
	TransTpl += "<div class=\"batch\" style=\"display:none\">\n";
	TransTpl += "<label><input class=\"J_BatchCheck\" type=\"checkbox\" aria-label=\"全选\" value=\"\" name=\"\">\n";
	TransTpl += "全选<\/label>\n";
	TransTpl += "&nbsp;<a class=\"J_BatchSet ncbtn-mini\" href=\"JavaScript:void(0);\">批量设置<\/a>\n";
	TransTpl += "<a class=\"J_BatchDel ncbtn-mini\" href=\"JavaScript:void(0);\">批量删除<\/a>\n";
	TransTpl += "<\/div>\n";
	TransTpl += "<div class=\"tbl-attach\">\n";
	TransTpl += "<div class=\"J_SpecialMessage\"><\/div>\n";
	TransTpl += "<a class=\"J_AddRule ncbtn-mini ml5\" href=\"JavaScript:void(0);\">";
	TransTpl += "<i class=\"icon-map-marker\"></i>为指定地区城市设置运费<\/a>\n";
	TransTpl += "<a class=\"J_ToggleBatch ncbtn-mini\" style=\"display:none\" href=\"JavaScript:void(0);\">";
	TransTpl += "<i class=\"icon-map-marker\"></i>批量操作<\/a>\n";
	TransTpl += "<\/div>\n";
	TransTpl += "<\/div>\n";
	TransTpl += "<\/div>\n";

	SpecialMessage = '';
	SpecialMessage += "<span  error_type=\"area\" class=\"msg J_Message\" style=\"display:none\"><i class=\"icon-exclamation-sign\"><\/i>指定地区城市为空或指定错误<\/span>\n";
	SpecialMessage += "<span error_type=\"postage\" class=\"msg J_Message\" style=\"display:none\"><i class=\"icon-exclamation-sign\"><\/i>运费应输入0.00至999.99的数字<\/span>\n";

	//记录当前起始值
	StartNum = $('.tbl-except>table>tbody>tr').size();

	//头模板
	RuleHead += "<table cellspacing=\"0\" class=\"ncsc-table-style\" style=\"width:100%\" cellpadding=\"0\" border=\"0\">\n";
	RuleHead += "<thead>\n";
	RuleHead += "<tr style='border-bottom: 1px solid #ddd;'>\n";
	RuleHead += "<th style='padding-left:5px'>运送到<\/th>\n";
	RuleHead += "<th class=\"w110\">首重运费(元)<\/th>\n";
	RuleHead += "<th class=\"w110\">首重(kg)<\/th>\n";
	RuleHead += "<th class=\"w110\">超重计费(元/kg)<\/th>\n";
	RuleHead += "<th class=\"w110\">操作<\/th>\n";
	RuleHead += "<\/tr>\n";
	RuleHead += "<\/thead>\n";
	RuleHead += "<tbody>\n";
	RuleHead += "<\/tbody>\n";
	RuleHead += "<\/table>\n";

	//单行内容模板
	RuleCell += "<tr class=\"bd-line\" data-group=\"nCurNum\">\n";
	RuleCell += "<td class=\"cell-area\" style='padding-left:5px'>\n";
	RuleCell += "<span class=\"area-group\">\n";
	RuleCell += "<input class=\"J_BatchField\" style=\"width:15px; display:none\" type=\"checkbox\" value=\"\" name=\"TRANSTYPE_nCurNum\">\n";
	RuleCell += "<p style=\"display:inline-block\">未添加地区<\/p><\/span>\n";
	RuleCell += "<input type=\"hidden\" value=\"\" name=\"areas[TRANSTYPE][CurNum]\">\n";
	RuleCell += "<\/td>\n";
	RuleCell += "<td>\n";
	RuleCell += "<input class=\"text w60\" type=\"text\" aria-label=\"首重运费\" maxlength=\"6\" autocomplete=\"off\" value=\"\" data-field=\"postage\" name=\"special[TRANSTYPE][CurNum][postage]\"><em class=\"add-on\">\n";
	RuleCell += "<i class=\"icon-renminbi\"><\/i>\n";
	RuleCell += "<\/em>\n";
	RuleCell += "<\/td>\n";
	RuleCell += "<td>\n";
	RuleCell += "<input class=\"text w60\" type=\"text\" aria-label=\"首重(kg)\" maxlength=\"6\" autocomplete=\"off\" value=\"\" data-field=\"postage_weight\" name=\"special[TRANSTYPE][CurNum][postage_weight]\"><em class=\"add-on\">\n";
	RuleCell += "<i >kg<\/i>\n";
	RuleCell += "<\/em>\n";
	RuleCell += "<\/td>\n";
	RuleCell += "<td>\n";
	RuleCell += "<input class=\"text w60\" type=\"text\" aria-label=\"计量(元/kg)\" maxlength=\"6\" autocomplete=\"off\" value=\"\" data-field=\"postage_money_overweight\" name=\"special[TRANSTYPE][CurNum][postage_money_overweight]\"><em class=\"add-on\">\n";
	RuleCell += "<i >元/kg<\/i>\n";
	RuleCell += "<\/em>\n";
	RuleCell += "<\/td>\n";
	RuleCell += "<td class=\"nscs-table-handle\">\n";
	RuleCell += "<span>\n";
	RuleCell += "<a class=\"btn-grapefruit J_DeleteRule\" ncNum=\"nCurNum\" href=\"JavaScript:void(0);\">\n";
	RuleCell += "<i class=\"icon-trash\"><\/i><p>删除<\/p>\n";
	RuleCell += "<\/a>\n";
	RuleCell += "<\/span>\n";
	RuleCell += "<span>\n";
	RuleCell += "<a class=\"btn-bluejeans\" data-group=\"nCurNum\" title=\"编辑运送区域\" area-haspopup=\"true\" area-controls=\"J_DialogArea\" entype=\'J_EditArea\' data-acc=\"event:enter\" href=\"JavaScript:void(0);\">\n";
	RuleCell += "<i class=\"icon-edit\"><\/i><p>编辑<\/p>\n";
	RuleCell += "<\/a>\n";	
	RuleCell += "<\/span>\n";
	RuleCell += "<\/td>\n";
	RuleCell += "<\/tr>\n";

	//删除当前行的唯一标识
	var curDelNum;
	
/*	为指定地区设置运费,增加一行运费规则*/
	$('.trans-line').on('click','.J_AddRule',function(){
		StartNum +=1;
		if ($(this).parent().parent().find('.tbl-except').html().replace("\n",'') == ''){
			$(this).parent().parent().find('.tbl-except').append(RuleHead);
		}
		cell = RuleCell.replace(/CurNum/g,StartNum); 
		cell = cell.replace(/TRANSTYPE/g,$(this).parent().parent().parent().attr('data-delivery')); 
		$(this).parent().parent().find('.tbl-except').find('table').append(cell);

		//如果没有批量操作a链接，则添加
		if ($(this).parent().parent().find('input[type="checkbox"]').css('display') == 'none'){
			$(this).next().css('display','').html('批量操作');
		}else{
			$(this).next().css('display','').html('取消批量');
			$(this).parent().parent().find('.tbl-except').find('.J_BatchField:last').css('display','');
		}
	});

/*	自定义转成整形的方法*/
	jQuery.fn.toInt = function() {
		var s = parseInt(jQuery(this).val().replace( /^0*/,''));
		return isNaN(s) ? 0 : s;
	};

/*	关闭选择区域层*/
	$('#dialog_areas').on('click','.ks-ext-close',function(){
	    $("#dialog_areas").css('display','none');
	    $("#dialog_batch").css('display','none');
	    $('.ks-ext-mask').css('display','none');
	    return false;
	});

	$('#dialog_batch').on('click','.ks-ext-close',function(){
	    $("#dialog_areas").css('display','none');
	    $("#dialog_batch").css('display','none');
	    $('.ks-ext-mask').css('display','none');
	    return false;
	});

/*	关闭选择区域层*/
	$('#dialog_areas').on('click','.J_Cancel',function(){
	    $("#dialog_areas").css('display','none');
	    $("#dialog_batch").css('display','none');
	    $('.ks-ext-mask').css('display','none');
	});

	$('#dialog_batch').on('click','.J_Cancel',function(){
	    $("#dialog_areas").css('display','none');
	    $("#dialog_batch").css('display','none');
	    $('.ks-ext-mask').css('display','none');
	});

/*	选择完区域后，确定事件*/
	$('#dialog_areas').on('click','.J_Submit',function (){
		var CityText = '', CityText2 = '', CityValue = '';
		//记录已选择的所有省及市的value，SelectArea下标为value值，值为true，如江苏省SelectArea[320000]=true,南京市SelectArea[320100]=true
		//取得已选的省市的text，返回给父级窗口，如果省份下的市被全选择，只返回显示省的名称，否则显示已选择的市的名称
		//首先找市被全部选择的省份
		$('#J_CityList').find('.ncsc-province-tab').each(function(){
			var a = $(this).find('input[type="checkbox"]').size();
			var b = $(this).find('input:checked').size();
			//市被全选的情况
			if (a == b){
				CityText += ($(this).find('.J_Province').next().html())+',';
			}else{
				//市被部分选中的情况
				$(this).find('.J_City').each(function(){
						//计算并准备传输选择的区域值（具体到市级ID），以，隔开
							if ($(this).attr('checked')){
								CityText2 += ($(this).next().html())+',';
							}
				});
			}
		});
		CityText += CityText2;

		//记录弹出层内所有已被选择的checkbox的值(省、市均记录)，记录到CityValue，SelectArea中
		$('#J_CityList').find('.ncsc-province-list').find('input[type="checkbox"]').each(function(){
			if ($(this).attr('checked')){
				CityValue += $(this).val()+',';
			}
		});

		//去掉尾部的逗号
		CityText = CityText.replace(/(,*$)/g,'');
		CityValue = CityValue.replace(/(,*$)/g,'');

		//返回选择的文本内容
		if (CityText == '')CityText = '未添加地区';
		$(objCurlArea).find('.area-group>p').html(CityText);
		//返回选择的值到隐藏域
		$('input[name="areas['+curTransType+']['+curIndex.substring(1)+']"]').val(CityValue+'|||'+CityText);
		//关闭弹出层与遮罩层
	    $("#dialog_areas").css('display','none');
	    $('.ks-ext-mask').css('display','none');
	    //清空check_num显示的数量
		$(".check_num").html('');
		$('#J_CityList').find('input[type="checkbox"]').attr('checked',false);
		//如果该配送方式，地区都不为空，隐藏地区的提示层
		isRemove = true;
		$('div[data-delivery="'+curTransType+'"]').find('input[type="hidden"]').each(function(){
			if ($(this).val()==''){
				isRemove = false;return false;
			}
		});
		if (isRemove == true){
			$('div[data-delivery="'+curTransType+'"]').find('span[error_type="area"]').css('display','none');
		}
	});

/*	省份点击事件*/
	$('#dialog_areas').on('click','.J_Province',function(){
		if ($(this).attr('checked')){
			//选择所有未被disabled的子地区
			$(this).parent().find('.ncsc-citys-sub').eq(0).find('input[type="checkbox"]').each(function(){
				if (!$(this).attr('disabled')){
					$(this).attr('checked',true);
				}else{
					$(this).attr('checked',false);
				}
			});
			//计算并显示所有被选中的子地区数量
			num = '('+$(this).parent().find('.ncsc-citys-sub').eq(0).find('input:checked').size()+')';
			if (num == '(0)') num = '';
			$(this).parent().parent().find(".check_num").eq(0).html(num);

			//如果该大区域所有省都选中，该区域选中
			input_checked 	= $(this).parent().parent().parent().find('input:checked').size();
			input_all 		= $(this).parent().parent().parent().find('input[type="checkbox"]').size();
			if (input_all == input_checked){
				$(this).parent().parent().parent().parent().find('.J_Group').attr('checked',true);
			}	

		}else{
			//取消全部子地区选择，取消显示数量
			$(this).parent().parent().find(".check_num").eq(0).html('');
			$(this).parent().find('.ncsc-citys-sub').eq(0).find('input[type="checkbox"]').attr('checked',false);
			//取消大区域选择
			$(this).parent().parent().parent().parent().find('.J_Group').attr('checked',false);
		}
	});

/*	大区域点击事件（华北、华东、华南...）*/
	$('#dialog_areas').on('click','.J_Group',function(){
		if ($(this).attr('checked')){
			//区域内所有没有被disabled复选框选中，带disabled说明已经被选择过了，不能再选
			$(this).parent().parent().parent().find('input[type="checkbox"]').each(function(){
				if (!$(this).attr('disabled')){
					$(this).attr('checked',true);
				}else{
					$(this).attr('checked',false);
				}				
			});
			//循环显示每个省下面的市级的数量
			$(this).parent().parent().parent().find('.ncsc-province-list').find('.ncsc-province').each(function(){
				//显示该省下面已选择的市的数量
				num = '('+$(this).find('.ncsc-citys-sub').find('input:checked').size()+')';
				//如果是0，说明没有选择，不显示数量
				if (num != '(0)'){
					$(this).find(".check_num").html(num);
				}
			});
		}else{
			//区域内所有筛选框取消选中
			$(this).parent().parent().parent().find('input[type="checkbox"]').attr('checked',false);
			//循环清空每个省下面显示的市级数量
			$(this).parent().parent().parent().find('.ncsc-province-list').find('.ncsc-province').each(function(){
				$(this).find(".check_num").html('');
			});
		}

	});

/*	关闭弹出的市级小层*/
	$('#dialog_areas').on('click','.close_button',function(){ 
	    $(this).parent().parent().parent().parent().removeClass('showCityPop');
	});

/*	市级地区单事件*/
	$('#dialog_areas').on('click','.J_City',function(){
		//显示选择市级数量，在所属省后面
		num = '('+$(this).parent().parent().find('input:checked').size()+')';
		if (num=='(0)')num='';
		$(this).parent().parent().parent().find(".check_num").eq(0).html(num);
		//如果市级地区全部选中，则父级省份也选中，反之有一个不选中,则省份和大区域也不选中
		if (!$(this).attr('checked')){
			//取消省份选择
			$(this).parent().parent().parent().find('.J_Province').attr('checked',false);
			//取消大区域选择
			$(this).parent().parent().parent().parent().parent().parent().find('.J_Group').attr('checked',false);
		}else{
			//如果该省所有市都选中，该省选中
			input_checked 	= $(this).parent().parent().find('input:checked').size();
			input_all 		= $(this).parent().parent().find('input[type="checkbox"]').size();
			if (input_all == input_checked){
				$(this).parent().parent().parent().find('.J_Province').attr('checked',true);
			}
			//如果该大区域所有省都选中，该区域选中
			input_checked 	= $(this).parent().parent().parent().parent().parent().find('input:checked').size();
			input_all 		= $(this).parent().parent().parent().parent().parent().find('input[type="checkbox"]').size();
			if (input_all == input_checked){
				$(this).parent().parent().parent().parent().parent().parent().find('.J_Group').attr('checked',true);
			}
		}
	});
	
/*	省份下拉事件*/
	$('#dialog_areas').on('click','.trigger',function () {
		objTrigger = this;objHead = $(this).parent();objPanel = $(this).next();
		if ($(this).next().css('display') == 'none'){
			//隐藏所有已弹出的省份下拉层，只显示当前点击的层
			$('.ks-contentbox').find('.ncsc-province').removeClass('showCityPop');
			$(this).parent().parent().addClass('showCityPop');
		}else{
			//隐藏当前的省份下拉层
			$(this).parent().parent().removeClass('showCityPop');
		}
		//点击省，市所在的head与panel层以外的区域均隐藏当前层
        var oHandle = $(this);
//        oHandle = document.getElementById($(this).attr('id'));//不兼容Ie8,废弃
		var de = document.documentElement?document.documentElement : document.body;
        de.onclick = function(e){
	        var e = e || window.event;
	        var target = e.target || e.srcElement;
	        var getTar = target.getAttribute("id");
	        while(target){
	        	//循环最外层一个时，会出现异常
				try{
					//jquery 转成DOM对象，比较两个DOM对象
	                if(target==$(objHead)[0])return true;
	                if(target==$(objPanel)[0])return true;
	                //暂不考虑使用ID比较
//	                if(target.getAttribute("id")==$(objHead).attr('id'))return true;
//	                if(target.getAttribute("id")==$(objPanel).attr('id'))return true;
				}catch(ex){};
	            target = target.parentNode;
	        }
	        $(objTrigger).parent().parent().removeClass('showCityPop');
        }
	});

	/*	选择运送区域*/
	$('.trans-line').on('click','a[entype="J_EditArea"]',function () {
		curTransType = 'kd';
        //取消所有已选择的checkbox
		$('#J_CityList').find('input[type="checkbox"]').attr('checked',false).attr('disabled',false);

		//取消显示所有统计数量
		$('#J_CityList').find('.check_num').html('');

		//记录当前行的标识n1,n2,n3....
		curIndex = $(this).attr('data-group');

		//记录当前操作的行，选择完地区会向该区域抛出值
		objCurlArea = $('tr[data-group="'+curIndex+'"]').children(1);
		//记录已选择的所有省及市的value，SelectArea下标为value值，值为true，如江苏省SelectArea[320000]=true,南京市SelectArea[320100]=true
		SelectArea = new Array();

		//取得当前行隐藏域内的city值，放入SelectArea数组中
		var expAreas = $('input[name="areas['+curTransType+']['+curIndex.substring(1)+']"]').val();
		expAreas = expAreas.split('|||');
		expAreas = expAreas[0].split(',');
		try{
			if(expAreas[0] != ''){
				for(var v in expAreas){
					SelectArea[expAreas[v]] = true;
				}
			}
			//初始化已选中的checkbox
			$('#J_CityList').find('.ncsc-province').each(function(){
				var count = 0;
				$(this).find('input[type="checkbox"]').each(function(){
					if(SelectArea[$(this).val()]==true){
						$(this).attr('checked',true);
						if($(this)[0].className!='J_Province') count++;
					}
				});
				if (count > 0){
					$(this).find('.check_num').html('('+count+')');
				}
	
			});

			//循环每一行，如果一行省都选中，则大区载选中
			$('#J_CityList>li').each(function(){
				$(this).find('.J_Group').attr('checked',true);
				father = this;
				$(this).find('.J_Province').each(function(){
					if (!$(this).attr('checked')){
						$(father).find('.J_Group').attr('checked',false);
						return ;
					}
				});
			});
		}catch(ex){}
		//其它行已选择的地区，不能再选择了
		$(objCurlArea).parent().parent().find('.area-group').each(function(){
			if ($(this).next().attr('name') != 'areas['+curTransType+']['+curIndex.substring(1)+']'){
				expAreas = $(this).next().val().split('|||');
				expAreas = expAreas[0].split(',');
				//重置SelectArea
				SelectArea = new Array();
				try{
					if(expAreas[0] != ''){
						for(var v in expAreas){
							SelectArea[expAreas[v]] = true;
						}
					}

					//其它行已选中的在这里都置灰
					$('#J_CityList').find('input[type="checkbox"]').each(function(){
						if(SelectArea[$(this).val()]==true){
							$(this).attr('disabled',true).attr('checked',false);
						}
					});
					//循环每一行，如果一行的省都被disabled，则大区域也disabled
					$('#J_CityList>li').each(function(){
						$(this).find('.J_Group').attr('disabled',true);
						father = this;
						$(this).find('.J_Province').each(function(){
							if (!$(this).attr('disabled')){
								$(father).find('.J_Group').attr('disabled',false);
								return ;
							}
						});
					});				
				}catch(ex){}
			}
		});
		//定位弹出层的坐标
		var pos = $(this).position();
		$("#dialog_areas").css({'position' : 'fixed','display' : 'block', 'z-index' : '9999'});
		$('.ks-ext-mask').css('display','block');
	
	});

	$('#title').blur(function(){
		if ($(this).val() !=''){
			$('p[error_type="title"]').css('display','none');
		}
	});

	/*	首费离开校验*/
	$('.trans-line').on('blur','input[data-field="postage"]',function (){
		var oNum = new Number($(this).val());
		oNum = oNum.toFixed(2);
		if (oNum > 999.99) oNum = 999.99;
		if (oNum=='NaN') oNum = '0.00'; 
		$(this).val(oNum);
		if($(this)[0].className=='w50 mr5 text input-error') $(this).removeClass('input-error');
		if($(this)[0].className=='w50 ml5 mr5 text input-error') $(this).removeClass('input-error');
		
		//如果是动态添加的首费,当所有首费输入框都不为空时，提示层span隐藏
		isRemove = true;
		$(this).parent().parent().parent().find('input[data-field="postage"]').each(function(){
			if ($(this).val()==''){
				isRemove = false;return false;
			}
		});
		//提示层span隐藏
		if (isRemove == true){
			$(this).parent().parent().parent().parent().parent().parent().find('.tbl-attach').find('.J_SpecialMessage').find('span[error_type="postage"]').css('display','none');
		}
	});

	/*	删除一行运费规则*/
	$('.trans-line').on('click','.J_DeleteRule',function (){
	    curDelNum = $(this).attr('ncNum');
		showDialog('确认删除吗?', 'confirm','', function(){
            curTransType = 'kd';
        	obj_parent = $('tr[data-group="'+curDelNum+'"]').parent();
        	$('tr[data-group="'+curDelNum+'"]').remove();
        	if ($(obj_parent).find('tr').html() == null){
        		$(obj_parent).parent().parent().parent().find('.batch').css('display','none');
        		$(obj_parent).parent().parent().parent().find('.J_ToggleBatch').css('display','none');
        		$(obj_parent).parent().parent().parent().find('.batch').next().find('span').css('display','none');
        	}else{
        		//如果该配送方式，地区都不为空，隐藏地区的提示层
        		isRemove = true;
        		$('div[data-delivery="'+curTransType+'"]').find('input[type="hidden"]').each(function(){
        			if ($(this).val()==''){
        				isRemove = false;return false;
        			}
        		});
        		if (isRemove == true){
        			$('div[data-delivery="'+curTransType+'"]').find('span[error_type="area"]').css('display','none');
        		}
        	}  
		});
	});

	/*批量操作*/
	$('.trans-line').on('click','.J_ToggleBatch',function(){
		if ($(this).parent().parent().find('.J_BatchField').eq(0).css('display')=='none'){
			$(this).parent().parent().find('.batch').css('display','');
			$(this).parent().parent().find('.batch').find('.J_BatchCheck').attr('checked',false);
			$(this).parent().parent().find('.J_BatchField').css('display','');
			$(this).html('取消批量');	
		}else{
			$(this).parent().parent().find('.batch').css('display','none');
			$(this).parent().parent().find('.J_BatchField').css('display','none');
			$(this).html('批量操作');	
		}
	});
	
	/*运费规则单行复选框事件*/
	$('.trans-line').on('click','.J_BatchField',function(){
		if (!$(this).attr('checked')){
			obj_parent = $(this).parent().parent().parent().parent().parent().parent().parent();
			$(obj_parent).find('.J_BatchCheck').attr('checked',false);
		}else{
			obj_tbody = $(this).parent().parent().parent().parent();
			checkbox_count = $(obj_tbody).find('.J_BatchField').size();
			checked_count = $(obj_tbody).find('input:checked').size();
			if (checkbox_count == checked_count){
				obj_parent = $(this).parent().parent().parent().parent().parent().parent().parent();
				$(obj_parent).find('.J_BatchCheck').attr('checked',true);			
			}
		}
	});
	
	/*批量设置全选*/
	$('.trans-line').on('click','.J_BatchCheck',function(){
		$('.J_BatchField').attr('checked',$(this).attr('checked') == 'checked' ? 'checked' : false);
	});

	/*批量设置弹出层*/
	$('.trans-line').on('click','.J_BatchSet',function(){
		if($('.tbl-except').find('input:checked').size()==0){
		    showDialog('请选择要批量设置的地区', 'error','','','','','','','','',2);return false;
		}
		//定义当前的父级框，来区分是在EXPRESS,EMS,POST哪种弹出的
		//定位弹出层的坐标
		var pos = $(this).position();
		var pos_x = pos.left-20;
		var pos_y = pos.top+20;
		$("#dialog_batch").css({'position' : 'fixed','display' : 'block', 'z-index' : '9999'});
		$('.ks-ext-mask').css('display','block');	
	});
	
	/*批量删除*/
	$('.trans-line').on('click','.J_BatchDel',function(){
		if($(this).parent().parent().find('.tbl-except').find('input:checked').size()==0){
			showDialog('请选择要批量处理的地区', 'error','','','','','','','','',2);return false;
		}
		showDialog('确认删除吗?', 'confirm','', function(){
    		$('tr[class="bd-line"]').each(function(){
    			if ($(this).find('.J_BatchField').attr('checked')){
    				$(this).remove();
    			}
    		});
    		if ($('.tbl-except').find('table>tbody>tr').length == 0){
    			$('.entity').find('.batch').css('display','none');
    			$('.J_ToggleBatch').css('display','none');
    			$('.entity').find('.batch').next().find('span').css('display','none');		
    		}else{
                curTransType = 'kd';
    			//如果该配送方式，地区都不为空，隐藏地区的提示层
    			isRemove = true;
    			$('div[data-delivery="'+curTransType+'"]').find('input[type="hidden"]').each(function(){
    				if ($(this).val()==''){
    					isRemove = false;return false;
    				}
    			});
    			if (isRemove == true){
    				$('div[data-delivery="'+curTransType+'"]').find('span[error_type="area"]').css('display','none');
    			}
    		}
		});
	});
	
	/*批量设置页面提交事件*/
	$('#dialog_batch').on('click','.J_SubmitPL',function(){
		var obj_this = $(this).parent().parent(); 
		$('.tbl-except>table>tbody>tr').each(function(){
			if ($(this).find('.J_BatchField').attr('checked')){
				$(this).find('input[data-field="start"]').val($(obj_this).find('input[data-field="start"]').val()).removeClass('input-error');
				$(this).find('input[data-field="postage"]').val($(obj_this).find('input[data-field="postage"]').val()).removeClass('input-error');
				$(this).find('input[data-field="plus"]').val($(obj_this).find('input[data-field="plus"]').val()).removeClass('input-error');
				$(this).find('input[data-field="postageplus"]').val($(obj_this).find('input[data-field="postageplus"]').val()).removeClass('input-error');
			}
		});
	    $("#dialog_batch").css('display','none');
	    $('.ks-ext-mask').css('display','none');	
	});
	
	/*保存售卖区域*/
	$('#submit_tpl').on('click',function(){
		$('.J_SpecialMessage').html(SpecialMessage);
		isSubmit = true;	
		//首件跟续件由于有默认值，鼠标离开也有默认值，这里只需判断首费与续费即可
	
		//首费JS空判断-------------------------------
		//只判断已显示的，即只判断EMS、平邮、快递中已选择的内容
		if($('.tbl-except').find('.cell-area').html() != null){
			isShowError = false;
			$('.tbl-except').find('input[data-field="postage"]').each(function(){
				if ($(this).val()==''){
					$(this).addClass('input-error');isShowError = true; isSubmit = false;return false;
				}
			});

			if (isShowError){
				$('.tbl-attach').find('span[error_type="postage"]').show();
			}
		}
		//地区JS空判断-------------------------------
		if($('.tbl-except').find('.cell-area').html() != null){
			isShowError = false;
			$('div[data-delivery="kd"]').find('input[type="hidden"]').each(function(){
				if ($(this).val()==''){
					$(this).addClass('input-error'); isShowError = true; isSubmit = false;return false;
				}
			});

			if (isShowError){
				$('.tbl-attach').find('span[error_type="area"]').show();
			}
		}	
		//售卖区域名称校验
		if ($('#title').val()==''){
			isSubmit = false;
			$('p[error_type="title"]').css('display','');
		}else{
			$('p[error_type="title"]').css('display','none');
		}
		if (isSubmit == true){
			return true;
		}else{
			return false;
		}
	});
});