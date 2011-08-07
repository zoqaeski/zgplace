var disappeardelay = 500  //menu disappear speed onMouseout (in miliseconds)
var enableanchorlink = 0 //Enable or disable the anchor link when clicked on? (1=e, 0=d)
var hidemenu_onclick = 1 //hide menu when user clicks within menu? (1=yes, 0=no)

var menuIsOpen = false;

var ie5 = document.all
var ns6 = document.getElementById && !document.all

// Split the string into part [0] and part [1]
temp=navigator.appVersion.split('MSIE');
// Parse the string for the "6" in 6.0
ieVer=parseInt(temp[1]);
// Is it greater than 6?
var isIE6=(ieVer >= 6)?1:0;

var adLayers = new Array("divTopmenuWidescreen","divHeaderContainer","eyeDiv");
var superBannerName = "adContainer";

var flashDivs = new Array("logo_flash","ticker_flash","hide_flash");

function createCookie(name, expiry, value, domain) {
   document.cookie = name+"="+value+"; "+expiry+"path=/" +"; domain="+domain;
}

// gets specified cookie, returns true if found
function getCookie(name) {
	var cookies = document.cookie;
	if (cookies != null) {
		var cookieParts = cookies.split("; ");
		var nameValuePair;
		for (var i=0; i < cookieParts.length; i++) {
			nameValuePair = cookieParts[i].split("=");
			if (nameValuePair[0]==name) {
				return true;
			}
		}
	}
	return false;
}

function xhide(obj, e, visible, hidden) {
    if (ie5 || ns6)
        dropmenuobj.style.left = dropmenuobj.style.top = -500
    if (e.type == "click" && obj.visibility == hidden || e.type == "mouseover")
        obj.visibility = visible
    else if (e.type == "click")
        obj.visibility = hidden
}

function iecompattest() {
    return (document.compatMode && document.compatMode != "BackCompat")? document.documentElement : document.body
}

function helpDropdown(obj, e, i, absoluteUrl) {
    dropdownmenuTM(obj, e, 'lowcontent' + i);
}

function stopEvent(e) {
    if (window.event)
        event.cancelBubble = true
    else if (e.stopPropagation)
        e.stopPropagation()
}

function dropdownmenuTM(obj, e, dropmenuID) {

    stopEvent(e);

    if (typeof dropmenuobj != "undefined") //hide previous menu
        dropmenuobj.style.visibility = "hidden"

    clearhidemenu()

    if (ie5 || ns6) {

        if (menuIsOpen) {
            setVisibilityAdLayers("hidden");
        }
        obj.onmouseout = delayhidemenu
        dropmenuobj = document.getElementById(dropmenuID)

        if (hidemenu_onclick) dropmenuobj.onclick = function() {
            dropmenuobj.style.visibility = 'hidden'
        }
        dropmenuobj.onmouseover = clearhidemenu
        dropmenuobj.onmouseout = ie5? function() {
            dynamichide(event)
        } : function(event) {
            dynamichide(event)
        }
        showhideTM(dropmenuobj.style, e, "visible", "hidden")

    }
    return clickreturnvalue()
}

function nodropdown(obj, e) {

    stopEvent(e);

    if (typeof dropmenuobj != "undefined") //hide previous menu
        dropmenuobj.style.visibility = "hidden"

    clearhidemenu()

    if (ie5 || ns6) {
        obj.onmouseout = delayhidemenu
    }

    return clickreturnvalue()
}

function showhideTM(obj, e, visible, hidden) {

    if (e.type == 'mouseover' && !menuIsOpen) {
        return;
    } else {
        menuIsOpen = true;
        setVisibilityAdLayers("hidden");
    }
    if (e.type == "mouseover" && (obj.visibility == hidden || obj.visibility != visible) || e.type == "click") {
        obj.visibility = visible;
    } else if (e.type == "mouseover") {
        obj.visibility = hidden;
    }
}

function clickreturnvalue() {
    if ((ie5 || ns6) && !enableanchorlink) return false
    else return true
}

function contains_ns6(a, b) {
    while (b.parentNode)
        if ((b = b.parentNode) == a)
            return true;
    return false;
}

