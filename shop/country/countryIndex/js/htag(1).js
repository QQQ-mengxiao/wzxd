(function(e){var m=function(p,o){return(p<<o)|(p>>>(32-o))};var a=function(s,p){var u,o,r,t,q;r=(s&2147483648);t=(p&2147483648);u=(s&1073741824);o=(p&1073741824);q=(s&1073741823)+(p&1073741823);if(u&o){return(q^2147483648^r^t)}if(u|o){if(q&1073741824){return(q^3221225472^r^t)}else{return(q^1073741824^r^t)}}else{return(q^r^t)}};var n=function(o,q,p){return(o&q)|((~o)&p)};var l=function(o,q,p){return(o&p)|(q&(~p))};var j=function(o,q,p){return(o^q^p)};var i=function(o,q,p){return(q^(o|(~p)))};var g=function(q,p,v,u,o,r,t){q=a(q,a(a(n(p,v,u),o),t));return a(m(q,r),p)};var c=function(q,p,v,u,o,r,t){q=a(q,a(a(l(p,v,u),o),t));return a(m(q,r),p)};var h=function(q,p,v,u,o,r,t){q=a(q,a(a(j(p,v,u),o),t));return a(m(q,r),p)};var d=function(q,p,v,u,o,r,t){q=a(q,a(a(i(p,v,u),o),t));return a(m(q,r),p)};var f=function(r){var v;var q=r.length;var p=q+8;var u=(p-(p%64))/64;var t=(u+1)*16;var w=Array(t-1);var o=0;var s=0;while(s<q){v=(s-(s%4))/4;o=(s%4)*8;w[v]=(w[v]|(r.charCodeAt(s)<<o));s++}v=(s-(s%4))/4;o=(s%4)*8;w[v]=w[v]|(128<<o);w[t-2]=q<<3;w[t-1]=q>>>29;return w};var b=function(r){var q="",o="",s,p;for(p=0;p<=3;p++){s=(r>>>(p*8))&255;o="0"+s.toString(16);q=q+o.substr(o.length-2,2)}return q};var k=function(p){p=p.replace(/\x0d\x0a/g,"\x0a");var o="";for(var r=0;r<p.length;r++){var q=p.charCodeAt(r);if(q<128){o+=String.fromCharCode(q)}else{if((q>127)&&(q<2048)){o+=String.fromCharCode((q>>6)|192);o+=String.fromCharCode((q&63)|128)}else{o+=String.fromCharCode((q>>12)|224);o+=String.fromCharCode(((q>>6)&63)|128);o+=String.fromCharCode((q&63)|128)}}}return o};e.extend({md5_h:function(o){var v=Array();var G,H,p,u,F,Q,P,N,K;var D=7,B=12,z=17,w=22;var O=5,L=9,J=14,I=20;var t=4,s=11,r=16,q=23;var E=6,C=10,A=15,y=21;o=k(o);v=f(o);Q=1732584193;P=4023233417;N=2562383102;K=271733878;for(G=0;G<v.length;G+=16){H=Q;p=P;u=N;F=K;Q=g(Q,P,N,K,v[G+0],D,3614090360);K=g(K,Q,P,N,v[G+1],B,3905402710);N=g(N,K,Q,P,v[G+2],z,606105819);P=g(P,N,K,Q,v[G+3],w,3250441966);Q=g(Q,P,N,K,v[G+4],D,4118548399);K=g(K,Q,P,N,v[G+5],B,1200080426);N=g(N,K,Q,P,v[G+6],z,2821735955);P=g(P,N,K,Q,v[G+7],w,4249261313);Q=g(Q,P,N,K,v[G+8],D,1770035416);K=g(K,Q,P,N,v[G+9],B,2336552879);N=g(N,K,Q,P,v[G+10],z,4294925233);P=g(P,N,K,Q,v[G+11],w,2304563134);Q=g(Q,P,N,K,v[G+12],D,1804603682);K=g(K,Q,P,N,v[G+13],B,4254626195);N=g(N,K,Q,P,v[G+14],z,2792965006);P=g(P,N,K,Q,v[G+15],w,1236535329);Q=c(Q,P,N,K,v[G+1],O,4129170786);K=c(K,Q,P,N,v[G+6],L,3225465664);N=c(N,K,Q,P,v[G+11],J,643717713);P=c(P,N,K,Q,v[G+0],I,3921069994);Q=c(Q,P,N,K,v[G+5],O,3593408605);K=c(K,Q,P,N,v[G+10],L,38016083);N=c(N,K,Q,P,v[G+15],J,3634488961);P=c(P,N,K,Q,v[G+4],I,3889429448);Q=c(Q,P,N,K,v[G+9],O,568446438);K=c(K,Q,P,N,v[G+14],L,3275163606);N=c(N,K,Q,P,v[G+3],J,4107603335);P=c(P,N,K,Q,v[G+8],I,1163531501);Q=c(Q,P,N,K,v[G+13],O,2850285829);K=c(K,Q,P,N,v[G+2],L,4243563512);N=c(N,K,Q,P,v[G+7],J,1735328473);P=c(P,N,K,Q,v[G+12],I,2368359562);Q=h(Q,P,N,K,v[G+5],t,4294588738);K=h(K,Q,P,N,v[G+8],s,2272392833);N=h(N,K,Q,P,v[G+11],r,1839030562);P=h(P,N,K,Q,v[G+14],q,4259657740);Q=h(Q,P,N,K,v[G+1],t,2763975236);K=h(K,Q,P,N,v[G+4],s,1272893353);N=h(N,K,Q,P,v[G+7],r,4139469664);P=h(P,N,K,Q,v[G+10],q,3200236656);Q=h(Q,P,N,K,v[G+13],t,681279174);K=h(K,Q,P,N,v[G+0],s,3936430074);N=h(N,K,Q,P,v[G+3],r,3572445317);P=h(P,N,K,Q,v[G+6],q,76029189);Q=h(Q,P,N,K,v[G+9],t,3654602809);K=h(K,Q,P,N,v[G+12],s,3873151461);N=h(N,K,Q,P,v[G+15],r,530742520);P=h(P,N,K,Q,v[G+2],q,3299628645);Q=d(Q,P,N,K,v[G+0],E,4096336452);K=d(K,Q,P,N,v[G+7],C,1126891415);N=d(N,K,Q,P,v[G+14],A,2878612391);P=d(P,N,K,Q,v[G+5],y,4237533241);Q=d(Q,P,N,K,v[G+12],E,1700485571);K=d(K,Q,P,N,v[G+3],C,2399980690);N=d(N,K,Q,P,v[G+10],A,4293915773);P=d(P,N,K,Q,v[G+1],y,2240044497);Q=d(Q,P,N,K,v[G+8],E,1873313359);K=d(K,Q,P,N,v[G+15],C,4264355552);N=d(N,K,Q,P,v[G+6],A,2734768916);P=d(P,N,K,Q,v[G+13],y,1309151649);Q=d(Q,P,N,K,v[G+4],E,4149444226);K=d(K,Q,P,N,v[G+11],C,3174756917);N=d(N,K,Q,P,v[G+2],A,718787259);P=d(P,N,K,Q,v[G+9],y,3951481745);Q=a(Q,H);P=a(P,p);N=a(N,u);K=a(K,F)}var M=b(Q)+b(P)+b(N)+b(K);return M.toLowerCase()}})})(jQuery);var G_HTAG_Domain_CNSZHTC2015=("https:"==document.location.protocol?"https://htag":"http://htag")+".haituncun.com";var G_HTAG_PageID_CNSZHTC2015="";var G_HTAG_PageEnterTime_CNSZHTC2015=Math.ceil(new Date().getTime()/1000);var G_HTAG_PageURL_CNSZHTC2015=window.location.href;var G_HTAG_UTMCID_CNSZHTC2015="";(function(c){var b="0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz".split("");Math.uuid=function(e,h){var k=b,g=[],f;h=h||k.length;if(e){for(f=0;f<e;f++){g[f]=k[0|Math.random()*h]}}else{var j;g[8]=g[13]=g[18]=g[23]="-";g[14]="4";for(f=0;f<36;f++){if(!g[f]){j=0|Math.random()*16;g[f]=k[(f==19)?(j&3)|8:j]}}}return g.join("")};Math.uuidFast=function(){var j=b,g=new Array(36),f=0,h;for(var e=0;e<36;e++){if(e==8||e==13||e==18||e==23){g[e]="-"}else{if(e==14){g[e]="4"}else{if(f<=2){f=33554432+(Math.random()*16777216)|0}h=f&15;f=f>>4;g[e]=j[(e==19)?(h&3)|8:h]}}}return g.join("")};Math.uuidCompact=function(){return"xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx".replace(/[xy]/g,function(g){var f=Math.random()*16|0,e=g=="x"?f:(f&3|8);
return e.toString(16)})};Date.prototype.Formath=function(e){var g=this.getHours();var h=e;var i={"M+":this.getMonth()+1,"d+":this.getDate(),"h+":g%12==0?12:g%12,"H+":this.getHours(),"m+":this.getMinutes(),"s+":this.getSeconds(),"q+":Math.floor((this.getMonth()+3)/3),"S":this.getMilliseconds()};if(/(y+)/.test(e)){e=e.replace(RegExp.$1,(this.getFullYear()+"").substr(4-RegExp.$1.length))}for(var f in i){if(new RegExp("("+f+")").test(e)){e=e.replace(RegExp.$1,(RegExp.$1.length==1)?(i[f]):(("00"+i[f]).substr((""+i[f]).length)))}}if(g>12&&h.indexOf("h")>0){return e+" PM"}return e};c.extend({getUrlVars:function(){var h=[],g;var e=window.location.href.slice(window.location.href.indexOf("?")+1).split("&");for(var f=0;f<e.length;f++){g=e[f].split("=");h.push(g[0]);h[g[0]]=g[1]}return h},getUrlVar:function(e){return c.getUrlVars()[e]},getDomain:function(h){if(!h){return""}if(h.indexOf("://")!=-1){h=h.substr(h.indexOf("://")+3)}if(h.indexOf("/")!=-1){h=h.substr(0,h.indexOf("/"))}h=h.toLowerCase();return h;var f=["com","net","org","gov","edu","mil","biz","name","info","mobi","pro","travel","museum","int","areo","post","rec"];var e=h.split(".");if(e.length<=1){return h}if(!isNaN(e[e.length-1])){return h}var g=0;while(g<f.length&&f[g]!=e[e.length-1]){g++}if(g!=f.length){return e[e.length-2]+"."+e[e.length-1]}else{g=0;while(g<f.length&&f[g]!=e[e.length-2]){g++}if(g==f.length){return e[e.length-2]+"."+e[e.length-1]}else{return e[e.length-3]+"."+e[e.length-2]+"."+e[e.length-1]}}},isNullVar:function(e){return !e&&e!==0&&typeof e!=="boolean"?true:false},htagLogOrder:function(g){try{var f="";if(typeof g=="string"){f=g}else{if(typeof g==="object"){f=JSON.stringify(g)}}a.log(G_HTAG_PageID_CNSZHTC2015,"order",a.getAllInfo({"order":f}),G_HTAG_Domain_CNSZHTC2015+"/ha_order.gif")}catch(h){}}});if(typeof d=="undefined"){var d=new Object()}d.getPlayerVersion=function(){var j=new d.PlayerVersion([0,0,0]);if(navigator.plugins&&navigator.mimeTypes.length){var f=navigator.plugins["Shockwave Flash"];if(f&&f.description){j=new d.PlayerVersion(f.description.replace(/([a-zA-Z]|\s)+/,"").replace(/(\s+r|\s+b[0-9]+)/,".").split("."))}}else{if(navigator.userAgent&&navigator.userAgent.indexOf("Windows CE")>=0){var g=1;var h=3;while(g){try{h++;g=new ActiveXObject("ShockwaveFlash.ShockwaveFlash."+h);j=new d.PlayerVersion([h,0,0])}catch(i){g=null}}}else{try{var g=new ActiveXObject("ShockwaveFlash.ShockwaveFlash.7")}catch(i){try{var g=new ActiveXObject("ShockwaveFlash.ShockwaveFlash.6");j=new d.PlayerVersion([6,0,21]);g.AllowScriptAccess="always"}catch(i){if(j.major==6){return j}}try{g=new ActiveXObject("ShockwaveFlash.ShockwaveFlash")}catch(i){}}if(g!=null){j=new d.PlayerVersion(g.GetVariable("$version").split(" ")[1].split(","))}}}return j};d.PlayerVersion=function(e){this.major=e[0]!=null?parseInt(e[0]):0;this.minor=e[1]!=null?parseInt(e[1]):0;this.rev=e[2]!=null?parseInt(e[2]):0};var a={version:"1.0.0",hasInit:0,arrImg:[],maxLength:2,taskQueue:[],init:function(){var k=this;if(k.hasInit||c("body").size()==0){return}if(navigator.userAgent.match(/iPhone|iPad|iPod/i)){c("body").css("cursor","pointer")}c("body").bind("mousedown",function(r){var n=c(r.target);var s="";while(n.length>0){if(n[0]==c("body")[0]){try{var q=n.attr("data-tag");if(q){if(s==""){s=q}else{s=q+"."+s}}}catch(p){}break}try{var v=n.attr("data-tag");if(v){if(s==""){s=v}else{s=v+"."+s}}}catch(p){}n=n.parent()}var u=c(r.target);while(u.length>0){if(u[0]==c("body")[0]){break}try{var m=u.attr("href")||u.attr("url");var t=u.attr("oldhref")||u.attr("oldurl");if(m){var w=m.toLowerCase().replace(/(^\s*)/g,"");if(!t&&s!=""&&w.indexOf("#")<0&&w.indexOf("javascript:")<0){if(m.indexOf("?")>0){if(u.attr("href")){u.attr("href",m+"&itag="+s)}else{u.attr("url",m+"&itag="+s)}}else{if(u.attr("href")){u.attr("href",m+"?itag="+s)}else{u.attr("url",m+"?itag="+s)}}if(u.attr("href")){u.attr("oldhref",m)}else{u.attr("oldurl",m)}}else{}if(G_HTAG_PageID_CNSZHTC2015&&G_HTAG_PageID_CNSZHTC2015!=""){k.log(G_HTAG_PageID_CNSZHTC2015,"close",null,G_HTAG_Domain_CNSZHTC2015+"/ha0.gif");G_HTAG_PageID_CNSZHTC2015=""}break}}catch(p){}u=u.parent()}});k.hasInit=1;var f=c.cookie("_uuid")||"";if(f==null||f=="undefined"||f==""){f=Math.uuidFast();c.cookie("_uuid",f,{expires:20000,path:"/",domain:c.getDomain(document.domain)})}var g=c.getUrlVar("itag");if(g&&g!=""){c.cookie("_itag",g,{path:"/",expires:30,domain:c.getDomain(document.domain)})}var j=c.getUrlVar("wtag");if(j&&j!=""){var i=document.referrer||"";if(i&&i!=""){if(c.getDomain(document.domain)!=c.getDomain(i)){c.cookie("_wtag",j,{path:"/",expires:30,domain:c.getDomain(document.domain)})}}}var l=c.md5_h(c.cookie("_uuid")+window.location.href+new Date().getTime());G_HTAG_PageID_CNSZHTC2015=l;G_HTAG_UTMCID_CNSZHTC2015="_utmc_"+c.md5_h((c.cookie("_uuid")||"")+G_HTAG_PageURL_CNSZHTC2015);if(c.cookie(G_HTAG_UTMCID_CNSZHTC2015)){}else{var h=c.cookie(G_HTAG_UTMCID_CNSZHTC2015,true,{expires:1/24/60/30,path:"/",domain:c.getDomain(document.domain)});var e=c.cookie("_htag_order");e=e=="null"?"":(c.isNullVar(e)?"":e);
if(e){k.log(G_HTAG_PageID_CNSZHTC2015,"order",k.getAllInfo(),G_HTAG_Domain_CNSZHTC2015+"/ha_order.gif")}else{k.log(G_HTAG_PageID_CNSZHTC2015,"open",k.getAllInfo())}}c(window).bind("beforeunload",function(m){if(c.cookie(G_HTAG_UTMCID_CNSZHTC2015)){}else{if(G_HTAG_PageID_CNSZHTC2015&&G_HTAG_PageID_CNSZHTC2015!=""){k.log(G_HTAG_PageID_CNSZHTC2015,"close",null,G_HTAG_Domain_CNSZHTC2015+"/ha0.gif");setTimeout(function(){},2000)}}});c(window).bind("unload",function(m){if(c.cookie(G_HTAG_UTMCID_CNSZHTC2015)){}else{if(G_HTAG_PageID_CNSZHTC2015&&G_HTAG_PageID_CNSZHTC2015!=""){setTimeout(function(){},2000)}}})},log:function(j,k,e,g){var l=this,f,n,h=[],m=(g||(G_HTAG_Domain_CNSZHTC2015+"/ha.gif"));var i="";if(!e||e==null){e=l.getAllInfo()}if(e!=null){for(f in e){if(e.hasOwnProperty(f)){n=e[f];i="&"+f+"="+encodeURIComponent(n)+i}}}l.send(m+"?t="+(new Date().getTime())+"&pos="+escape(encodeURIComponent(j))+"&opr="+escape(encodeURIComponent(k))+i)},send:function(j){if(typeof(j)=="undefined"||j==""){return}var l=this,f,m,h,e=0,g=-1;h=l.arrImg;e=h.length;for(var k=0;k<e;k++){if(h[k].f==0){g=k;break}}if(g==-1){if(e==l.maxLength){l.taskQueue.push(j);return}f=c(new Image());h.push({f:1,img:f});g=(e==0?0:e)}else{f=h[g].img}h[g].f=1;f.data("vid",g);m=function(){var i=c(this).data("vid");if(i>=0){h[i].f=0}if(l.taskQueue.length>0){l.send(l.taskQueue.shift())}};f.unbind().load(m).error(m);c(f).attr("src",j)},getAllInfo:function(h){var l=this;var f={};if(c.cookie("_uuid")){f.uuid=c.cookie("_uuid")}else{var m=Math.uuidFast();c.cookie("_uuid",m,{expires:20000,path:"/",domain:c.getDomain(document.domain)});f.uuid=m}if(document){f.ref=document.referrer||""}if(window&&window.screen){f.scwh=(window.screen.width||0)+"_"+(window.screen.height||0)}f.flsh=l.getFlashVersion();f.java=l.getJavaEnabled();f.lang=l.getLang();f.bros=l.getBrowserInfo();f.js_v=l.version;f.time=new Date().Formath("yyyy-MM-dd HH:mm:ss");if(G_haq){for(var g in G_haq){switch(G_haq[g][0]){case"_setAccount":f.acot=G_haq[g][1];break;default:break}}}f.uptm=5;if(G_HTAG_PageEnterTime_CNSZHTC2015){f.uptm=Math.ceil(new Date().getTime()/1000)-parseInt(G_HTAG_PageEnterTime_CNSZHTC2015)}var j=c.cookie("_itag");j=j=="null"?"":j;f.itag=c.isNullVar(j)?"":j;var o=c.cookie("_wtag");o=o=="null"?"":o;f.wtag=c.isNullVar(o)?"":o;f.url=G_HTAG_PageURL_CNSZHTC2015;var n=c.cookie("_htag_order");n=n=="null"?"":(c.isNullVar(n)?"":n);if(n){f.order=n;c.cookie("_htag_order",null,{path:"/",domain:c.getDomain(document.domain)})}if(!h||h==null){}else{if(typeof h==="object"){for(var e in h){f[e]=h[e]}}}return f},getBrowserInfo:function(){var f=this;var e="other";var g=navigator.userAgent.toLowerCase();if(c.browser.msie){e="IE"}else{if(/ucweb/.test(g)){e="uc"}else{if(/bidubrowser/.test(g)){e="baidu"}else{if(/metasr/.test(g)){e="sougou"}else{if(/lbbrowser/.test(g)){e="lb"}else{if(/qqbrowser/.test(g)){e="qq"}else{if(/maxthon/.test(g)){e="maxthon"}else{if(/360se/.test(g)){e="360se"}else{if(/360ee/.test(g)){e="360ee"}else{if(c.browser.chrome){e="chrome"}else{if(c.browser.safari){e="safari"}else{if(c.browser.webkit){e="webkit"}else{if(c.browser.mozilla){e="mozilla"}else{if(c.browser.opera){e="opera"}}}}}}}}}}}}}}return e+c.browser.version},getLang:function(){return(navigator.language?navigator.language:navigator.userLanguage||"")},getFlashVersion:function(){var e=d.getPlayerVersion();return e.major+"."+e.minor+"."+e.rev},getJavaEnabled:function(){return(navigator.javaEnabled()?1:0)}};a.init()})(jQuery);