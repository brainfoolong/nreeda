/**
 * This file is part of Choqled PHP Framework and/or part of a BFLDEV Software Product.
 * This file is licensed under "GNU General Public License" Version 3 (GPL v3).
 * If you find a bug or you want to contribute some code snippets, let me know at http://bfldev.com/nreeda
 * Suggestions and ideas are also always helpful.

 * @author Roland Eigelsreiter (BrainFooLong)
 * @product nReeda - Web-based Open Source RSS/XML/Atom Feed Reader
 * @link http://bfldev.com/nreeda
**/
var scrollDelay = null;

$(document).on("keydown", function(ev){
    // reload feeds
    if(ev.keyCode == 82 && $(ev.target).is("body")){
        Feeds.initPage();
    }
    // ESC
    if(ev.keyCode == 27){
        Global.hideAllLayers();
    }
}).on("domchanged", function(){
    var f = function(string){
        string = parseInt(string);
        return string <= 9 ? "0"+string : string;
    };
    $("time").filter("[datetime]").each(function(){
        var d = new Date(parseInt($(this).attr("datetime"))*1000);
        $(this).removeAttr("datetime");
        $(this).text(f(d.getDate())+"."+f(d.getMonth()+1)+"."+d.getFullYear()+" "+f(d.getHours())+":"+f(d.getMinutes()));
    });
    $(window).trigger("update");
    if($("h1").length){
        $("title").text($("h1").first().text());
    }
});

$(window).on("resize update scroll", function(){
    if(!$("body").hasClass("mobile")){
        var sh = $(".sidebar").outerHeight() + 15;
        var wh = $(window).height();
        if(sh > wh){
            var h = sh - wh;
            var t = $(window).scrollTop();
            if(h < t) t = h;
            $(".sidebar").stop().animate({marginTop : -t});
        }else{
            $(".sidebar").stop().animate({marginTop : 0});
        }
    }
    $(".container").css("min-height", Math.max($(window).height(), $(".sidebar").outerHeight(), $(".content").outerHeight())+"px");
    $(".container.c").width($(window).width() - $(".container.a").outerWidth() - $(".container.b").outerWidth());
    clearTimeout(scrollDelay);
    scrollDelay = setTimeout(function(){
        $(window).trigger("scroll-delayed");
    }, 500);
    $(".parent-width").each(function(){
        var sub = 0;
        if($(this).attr("data-sub")) sub = parseInt($(this).attr("data-sub"));
        $(this).innerWidth($(this).parent().innerWidth() - sub);
    });
});

$(window).on("scroll-delayed", function(){
    var h = $(window).height();
    var scroll = $(window).scrollTop();

    var entries = [];
    var tmp = $(".entry-readed");
    var size = tmp.length;
    tmp.each(function(index){
        var t = $(this).offset().top;
        if(scroll + $("#feeds").offset().top + 50 > t){
            var e = $(this).parent();
            e.addClass("readed");
            entries.push(e.attr("data-id"));
            $(this).remove();
        }
    });
    if(entries.length){
        API.req("set-entries-readed", {ids : entries}, Global.updateNewsCache);
    }
});

$(window).on("scroll", function(){
    var h = $(window).height();
    var scroll = $(window).scrollTop();
    $("#feed-view-trigger").each(function(){
        var t = $(this).offset().top;
        if(t <= (scroll + h)){
            $(this).remove();
            Feeds.loadNextEntries();
        }
    });
    $(".feed-start").each(function(){
        var t = $(this).offset().top;
        if(scroll + h > t){
            var e = $(this).parent();
            if(e.find(".has-image").length) e.addClass("has-image");
            $(this).remove();
        }
    });
});

/**
* The API
*
* @type Object
*/
var API = {
    /**
    * Do a request
    *
    * @param action
    * @param data
    * @param callback
    */
    req : function(action, data, callback){
        $.post(Global.vars.apiUrl, {"action" : action, "data" : data}, callback).fail(function(xhr, textStatus){
            if (xhr.status == 500) {
                Global.message("Error: "+textStatus);
            }
        });
    }
};

