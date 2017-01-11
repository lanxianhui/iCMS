(function($) {
    window.iCMS = {
        config:{
            API: '/public/api.php',
            PUBLIC: '/',
            COOKIE: 'iCMS_',
            AUTH:'USER_AUTH',
            DIALOG:[]
        },
        UI:{
            alert: function(msg,ok,callback) {
                return iCMS.alert(msg,ok,callback);
            },
            dialog: function(options,callback) {
                return iCMS.dialog(options,callback);
            }
        },
        init: function(options) {
            this.config = $.extend(this.config,options);
        },
        api: function(app, _do) {
            return iCMS.config.API + '?app=' + app + (_do || '');
        },
        multiple: function(a) {
            var $this = $(a),
            $parent   = $this.parent(),
            param     = iCMS.param($this),
            _param    = iCMS.param($parent);
            return $.extend(param,_param);
        },
        param: function(a,_param) {
            if(_param){
                a.attr('data-param',iCMS.json2str(_param));
                return;
            }
            var param = a.attr('data-param') || false;
            if (!param) return {};
            return $.parseJSON(param);
        },
        tip: function(el,title,placement) {
            placement = placement||el.attr('data-placement');
            var container = el.attr('data-container');
            if(container){
                $(container).html('');
            }
            el.tooltip('destroy');
            el.tooltip({
              html: true,container:container||false,
              placement: placement||'right',
              trigger: 'manual',
              title:title
            }).tooltip('show');
        },
        alert: function(msg,ok,callback) {
            var opts = ok ? {
                label: 'success',
                icon: 'check'
            } : {
                label: 'warning',
                icon: 'warning'
            }
            opts.id      = 'iPHP-DIALOG-ALERT';
            opts.skin    = 'iCMS_dialog_alert'
            opts.content = msg;
            opts.height  = 150;
            opts.lock    = true;
            opts.time    = 3000;
            window.top.iCMS.dialog(opts,callback);
        },
        dialog: function(options,callback) {
            var defaults = {
                id:'iCMS-DIALOG',
                title:'iCMS - 提示信息',
                width:360,height:150,
                className:'iCMS_UI_DIALOG',
                backdropBackground: '#333',
                backdropOpacity: 0.5,
                fixed: true,
                autofocus: false,
                quickClose: true,
                lock: true,
                time: null,
                label:'success',icon: 'check',api:false,elemBack:'beforeremove'
            },
            timeOutID = null,
            opts = $.extend(defaults,iCMS.config.DIALOG,options);

            if(opts.follow){
                opts.fixed = false;
                opts.lock  = false;
                opts.skin = 'iCMS_tooltip_popup'
                opts.className = 'ui-popup';
                opts.backdropOpacity = 0;
            }
            var content = opts.content;
            //console.log(typeof content);
            if (content instanceof jQuery){
                opts.content = content;
            }else if (typeof content === "string") {
                opts.content = __msg(content);
            }
            opts.onclose = function(){
                __callback('close');
            };
            opts.onbeforeremove = function(){
                __callback('beforeremove');
            };
            opts.onremove = function(){
                __callback('remove');
            };
            var d = window.dialog(opts);

            if(opts.lock){
                d.showModal();
                // $(d.backdrop).addClass("ui-popup-overlay").click(function(){
                //     d.close().remove();
                // })
            }else{
                d.show(opts.follow);
                if(opts.follow){
                    //$(d.backdrop).remove();
                    // $("body").bind("click",function(){
                    //     d.close().remove();
                    // })
                }
                //$(d.backdrop).css("opacity","0");
            }
            if(opts.time){
                timeOutID = window.setTimeout(function(){
                    d.destroy();
                },opts.time);
            }
            d.destroy = function (){
                d.close().remove();
            }

            function __callback(type){
                window.clearTimeout(timeOutID);
                if (typeof(callback) === "function") {
                    callback(type);
                }
            }
            function __msg(content){
                return '<table class=\"ui-dialog-table\" align=\"center\"><tr><td valign=\"middle\">'
                +'<div class=\"iPHP-msg\">'
                +'<span class=\"label label-' + opts.label + '\">'
                +'<i class=\"fa fa-' + opts.icon + '\"></i> '
                + content
                + '</span></div>'
                +'</td></tr></table>';
            }
            return d;
        },
        random: function(len,ischar) {
            len = len || 16;
            var chars = "abcdefhjmnpqrstuvwxyz23456789ABCDEFGHJKLMNPQRSTUVWYXZ";
            if(ischar){
                var chars = "abcdefhjmnpqrstuvwxyz";
            }
            var code = '';
            for (i = 0; i < len; i++) {
                code += chars.charAt(Math.floor(Math.random() * chars.length))
            }
            return code;
        },

        json2str:function(o){
            var arr = [];
            var fmt = function(s) {
                if (typeof s == 'object' && s != null) return iCMS.json2str(s);
                return /^(string|number)$/.test(typeof s) ? '"' + s + '"' : s;
            }
            for (var i in o)
                 arr.push('"' + i + '":'+ fmt(o[i]));
            return '{' + arr.join(',') + '}';
        },
        format:function(content,ubb) {
            content = content.replace(/\/"/g, '"')
                .replace(/\\\&quot;/g, "")
                .replace(/\r/g, "")
                .replace(/on(\w+)="[^"]+"/ig, "")
                .replace(/<script[^>]*?>(.*?)<\/script>/ig, "")
                .replace(/<style[^>]*?>(.*?)<\/style>/ig, "")
                .replace(/style=[" ]?([^"]+)[" ]/ig, "")
                .replace(/<a[^>]+href=[" ]?([^"]+)[" ]?[^>]*>(.*?)<\/a>/ig, "[url=$1]$2[/url]")
                .replace(/<img[^>]+src=[" ]?([^"]+)[" ]?[^>]*>/ig, "[img]$1[/img]")
                .replace(/<embed/g, "\n<embed")
                .replace(/<embed[^>]+class="edui-faked-video"[^"].+src=[" ]?([^"]+)[" ]+width=[" ]?([^"]\d+)[" ]+height=[" ]?([^"]\d+)[" ]?[^>]*>/ig, "[video=$2,$3]$1[/video]")
                .replace(/<embed[^>]+class="edui-faked-music"[^"].+src=[" ]?([^"]+)[" ]+width=[" ]?([^"]\d+)[" ]+height=[" ]?([^"]\d+)[" ]?[^>]*>/ig, "[music=$2,$3]$1[/music]")
                .replace(/<b[^>]*>(.*?)<\/b>/ig, "[b]$1[/b]")
                .replace(/<strong[^>]*>(.*?)<\/strong>/ig, "[b]$1[/b]")
                .replace(/<p[^>]*?>/g, "\n\n")
                .replace(/<br[^>]*?>/g, "\n")
                .replace(/<[^>]*?>/g, "");
            if(ubb){
                content = content.replace(/\n+/g, "[iCMS.N]");
                content = this.n2p(content,ubb);
                return content;
            }
            content = content.replace(/\[url=([^\]]+)\]\n(\[img\]\1\[\/img\])\n\[\/url\]/g, "$2")
                .replace(/\[img\](.*?)\[\/img\]/ig, '<p><img src="$1" /></p>')
                .replace(/\[b\](.*?)\[\/b\]/ig, '<b>$1</b>')
                .replace(/\[url=([^\]|#]+)\](.*?)\[\/url\]/g, '$2')
                .replace(/\[url=([^\]]+)\](.*?)\[\/url\]/g, '<a target="_blank" href="$1">$2</a>')
               .replace(/\n+/g, "[iCMS.N]");

            content = this.n2p(content);
            content = content.replace(/#--iCMS.PageBreak--#/g, "<!---->#--iCMS.PageBreak--#")
                .replace(/<p>\s*<p>/g, '<p>')
                .replace(/<\/p>\s*<\/p>/g, '</p>')
                .replace(/<p>\s*<\/p>/g, '')
                .replace(/\[video=(\d+),(\d+)\](.*?)\[\/video\]/ig, '<embed type="application/x-shockwave-flash" class="edui-faked-video" pluginspage="http://www.macromedia.com/go/getflashplayer" src="$3" width="$1" height="$2" wmode="transparent" play="true" loop="false" menu="false" allowscriptaccess="never" allowfullscreen="true"/>')
                .replace(/\[music=(\d+),(\d+)\](.*?)\[\/music\]/ig, '<embed type="application/x-shockwave-flash" class="edui-faked-music" pluginspage="http://www.macromedia.com/go/getflashplayer" src="$3" width="$1" height="$2" wmode="transparent" play="true" loop="false" menu="false" allowscriptaccess="never" allowfullscreen="true" align="none"/>')
                .replace(/<p><br\/><\/p>/g, '');
            return content;
        },
        n2p:function(cc,ubb) {
            var c = '',s = cc.split("[iCMS.N]");
            for (var i = 0; i < s.length; i++) {
                while (s[i].substr(0, 1) == " " || s[i].substr(0, 1) == "　") {
                    s[i] = s[i].substr(1, s[i].length);
                }
                if (s[i].length > 0){
                    if(ubb){
                        c += s[i] + "\n";
                    }else{
                        c += "<p>" + s[i] + "</p>";
                    }
                }
            }
            return c;
        }
    };
})(jQuery);


function pad(num, n) {
    num = num.toString();
    return Array(n > num.length ? (n - ('' + num).length + 1) : 0).join(0) + num;
}
