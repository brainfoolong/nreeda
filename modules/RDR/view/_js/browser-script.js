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
    var exist = document.getElementById("nreeda-frame");
    if(exist){
        exist.parentNode.removeChild(exist);
        return;
    }
    var nreedaUrl = "{url}";
    var linkTags = document.getElementsByTagName("link");
    var feeds = "";
    var site = window.location.host;
    nreedaUrl += "?site="+site;
    var found = false;
    for(var i in linkTags){
        var tag = linkTags[i];
        if(typeof tag.getAttribute != "function") continue;
        var type = tag.getAttribute("type");
        var rel = tag.getAttribute("rel");
        var href = tag.getAttribute("href");
        var title = tag.getAttribute("title") || href;
        if(rel && type && rel.match(/alternate/i) && type.match(/application.*(atom|rss)/i)){
            nreedaUrl += "&feed[]="+encodeURIComponent(href)+";"+encodeURIComponent(title);
            found = true;
        }
    }
    if(!found){
        alert("{nofeed}");
    }else{
        (function(){
            var d=document, g=d.createElement("iframe"), s=d.getElementsByTagName("body")[0];
            g.src=nreedaUrl;
            g.id = "nreeda-frame";
            g.style.position = "fixed";
            g.style.zIndex = "2147483646";
            g.style.border = "5px solid white";
            g.style.top = "10%";
            g.style.left = "10%";
            g.style.width = "80%";
            g.style.height = "80%";
            g.frameborder = "0";
            g.scrolling = "auto";
            s.appendChild(g)
        })();
    }}
)();