/**
* General feeds class
*
* @type Object
*/
var Feeds = {
    feedPage : false,
    requestCounter : 0,

    /**
    * Initialise the feed page
    */
    initPage : function(){
        if(!Feeds.feedPage) return;
        Global.hideAllLayers();
        Feeds.requestCounter = 0;
        $("#feeds").html('');
        window.scrollTo(0,0);
        Feeds.loadNextEntries();
        $("#all-read").off().on("click", function(ev){
            Feeds.allRead();
            ev.stopPropagation();
            ev.preventDefault();
        })
    },

    /**
    * Mark all as read
    */
    allRead : function(){
        $("#feeds").html('');
        Feeds.showHideLoading("show");
        API.req("mark-all-as-readed", null, function(){
            Feeds.initPage();
        });
    },

    /**
    * Show hide loading indicator
    *
    * @param action
    */
    showHideLoading : function(action){
        $("#feeds-loading")[action]();
    },

    /**
    * Load next bunch of entries
    */
    loadNextEntries : function(){
        Feeds.showHideLoading("show");
        $.post(window.location.href, {"requestCounter" : Feeds.requestCounter++}, function(data){
            data = $.parseJSON(data);
            Global.updateUserAjaxData(data);
            $("#feeds").append(data.content);
            $("#c-news").text(data.count);
            Feeds.showHideLoading("hide");
            if($(".entry").not(".readed").length){
                $("#all-read-btn").on("click", function(){
                    Feeds.allRead();
                }).parent().show();
            }
            $(document).trigger("domchanged");
        });
    },

    /**
    * Initialise a single feed
    *
    * @param data
    */
    feedInit : function(data){
        var e = $("#entry-"+data.id);
        e.on("click", function(){
            API.req("set-entries-readed", {ids : [data.id]}, Global.updateNewsCache);
            e.addClass("readed");
            window.open(data.link);
        });
        e.find(".small a").on("click", function(ev){
            ev.stopPropagation();
        });
        e.find("a.readed").on("click", function(ev){
            API.req("set-entries-readed", {ids : [data.id]}, Global.updateNewsCache);
            e.addClass("readed");
            $(this).closest("span").remove();
            ev.preventDefault();
        });
        e.find("a.saved").on("click", function(ev){
            API.req("set-entries-saved", {ids : [data.id]}, Global.updateNewsCache);
            $(this).closest("span").remove();
            ev.preventDefault();
        });
        e.find("a.adminview").on("click", function(ev){
            var tmp = e.find("div.adminview");
            tmp.off("click").on("click", function(ev){
                ev.stopPropagation();
            });
            tmp.show().html('<div class="loading"></div>');
            $.post(Global.vars.ajaxUrl, {action : "admin-feed", fid : $(this).closest(".entry").attr("data-feed"), eid : $(this).closest(".entry").attr("data-id")}, function(data){
                tmp.show().html(data);
                var to = null;
                tmp.find("textarea[data-field]").on("keyup blur", function(){
                    clearTimeout(to);
                    var self = $(this);
                    to = setTimeout(function(){
                        var html = Feeds.executeJS(t, self.val());
                        tmp.find(".formated").text(html.text());
                        API.req("set-feed-property", {field : self.attr("data-field"), feed : tmp.closest(".entry").attr("data-feed"), value : self.val()});
                    }, 500);
                })
                e.find(".text, .adminview .formated").text(t.text());
            });
            ev.preventDefault();
        });
        // prevent images from being loaded because jquery parse it
        // remove other external files/includes completely
        var text = data.text;
        text = text.replace(/\<img([^>])+src=/ig, "<img$1data-src=");
        text = text.replace(/<(embed|script|link|object).*?\/>/ig, "");
        text = text.replace(/<(embed|script|link|object).*?<\/(embed|script|link|object)>/ig, "");
        var t = $($.parseHTML('<div>'+text+'</div>'));
        t.find("br").replaceWith("<br/> ");
        if(e.find(".image").length){
            if(data.image){
                e.find(".image").addClass("has-image").css("background-image", "url("+Global.vars["proxyUrl"]+"?type=image&url="+encodeURIComponent(data.image)+")");
            }else{
                t.find("img").each(function(){
                    $(this).on("load", function(){
                        if($(this).width() > 1 && $(this).width() > 1){
                            e.find(".image").addClass("has-image").css("background-image", "url("+$(this).attr("src")+")");
                        }
                    });
                    $(this).attr("src", Global.vars["proxyUrl"]+"?type=image&url="+encodeURIComponent($(this).attr("data-src")));
                });
            }
        }
        if(data.contentJS){
            t = Feeds.executeJS(t, data.contentJS);
        }
        e.find(".text").text(t.text());
    },

    /**
    * Execute JS on a html object
    *
    * @param html
    * @param contentJS
    * @returns New HTML Object
    */
    executeJS : function(html, contentJS){
        try{
            html = '<div>'+html.html()+'</div>';
            eval(contentJS);
        }catch(e){console.error(e)};
        return typeof html == "object" ? html : $($.parseHTML(html));
    }
};

