/**
 * This file is part of Choqled PHP Framework and/or part of a BFLDEV Software Product.
 * This file is licensed under "GNU General Public License" Version 3 (GPL v3).
 * If you find a bug or you want to contribute some code snippets, let me know at http://bfldev.com/nreeda
 * Suggestions and ideas are also always helpful.

 * @author Roland Eigelsreiter (BrainFooLong)
 * @product nReeda - Web-based Open Source RSS/XML/Atom Feed Reader
 * @link http://bfldev.com/nreeda
**/
(function(){
    var nreedaUrl = "{url}";
    var linkTags = document.getElementsByTagName("link");
    var feeds = "";
    var site = window.location.host;
    nreedaUrl += "?site="+site;
    var found = 0;
    for(var i in linkTags){
        var tag = linkTags[i];
        if(typeof tag.getAttribute != "function") continue;
        var type = tag.getAttribute("type");
        var rel = tag.getAttribute("rel");
        var href = tag.getAttribute("href");
        var title = tag.getAttribute("title") || href;
        if(rel && type && rel.match(/alternate/i) && type.match(/application.*(atom|rss)/i)){
            nreedaUrl += "&feed[]="+encodeURIComponent(href)+";"+encodeURIComponent(title);
            found++;
        }
    }
    if(!found){
        alert("{nofeed}");
    }else{
        if(confirm("{forward}".replace(/\%s/ig, found))){
            var w = parseInt(screen.width * 0.8);
            var h = parseInt(screen.height * 0.7);
            var l = parseInt((screen.width / 2) - (w / 2));
            var t = parseInt((screen.height / 2) - (h / 2));
            window.open(nreedaUrl, "_blank", "width="+w+", height="+h+", top="+t+", left="+l+", resizable=yes, scrollbars=yes");
        }
    }
})();