function dynamichide(e) {
    if (ie5 && !dropmenuobj.contains(e.toElement))
        delayhidemenu()
    else if (ns6 && e.currentTarget != e.relatedTarget && !contains_ns6(e.currentTarget, e.relatedTarget))
        delayhidemenu()
}

function delayhidemenu() {
    delayhide = setTimeout(closeMenu, disappeardelay)
    delayhideAds = setTimeout("setVisibilityAdLayers('visible')", disappeardelay)
}

function closeMenu() {
    menuIsOpen = false;
    dropmenuobj.style.visibility = 'hidden';
}

function clearhidemenu() {
    if (typeof delayhide != "undefined") {
        clearTimeout(delayhide)
    }
    if (typeof delayhideAds != "undefined") {
        clearTimeout(delayhideAds)
    }
}

function setVisibilityAdLayers(v) {

    var x;
    var ad;
    for (x in adLayers) {
        ad = document.getElementById(adLayers[x]);
        if (ad) hideAdsInDiv(ad, v);
    }
    hideFlashDivs(v);
}

function hideAdsInDiv(obj, visibility) {
	var x;
	var ad;
	var classes = new Array();
	classes = topMenuGetElementsByClassName(obj, "div", superBannerName);
	for (x in classes) {
		ad = classes[x];
		if (ad.style) ad.style.visibility = visibility;
	}
}

function hideFlashDivs(visibility) {
	var x;
    var layer;
    for (x in flashDivs) {
		layer = document.getElementById(flashDivs[x]);
       	if (layer) layer.style.visibility = visibility;
    }
}

/*
		Functions to create date for ASP-topmenu
*/
function createTopmenuArray() {
	for (i = 0; i < createTopmenuArray.arguments.length; i++)
		this[i + 1] = createTopmenuArray.arguments[i];
}
function getTopmenuDate() {				
	var months = new createTopmenuArray('januari','februari','mars','april','maj','juni','juli','augusti','september','oktober','november','december');
	var days = new createTopmenuArray('s&ouml;ndag','m&aring;ndag','tisdag','onsdag','torsdag','fredag','l&ouml;rdag');
	var date = new Date();
	var dayname = date.getDay() + 1;
	var day = date.getDate();
	var month = date.getMonth() + 1;
	var yy = date.getYear();
	var year = (yy < 1000) ? yy + 1900 : yy;	
	return days[dayname] + " " + day + " " + months[month] + " " + year;
}

/*
    Written by Jonathan Snook, http://www.snook.ca/jonathan
    Add-ons by Robert Nyman, http://www.robertnyman.com
*/

function topMenuGetElementsByClassName(oElm, strTagName, strClassName){
    var arrElements = (strTagName == "*" && oElm.all)? oElm.all : oElm.getElementsByTagName(strTagName);
    var arrReturnElements = new Array();
    strClassName = strClassName.replace(/\-/g, "\\-");
    var oRegExp = new RegExp("(^|\\s)" + strClassName + "(\\s|$)");
    var oElement;
    for(var i=0; i<arrElements.length; i++){
        oElement = arrElements[i];      
        if(oRegExp.test(oElement.className)){
            arrReturnElements.push(oElement);
        }   
    }
    return (arrReturnElements)
}

/**
* @author Remy Sharp
* @url http://remysharp.com/2007/01/25/jquery-tutorial-text-box-hints/
*/

(function ($) {

$.fn.hint = function (blurClass) {
    if (!blurClass) blurClass = 'blur';
    
    return this.each(function () {
        var $input = $(this),
            title = $input.attr('title'),
            $form = $(this.form),
            $win = $(window);

        function remove() {
            if (this.value === title && $input.hasClass(blurClass)) {
                $input.val('').removeClass(blurClass);
            }
        }

        // only apply logic if the element has the attribute
        if (title) { 
            // on blur, set value to title attr if text is blank
            $input.blur(function () {
                if (this.value === '') {
                    $input.val(title).addClass(blurClass);
                }
            }).focus(remove).blur(); // now change all inputs to title
            
            // clear the pre-defined text when form is submitted
            $form.submit(remove);
            $win.unload(remove); // handles Firefox's autocomplete
        }
    });
};

})(jQuery)