/**
* General sidebar class
*
* @type Object
*/
var Sidebar = {

    w : 0,

    /**
    * Init the sidebar
    */
    init : function(){
        $(document).on("click", function(ev){
            if(!$(ev.target).closest("#sidebar-icons").length){
                $("#sidebar-icons .icon-box.toggle").removeClass("opened");
                $("#sidebar-icons").removeClass("opened");
            }
        });
        $("#sidebar-icons .toggle .icon").on("click", function(ev){
            var open = $(this).parent().hasClass("opened");
            $("#sidebar-icons .opened").removeClass("opened");
            $("#sidebar-icons").removeClass("opened");
            if(!open){
                $("#sidebar-icons").addClass("opened");
                $(this).parent().toggleClass("opened");
                $(this).parent().find(".focus").trigger("focus");
            }
        });
        $("#sidebar-icons .add-feed").on("click", function(){
            var url = $(this).prevAll("input").val();
            var cat = $(this).prevAll("select").val();
            if(!url.match(/\:\/\/.*\/.*/i)){
                Global.message("Not a correct URL");
                return;
            }
            Global.message('Adding '+url+'...', true);
            API.req("add-feed", {"url" : url, "category" : cat}, function(){
                window.location.href = window.location.href.replace(/\#.*/ig, "");
            });
        });
        $("#sidebar div.main").on("click", function(){
            $(this).next().toggle();
        });
        $("#sidebar div.main a").on("click", function(e){
            e.stopPropagation();
        });
        $("#sidebar .sub-container a").each(function(){
            var url = window.location.href;
            if($(this).attr("data-noparams")) url = url.replace(/\?.*/g, "");
            if(this.href == url){
                $(this).addClass("active").closest(".sub-container").prev().trigger("click");
                return false;
            }
        });
        $("#settings-toggle").find("select[data-setting]").on("change", function(){
            API.req("update-setting-user", {"key" : $(this).attr("data-setting"), "value" : this.value}, function(){
                Global.message("Setting changed");
                Feeds.initPage();
            });
        });

        $("#sidebar .container").on("mousewheel DOMMouseScroll", function(ev){
            var e = window.event || ev.originalEvent;
            var delta = Math.max(-1, Math.min(1, (e.wheelDelta || -e.detail)));
            var step = 40;
            var t = $(this).data("t") + (step * delta);
            if(t < $(this).data("mint")) t = $(this).data("mint");
            if(t > 0) t = 0;
            $(this).css("top", t+"px");
            $(this).data("t", t);
            if(t != 0 && t != $(this).data("mint")){
                ev.stopPropagation();
                ev.preventDefault();
            }
        }).on("update-scrollbars", function(){
            if(!$(this).data("t")) $(this).data("t", 0);
            $(this).data("mint", $(this).parent().height() - $(this).height());
        });
    }
};

/**
* Global class
*
* @type Object
*/
var Global = {

    vars : {},
    updateNewsIV : null,

    /**
    * On init page
    */
    init : function(userData){
        if(userData){
            Global.updateUserAjaxData(userData);
        }
        Sidebar.init();

        if($("#sidebar").length) {
            Global.updateNewsIV = setInterval(Global.updateNewsCache, 20000);
        }

        $(window).trigger("update");
        $(document).trigger("domchanged");
        Global.message(Global.vars.message);
    },

    /**
    * Update the news cache
    */
    updateNewsCache : function(){
        API.req("update-newscache", null, function(data){
            data = $.parseJSON(data);
            Global.updateUserAjaxData(data);
        });
    },

    /**
    * Update all things that can be updated with users ajax data
    *
    * @param data
    */
    updateUserAjaxData : function(data){
        if(typeof data == "string") data = $.parseJSON(data);
        $("#sidebar .counter").text(0);
        for(var i in data){
            $("#sidebar .counter[data-id='"+i+"']").text(data[i]);
        }
    },

    /**
    * Global message
    *
    * @param message
    * @param stay
    */
    message : function(message, stay){
        if(!message) {
            $("#top-message").stop(true).hide();
            return;
        }
        $("#top-message").stop(true).fadeOut(0).show().fadeIn(200).html(message);
        if(!stay){
            $("#top-message").fadeOut(10000, function(){
                $(this).hide();
            });
        }
    },

    /**
    * Hide all overlapping layers
    */
    hideAllLayers : function(){
        $("#sidebar-icons .opened").removeClass("opened");
        $("div.adminview").hide();
        Global.message();
    }
};