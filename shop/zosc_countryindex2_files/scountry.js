jQuery(function () {
    var $ = jQuery;
    
    //slides
    $('.head').slidesjs({
        width: '100%',
        height: 480,
        play: {
            active: false,
            interval: 5000,
            auto: true
        },
        navigation: false
    });
    
    $('.tabarea').each(function () {
        var $this = $(this),
            $tab = $this.find('.tabtitle a'),
            $cont = $this.find('.tabcont .plist');
            
        tab($tab, $cont, 'current', 'click');
        $tab.click(function () {
            $cont.eq($(this).index()).find('img.lazy').trigger('appear');
        });
    });
    
    //回到顶部
    $('.sidebar').css({
        'margin-top': -$('.sidebar').height() / 2
    });
    
    $('.sidebar .toTop').click(function (e) {
        e.preventDefault();
        $('html, body').animate({
            scrollTop: 0
        });
    });
    
    //scroll to change sidebar highlight
    (function () {
        var $pblock = $('.cpart'),
            blockPos = [],
            len = 0,
            index = 0,
            i = 0;
            
        $pblock.each(function () {
            var $this = $(this);
            blockPos.push($this.offset().top - 10);
        });
        
        $(window).scroll(function () {
            var nowTop = $(document).scrollTop(),
                len = blockPos.length;
            
            if (nowTop < blockPos[0] - $(window).height() / 2 + 200) {  //200是调节值，无实际意义
                $('.sidebar').removeClass('on');
            } else {
                $('.sidebar').addClass('on');
            }
            
            if (nowTop >= blockPos[len - 1]) {
                index = len - 1;
            } else {
                for (i = 1; i < len; i++) {
                    if (nowTop <= blockPos[i]) {
                        index = i - 1;
                        break;
                    }
                }
            }
            $('.sidebar a').removeClass('cur').eq(index).addClass('cur');
        });
    })();
    
    $.ajax({
        type: 'get',
        url: ROOT_URL + 'flashsales/product/list/',
        dataType: 'json',
        success: function (data) {
            if (!data.length) {
                return;
            }
            
            var dataPre = data[1],
                data = data[0],
                cur_time = data['current_time'],
                end_time = data['end_time'],
                items = data.items,
                i = 0,
                outHtml = [];
            
            //开始到计时
            $('.now .timer').countdown({
                curstamp: cur_time,
                timestamp: end_time,
                callback: function () {
                    $('.onsale.now').css('display', 'none');
                }
            });
            
            //tmp 是正常模板，tmp2 是缺货模板
            var tmp = 
                '<li class="left">' +
                    '<a href="{url}" target="_blank">' +
                        '<img src="{thumb}" alt="" class="thumb" width="232" height="232">' +
                        '<span class="pname">{pname}</span>' +
                        '<span class="price">￥{sprice}<del>￥{price}</del></span>' +
                        '{activity}' +
                    '</a>' +
                '</li>',
                tmp2 = 
                '<li class="left">' +
                    '<a href="{url}" target="_blank">' +
                        '<img src="{thumb}" alt="" class="thumb" width="232" height="232">' +
                        '<span class="pname">{pname}</span>' +
                        '<span class="price">￥{sprice}<del>￥{price}</del></span>' +
                        '{activity}' +
                    '</a>' +
                    '<span class="mask"></span>' +
                    '<img src="' + ROOT_URL + 'skin/frontend/PlumTree/default/images/scountry/soldout.png" alt="" class="soldout" width="88" height="88">' +
                '</li>';
                
            for (i = 0; i < 4; i++) {
                if (items[i].is_in_stock - 0 === 1) {
                    outHtml.push(
                        tmp.replace('{url}', ROOT_URL + items[i]['url_path'])
                           .replace('{thumb}', items[i].image)
                           .replace('{pname}', items[i].name)
                           .replace('{sprice}', items[i]['activity_price'])
                           .replace('{price}', items[i].price)
                           .replace('{activity}', (items[i]['activity_icon'] - 0 !== 0) ? '<img class="activity" src="http://assets.haituncun.com/media/activityIcon/' + items[i]['activity_icon'] + '">' : '')
                    );
                } else {
                    outHtml.push(
                        tmp2.replace('{url}', ROOT_URL + items[i]['url_path'])
                            .replace('{thumb}', items[i].image)
                            .replace('{pname}', items[i].name)
                            .replace('{sprice}', items[i]['activity_price'])
                            .replace('{price}', items[i].price)
                            .replace('{activity}', (items[i]['activity_icon'] - 0 !== 0) ? '<img class="activity" src="http://assets.haituncun.com/media/activityIcon/' + items[i]['activity_icon'] + '">' : '')
                    );
                }
            }
            
            $('.onsale.now .plist').find('ul').html(outHtml.join(''));
            $('.onsale.now').css('display', 'block');
            
            //如果不存在预告，就不执行这段，并隐藏预告区域
            if (dataPre) {
                tmp =
                    '<li class="left">' +
                        '<a href="{url}" target="_blank">' +
                            '<img src="{thumb}" alt="" class="thumb" width="232" height="232">' +
                            '<span class="pname">{pname}</span>' +
                            '<span class="price">￥{sprice}<del>￥{price}</del></span>' +
                            '<div class="mask"></div>' +
                            '<p class="predict">每日上午10:00<span>准时抢购</span></p>' +
                        '</a>' +
                    '</li>';
                    
                outHtml = [];
                
                for (i = 0; i < 4; i++) {
                    outHtml.push(
                        tmp.replace('{url}', ROOT_URL + dataPre.items[i]['url_path'])
                           .replace('{thumb}', dataPre.items[i].image)
                           .replace('{pname}', dataPre.items[i].name)
                           .replace('{sprice}', dataPre.items[i]['activity_price'])
                           .replace('{price}', dataPre.items[i].price)
                           .replace('{activity}', (dataPre.items[i]['activity_icon'] - 0 !== 0) ? '<img class="activity" src="http://assets.haituncun.com/media/activityIcon/' + items[i]['activity_icon'] + '">' : '')
                    );
                }
                
                $('.onsale.pre .plist').find('ul').html(outHtml.join(''));
                $('.onsale.pre').css('display', 'block');
            }
            
            if ($('.onsale.now').is(':visible') && $('.onsale.pre').is(':hidden')) {
                $('.onsale.now').css('padding-bottom', '0');
            }
        }
    });
    
    (function () {
        var ids = [];
        
        $('.cpart .plist ul').each(function () {
            ids.push($(this).data('categoryid'));
        });
        
        $.ajax({
            type: 'post',
            url: ROOT_URL + 'catalog/product/getstockbycategory/category_id/' + ids.join('-') + '/limit/8/',
            dataType: 'json',
            success: function (data) {
                for (var i = 0; i < data.length; i += 1) {
                    if (data[i]['is_stock'] - 0) continue;
                    $('.cpart .plist li a').eq(i).append('<div class="mask"></div><img src="http://assets.haituncun.com/skin/frontend/PlumTree/default/images/scountry/soldout.png" class="soldout" width="88" height="88">');
                }
            }
        })
    })();
});


//倒计时
(function ($) {
    var hours = 60 * 60,
        min = 60;
    $.fn.countdown = function (prop) {
        var options = $.extend({
            curstamp: 0,
            timestamp: 0,
            callback: function () {}
        }, prop);
        
        var _this = this,
            leftAll, left, d, h, m, s, position;
            
        init();
        
        leftAll = Math.floor(options.timestamp - options.curstamp);
        
        (function tick() {
            leftAll--;
            
            if (leftAll < 0) {
                leftAll = 0;
                options.callback();
            }
            
            left = leftAll;
            
            h = Math.floor(left / hours);
            _this.find('.countHours').text(num(h));
            left -= h * hours;
            
            m = Math.floor(left / min);
            _this.find('.countMinutes').text(num(m));
            left -= m * min;
            
            s = left;
            _this.find('.countSeconds').text(num(s));
            
            setTimeout(tick, 1000);
            
        })();
        
        function init() {
            _this.html('<i></i>距离本场结束：<span class="countHours"></span>时<span class="countMinutes"></span>分<span class="countSeconds"></span>秒');
        }
        
        function num(num) {
            return num.toString().replace(/^(\d)$/,'0$1');
        }        
    };    
})(jQuery);